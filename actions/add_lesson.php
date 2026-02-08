<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $content = trim($_POST['content']);

    if (empty($title)) {
        header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&error=Lesson title is required");
        exit;
    }

    // Handle File Upload
    $material_path = null;
    // Check if file was uploaded (error 4 means no file uploaded)
    if (isset($_FILES['material']) && $_FILES['material']['error'] !== UPLOAD_ERR_NO_FILE) {
        
        if ($_FILES['material']['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds the maximum upload size limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds the form MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            $errorMsg = $errorMessages[$_FILES['material']['error']] ?? 'Unknown file upload error: ' . $_FILES['material']['error'];
            header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&error=" . urlencode($errorMsg));
            exit;
        }

        // Use absolute path relative to this script's location
        // actions/ is one level deep, so dirname(__DIR__) gives the project root
        $uploadDir = dirname(__DIR__) . '/public/uploads/materials/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&error=" . urlencode("Failed to create upload directory"));
                exit;
            }
        }
        
        // Check if directory is writable
        if (!is_writable($uploadDir)) {
            header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&error=" . urlencode("Upload directory is not writable"));
            exit;
        }
        
        $fileExt = strtolower(pathinfo($_FILES['material']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
        
        if (!in_array($fileExt, $allowedExts)) {
            header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&error=" . urlencode("Invalid file type. Allowed: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR"));
            exit;
        }
        
        $fileName = uniqid('material_', true) . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['material']['tmp_name'], $targetPath)) {
            $material_path = $fileName;
        } else {
            header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&error=" . urlencode("Failed to save uploaded file. Check permissions."));
            exit;
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