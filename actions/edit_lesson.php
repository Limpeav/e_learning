<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = $_POST['lesson_id'];
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    if ($_SESSION['role'] !== 'teacher') {
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
                $uploadSql = ", material_path = ?";
                $params[] = $fileName;
            }
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
