<?php
// staff_homework_submission.php
session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

include('Header_Staff.php');
include('Config.php');

if (!isset($_GET['homework_id'])) {
    die("Homework ID not provided.");
}
$homework_id = intval($_GET['homework_id']);

// Get homework details.
$stmt = $conn->prepare("SELECT h.*, c.class_name, c.id as class_id FROM homework h LEFT JOIN classes c ON h.class_id = c.id WHERE h.id = ?");
$stmt->bind_param("i", $homework_id);
$stmt->execute();
$result = $stmt->get_result();
$homework = $result->fetch_assoc();
$stmt->close();

if (!$homework) {
    die("Homework not found.");
}

// Get the list of student IDs for the class from class_students table.
$stmt = $conn->prepare("SELECT student_ids FROM class_students WHERE class_id = ?");
$stmt->bind_param("i", $homework['class_id']);
$stmt->execute();
$csResult = $stmt->get_result();
$classStudents = $csResult->fetch_assoc();
$stmt->close();

$studentIds = [];
if ($classStudents && !empty($classStudents['student_ids'])) {
    // Assumes student_ids are stored as comma separated values.
    $studentIds = array_map('intval', explode(",", $classStudents['student_ids']));
}

// Fetch details for all students in that class.
$students = [];
if (!empty($studentIds)) {
    // Create a string of question marks for the IN clause
    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
    $stmt = $conn->prepare("SELECT id, full_name FROM students WHERE id IN ($placeholders)");
    $types = str_repeat('i', count($studentIds));
    $stmt->bind_param($types, ...$studentIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[$row['id']] = $row;
    }
    $stmt->close();
}

// Fetch homework submissions for the homework.
$submissions = [];
$stmt = $conn->prepare("SELECT * FROM homework_submissions WHERE homework_id = ?");
$stmt->bind_param("i", $homework_id);
$stmt->execute();
$subResult = $stmt->get_result();
while ($submission = $subResult->fetch_assoc()) {
    // Key by student_id for easy lookup.
    $submissions[$submission['student_id']] = $submission;
}
$stmt->close();

// Fetch lock status for homework from homework_lock_status table.
$lock_status = [];
if (!empty($studentIds)) {
    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
    $stmt = $conn->prepare("SELECT student_id, is_locked FROM homework_lock_status WHERE homework_id = ? AND student_id IN ($placeholders)");
    // Build parameter list: first homework_id, then all student IDs.
    $types = 'i' . str_repeat('i', count($studentIds));
    $params = array_merge([$homework_id], $studentIds);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $lock_status[$row['student_id']] = $row['is_locked'];
    }
    $stmt->close();
}
?>
<div class="content">
    <h2>Submissions for: <?php echo htmlspecialchars($homework['assignment_name']); ?></h2>
    <p>Class: <?php echo htmlspecialchars($homework['class_name']); ?></p>
    <p>Due: <?php echo htmlspecialchars($homework['due_date'] . " " . $homework['due_time']); ?></p>
    
    <table class="table">
        <tr>
            <th>Student Name</th>
            <th>Status</th>
            <th>Submission Time</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($students as $student) { 
            $hasSubmitted = isset($submissions[$student['id']]);
            $submissionTime = $hasSubmitted ? htmlspecialchars($submissions[$student['id']]['submitted_at']) : "";
            // Check lock status from the new table. Default to 0 (unlocked) if not set.
            $isLocked = isset($lock_status[$student['id']]) ? $lock_status[$student['id']] : 0;
        ?>
        <tr>
            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
            <td>
                <?php if ($hasSubmitted) { ?>
                    <img src="Y.ico" alt="Submitted" title="Submitted" />
                <?php } else { ?>
                    <img src="N.ico" alt="Not Submitted" title="Not Submitted" />
                <?php } ?>
            </td>
            <td><?php echo $submissionTime; ?></td>
            <td>
                <?php if ($isLocked == 0) { ?>
                    <a href="staff_lock_homework.php?homework_id=<?php echo $homework_id; ?>&student_id=<?php echo $student['id']; ?>">
                        <img src="Lock.ico" alt="Lock Homework" title="Lock Homework" />
                    </a>
                <?php } else { ?>
                    <a href="staff_unlock_homework.php?homework_id=<?php echo $homework_id; ?>&student_id=<?php echo $student['id']; ?>">
                        <img src="Unlock.ico" alt="Unlock Homework" title="Unlock Homework" />
                    </a>
                <?php } ?>
                <?php if ($hasSubmitted) { ?>
                    <a href="staff_view_quiz_submission.php?homework_id=<?php echo $homework_id; ?>&student_id=<?php echo $student['id']; ?>">
                        <img src="View.ico" alt="View Submission" title="View Submission" />
                    </a>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
