<?php
// submit_quiz.php
session_start();
if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}

include('Config.php');

$quiz_id = $_POST['quiz_id'];
$homework_id = $_POST['homework_id'];
$student_id = $_SESSION['student_id'];

// Process answers – loop through quiz questions
$result = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id");
while($q = $result->fetch_assoc()){
    $userAnswer = $_POST["answer_$q[id]"];
    // Insert submission into a quiz_submissions table (assumed to exist)
    $stmt = $conn->prepare("INSERT INTO quiz_submissions (quiz_id, homework_id, student_id, question_id, answer, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiss", $quiz_id, $homework_id, $student_id, $q['id'], $userAnswer);
    $stmt->execute();
    $stmt->close();
}
// Mark homework as submitted (assumes a homework_submissions table exists)
$stmt = $conn->prepare("INSERT INTO homework_submissions (homework_id, student_id, submitted_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $homework_id, $student_id);
$stmt->execute();
$stmt->close();

header("Location: student_dashboard.php");
exit();
?>
