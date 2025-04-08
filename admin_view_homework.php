<?php
// admin_view_homework.php

include('header.php');
include('Config.php');

// Get all homework assignments with their class names.
$query = "SELECT h.*, c.class_name 
          FROM homework h 
          LEFT JOIN classes c ON h.class_id = c.id 
          ORDER BY h.created_at DESC";
$result = $conn->query($query);
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
                <a href="admin_homework_submission.php?homework_id=<?php echo $row['id']; ?>">
                    <button class="button">View Submissions</button>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
