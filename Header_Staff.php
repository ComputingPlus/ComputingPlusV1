<?php
// Header_Staff.php – Staff header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$staff_profile_pic = isset($_SESSION['staff_profile_pic']) ? $_SESSION['staff_profile_pic'] : 'default.png';
$staff_fullname    = isset($_SESSION['staff_fullname']) ? $_SESSION['staff_fullname'] : 'Staff Member';
?>
<!DOCTYPE html>
<html>
<head>
    <title>ComputingPlus - Staff Dashboard</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" type="text/css" href="Css.php">
</head>
<body>
<div class="header">
    <a href="Staff_dashboard.php">
        <img src="Logo.PNG" alt="Logo" class="logo">
    </a>
    <nav class="nav">
        <a href="staff_myclass.php">My Class</a>
    </nav>
    <div class="staff-info">
        <img src="<?php echo $staff_profile_pic; ?>" alt="Profile Picture" class="logo" style="width:50px; border-radius:50%;">
        <span><?php echo $staff_fullname; ?></span>
    </div>
</div>
