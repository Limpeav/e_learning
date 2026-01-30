<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'teacher') {
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&error=All fields are required");
        exit;
    }

    // Handle File Upload
    $material_path = null;
    if (isset($_FILES['material']) && $_FILES['material']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../public/uploads/materials/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['material']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
        
        if (in_array($fileExt, $allowedExts)) {
            $fileName = uniqid('material_', true) . '.' . $fileExt;
            if (move_uploaded_file($_FILES['material']['tmp_name'], $uploadDir . $fileName)) {
                $material_path = $fileName;
            }
        }
    }

    // Try to insert with material_path. If column allows NULL, this is fine.
    // Use proper error handling to detect if column exists or not if possible, but PDO w/o verbose mode might just fail.
    // We assume the column exists or will be added.
    $stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, type, content, material_path) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$course_id, $title, $type, $content, $material_path])) {
        header("Location: ../views/teacher/view_course.php?id=$course_id&success=Lesson added successfully");
        exit;
    } else {
        header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&error=Failed to add lesson");
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>