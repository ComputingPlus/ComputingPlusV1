<?php
session_start();

// If user is already logged in, redirect to dashboard to avoid redirect loops.
if (isset($_SESSION['staff_id'])) {
    header("Location: staff_dashboard.php");
    exit();
}

include('Header_LF.php');

include('Config.php');

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Note: For security, consider hashing passwords and using password_verify().
    $stmt = $conn->prepare("SELECT * FROM staff WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $staff = $result->fetch_assoc();
        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['staff_fullname'] = $staff['full_name'];
        $_SESSION['staff_profile_pic'] = $staff['profile_picture'];
        header("Location: staff_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Staff Login - ComputingPlus</title>
    <link rel="stylesheet" type="text/css" href="Css.php">
    <style>
        /* Base styles */
        body {
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        /* Container to align the form to the right side of the screen */
        .container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: 100vh;
            padding-right: 50px;
        }
        /* Login form styling */
        .login-form {
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 350px;
        }
        .login-form h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .login-form label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 10px 40px 10px 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
            transition: border-color 0.3s ease;
        }
        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus {
            border-color: #2575fc;
            outline: none;
        }
        /* Set background icons for inputs */
        .login-form input[type="text"] {
            background-image: url('User.ico');
        }
        .login-form input[type="password"] {
            background-image: url('pass.ico');
        }
        .login-form .button {
            width: 100%;
            padding: 12px;
            background: #2575fc;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .login-form .button:hover {
            background: #1a5fb4;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-form">
        <h2>Staff Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" name="login" class="button">Login</button>
        </form>
    </div>
</div>
</body>
</html>
