<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../views/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $new_password = $_POST['new_password'] ?? null;
    $confirm_password = $_POST['confirm_password'] ?? null;

    if (!$user_id || !$new_password || !$confirm_password) {
        header("Location: ../views/admin/view_user.php?id=$user_id&error=All fields are required");
        exit();
    }

    if ($new_password !== $confirm_password) {
        header("Location: ../views/admin/view_user.php?id=$user_id&error=Passwords do not match");
        exit();
    }

    if (strlen($new_password) < 6) {
        header("Location: ../views/admin/view_user.php?id=$user_id&error=Password must be at least 6 characters");
        exit();
    }

    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);

        header("Location: ../views/admin/view_user.php?id=$user_id&success=Password reset successfully");
        exit();
    } catch (PDOException $e) {
        header("Location: ../views/admin/view_user.php?id=$user_id&error=Database error: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: ../views/admin/users.php");
    exit();
}
