<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}


include("Header_student.php");
include('Config.php');

// First, get the student's class.
$student_id = $_SESSION['student_id'];
$classQuery = "SELECT cs.class_id 
               FROM class_students cs 
               JOIN students s ON FIND_IN_SET(s.id, cs.student_ids) 
               WHERE s.id = $student_id 
               LIMIT 1";
$classResult = $conn->query($classQuery);
if (!$classResult || $classResult->num_rows == 0) {
    die("Student's class not found.");
}
$classRow = $classResult->fetch_assoc();
$class_id = $classRow['class_id'];

// Now, get a valid homework assignment for this student's class.
$stmt = $conn->prepare("SELECT id, quiz FROM homework WHERE class_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("No homework assignment found for your class. Please contact your teacher.");
}
$homeworkRow = $result->fetch_assoc();
$homework_id = $homeworkRow['id'];
$quiz_id = $homeworkRow['quiz']; // assuming the homework record stores a valid quiz id
$stmt->close();

// Process form submission: Save answers to database and redirect to dashboard.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $answers = [
        1 => $_POST['q1'], 2 => $_POST['q2'], 3 => $_POST['q3'],
        4 => $_POST['q4'], 5 => $_POST['q5'], 6 => $_POST['q6'],
        7 => $_POST['q7'], 8 => $_POST['q8'], 9 => $_POST['q9'],
        10 => $_POST['q10'], 11 => $_POST['q11'], 12 => $_POST['q12'],
        13 => $_POST['q13'], 14 => $_POST['q14'], 15 => $_POST['q15'],
        16 => $_POST['q16'], 17 => $_POST['q17'], 18 => $_POST['q18'],
        19 => $_POST['q19'], 20 => $_POST['q20'], 21 => $_POST['q21'],
        22 => $_POST['q22'], 23 => $_POST['q23'], 24 => $_POST['q24'],
        25 => $_POST['q25'], 26 => $_POST['q26']
    ];

    foreach ($answers as $question_id => $answer) {
        $stmt = $conn->prepare("INSERT INTO quiz_submissions (quiz_id, homework_id, student_id, question_id, answer) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $quiz_id, $homework_id, $student_id, $question_id, $answer);
        $stmt->execute();
        $stmt->close();
    }

    // Insert a marker record into assessment_answers to record assessment completion.
    $stmt = $conn->prepare("INSERT INTO assessment_answers (student_id, question_number, answer) VALUES (?, ?, ?)");
    $dummyQuestion = 0; // Indicates overall assessment completion.
    $dummyAnswer = 'completed';
    $stmt->bind_param("iis", $student_id, $dummyQuestion, $dummyAnswer);
    $stmt->execute();
    $stmt->close();

    header("Location: Student_Dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Assessment</title>
    <style>
        /* Basic Reset */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: url('AssBack.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        /* Section styling for card look */
        .section {
            display: none;
        }
        /* Video styling */
        video {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        /* Input & Textarea styling */
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 8px;
            margin-bottom: 16px;
        }
        /* Button styling */
        button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #0056b3;
        }
        /* Custom card for assessment sections */
        .card-header {
            font-size: 24px;
            margin-bottom: 20px;
            color: #007bff;
        }
        .question {
            text-align: left;
            margin-bottom: 20px;
        }
        .question p {
            font-weight: bold;
            margin-bottom: 8px;
        }
        /* Next button styling inside a question */
        .question .nextBtn {
            float: right;
        }
        /* Clearfix for questions */
        .question::after {
            content: "";
            display: table;
            clear: both;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            button {
                font-size: 14px;
                padding: 10px 16px;
            }
            .card-header {
                font-size: 20px;
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Show first section (Video1) inside its card.
            document.getElementById("section1").style.display = "block";

            // Video ended event listeners to show the enter button.
            document.getElementById("video1").addEventListener("ended", function () {
                document.getElementById("enterBtn1").style.display = "block";
            });
            document.getElementById("video2").addEventListener("ended", function () {
                document.getElementById("enterBtn2").style.display = "block";
            });
            document.getElementById("video3").addEventListener("ended", function () {
                document.getElementById("enterBtn3").style.display = "block";
            });
            document.getElementById("video4").addEventListener("ended", function () {
                document.getElementById("finalBtn").style.display = "block";
            });

            // Function to switch sections.
            window.showSection = function (current, next) {
                document.getElementById(current).style.display = "none";
                document.getElementById(next).style.display = "block";
            };

            // Set up one-question-at-a-time navigation for a section.
            function setupQuestions(sectionId) {
                var questions = document.querySelectorAll("#" + sectionId + " .question");
                questions.forEach(function (q, index) {
                    q.style.display = (index === 0) ? "block" : "none";
                });
                questions.forEach(function (q, index) {
                    var nextBtn = q.querySelector(".nextBtn");
                    if (nextBtn) {
                        nextBtn.addEventListener("click", function () {
                            q.style.display = "none";
                            if (index + 1 < questions.length) {
                                questions[index + 1].style.display = "block";
                            }
                        });
                    }
                });
            }
            // Setup navigation for questions in sections 2, 3, and 5.
            setupQuestions("section2");
            setupQuestions("section3");
            setupQuestions("section5");
        });
    </script>
</head>
<body>
<div class="container">
    <form method="POST" action="Student_Assessment.php">
        <!-- Section 1: Video1 -->
        <div id="section1" class="card section">
            <div class="card-header">Assessment Part 1 - Video</div>
            <video id="video1" controls>
                <source src="Video1.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <br>
            <button type="button" id="enterBtn1" style="display:none;" onclick="showSection('section1','section2')">
                Enter Assessment
            </button>
        </div>

        <!-- Section 2: Questions 1-10 -->
        <div id="section2" class="card section">
            <div class="card-header">Assessment Part 1</div>
            <div class="question">
                <p>Question 1. What Does HTML Stand for?</p>
                <input type="text" name="q1" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 2. What does the tag &lt;p&gt; &lt;/p&gt; mean?</p>
                <input type="text" name="q2" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 3. What Does CSS Stand for?</p>
                <input type="text" name="q3" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 4. What is the definition of a computer?</p>
                <input type="text" name="q4" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 5. What is the definition of Programming?</p>
                <input type="text" name="q5" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 6. Give me an example of software used for HTML, PHP, or other offline coding environments?</p>
                <input type="text" name="q6" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 7. Define what an Arduino Board is and how it functions?</p>
                <input type="text" name="q7" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 8. Where does storage save to in HTML? (Clue: Begins with J)</p>
                <input type="text" name="q8" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 9. Define RGB.</p>
                <input type="text" name="q9" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 10. How does a computer work?</p>
                <input type="text" name="q10" required>
                <button type="button" class="nextBtn" onclick="showSection('section2','sectionVideo2')">Finish Part 1</button>
            </div>
        </div>

        <!-- Section Video2 -->
        <div id="sectionVideo2" class="card section">
            <div class="card-header">Assessment Part 1 - Video 2</div>
            <video id="video2" controls>
                <source src="Video2.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <br>
            <button type="button" id="enterBtn2" style="display:none;" onclick="showSection('sectionVideo2','section3')">
                Enter Part 2
            </button>
        </div>

        <!-- Section 3: Questions 11-20 -->
        <div id="section3" class="card section">
            <div class="card-header">Assessment Part 2</div>
            <div class="question">
                <p>Question 11. What is this piece of equipment?</p>
                <img src="1.jfif" alt="Equipment 1" style="max-width:300px; display:block; margin-bottom:10px;">
                <input type="text" name="q11" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 12. What is this piece of equipment?</p>
                <img src="2.jpg" alt="Equipment 2" style="max-width:300px; display:block; margin-bottom:10px;">
                <input type="text" name="q12" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 13. What is this piece of equipment?</p>
                <img src="3.jpg" alt="Equipment 3" style="max-width:300px; display:block; margin-bottom:10px;">
                <input type="text" name="q13" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 14. What is this piece of equipment?</p>
                <img src="4.jpg" alt="Equipment 4" style="max-width:300px; display:block; margin-bottom:10px;">
                <input type="text" name="q14" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 15. What is this piece of equipment?</p>
                <img src="5.jpg" alt="Equipment 5" style="max-width:300px; display:block; margin-bottom:10px;">
                <input type="text" name="q15" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 16. What is this piece of equipment?</p>
                <img src="6.jpg" alt="Equipment 6" style="max-width:300px; display:block; margin-bottom:10px;">
                <input type="text" name="q16" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 17. What is an LED?</p>
                <input type="text" name="q17" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 18. What does the shorter leg of an LED do?</p>
                <input type="text" name="q18" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 19. What does the longer leg of an LED do?</p>
                <input type="text" name="q19" required>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 20. What one piece of equipment runs a PC?</p>
                <input type="text" name="q20" required>
                <button type="button" class="nextBtn" onclick="showSection('section3','sectionVideo3')">Finish Part 2</button>
            </div>
        </div>

        <!-- Section Video3 -->
        <div id="sectionVideo3" class="card section">
            <div class="card-header">Assessment Part 2 - Video 3</div>
            <video id="video3" controls>
                <source src="Video3.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <br>
            <button type="button" id="enterBtn3" style="display:none;" onclick="showSection('sectionVideo3','section5')">
                Enter Part 3
            </button>
        </div>

        <!-- Section 5: Questions 21-26 -->
        <div id="section5" class="card section">
            <div class="card-header">Assessment Part 3</div>
            <div class="question">
                <p>Question 21. What is a computer program?</p>
                <textarea name="q21" class="inputWide" required></textarea>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 22. Can you explain what an algorithm is?</p>
                <textarea name="q22" class="inputWide" required></textarea>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 23. How is data represented in a computer?</p>
                <textarea name="q23" class="inputWide" required></textarea>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 24. What is a variable in programming?</p>
                <textarea name="q24" class="inputWide" required></textarea>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 25. How does the internet connect different computers?</p>
                <textarea name="q25" class="inputWide" required></textarea>
                <button type="button" class="nextBtn">Next</button>
            </div>
            <div class="question">
                <p>Question 26. Differentiate between high-level and low-level programming languages. Provide examples of each and discuss scenarios where one might be preferred over the other.</p>
                <textarea name="q26" class="inputWide" required></textarea>
                <button type="button" class="nextBtn" onclick="showSection('section5','sectionVideo4')">Finish Part 3</button>
            </div>
        </div>

        <!-- Section Video4 -->
        <div id="sectionVideo4" class="card section">
            <div class="card-header">Assessment Part 3 - Video 4 (Final)</div>
            <video id="video4" controls>
                <source src="Video4.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <br>
            <button type="button" id="finalBtn" style="display:none;" onclick="document.forms[0].submit();">
                Submit Assessment
            </button>
        </div>
    </form>
</div>
</body>
</html>
