<?php
require_once '../config/db.php';
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    if ($role === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: ../views/admin/courses.php?success=Course deleted");
    } elseif ($role === 'teacher') {
        try {
            $pdo->beginTransaction();

            // Verify ownership
            $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
            $stmt->execute([$id, $user_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Access denied");
            }

            // Delete lessons
            $stmt = $pdo->prepare("DELETE FROM lessons WHERE course_id = ?");
            $stmt->execute([$id]);

            // Delete enrollments
            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id = ?");
            $stmt->execute([$id]);

            // Delete queries
            $stmt = $pdo->prepare("DELETE FROM queries WHERE course_id = ?");
            $stmt->execute([$id]);

            // Handle quizzes and their dependencies
            // Get quiz IDs for this course
            $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE course_id = ?");
            $stmt->execute([$id]);
            $quiz_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($quiz_ids)) {
                $in_query = implode(',', array_fill(0, count($quiz_ids), '?'));
                
                // Delete quiz results
                $stmt = $pdo->prepare("DELETE FROM quiz_results WHERE quiz_id IN ($in_query)");
                $stmt->execute($quiz_ids);

                // Delete questions
                $stmt = $pdo->prepare("DELETE FROM questions WHERE quiz_id IN ($in_query)");
                $stmt->execute($quiz_ids);

                // Delete quizzes
                $stmt = $pdo->prepare("DELETE FROM quizzes WHERE course_id = ?");
                $stmt->execute([$id]);
            }

            // Finally, delete the course
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);

            $pdo->commit();
            header("Location: ../views/teacher/dashboard.php?success=Course deleted");
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: ../views/teacher/dashboard.php?error=Deletion failed: " . urlencode($e->getMessage()));
        }
    } else {
        header("Location: ../index.php");
    }
} else {
    header("Location: ../index.php");
}
?>