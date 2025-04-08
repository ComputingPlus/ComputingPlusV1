<?php
// admin_class_view.php

include('header.php');
include('Config.php');

if (!isset($_GET['class_id'])) {
    die("Class ID not provided.");
}
$class_id = intval($_GET['class_id']);

// Get class details
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();
$stmt->close();

if (!$class) {
    die("Class not found.");
}

// Process add students to class
if (isset($_POST['add_students'])) {
    $student_ids = implode(",", $_POST['students']);
    // Insert new record or update existing record in class_students table
    // (For simplicity, we insert a new record each time.)
    $stmt = $conn->prepare("INSERT INTO class_students (class_id, student_ids, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $class_id, $student_ids);
    $stmt->execute();
    $stmt->close();
}

// Process set homework
if (isset($_POST['set_homework'])) {
    $assignmentName = $_POST['assignment_name'];
    $quiz = $_POST['quiz'];
    $dueDate = $_POST['due_date'];
    $dueTime = $_POST['due_time'];
    $notes = $_POST['notes'];
    $stmt = $conn->prepare("INSERT INTO homework (class_id, assignment_name, quiz, due_date, due_time, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isssss", $class_id, $assignmentName, $quiz, $dueDate, $dueTime, $notes);
    $stmt->execute();
    $stmt->close();
}

// Retrieve student list for this class (from class_students table)
$classStudents = [];
$result = $conn->query("SELECT student_ids FROM class_students WHERE class_id = $class_id LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $classStudents = explode(",", $row['student_ids']);
}

// Retrieve all students for selection (for the sidebar form)
$studentsResult = $conn->query("SELECT id, full_name FROM students");

// Retrieve quizzes for homework selection
$quizzesResult = $conn->query("SELECT id, quiz_name FROM homework_quiz");

// Retrieve homework assignments for this class (for the overview table)
$stmt = $conn->prepare("SELECT * FROM homework WHERE class_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$homeworkResult = $stmt->get_result();
$stmt->close();
?>
<div class="content">
    <h2>Class View: <?php echo htmlspecialchars($class['class_name']); ?></h2>
    <p><?php echo htmlspecialchars($class['class_details']); ?></p>
    
    <button class="button" onclick="toggleSidebar('homeworkSidebar')">Set Homework</button>
    <button class="button" onclick="toggleSidebar('studentsSidebar')">Add Students to Class</button>
    
    <h3>Student List</h3>
    <table class="table">
        <tr>
            <th>Student Name</th>
        </tr>
        <?php 
        if (!empty($classStudents)) {
            foreach ($classStudents as $studentId) {
                $res = $conn->query("SELECT full_name FROM students WHERE id = $studentId");
                if ($res && $s = $res->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($s['full_name']) . "</td></tr>";
                }
            }
        } else {
            echo "<tr><td>No students added yet.</td></tr>";
        }
        ?>
    </table>
    
    <h3>Set Homework Overview</h3>
    <table class="table">
        <tr>
            <th>Assignment Name</th>
            <th>Due Date &amp; Time</th>
            <th>Actions</th>
        </tr>
        <?php while ($hw = $homeworkResult->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($hw['assignment_name']); ?></td>
            <td><?php echo htmlspecialchars($hw['due_date'] . " " . $hw['due_time']); ?></td>
            <td>
                <a href="admin_homework_submission.php?homework_id=<?php echo $hw['id']; ?>">
                    <button class="button">View Submissions</button>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Sidebar for adding students -->
<div class="sidebar" id="studentsSidebar">
    <h3>Add Students to Class</h3>
    <form method="post">
        <label>Select Students</label>
        <select name="students[]" multiple required>
            <?php while ($student = $studentsResult->fetch_assoc()) { ?>
                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option>
            <?php } ?>
        </select>
        <button type="submit" name="add_students" class="button">Submit</button>
        <button type="button" class="button" onclick="toggleSidebar('studentsSidebar')">Close</button>
    </form>
</div>

<!-- Sidebar for setting homework -->
<div class="sidebar" id="homeworkSidebar">
    <h3>Set Homework</h3>
    <form method="post">
        <label>Assignment Name</label>
        <input type="text" name="assignment_name" required>
        
        <label>Quiz</label>
        <select name="quiz" required>
            <?php while ($quiz = $quizzesResult->fetch_assoc()) { ?>
                <option value="<?php echo $quiz['id']; ?>"><?php echo htmlspecialchars($quiz['quiz_name']); ?></option>
            <?php } ?>
        </select>
        
        <label>Due Date</label>
        <input type="date" name="due_date" required>
        
        <label>Due Time</label>
        <input type="time" name="due_time" required>
        
        <label>Notes</label>
        <textarea name="notes"></textarea>
        
        <button type="submit" name="set_homework" class="button">Submit</button>
        <button type="button" class="button" onclick="toggleSidebar('homeworkSidebar')">Close</button>
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
