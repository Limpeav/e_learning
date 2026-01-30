<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'teacher') {
    $question_id = $_POST['question_id'];
    $quiz_id = $_POST['quiz_id'];
    $course_id = $_POST['course_id'];
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = ''; // Keeping consistent with 3-option structure
    $correct_option = $_POST['correct_option'];

    // Update query
    $stmt = $pdo->prepare("UPDATE questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE id = ? AND quiz_id = ?");
    
    if ($stmt->execute([$question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $question_id, $quiz_id])) {
        header("Location: ../views/teacher/manage_quiz.php?course_id=$course_id&success=Question updated successfully");
        exit;
    } else {
        header("Location: ../views/teacher/manage_quiz.php?course_id=$course_id&error=Failed to update question");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>