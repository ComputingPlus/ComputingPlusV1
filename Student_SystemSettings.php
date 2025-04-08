<?php
include('Header_student.php');

include('Config.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}
$student_id = $_SESSION['student_id'];

// Retrieve student details (assumed stored in the students table)
$stmt = $conn->prepare("SELECT full_name, username, password FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$studentDetails = $result->fetch_assoc();
$stmt->close();

// Process password change form submission
$passwordChangeMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_new_password'];
    
    // For demonstration, assuming plain-text storage (in production, use password_hash etc.)
    if ($current_password !== $studentDetails['password']) {
        $passwordChangeMessage = 'Current password is incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $passwordChangeMessage = 'New passwords do not match.';
    } else {
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $student_id);
        if ($stmt->execute()) {
            $passwordChangeMessage = 'Password updated successfully.';
            $studentDetails['password'] = $new_password;
        } else {
            $passwordChangeMessage = 'Error updating password.';
        }
        $stmt->close();
    }
}

// Retrieve student's class(es)
$classQuery = "SELECT cs.class_id, c.class_name, c.teachers FROM class_students cs 
               JOIN classes c ON cs.class_id = c.id 
               WHERE FIND_IN_SET(?, cs.student_ids)";
$stmt = $conn->prepare($classQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$classResult = $stmt->get_result();
$classes = [];
while ($row = $classResult->fetch_assoc()) {
    $classes[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student System Settings</title>
    <style>
        /* Card layout */
        .card { border: 1px solid #ccc; padding: 15px; margin: 10px; border-radius: 5px; }
        .container { display: flex; }
        .left, .right { width: 50%; padding: 10px; }
        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 300px;
            height: 100%;
            background: #f0f0f0;
            border-left: 1px solid #ccc;
            padding: 20px;
            transition: right 0.3s;
            overflow-y: auto;
        }
        .sidebar.active { right: 0; }
        .close-btn { cursor: pointer; color: red; float: right; }
        .button { padding: 8px 12px; background: #007BFF; color: #fff; border: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="content">
    <h2>My Profile</h2>
    <div class="container">
        <!-- Left Section: My Details and Change Password -->
        <div class="left">
            <div class="card">
                <h3>My Details</h3>
                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($studentDetails['full_name']); ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($studentDetails['username']); ?></p>
            </div>
            <div class="card">
                <h3>Change Password <img src="View.ico" alt="View" style="width:16px; vertical-align:middle;"></h3>
                <?php if ($passwordChangeMessage) { echo "<p>" . htmlspecialchars($passwordChangeMessage) . "</p>"; } ?>
                <button class="button" onclick="toggleSidebar()">Change Password</button>
            </div>
        </div>
        <!-- Right Section: My Class -->
        <div class="right">
            <div class="card">
                <h3>My Class</h3>
                <?php if (count($classes) > 0) { ?>
                    <ul>
                        <?php foreach ($classes as $cls) { ?>
                            <li style="cursor:pointer;" onclick="showClassDetails('<?php echo addslashes(htmlspecialchars($cls['class_name'])); ?>', '<?php echo addslashes(htmlspecialchars($cls['teachers'])); ?>')">
                                <?php echo htmlspecialchars($cls['class_name']); ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>You are not enrolled in any class.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Sidebar -->
<div id="passwordSidebar" class="sidebar">
    <span class="close-btn" onclick="toggleSidebar()">X</span>
    <h3>Change Password</h3>
    <form method="post" action="">
        <label for="current_password">Current Password:</label><br>
        <input type="password" name="current_password" required><br><br>
        <label for="new_password">New Password:</label><br>
        <input type="password" name="new_password" required><br><br>
        <label for="confirm_new_password">Confirm New Password:</label><br>
        <input type="password" name="confirm_new_password" required><br><br>
        <button type="submit" name="change_password" class="button">Change</button>
    </form>
</div>

<!-- Class Details Sidebar -->
<div id="classSidebar" class="sidebar">
    <span class="close-btn" onclick="toggleClassSidebar()">X</span>
    <h3>Class Details</h3>
    <p id="className"></p>
    <p id="classTeachers"></p>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('passwordSidebar');
    sidebar.classList.toggle('active');
}

function showClassDetails(name, teachers) {
    document.getElementById('className').innerHTML = "<strong>Class Name:</strong> " + name;
    document.getElementById('classTeachers').innerHTML = "<strong>Teachers:</strong> " + teachers;
    document.getElementById('classSidebar').classList.add('active');
}

function toggleClassSidebar() {
    var sidebar = document.getElementById('classSidebar');
    sidebar.classList.toggle('active');
}
</script>
</body>
</html>
