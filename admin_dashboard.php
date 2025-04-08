<?php
// admin_dashboard.php

// Include the header file.
include('header.php');

// Include the configuration file that sets up the database connection
// and helper functions.
require_once 'config.php';

// Initialize counters.
$staffCount = 0;
$studentCount = 0;

// Get count for recently added staff within the last 7 days using the helper function.
$sql = "SELECT COUNT(*) AS count FROM staff WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = dbQuery($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $staffCount = $row['count'];
}

// Get count for recently added students within the last 7 days using the helper function.
$sql = "SELECT COUNT(*) AS count FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = dbQuery($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $studentCount = $row['count'];
}
?>
<div class="content">
    <h2>Admin Dashboard</h2>
    <p>Recent Staff Added: <?php echo $staffCount; ?></p>
    <p>Recent Students Added: <?php echo $studentCount; ?></p>
</div>
</body>
</html>


