<?php
require_once '../config/db.php';
session_start();

if ($_SESSION['role'] === 'admin' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $course_id = $_GET['course_id'];
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../views/teacher/manage_quiz.php?course_id=$course_id&success=Question deleted");
} else {
    header("Location: ../index.php");
}
?>