<?php
// admin_homework_quiz.php

include('header.php');
include('Config.php');

// Process create HW Quiz
if(isset($_POST['create_quiz'])){
    $quizName = $_POST['quiz_name'];
    $quizDescription = $_POST['quiz_description'];
    $numQuestions = $_POST['num_questions'];
    
    // Insert quiz into homework_quiz table
    $stmt = $conn->prepare("INSERT INTO homework_quiz (quiz_name, quiz_description, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $quizName, $quizDescription);
    $stmt->execute();
    $quiz_id = $stmt->insert_id;
    $stmt->close();
    
    // Loop through each question
    for($i=1; $i<=$numQuestions; $i++){
        $question = $_POST["question_$i"];
        $answer = $_POST["answer_$i"];
        $image = "";
        if(isset($_FILES["image_$i"]) && $_FILES["image_$i"]['error'] == 0){
            $image = "uploads/" . basename($_FILES["image_$i"]['name']);
            move_uploaded_file($_FILES["image_$i"]['tmp_name'], $image);
        }
        $stmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question, answer, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $quiz_id, $question, $answer, $image);
        $stmt->execute();
        $stmt->close();
    }
}

// Retrieve quizzes
$result = $conn->query("SELECT * FROM homework_quiz");
?>

<div class="content">
    <h2>Homework Quizzes</h2>
    <button class="button" onclick="toggleSidebar('quizSidebar')">Create HW Quiz</button>
    <table class="table">
        <tr>
            <th>Quiz Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $row['quiz_name']; ?></td>
            <td><?php echo $row['quiz_description']; ?></td>
            <td>
                <a href="admin_view_quiz.php?quiz_id=<?php echo $row['id']; ?>">
                    <button class="button"><img src="Enter.ico" alt="Enter"></button>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Sidebar for creating quiz -->
<div class="sidebar" id="quizSidebar">
    <h3>Create HW Quiz</h3>
    <form method="post" enctype="multipart/form-data">
        <label>Quiz Name</label>
        <input type="text" name="quiz_name" required>
        
        <label>Quiz Description</label>
        <textarea name="quiz_description" required></textarea>
        
        <label>Number of Questions (Max 20)</label>
        <input type="number" name="num_questions" min="1" max="20" required>
        
        <div id="questionsContainer"></div>
        
        <button type="submit" name="create_quiz" class="button">Submit</button>
        <button type="button" class="button" onclick="toggleSidebar('quizSidebar')">Close</button>
    </form>
</div>

<script>
document.querySelector('input[name="num_questions"]').addEventListener('change', function(){
    var num = this.value;
    var container = document.getElementById('questionsContainer');
    container.innerHTML = '';
    for(var i=1; i<=num; i++){
        container.innerHTML += '<h4>Question '+i+'</h4>';
        container.innerHTML += '<label>Question</label><input type="text" name="question_'+i+'" required>';
        container.innerHTML += '<label>Answer</label><input type="text" name="answer_'+i+'" required>';
        container.innerHTML += '<label>Optional Image</label><input type="file" name="image_'+i+'"><br>';
    }
});
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
