<?php
require_once '../config/db.php';
session_start();

if (!isset($_GET['id']) || !isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'];
$course_id = $_GET['course_id'] ?? null;
$role = $_SESSION['role'];

if ($role === 'admin') {
    try {
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->execute([$id]);

        $redirect = $course_id ? "../views/teacher/view_course.php?id=$course_id" : "../views/admin/courses.php";
        header("Location: $redirect&success=Lesson deleted");
    } catch (PDOException $e) {
        $redirect = $course_id ? "../views/teacher/view_course.php?id=$course_id" : "../views/admin/courses.php";
        header("Location: $redirect&error=Deletion failed");
    }
} else {
    header("Location: ../index.php");
}
?>