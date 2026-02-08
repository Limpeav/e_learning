<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        die("Unauthorized access.");
    }

    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $teacher_id = $_SESSION['user_id'];

    // Verify ownership
    $stmt = $pdo->prepare("SELECT thumbnail FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    if (!$course) {
        die("Unauthorized course access.");
    }

    $thumbnail = $course['thumbnail'];

    // Handle thumbnail upload
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['thumbnail']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . "." . $ext;
            $destination = '../public/uploads/' . $new_name;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $destination)) {
                $thumbnail = $new_name;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, thumbnail = ? WHERE id = ?");
    if ($stmt->execute([$title, $description, $thumbnail, $course_id])) {
        header("Location: ../views/teacher/dashboard.php?msg=Course updated successfully");
    } else {
        echo "Error updating course.";
    }
}
?>
