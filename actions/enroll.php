<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'student') {
    $student_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'];

    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->execute([$student_id, $course_id]);
    if ($stmt->fetch()) {
        header("Location: ../index.php?info=Already enrolled");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
    if ($stmt->execute([$student_id, $course_id])) {
        header("Location: ../index.php?success=Enrolled successfully");
        exit;
    } else {
        header("Location: ../index.php?error=Enrollment failed");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>