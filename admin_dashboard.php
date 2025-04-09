<?php
// admin_dashboard.php

// Include your header (if it doesn’t modify the connection, it’s fine)
include('header.php');

// Include the config file to load connection details.
require_once 'config.php';

// Create the MySQLi connection using the credentials from config.php.
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check for a connection error.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Now you have access to $conn.
$staffCount = 0;
$studentCount = 0;

// Query for recent staff count (last 7 days).
$sql = "SELECT COUNT(*) AS count FROM staff WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $staffCount = $row['count'];
} else {
    error_log("MySQL Query Error (staff): " . $conn->error);
}

// Query for recent student count (last 7 days).
$sql = "SELECT COUNT(*) AS count FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $studentCount = $row['count'];
} else {
    error_log("MySQL Query Error (students): " . $conn->error);
}
?>

<div class="content">
    <h2>Admin Dashboard</h2>
    <p>Recent Staff Added: <?php echo $staffCount; ?></p>
    <p>Recent Students Added: <?php echo $studentCount; ?></p>
</div>
</body>
</html>
