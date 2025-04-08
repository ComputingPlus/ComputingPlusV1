<?php
// Header_student.php – Student header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'Student';
?>
<!DOCTYPE html>
<html>
<head>
    <title>ComputingPlus - Student Dashboard</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" type="text/css" href="Css.php">
</head>
<body>
<div class="header">
    <a href="Student_Dashboard.php">
        <img src="Logo.PNG" alt="Logo" class="logo">
    </a>
    <nav class="nav">
        <a href="Student_SystemSettings.php">My Profile/Settings</a>
    </nav>
    <div class="student-info" style="color:#fff;">
        <?php echo $student_name; ?>
    </div>
</div>
