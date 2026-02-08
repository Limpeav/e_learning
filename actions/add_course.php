<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
    $teacher_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $thumbnail = null;

    if (empty($title) || empty($description)) {
        header("Location: ../views/teacher/add_course.php?error=Title and Description are required");
        exit;
    }

    // Handle File Upload
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['thumbnail']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($ext), $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $destination = '../public/uploads/' . $new_name;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $destination)) {
                $thumbnail = $new_name;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO courses (teacher_id, title, description, thumbnail) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$teacher_id, $title, $description, $thumbnail])) {
        header("Location: ../views/teacher/dashboard.php?success=Course created successfully");
        exit;
    } else {
        header("Location: ../views/teacher/add_course.php?error=Failed to create course");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>