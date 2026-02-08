<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = $_POST['lesson_id'];
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    if ($_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit;
    }

    // Verify ownership of the course the lesson belongs to
    $stmt = $pdo->prepare("
        SELECT c.id 
        FROM courses c 
        JOIN lessons l ON c.id = l.course_id 
        WHERE l.id = ? AND c.teacher_id = ?
    ");
    $stmt->execute([$lesson_id, $user_id]);
    if (!$stmt->fetch()) {
        header("Location: ../views/teacher/dashboard.php?error=Unauthorized access");
        exit;
    }

    // Handle File Upload for Update
    $uploadSql = "";
    $params = [$title, $content];
    
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
            header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&edit_id=$lesson_id&error=" . urlencode($errorMsg));
            exit;
        }

        // Use absolute path relative to this script's location
        // actions/ is one level deep, so dirname(__DIR__) gives the project root
        $uploadDir = dirname(__DIR__) . '/public/uploads/materials/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&edit_id=$lesson_id&error=" . urlencode("Failed to create upload directory"));
                exit;
            }
        }
        
        // Check if directory is writable
        if (!is_writable($uploadDir)) {
            header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&edit_id=$lesson_id&error=" . urlencode("Upload directory is not writable"));
            exit;
        }
        
        $fileExt = strtolower(pathinfo($_FILES['material']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
        
        if (!in_array($fileExt, $allowedExts)) {
            header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&edit_id=$lesson_id&error=" . urlencode("Invalid file type. Allowed: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR"));
            exit;
        }
        
        $fileName = uniqid('material_', true) . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['material']['tmp_name'], $targetPath)) {
            $uploadSql = ", material_path = ?";
            $params[] = $fileName;
        } else {
            header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&edit_id=$lesson_id&error=" . urlencode("Failed to save uploaded file. Check permissions."));
            exit;
        }
    }
    
    $params[] = $lesson_id;

    $stmt = $pdo->prepare("UPDATE lessons SET title = ?, content = ? $uploadSql WHERE id = ?");
    
    if ($stmt->execute($params)) {
        header("Location: ../views/teacher/view_course.php?id=$course_id&success=Lesson updated successfully");
    } else {
        header("Location: ../views/teacher/add_lesson.php?course_id=$course_id&edit_id=$lesson_id&error=Could not update lesson");
    }
    exit;
}
?>
