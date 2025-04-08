<?php
// admin_staff.php

include('header.php');
include('Config.php');

// Check if uploads directory exists, if not, create it.
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$errorMsg = "";
$successMsg = "";

// Add staff process
if (isset($_POST['add_staff'])) {
    $fullName = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $profilePic = "pfp.jpg"; // default profile picture

    // Check for duplicate username in staff table
    $checkStmt = $conn->prepare("SELECT id FROM staff WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $errorMsg = "Username already exists. Please choose a different username.";
    }
    $checkStmt->close();

    // If no error so far, process the file upload (if provided)
    if (empty($errorMsg)) {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $profilePic = $uploadDir . basename($_FILES['profile_picture']['name']);
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profilePic)) {
                $errorMsg = "Error moving uploaded file.";
            }
        }
    }

    // If no error occurred, insert the new staff member into the database
    if (empty($errorMsg)) {
        $stmt = $conn->prepare("INSERT INTO staff (full_name, username, password, profile_picture, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $fullName, $username, $password, $profilePic);
        if ($stmt->execute()) {
            $successMsg = "Staff member added successfully.";
        } else {
            $errorMsg = "Error adding staff: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Retrieve staff list
$result = $conn->query("SELECT * FROM staff");
?>

<div class="content">
    <h2>Staff Management</h2>
    <?php if ($errorMsg) { echo "<p style='color:red;'>$errorMsg</p>"; } ?>
    <?php if ($successMsg) { echo "<p style='color:green;'>$successMsg</p>"; } ?>
    <button class="button" onclick="toggleSidebar('staffSidebar')">Add Staff</button>
    <table class="table">
        <tr>
            <th>Staff Name</th>
            <th>Username</th>
            <th>Password</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['password']); ?></td>
            </tr>
        <?php } ?>
    </table>
</div>

<!-- Sidebar for adding staff -->
<div class="sidebar" id="staffSidebar">
    <h3>Add Staff</h3>
    <form method="post" enctype="multipart/form-data">
        <label>Staff Full Name</label>
        <input type="text" name="full_name" required>
        
        <label>Profile Picture</label>
        <input type="file" name="profile_picture">
        
        <label>Username</label>
        <input type="text" name="username" required>
        
        <label>Password</label>
        <input type="password" name="password" required>
        
        <button type="submit" name="add_staff" class="button">Add</button>
        <button type="button" class="button" onclick="toggleSidebar('staffSidebar')">Close</button>
    </form>
</div>

<script>
function toggleSidebar(id) {
    var sidebar = document.getElementById(id);
    if (sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
    } else {
        sidebar.classList.add('active');
    }
}
</script>
</body>
</html>
