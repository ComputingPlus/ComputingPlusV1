<?php
// staff_view_homework.php
session_start();
if(!isset($_SESSION['staff_id'])){
    header("Location: staff_login.php");
    exit();
}

include('Header_Staff.php');
include('Config.php');

$staff_id = $_SESSION['staff_id'];
// Get homework assignments for classes where the staff member is listed in the 'teachers' field.
$query = "SELECT h.*, c.class_name 
          FROM homework h 
          LEFT JOIN classes c ON h.class_id = c.id 
          WHERE FIND_IN_SET(?, c.teachers) > 0 
          ORDER BY h.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="content">
    <h2>Homework Assignments</h2>
    <table class="table">
        <tr>
            <th>Assignment Name</th>
            <th>Class</th>
            <th>Due Date & Time</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['assignment_name']); ?></td>
            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
            <td><?php echo htmlspecialchars($row['due_date'] . " " . $row['due_time']); ?></td>
            <td>
                <a href="staff_homework_submission.php?homework_id=<?php echo $row['id']; ?>">
                    <button class="button">View Submissions</button>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
