<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
    $quiz_id = $_POST['quiz_id'];
    $course_id = $_POST['course_id'];
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = ''; // Defaulting to empty string for 3-option quiz
    $correct_option = $_POST['correct_option'];

    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option])) {
        header("Location: ../views/teacher/manage_quiz.php?course_id=$course_id&success=Question added");
        exit;
    } else {
        header("Location: ../views/teacher/manage_quiz.php?course_id=$course_id&error=Failed to add question");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>