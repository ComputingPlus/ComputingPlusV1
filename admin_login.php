<?php
// admin_login.php

// Include header file
include 'header_LF.php';

// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and trim the submitted username and password
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate credentials
    if ($username === 'Admin' && $password === '1234') {
        // Set session variable or perform additional login logic here
        $_SESSION['loggedin'] = true;
        // Redirect to an admin dashboard or another secure page
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
</head>
<body>
    <h2>Admin Login</h2>
    <?php 
    // Display error message if credentials are invalid
    if (isset($error)) {
        echo "<p style='color:red;'>$error</p>";
    }
    ?>
    <form action="" method="POST">
        <div>
            <label for="username">Username:</label>
            <!-- Set the default value to "Admin" -->
            <input type="text" id="username" name="username"  required>
        </div>
        <br>
        <div>
            <label for="password">Password:</label>
            <!-- Password field remains empty for security -->
            <input type="password" id="password" name="password" required>
        </div>
        <br>
        <div>
            <input type="submit" value="Login">
        </div>
    </form>
</body>
</html>
