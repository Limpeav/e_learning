<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
    $student_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'];
    $question = $_POST['question'];

    $stmt = $pdo->prepare("INSERT INTO queries (student_id, course_id, question) VALUES (?, ?, ?)");
    if ($stmt->execute([$student_id, $course_id, $question])) {
        header("Location: ../views/student/view_course.php?id=$course_id&msg=query_posted");
    } else {
        header("Location: ../views/student/view_course.php?id=$course_id&error=failed");
    }
} else {
    header('Location: ../index.php');
}
exit();
