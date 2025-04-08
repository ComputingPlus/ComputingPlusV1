<?php
// admin_view_quiz.php

include('header.php');
include('Config.php');

$quiz_id = $_GET['quiz_id'];

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM homework_quiz WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc();
$stmt->close();

// Get quiz questions
$questionsResult = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id");
?>

<div class="content">
    <h2>Quiz: <?php echo $quiz['quiz_name']; ?></h2>
    <p><?php echo $quiz['quiz_description']; ?></p>
    <table class="table">
        <tr>
            <th>Question</th>
            <th>Answer</th>
            <th>Image</th>
        </tr>
        <?php while($q = $questionsResult->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $q['question']; ?></td>
            <td><?php echo $q['answer']; ?></td>
            <td><?php if($q['image']){ echo "<img src='".$q['image']."' width='50'>"; } ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
