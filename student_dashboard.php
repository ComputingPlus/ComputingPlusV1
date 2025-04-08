<?php
// student_dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('Header_student.php');
include('Config.php');

// Set timezone to UK (British) time
date_default_timezone_set('Europe/London');

$student_id = $_SESSION['student_id'];

// Retrieve the student's class from class_students table.
// (Assumes student_ids is stored as a comma-separated list in class_students)
$result = $conn->query("SELECT cs.class_id FROM class_students cs JOIN students s ON FIND_IN_SET(s.id, cs.student_ids) WHERE s.id = $student_id LIMIT 1");
$class = $result->fetch_assoc();

// Check if the student has an active assessment set for the class.
// This must be done before any output.
if ($class) {
    $class_id = $class['class_id'];
    
    // Check if there is a new assessment assigned to this class.
    $assessmentQuery = "SELECT * FROM class_assessments WHERE class_id = $class_id ORDER BY created_at DESC LIMIT 1";
    $assessmentResult = $conn->query($assessmentQuery);
    
    if ($assessmentResult && $assessmentResult->num_rows > 0) {
        $assessment = $assessmentResult->fetch_assoc();
        // Use the assessment's creation time as a threshold.
        $assessmentTime = $assessment['created_at'];
        
        // Check if the student has completed the assessment using the marker record in assessment_answers.
        // We assume that when the assessment is completed a dummy row with question_number 0 is inserted.
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM assessment_answers WHERE student_id = ? AND submitted_at >= ?");
        $stmt->bind_param("is", $student_id, $assessmentTime);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // If no record exists, redirect the student to the assessment page.
        if ($res['count'] == 0) {
            header("Location: Student_Assessment.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <style>
        /* You can include your custom styling here or in a separate CSS file */
        .content { margin: 20px; }
        .hw-card { border: 1px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .hw-buttons button { margin-right: 5px; }
    </style>
</head>
<body>
<div class="content">
    <h2>Student Dashboard</h2>
    <?php if ($class) { 
        // Retrieve class details.
        $class_id = $class['class_id'];
        $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $classResult = $stmt->get_result();
        $classDetails = $classResult->fetch_assoc();
        $stmt->close();
        
        // Retrieve teacher name from the class. Assumes teachers field contains comma-separated staff IDs.
        $teacher_name = "Your teacher";
        if (!empty($classDetails['teachers'])) {
            $teacher_ids = explode(",", $classDetails['teachers']);
            if (count($teacher_ids) > 0) {
                $first_teacher_id = intval($teacher_ids[0]);
                $stmt = $conn->prepare("SELECT full_name FROM staff WHERE id = ?");
                $stmt->bind_param("i", $first_teacher_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $teacher_name = $row['full_name'];
                }
                $stmt->close();
            }
        }
        
        // Retrieve homework assignments for this class.
        $homeworkResult = $conn->query("SELECT * FROM homework WHERE class_id = $class_id");
        
        // Arrays to hold categorized assignments.
        $recentHW = [];
        $completeHW = [];
        $overdueHW = [];
        $allHWIds = [];
        
        if ($homeworkResult && $homeworkResult->num_rows > 0) { 
            while ($hw = $homeworkResult->fetch_assoc()) { 
                $allHWIds[] = $hw['id'];
                
                // Check if the student has submitted this homework.
                $submissionStmt = $conn->prepare("SELECT id FROM homework_submissions WHERE homework_id = ? AND student_id = ?");
                $submissionStmt->bind_param("ii", $hw['id'], $student_id);
                $submissionStmt->execute();
                $submissionResult = $submissionStmt->get_result();
                $submitted = ($submissionResult->num_rows > 0);
                $submissionStmt->close();
                
                // Calculate due timestamp.
                $dueTimestamp = strtotime($hw['due_date'] . ' ' . $hw['due_time']);
                $currentTimestamp = time();
                
                if ($submitted) {
                    $completeHW[] = $hw;
                } else if ($currentTimestamp > $dueTimestamp) {
                    $overdueHW[] = $hw;
                } else {
                    $recentHW[] = $hw;
                }
            }
        }
        
        // Fetch lock status for all homework assignments for this student.
        $hw_lock_status = [];
        if (!empty($allHWIds)) {
            $placeholders = implode(',', array_fill(0, count($allHWIds), '?'));
            $stmt = $conn->prepare("SELECT homework_id, is_locked FROM homework_lock_status WHERE student_id = ? AND homework_id IN ($placeholders)");
            $types = 'i' . str_repeat('i', count($allHWIds));
            $params = array_merge([$student_id], $allHWIds);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()){
                $hw_lock_status[$row['homework_id']] = $row['is_locked'];
            }
            $stmt->close();
        }
        ?>
        <h3>Your Class</h3>
        <p>Class: <?php echo htmlspecialchars($classDetails['class_name']); ?></p>
        
        <h3>Set Homework</h3>
        <div class="hw-buttons">
            <button class="button" onclick="showHW('recentHW')">Recent HW</button>
            <button class="button" onclick="showHW('completeHW')">Complete HW</button>
            <button class="button" onclick="showHW('overdueHW')">OverDue HW</button>
        </div>
        
        <!-- Container for Homework Sections -->
        <div id="hwContent">
            <!-- Recent Homework Section (default visible) -->
            <div id="recentHW" style="display: block;">
                <?php if (count($recentHW) > 0) { ?>
                    <?php foreach ($recentHW as $hw) { 
                        $dueDate = date("d/m/Y", strtotime($hw['due_date']));
                        $dueTime = date("H:i", strtotime($hw['due_time']));
                        $isLocked = (isset($hw_lock_status[$hw['id']]) && $hw_lock_status[$hw['id']] == 1);
                    ?>
                        <div class="hw-card">
                            <?php if ($isLocked) { ?>
                                <div style="cursor:pointer;" onclick="showLockModal('<?php echo addslashes($teacher_name); ?>')">
                                    <h4><?php echo htmlspecialchars($hw['assignment_name']); ?></h4>
                                    <p><strong>Due:</strong> <?php echo $dueDate . " " . $dueTime; ?></p>
                                    <p><strong>Class:</strong> <?php echo htmlspecialchars($classDetails['class_name']); ?></p>
                                    <p style="text-align:right;">
                                        <img src="Lock.ico" alt="Locked" style="width:20px; height:20px;">
                                    </p>
                                </div>
                            <?php } else { ?>
                                <a href="student_assignment_view.php?id=<?php echo $hw['id']; ?>" style="text-decoration:none; color:inherit;">
                                    <h4><?php echo htmlspecialchars($hw['assignment_name']); ?></h4>
                                    <p><strong>Due:</strong> <?php echo $dueDate . " " . $dueTime; ?></p>
                                    <p><strong>Class:</strong> <?php echo htmlspecialchars($classDetails['class_name']); ?></p>
                                    <p style="text-align:right;">
                                        <img src="Enter.ico" alt="Enter" style="width:20px; height:20px;">
                                    </p>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No recent homework assignments.</p>
                <?php } ?>
            </div>
            
            <!-- Complete Homework Section -->
            <div id="completeHW" style="display: none;">
                <?php if (count($completeHW) > 0) { ?>
                    <?php foreach ($completeHW as $hw) { 
                        $dueDate = date("d/m/Y", strtotime($hw['due_date']));
                        $dueTime = date("H:i", strtotime($hw['due_time']));
                    ?>
                        <div class="hw-card" style="background-color:#f9f9f9;">
                            <h4><?php echo htmlspecialchars($hw['assignment_name']); ?></h4>
                            <p><strong>Due:</strong> <?php echo $dueDate . " " . $dueTime; ?></p>
                            <p><strong>Class:</strong> <?php echo htmlspecialchars($classDetails['class_name']); ?></p>
                            <p style="text-align:right;">
                                <img src="Enter.ico" alt="Completed" style="width:20px; height:20px; opacity:0.5;">
                            </p>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No completed homework assignments.</p>
                <?php } ?>
            </div>
            
            <!-- Overdue Homework Section -->
            <div id="overdueHW" style="display: none;">
                <?php if (count($overdueHW) > 0) { ?>
                    <?php foreach ($overdueHW as $hw) { 
                        $dueDate = date("d/m/Y", strtotime($hw['due_date']));
                        $dueTime = date("H:i", strtotime($hw['due_time']));
                        $isLocked = (isset($hw_lock_status[$hw['id']]) && $hw_lock_status[$hw['id']] == 1);
                    ?>
                        <div class="hw-card">
                            <?php if ($isLocked) { ?>
                                <div style="cursor:pointer;" onclick="showLockModal('<?php echo addslashes($teacher_name); ?>')">
                                    <h4><?php echo htmlspecialchars($hw['assignment_name']); ?></h4>
                                    <p><strong>Due:</strong> <?php echo $dueDate . " " . $dueTime; ?></p>
                                    <p><strong>Class:</strong> <?php echo htmlspecialchars($classDetails['class_name']); ?></p>
                                    <p style="text-align:right;">
                                        <img src="Lock.ico" alt="Locked" style="width:20px; height:20px;">
                                    </p>
                                </div>
                            <?php } else { ?>
                                <a href="student_assignment_view.php?id=<?php echo $hw['id']; ?>" style="text-decoration:none; color:inherit;">
                                    <h4><?php echo htmlspecialchars($hw['assignment_name']); ?></h4>
                                    <p><strong>Due:</strong> <?php echo $dueDate . " " . $dueTime; ?></p>
                                    <p><strong>Class:</strong> <?php echo htmlspecialchars($classDetails['class_name']); ?></p>
                                    <p style="text-align:right;">
                                        <img src="Enter.ico" alt="Enter" style="width:20px; height:20px;">
                                    </p>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No overdue homework assignments.</p>
                <?php } ?>
            </div>
        </div>
        
    <?php } else { ?>
        <p>No class assigned.</p>
    <?php } ?>
</div>

<!-- Modal HTML -->
<div id="lockModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeLockModal()">&times;</span>
    <p id="lockMessage"></p>
  </div>
</div>

<!-- Modal CSS -->
<style>
.modal {
  display: none;
  position: fixed;
  z-index: 100;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4);
}

.modal-content {
  background-color: #fefefe;
  margin: 15% auto;
  padding: 20px;
  border: 1px solid #888;
  width: 80%;
  max-width: 400px;
  border-radius: 8px;
  text-align: center;
}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
}
</style>

<!-- Modal JavaScript -->
<script>
function showLockModal(teacherName) {
    var modal = document.getElementById("lockModal");
    var message = document.getElementById("lockMessage");
    message.textContent = "Cannot Access Document Because " + teacherName + " Has Locked this assignment for you";
    modal.style.display = "block";
}

function closeLockModal() {
    var modal = document.getElementById("lockModal");
    modal.style.display = "none";
}

function showHW(section) {
    document.getElementById('recentHW').style.display = 'none';
    document.getElementById('completeHW').style.display = 'none';
    document.getElementById('overdueHW').style.display = 'none';
    document.getElementById(section).style.display = 'block';
}
</script>
</body>
</html>
