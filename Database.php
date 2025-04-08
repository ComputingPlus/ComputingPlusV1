<?php
// Database.php

// Process form submission.
if (isset($_POST['submit'])) {

    // Get and trim form values.
    $dbHost = trim($_POST['servername']);
    $dbUser = trim($_POST['username']);
    $dbPass = trim($_POST['password']);
    $dbName = trim($_POST['dbname']);

    // Connect to MySQL server without selecting a database.
    $conn = new mysqli($dbHost, $dbUser, $dbPass);
    if ($conn->connect_error) {
        die("<div class='error'>Connection failed: " . $conn->connect_error . "</div>");
    }

    // Create the database if it doesn't exist.
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbName`";
    if (!$conn->query($sql)) {
        die("<div class='error'>Error creating database: " . $conn->error . "</div>");
    }

    // Select the newly created database.
    if (!$conn->select_db($dbName)) {
        die("<div class='error'>Error selecting database: " . $conn->error . "</div>");
    }

    // Define table creation queries.
    $queries = [];

    // Table: staff
    $queries[] = "CREATE TABLE IF NOT EXISTS staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255) DEFAULT 'default.png',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    // Table: students
    $queries[] = "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    // Table: classes
    $queries[] = "CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_name VARCHAR(255) NOT NULL,
        class_details TEXT,
        teachers VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    // Table: class_students
    $queries[] = "CREATE TABLE IF NOT EXISTS class_students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        student_ids TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )";

    // Table: homework
    $queries[] = "CREATE TABLE IF NOT EXISTS homework (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        assignment_name VARCHAR(255) NOT NULL,
        quiz INT NOT NULL,
        due_date DATE NOT NULL,
        due_time TIME NOT NULL,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )";

    // Table: homework_quiz
    $queries[] = "CREATE TABLE IF NOT EXISTS homework_quiz (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_name VARCHAR(255) NOT NULL,
        quiz_description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    // Table: quiz_questions
    $queries[] = "CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT NOT NULL,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        image VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (quiz_id) REFERENCES homework_quiz(id) ON DELETE CASCADE
    )";

    // Table: quiz_submissions
    $queries[] = "CREATE TABLE IF NOT EXISTS quiz_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT NOT NULL,
        homework_id INT NOT NULL,
        student_id INT NOT NULL,
        question_id INT NOT NULL,
        answer TEXT NOT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (quiz_id) REFERENCES homework_quiz(id) ON DELETE CASCADE,
        FOREIGN KEY (homework_id) REFERENCES homework(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
    )";

    // Table: homework_submissions
    $queries[] = "CREATE TABLE IF NOT EXISTS homework_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        homework_id INT NOT NULL,
        student_id INT NOT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (homework_id) REFERENCES homework(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )";

    // Table: homework_lock_status
    $queries[] = "CREATE TABLE IF NOT EXISTS homework_lock_status (
        id INT AUTO_INCREMENT PRIMARY KEY,
        homework_id INT NOT NULL,
        student_id INT NOT NULL,
        is_locked TINYINT(1) NOT NULL DEFAULT 0,
        locked_at TIMESTAMP NULL DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_homework_student (homework_id, student_id),
        FOREIGN KEY (homework_id) REFERENCES homework(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )";

    // Table: assessment_answers
    $queries[] = "CREATE TABLE IF NOT EXISTS assessment_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        question_number INT NOT NULL,
        answer TEXT NOT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )";

    // Table: class_assessments
    $queries[] = "CREATE TABLE IF NOT EXISTS class_assessments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        assessment_name VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )";

    // Execute the table creation queries.
    foreach ($queries as $q) {
        if (!$conn->query($q)) {
            die("<div class='error'>Error creating table: " . $conn->error . "</div>");
        }
    }

    // Save configuration options to a file named config.php.
    $configContent = "<?php\n";
    $configContent .= "\$dbHost = '" . addslashes($dbHost) . "';\n";
    $configContent .= "\$dbUser = '" . addslashes($dbUser) . "';\n";
    $configContent .= "\$dbPass = '" . addslashes($dbPass) . "';\n";
    $configContent .= "\$dbName = '" . addslashes($dbName) . "';\n";
    $configContent .= "?>\n";

    if (file_put_contents('config.php', $configContent) === false) {
        die("<div class='error'>Error saving configuration file.</div>");
    }

    // Include the header file.
    include 'Header_LF.php';

    // Redirect to Index.html after successful processing.
    header("Location: Index.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .container {
            background: #fff;
            max-width: 500px;
            margin: 80px auto;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: 500;
            display: block;
            margin-top: 15px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        input[type="submit"] {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }
        .error {
            text-align: center;
            margin: 20px auto;
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🛠 Database Configuration</h2>
    <form method="post" action="">
        <label for="servername">Server Name:</label>
        <input type="text" name="servername" id="servername" placeholder="e.g. 127.0.0.1" required>

        <label for="username">MySQL Username:</label>
        <input type="text" name="username" id="username" placeholder="e.g. root" required>

        <label for="password">MySQL Password:</label>
        <input type="password" name="password" id="password" placeholder="Your password" required>

        <label for="dbname">New Database Name:</label>
        <input type="text" name="dbname" id="dbname" placeholder="e.g. MyNewDB" required>

        <input type="submit" name="submit" value="Create Database and Tables">
    </form>
</div>

</body>
</html>
