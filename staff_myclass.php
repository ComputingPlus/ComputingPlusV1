<?php
// staff_myclass.php
session_start();
if(!isset($_SESSION['staff_id'])){
    header("Location: staff_login.php");
    exit();
}

include('Header_Staff.php');
include('Config.php');

$staff_id = $_SESSION['staff_id'];
$result = $conn->query("SELECT * FROM classes WHERE FIND_IN_SET($staff_id, teachers)");
?>

<div class="content">
    <h2>My Class</h2>
    <?php if($result->num_rows == 0){ ?>
        <p>No class assigned</p>
    <?php } else { ?>
        <table class="table">
            <tr>
                <th>Class Name</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $result->fetch_assoc()){ ?>
            <tr>
                <td><?php echo $row['class_name']; ?></td>
                <td>
                    <a href="staff_class_view.php?class_id=<?php echo $row['id']; ?>">
                        <button class="button"><img src="Enter.ico" alt="Enter"></button>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>
</body>
</html>
