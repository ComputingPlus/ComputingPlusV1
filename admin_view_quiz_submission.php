<?php
// admin_view_quiz_submission.php

include('header.php');
include('Config.php');

if(!isset($_GET['homework_id']) || !isset($_GET['student_id'])) {
    die("Required parameters not provided.");
}
$homework_id = intval($_GET['homework_id']);
$student_id = intval($_GET['student_id']);

// Get quiz submissions for this homework and student.
$query = "SELECT qs.*, qq.question, qq.answer AS correct_answer 
          FROM quiz_submissions qs 
          LEFT JOIN quiz_questions qq ON qs.question_id = qq.id 
          WHERE qs.homework_id = ? AND qs.student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $homework_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="content">
    <h2>Quiz Answers</h2>
    <table class="table">
        <tr>
            <th>Question</th>
            <th>Correct Answer</th>
            <th>Student Answer</th>
        </tr>
        <?php while($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['question']); ?></td>
            <td><?php echo htmlspecialchars($row['correct_answer']); ?></td>
            <td><?php echo htmlspecialchars($row['answer']); ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
