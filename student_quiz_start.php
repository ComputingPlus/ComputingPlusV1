<?php
// student_quiz_start.php
session_start();
if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}

include('Header_student.php');
include('Config.php');

$quiz_id = $_GET['quiz_id'];
$homework_id = $_GET['homework_id'];

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM homework_quiz WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quizResult = $stmt->get_result();
$quiz = $quizResult->fetch_assoc();
$stmt->close();

// Get quiz questions
$questionsResult = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id");
?>
<div class="content">
    <h2><?php echo $quiz['quiz_name']; ?></h2>
    <p><?php echo $quiz['quiz_description']; ?></p>
    <form method="post" action="submit_quiz.php">
        <?php 
        $i = 1;
        while($q = $questionsResult->fetch_assoc()){
            echo "<div class='question'>";
            echo "<p>Question $i: " . $q['question'] . "</p>";
            echo "<input type='text' name='answer_$q[id]' required>";
            if($q['image']){
                echo "<img src='".$q['image']."' width='500'>";
            }
            echo "</div>";
            $i++;
        }
        ?>
        <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
        <input type="hidden" name="homework_id" value="<?php echo $homework_id; ?>">
        <button type="submit" class="button">Submit Quiz</button>
    </form>
</div>
</body>
</html>
