<?php
/**
 * Quiz Submission Handler
 * Processes quiz submissions and saves results with support for multiple attempts
 */

require_once "../config/db.php";
session_start();

// Validate user is logged in and is a student
if (
    $_SERVER["REQUEST_METHOD"] !== "POST" ||
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "student"
) {
    header("Location: ../index.php");
    exit();
}

// Get form data
$quiz_id = $_POST["quiz_id"] ?? null;
$course_id = $_POST["course_id"] ?? null;
$answers = $_POST["answers"] ?? [];
$student_id = $_SESSION["user_id"];

// Validate required fields
if (!$quiz_id || !$course_id) {
    header("Location: ../views/student/view_course.php?id=$course_id");
    exit();
}

try {
    // Verify student is enrolled in the course
    $stmt = $pdo->prepare(
        "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?",
    );
    $stmt->execute([$student_id, $course_id]);
    if (!$stmt->fetch()) {
        header(
            "Location: ../views/student/take_quiz.php?course_id=$course_id&error=not_enrolled",
        );
        exit();
    }

    // Verify quiz exists and belongs to the course
    $stmt = $pdo->prepare(
        "SELECT id FROM quizzes WHERE id = ? AND course_id = ?",
    );
    $stmt->execute([$quiz_id, $course_id]);
    if (!$stmt->fetch()) {
        header(
            "Location: ../views/student/take_quiz.php?course_id=$course_id&error=invalid_quiz",
        );
        exit();
    }

    // Fetch all questions with correct answers
    $stmt = $pdo->prepare(
        "SELECT id, correct_option FROM questions WHERE quiz_id = ? ORDER BY id ASC",
    );
    $stmt->execute([$quiz_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($questions)) {
        header(
            "Location: ../views/student/take_quiz.php?course_id=$course_id&error=no_questions",
        );
        exit();
    }

    // Validate that all questions have been answered
    $total_questions = count($questions);
    if (count($answers) < $total_questions) {
        header(
            "Location: ../views/student/take_quiz.php?course_id=$course_id&error=incomplete&retake=1",
        );
        exit();
    }

    // Calculate score
    $correct_count = 0;
    $answer_details = [];

    foreach ($questions as $question) {
        $question_id = $question["id"];
        $correct_option = $question["correct_option"];
        $selected_option = $answers[$question_id] ?? null;

        if ($selected_option) {
            $is_correct = $selected_option === $correct_option ? 1 : 0;
            if ($is_correct) {
                $correct_count++;
            }

            $answer_details[] = [
                "question_id" => $question_id,
                "selected_option" => $selected_option,
                "is_correct" => $is_correct,
            ];
        }
    }

    // Calculate percentage score
    $score =
        $total_questions > 0
            ? round(($correct_count / $total_questions) * 100)
            : 0;

    // Begin transaction for atomic operation
    $pdo->beginTransaction();

    // Save overall quiz result (allows multiple attempts)
    $stmt = $pdo->prepare(
        "INSERT INTO quiz_results (student_id, quiz_id, score, taken_at) VALUES (?, ?, ?, NOW())",
    );
    $stmt->execute([$student_id, $quiz_id, $score]);

    // Get the ID of the quiz result we just inserted
    $result_id = $pdo->lastInsertId();

    // Save detailed answers for each question with result_id
    $stmt_ans = $pdo->prepare(
        "INSERT INTO student_answers (student_id, quiz_id, result_id, question_id, selected_option, is_correct, answered_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())",
    );

    foreach ($answer_details as $detail) {
        $stmt_ans->execute([
            $student_id,
            $quiz_id,
            $result_id,
            $detail["question_id"],
            $detail["selected_option"],
            $detail["is_correct"],
        ]);
    }

    // Commit transaction
    $pdo->commit();

    // Get attempt number for display
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as attempt_count FROM quiz_results WHERE student_id = ? AND quiz_id = ?",
    );
    $stmt->execute([$student_id, $quiz_id]);
    $attempt_data = $stmt->fetch();
    $attempt_number = $attempt_data["attempt_count"];

    // Redirect with success message
    header(
        "Location: ../views/student/take_quiz.php?course_id=$course_id&submitted=1&score=$score&attempt=$attempt_number",
    );
    exit();
} catch (PDOException $e) {
    // Rollback transaction on database error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log error (in production, use proper logging)
    error_log("Quiz submission error: " . $e->getMessage());

    // Show detailed error for debugging
    echo "<div style='background: #fee; padding: 20px; border: 2px solid #c00; margin: 20px; border-radius: 8px;'>";
    echo "<h2 style='color: #c00;'>❌ Database Error</h2>";
    echo "<p><strong>Error Message:</strong> " .
        htmlspecialchars($e->getMessage()) .
        "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<hr>";
    echo "<h3>Likely Issues:</h3>";
    echo "<ul>";
    echo "<li>The <code>student_answers</code> table might not exist</li>";
    echo "<li>The <code>result_id</code> column might be missing</li>";
    echo "<li>Foreign key constraints might be failing</li>";
    echo "</ul>";
    echo "<h3>Quick Fix:</h3>";
    echo "<pre style='background: #333; color: #0f0; padding: 10px;'>";
    echo "mysql -u root -p e_learning < fix_student_answers_table.sql";
    echo "</pre>";
    echo "<p><a href='../views/student/take_quiz.php?course_id=$course_id&retake=1' style='display: inline-block; background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>← Back to Quiz</a></p>";
    echo "</div>";
    exit();
} catch (Exception $e) {
    // Rollback transaction on any other error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log error
    error_log("Quiz submission error: " . $e->getMessage());

    // Show detailed error for debugging
    echo "<div style='background: #fee; padding: 20px; border: 2px solid #c00; margin: 20px; border-radius: 8px;'>";
    echo "<h2 style='color: #c00;'>❌ Submission Error</h2>";
    echo "<p><strong>Error Message:</strong> " .
        htmlspecialchars($e->getMessage()) .
        "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "<p><strong>Details:</strong> Check that all form data was submitted correctly.</p>";
    echo "<p><a href='../views/student/take_quiz.php?course_id=$course_id&retake=1' style='display: inline-block; background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>← Back to Quiz</a></p>";
    echo "</div>";
    exit();
}
