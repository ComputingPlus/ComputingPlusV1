<?php
// admin_homework_submission.php

include('header.php');
include('Config.php');

if(!isset($_GET['homework_id'])) {
    die("Homework ID not provided.");
}
$homework_id = intval($_GET['homework_id']);

// Get homework details.
$stmt = $conn->prepare("SELECT h.*, c.class_name FROM homework h LEFT JOIN classes c ON h.class_id = c.id WHERE h.id = ?");
$stmt->bind_param("i", $homework_id);
$stmt->execute();
$result = $stmt->get_result();
$homework = $result->fetch_assoc();
$stmt->close();

if(!$homework) {
    die("Homework not found.");
}

// Get homework submissions with student info.
$query = "SELECT hs.*, s.full_name 
          FROM homework_submissions hs 
          LEFT JOIN students s ON hs.student_id = s.id 
          WHERE hs.homework_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $homework_id);
$stmt->execute();
$subResult = $stmt->get_result();
?>
<div class="content">
    <h2>Submissions for: <?php echo htmlspecialchars($homework['assignment_name']); ?></h2>
    <p>Class: <?php echo htmlspecialchars($homework['class_name']); ?></p>
    <p>Due: <?php echo htmlspecialchars($homework['due_date'] . " " . $homework['due_time']); ?></p>
    
    <table class="table">
        <tr>
            <th>Student Name</th>
            <th>Submission Time</th>
            <th>Actions</th>
        </tr>
        <?php while($submission = $subResult->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($submission['full_name']); ?></td>
            <td><?php echo htmlspecialchars($submission['submitted_at']); ?></td>
            <td>
                <a href="admin_view_quiz_submission.php?homework_id=<?php echo $homework_id; ?>&student_id=<?php echo $submission['student_id']; ?>">
                    <button class="button">View Quiz Answers</button>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
