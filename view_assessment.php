<?php
// view_assessment.php
session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

include('Header_staff.php');
include('Config.php');

if (!isset($_GET['assessment_id'])) {
    die("Assessment ID not provided.");
}
$assessment_id = intval($_GET['assessment_id']);

// Get assessment details
$stmt = $conn->prepare("SELECT * FROM class_assessments WHERE id = ?");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();
$assessment = $result->fetch_assoc();
$stmt->close();

if (!$assessment) {
    die("Assessment not found.");
}

// Get class details for this assessment
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $assessment['class_id']);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();
$stmt->close();

if (!$class) {
    die("Class not found.");
}

// Retrieve the list of student IDs enrolled in this class
$classStudents = [];
$result = $conn->query("SELECT student_ids FROM class_students WHERE class_id = {$class['id']} LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $classStudents = explode(",", $row['student_ids']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>View Assessment</title>
    <style>
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .button { padding: 5px 10px; margin: 2px; }
    </style>
</head>
<body>
    <h2>Assessment: <?php echo htmlspecialchars($assessment['assessment_name']); ?></h2>
    <h3>Class: <?php echo htmlspecialchars($class['class_name']); ?></h3>
    
    <h3>Students</h3>
    <table class="table">
        <tr>
            <th>Student Name</th>
            <th>Completed Assessment</th>
            <th>Actions</th>
        </tr>
        <?php 
        // We'll use the assessment's created_at value as a threshold for submission.
        $assessmentCreation = $assessment['created_at'];
        if (!empty($classStudents)) {
            foreach ($classStudents as $studentId) {
                $sRes = $conn->query("SELECT full_name FROM students WHERE id = $studentId");
                $student = $sRes->fetch_assoc();
                $studentName = $student ? htmlspecialchars($student['full_name']) : 'Unknown';
                
                // Check if this student has submitted answers for this assessment.
                // Instead of filtering on a nonexistent "assessment_id" column, we check if the submission time is on or after the assessment's creation time.
                $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM assessment_answers WHERE student_id = ? AND submitted_at >= ?");
                $stmt->bind_param("is", $studentId, $assessmentCreation);
                $stmt->execute();
                $res = $stmt->get_result();
                $data = $res->fetch_assoc();
                $stmt->close();
                
                $hasSubmitted = ($data['cnt'] > 0);
                ?>
                <tr>
                    <td><?php echo $studentName; ?></td>
                    <td>
                        <?php if ($hasSubmitted) { ?>
                            <img src="Y.ico" alt="Yes" style="height:16px;">
                        <?php } else { ?>
                            <img src="N.ico" alt="No" style="height:16px;">
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($hasSubmitted) { ?>
                            <a href="view_assessment_response.php?student_id=<?php echo $studentId; ?>&assessment_id=<?php echo $assessment_id; ?>">
                                <button class="button">View Response</button>
                            </a>
                        <?php } else { ?>
                            N/A
                        <?php } ?>
                    </td>
                </tr>
            <?php }
        } else { ?>
            <tr><td colspan="3">No students in this class.</td></tr>
        <?php } ?>
    </table>
</body>
</html>
