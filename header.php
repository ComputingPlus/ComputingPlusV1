<?php
// header.php

// Include the configuration that holds your credentials.
require_once 'config.php';

// If a connection is not already created, then create it.
if (!isset($conn)) {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

// ... rest of your header.php code follows ...
?>
<!DOCTYPE html>
<html>
<head>
    <title>ComputingPlus - Admin Dashboard</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" type="text/css" href="Css.php">
</head>
<body>
<div class="header">
    <img src="Logo.PNG" alt="Logo" class="logo">
    <nav class="nav">
        <a href="admin_class.php">Class</a>
        <a href="admin_students.php">Students</a>
        <a href="admin_staff.php">Staff</a>
        <a href="admin_homework_quiz.php">Homework Quizes</a>
    </nav>
</div>
