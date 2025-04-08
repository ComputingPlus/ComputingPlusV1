<?php
session_start();

// Debug: Uncomment the following line to inspect session variables if needed
// var_dump($_SESSION);

// Redirect to login if the staff_id session variable is not set.
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}


include('Header_Staff.php');
include('Config.php');

// Get recent counts (example queries)
$staffCount = 0;
$studentCount = 0;

// Get count of staff added in the last 7 days.
$result = $conn->query("SELECT COUNT(*) as count FROM staff WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($result) {
    $row = $result->fetch_assoc();
    $staffCount = $row['count'];
}

// Get count of students added in the last 7 days.
$result = $conn->query("SELECT COUNT(*) as count FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($result) {
    $row = $result->fetch_assoc();
    $studentCount = $row['count'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard - ComputingPlus</title>
    <link rel="stylesheet" type="text/css" href="Css.php">
    <style>
        /* Add your dashboard styling here if needed */
        .content {
            margin: 20px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
<div class="content">
    <h2>Staff Dashboard</h2>
    <p>Recent Staff Added: <?php echo $staffCount; ?></p>
    <p>Recent Students Added: <?php echo $studentCount; ?></p>
</div>
</body>
</html>
