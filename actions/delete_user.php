<?php
require_once '../config/db.php';
session_start();

if ($_SESSION['role'] === 'admin' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../views/admin/users.php?success=User deleted");
} else {
    header("Location: ../index.php");
}
?>