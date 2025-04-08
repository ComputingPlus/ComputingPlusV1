<?php
// student_quiz_overview.php
session_start();
if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}

include('Header_student.php');
include('Config.php');

$homework_id = $_GET['homework_id'];
// Get homework details to determine which quiz to take
$stmt = $conn->prepare("SELECT * FROM homework WHERE id = ?");
$stmt->bind_param("i", $homework_id);
$stmt->execute();
$result = $stmt->get_result();
$hw = $result->fetch_assoc();
$stmt->close();

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM homework_quiz WHERE id = ?");
$stmt->bind_param("i", $hw['quiz']);
$stmt->execute();
$quizResult = $stmt->get_result();
$quiz = $quizResult->fetch_assoc();
$stmt->close();
?>
<div class="content">
    <h2>Quiz Overview</h2>
    <p>Quiz Name: <?php echo $quiz['quiz_name']; ?></p>
    <p>Description: <?php echo $quiz['quiz_description']; ?></p>
    <button class="button" onclick="window.location.href='student_quiz_start.php?quiz_id=<?php echo $quiz['id']; ?>&homework_id=<?php echo $homework_id; ?>'">Start Quiz</button>
</div>
</body>
</html>
