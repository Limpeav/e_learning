<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    $query_id = $_POST['query_id'];
    $answer = $_POST['answer'];
    $now = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("UPDATE queries SET answer = ?, answered_at = ? WHERE id = ?");
    if ($stmt->execute([$answer, $now, $query_id])) {
        header("Location: ../views/teacher/manage_queries.php?msg=answered");
    } else {
        header("Location: ../views/teacher/manage_queries.php?error=failed");
    }
} else {
    header('Location: ../index.php');
}
exit();
