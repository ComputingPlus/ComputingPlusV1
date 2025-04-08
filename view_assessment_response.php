<?php
// view_assessment_response.php

session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}


include('Header_staff.php');
include('Config.php');

// Validate query parameters.
if (!isset($_GET['student_id']) || !isset($_GET['assessment_id'])) {
    die("Required parameters missing.");
}
$student_id = intval($_GET['student_id']);
$assessment_id = intval($_GET['assessment_id']);

// Get the assessment details from class_assessments.
$stmt = $conn->prepare("SELECT * FROM class_assessments WHERE id = ?");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();
$assessment = $result->fetch_assoc();
$stmt->close();
if (!$assessment) {
    die("Assessment not found.");
}

// Get the student details.
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$studentResult = $stmt->get_result();
$student = $studentResult->fetch_assoc();
$stmt->close();
if (!$student) {
    die("Student not found.");
}

// Use the assessment's creation time as a threshold for filtering submissions.
$assessmentCreation = $assessment['created_at'];

// Retrieve the student's quiz submissions that were recorded after the assessment was set.
// Joining quiz_submissions with quiz_questions (if available) to display the question text.
$query = "SELECT qs.question_id, qs.answer, qs.submitted_at, qq.question 
          FROM quiz_submissions qs 
          LEFT JOIN quiz_questions qq ON qs.question_id = qq.id 
          WHERE qs.student_id = ? AND qs.submitted_at >= ? 
          ORDER BY qs.question_id ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $student_id, $assessmentCreation);
$stmt->execute();
$submissionResult = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Assessment Response</title>
    <style>
        .table {
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
    </style>
</head>
<body>
    <h2>Assessment Response</h2>
    <h3>Assessment: <?php echo htmlspecialchars($assessment['assessment_name']); ?></h3>
    <h3>Student: <?php echo htmlspecialchars($student['full_name']); ?></h3>
    <h4>Responses submitted after: <?php echo htmlspecialchars($assessmentCreation); ?></h4>

    <?php if ($submissionResult->num_rows > 0) { ?>
        <table class="table">
            <tr>
                <th>Question Number</th>
                <th>Question Text</th>
                <th>Student's Answer</th>
                <th>Submission Time</th>
            </tr>
            <?php while ($row = $submissionResult->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['question_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['question']); ?></td>
                    <td><?php echo htmlspecialchars($row['answer']); ?></td>
                    <td><?php echo htmlspecialchars($row['submitted_at']); ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No responses found for this assessment.</p>
    <?php } ?>
</body>
</html>
