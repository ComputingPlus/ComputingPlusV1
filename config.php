<?php
// config.php
// This file was generated during the setup process. It contains the database
// connection credentials and opens a connection to the MySQL server.
// Ensure that this file is protected from unauthorized access.

// Replace the following values if necessary:
$dbHost = 'localhost';  // Your MySQL server address
$dbUser = 'Admin';         // Your MySQL username
$dbPass = '123';     // Your MySQL password
$dbName = 'Cplus';         // Your database name

// Create the MySQLi connection.
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check the connection.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
