<?php
// staff_lock_homework.php
session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

if (!isset($_GET['homework_id']) || !isset($_GET['student_id'])) {
    die("Required parameters not provided.");
}

$homework_id = intval($_GET['homework_id']);
$student_id = intval($_GET['student_id']);


include('Config.php');

// Check if a lock record exists
$stmt = $conn->prepare("SELECT id FROM homework_lock_status WHERE homework_id = ? AND student_id = ?");
$stmt->bind_param("ii", $homework_id, $student_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update the record to locked.
    $stmt->close();
    $stmt = $conn->prepare("UPDATE homework_lock_status SET is_locked = 1, locked_at = NOW() WHERE homework_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $homework_id, $student_id);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt->close();
    // Insert a new record with the locked state.
    $stmt = $conn->prepare("INSERT INTO homework_lock_status (homework_id, student_id, is_locked, locked_at) VALUES (?, ?, 1, NOW())");
    $stmt->bind_param("ii", $homework_id, $student_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to the homework submissions page.
header("Location: staff_homework_submission.php?homework_id=" . $homework_id);
exit();
?>
