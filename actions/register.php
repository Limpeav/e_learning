<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'student'; // Force student role for public registration


    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        header("Location: ../views/auth/register.php?error=All fields are required");
        exit;
    }

    // Check if email or username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        header("Location: ../views/auth/register.php?error=Email or Username already taken");
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $email, $hashedPassword, $role])) {
        header("Location: ../views/auth/login.php?success=Registration successful. Please login.");
        exit;
    } else {
        header("Location: ../views/auth/register.php?error=Registration failed");
        exit;
    }
} else {
    header("Location: ../views/auth/register.php");
    exit;
}
?>