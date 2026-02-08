<?php
require_once '../config/db.php';
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    if ($role === 'admin') {
        try {
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: ../views/admin/courses.php?success=Course deleted");
        } catch (PDOException $e) {
            header("Location: ../views/admin/courses.php?error=Deletion failed: " . urlencode($e->getMessage()));
        }
    } else {
        header("Location: ../index.php");
    }
} else {
    header("Location: ../index.php");
}
?>