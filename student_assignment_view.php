<?php
include('Header_student.php');  // This already starts the session.

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}


include('Config.php');

// Check if the assignment ID is provided using the "id" parameter.
if (!isset($_GET['id'])) {
    echo "<p>Error: No assignment ID provided.</p>";
    exit();
}

$homework_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM homework WHERE id = ?");
$stmt->bind_param("i", $homework_id);
$stmt->execute();
$result = $stmt->get_result();
$hw = $result->fetch_assoc();
$stmt->close();

// Check if the assignment exists.
if (!$hw) {
    echo "<p>Error: Assignment not found.</p>";
    exit();
}
?>
<div class="content">
    <h2>Assignment Details</h2>
    <p>Assignment Name: <?php echo htmlspecialchars($hw['assignment_name']); ?></p>
    <p>Due Date: <?php echo date("M d, Y", strtotime($hw['due_date'])); ?> <?php echo date("h:i A", strtotime($hw['due_time'])); ?></p>
    <p>Class: 
    <?php 
        $stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ?");
        $stmt->bind_param("i", $hw['class_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $class = $res->fetch_assoc();
        echo htmlspecialchars($class['class_name']);
        $stmt->close();
    ?>
    </p>
    <button class="button" onclick="window.location.href='student_quiz_overview.php?homework_id=<?php echo $hw['id']; ?>'">Take Quiz</button>
</div>
</body>
</html>
