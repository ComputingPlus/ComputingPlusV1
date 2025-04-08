<?php
// admin_class.php

include('header.php');
include('Config.php');

// Create class process
if(isset($_POST['create_class'])){
    $className = $_POST['class_name'];
    $classDetails = $_POST['class_details'];
    $teachers = implode(",", $_POST['teachers']);
    $stmt = $conn->prepare("INSERT INTO classes (class_name, class_details, teachers, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $className, $classDetails, $teachers);
    $stmt->execute();
    $stmt->close();
}

// Retrieve classes
$result = $conn->query("SELECT * FROM classes");

// Retrieve teachers for selection
$teachersResult = $conn->query("SELECT id, full_name FROM staff");
?>

<div class="content">
    <h2>Class Management</h2>
    <button class="button" onclick="toggleSidebar('classSidebar')">Create Class</button>
    <table class="table">
        <tr>
            <th>Class Name</th>
            <th>Teachers</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $row['class_name']; ?></td>
            <td><?php echo $row['teachers']; ?></td>
            <td>
                <button class="button" onclick="deleteClass(<?php echo $row['id']; ?>)"><img src="Delete.ico" alt="Delete"></button>
                <a href="admin_class_view.php?class_id=<?php echo $row['id']; ?>"><button class="button"><img src="Enter.ico" alt="Enter"></button></a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Sidebar for creating class -->
<div class="sidebar" id="classSidebar">
    <h3>Create Class</h3>
    <form method="post">
        <label>Class Name</label>
        <input type="text" name="class_name" required>
        
        <label>Class Details</label>
        <textarea name="class_details" required></textarea>
        
        <label>Teachers</label>
        <select name="teachers[]" multiple required>
            <?php while($teacher = $teachersResult->fetch_assoc()){ ?>
                <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['full_name']; ?></option>
            <?php } ?>
        </select>
        
        <button type="submit" name="create_class" class="button">Submit</button>
        <button type="button" class="button" onclick="toggleSidebar('classSidebar')">Close</button>
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
function deleteClass(id) {
    if(confirm("Are you sure you want to delete this class?")){
        window.location.href = 'delete_class.php?class_id=' + id;
    }
}
</script>
</body>
</html>
