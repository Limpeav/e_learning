<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: ../views/auth/login.php?error=All fields are required");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../views/admin/dashboard.php");
        } else {
            // Redirect students to the home page
            header("Location: ../index.php");
        }
        exit;
    } else {
        header("Location: ../views/auth/login.php?error=Invalid email or password");
        exit;
    }
} else {
    header("Location: ../views/auth/login.php");
    exit;
}
?>