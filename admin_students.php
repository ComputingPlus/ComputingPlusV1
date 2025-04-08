<?php
// admin_students.php

include('header.php');
include('Config.php');

// Process add student
if(isset($_POST['add_student'])){
    $fullName = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("INSERT INTO students (full_name, username, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $fullName, $username, $password);
    $stmt->execute();
    $stmt->close();
}

// Retrieve students
$result = $conn->query("SELECT * FROM students");
?>

<div class="content">
    <h2>Student Management</h2>
    <button class="button" onclick="toggleSidebar('studentSidebar')">Add Student</button>
    <table class="table">
        <tr>
            <th>Student Name</th>
            <th>Username</th>
            <th>Password</th>
        </tr>
        <?php while($row = $result->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $row['full_name']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['password']; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Sidebar for adding student -->
<div class="sidebar" id="studentSidebar">
    <h3>Add Student</h3>
    <form method="post">
        <label>Student Full Name</label>
        <input type="text" name="full_name" required>
        
        <label>Username</label>
        <input type="text" name="username" required>
        
        <label>Password</label>
        <input type="password" name="password" required>
        
        <button type="submit" name="add_student" class="button">Add</button>
        <button type="button" class="button" onclick="toggleSidebar('studentSidebar')">Close</button>
    </form>
</div>

<script>
function toggleSidebar(id) {
    var sidebar = document.getElementById(id);
    if(sidebar.classList.contains('active')){
        sidebar.classList.remove('active');
    } else {
        sidebar.classList.add('active');
    }
}
</script>
</body>
</html>
