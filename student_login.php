<?php
// student_login.php
session_start();

include('Header_LF.php');
include('Config.php');

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 1){
        $student = $result->fetch_assoc();
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['full_name'];
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Login - ComputingPlus</title>
    <link rel="stylesheet" type="text/css" href="Css.php">
</head>
<body>
<div class="login-form">
    <h2>Student Login</h2>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <label>Username</label>
        <input type="text" name="username" required>
        
        <label>Password</label>
        <input type="password" name="password" required>
        
        <button type="submit" name="login" class="button">Login</button>
    </form>
</div>
</body>
</html>
