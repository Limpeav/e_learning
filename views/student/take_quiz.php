<?php
require_once "../../config/db.php";
include "../../includes/header.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit();
}

$course_id = $_GET["course_id"] ?? null;
$retake = isset($_GET["retake"]) && $_GET["retake"] === "1";
$view_attempt = $_GET["attempt"] ?? null;

if (!$course_id) {
    header("Location: dashboard.php");
    exit();
}

// Verify enrollment
$stmt = $pdo->prepare(
    "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?",
);
$stmt->execute([$_SESSION["user_id"], $course_id]);
if (!$stmt->fetch()) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>You are not enrolled in this course.</div></div>";
    exit();
}

// Fetch course details
$stmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

// Fetch quiz
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ?");
$stmt->execute([$course_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    echo "<div class='container mt-5'>";
    echo "<div class='alert alert-warning rounded-4 shadow-sm border-0'><i class='bi bi-info-circle me-2'></i>No quiz has been created for this course yet.</div>";
    echo "<a href='view_course.php?id=$course_id' class='btn btn-primary rounded-pill px-4'><i class='bi bi-arrow-left me-2'></i>Back to Course</a>";
    echo "</div>";
    exit();
}

// Fetch ALL quiz attempts for this student
$stmt = $pdo->prepare(
    "SELECT * FROM quiz_results WHERE student_id = ? AND quiz_id = ? ORDER BY taken_at DESC",
);
$stmt->execute([$_SESSION["user_id"], $quiz["id"]]);
$all_attempts = $stmt->fetchAll();

$total_attempts = count($all_attempts);
$has_taken_quiz = $total_attempts > 0;

// Determine which result to display
$current_result = null;
if (!$retake && $has_taken_quiz) {
    if ($view_attempt !== null && isset($all_attempts[$view_attempt])) {
        $current_result = $all_attempts[$view_attempt];
    } else {
        $current_result = $all_attempts[0]; // Most recent by default
    }
}

// Calculate statistics
$best_score = 0;
$average_score = 0;
if ($has_taken_quiz) {
    $best_score = max(array_column($all_attempts, "score"));
    $average_score = round(
        array_sum(array_column($all_attempts, "score")) / $total_attempts,
    );
}

// Fetch questions
$stmt = $pdo->prepare(
    "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC",
);
$stmt->execute([$quiz["id"]]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    echo "<div class='container mt-5'>";
    echo "<div class='alert alert-info rounded-4 shadow-sm border-0'><i class='bi bi-info-circle me-2'></i>The teacher hasn't added any questions to this quiz yet.</div>";
    echo "<a href='view_course.php?id=$course_id' class='btn btn-primary rounded-pill px-4'><i class='bi bi-arrow-left me-2'></i>Back to Course</a>";
    echo "</div>";
    exit();
}

// Fetch answers for current result
$student_answers = [];
if ($current_result) {
    // Check if student_answers table exists and has data
    try {
        // Fetch answers using result_id for accurate attempt matching
        $stmt = $pdo->prepare(
            "SELECT question_id, selected_option, is_correct
             FROM student_answers
             WHERE result_id = ?",
        );
        $stmt->execute([$current_result["id"]]);

        while ($row = $stmt->fetch()) {
            $student_answers[$row["question_id"]] = $row;
        }
    } catch (PDOException $e) {
        // If student_answers table doesn't exist or has issues, continue without answers
        // This allows the page to still work for first-time quiz takers
        error_log("Error fetching student answers: " . $e->getMessage());
    }
}

$total_questions = count($questions);
$is_taking_quiz = $retake || !$has_taken_quiz;
?>

<style>
.quiz-container {
    max-width: 900px;
    margin: 0 auto;
}

.quiz-option {
    transition: all 0.2s ease;
    cursor: pointer;
}

.quiz-option:hover {
    transform: translateX(5px);
}

.quiz-option input[type="radio"]:checked + label {
    background-color: #2563eb !important;
    color: white !important;
    border-color: #2563eb !important;
}

.quiz-option input[type="radio"]:checked + label .option-letter {
    background-color: white !important;
    color: #2563eb !important;
}

.quiz-option.correct label {
    background-color: #10b981 !important;
    color: white !important;
    border-color: #10b981 !important;
}

.quiz-option.incorrect label {
    background-color: #ef4444 !important;
    color: white !important;
    border-color: #ef4444 !important;
}

.quiz-option.correct label .option-letter {
    background-color: white !important;
    color: #10b981 !important;
}

.quiz-option.incorrect label .option-letter {
    background-color: white !important;
    color: #ef4444 !important;
}

.quiz-option.disabled label {
    opacity: 0.4;
    cursor: not-allowed;
}

.progress-bar-animated {
    animation: progress-animation 1s ease-in-out;
}

@keyframes progress-animation {
    from { width: 0; }
}

.question-card {
    animation: slideInUp 0.4s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.score-display {
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.attempt-badge {
    cursor: pointer;
    transition: all 0.2s ease;
}

.attempt-badge:hover {
    transform: scale(1.05);
}

.attempt-badge.active {
    border: 2px solid #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}
</style>

<div class="container mt-4 mb-5 quiz-container">
    <!-- Back Button -->
    <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn btn-outline-secondary rounded-pill mb-4 px-4">
        <i class="bi bi-arrow-left me-2"></i>Back to Course
    </a>

    <!-- Success/Error Messages -->
    <?php if (isset($_GET["submitted"])): ?>
    <div class="alert alert-success rounded-4 shadow-sm border-0 d-flex align-items-center mb-4 alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
        <div class="flex-grow-1">
            <div class="fw-bold">Quiz Submitted Successfully!</div>
            <div class="small">Your answers have been recorded. Scroll down to review your results.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
    <div class="alert alert-danger rounded-4 shadow-sm border-0 d-flex align-items-center mb-4 alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
        <div class="flex-grow-1">
            <div class="fw-bold">Submission Failed</div>
            <div class="small">There was an error submitting your quiz. Please try again.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if ($retake): ?>
    <div class="alert alert-info rounded-4 shadow-sm border-0 d-flex align-items-center mb-4">
        <i class="bi bi-arrow-repeat fs-4 me-3 text-info"></i>
        <div class="flex-grow-1">
            <div class="fw-bold">Retaking Quiz - Attempt #<?php echo $total_attempts +
                1; ?></div>
            <div class="small">Your previous attempts are saved. This is a new attempt to improve your score.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quiz Header -->
    <div class="text-center mb-5">
        <div class="mb-3">
            <?php if (!$is_taking_quiz): ?>
                <span class="badge bg-success px-4 py-2 rounded-pill fs-6">
                    <i class="bi bi-check-circle-fill me-2"></i>Completed
                </span>
            <?php else: ?>
                <span class="badge bg-primary px-4 py-2 rounded-pill fs-6">
                    <i class="bi bi-pencil-square me-2"></i><?php echo $retake
                        ? "Retaking"
                        : "In Progress"; ?>
                </span>
            <?php endif; ?>
        </div>

        <h2 class="fw-bold text-accent mb-2">
            <i class="bi bi-patch-question-fill text-primary me-2"></i>
            <?php echo htmlspecialchars($quiz["title"]); ?>
        </h2>

        <p class="text-muted mb-4"><?php echo htmlspecialchars(
            $course["title"],
        ); ?></p>

        <div class="d-flex justify-content-center gap-4 flex-wrap">
            <div class="d-flex align-items-center">
                <i class="bi bi-list-ol text-primary me-2"></i>
                <span class="fw-semibold"><?php echo $total_questions; ?> Questions</span>
            </div>
            <?php if ($has_taken_quiz): ?>
                <div class="d-flex align-items-center">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    <span class="fw-semibold"><?php echo $total_attempts; ?> Attempt<?php echo $total_attempts >
 1
     ? "s"
     : ""; ?></span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-trophy text-warning me-2"></i>
                    <span class="fw-semibold">Best: <?php echo $best_score; ?>%</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$is_taking_quiz && $has_taken_quiz): ?>
        <!-- Attempt History -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-clock-history text-primary me-2"></i>Attempt History
                </h5>
                <div class="d-flex gap-2 flex-wrap">
                    <?php foreach ($all_attempts as $idx => $attempt): ?>
                        <?php
                        $attempt_num = $total_attempts - $idx;
                        $is_current =
                            ($view_attempt === null && $idx === 0) ||
                            $view_attempt == $idx;
                        $score_color =
                            $attempt["score"] >= 80
                                ? "success"
                                : ($attempt["score"] >= 60
                                    ? "warning"
                                    : "danger");
                        $is_best = $attempt["score"] == $best_score;
                        ?>
                        <a href="take_quiz.php?course_id=<?php echo $course_id; ?>&attempt=<?php echo $idx; ?>"
                           class="attempt-badge badge bg-<?php echo $score_color; ?> bg-opacity-10 text-<?php echo $score_color; ?> border border-<?php echo $score_color; ?> px-3 py-2 text-decoration-none <?php echo $is_current
     ? "active"
     : ""; ?>">
                            <div class="fw-bold">Attempt #<?php echo $attempt_num; ?></div>
                            <div class="small">
                                <?php echo $attempt["score"]; ?>%
                                <?php if (
                                    $is_best
                                ): ?><i class="bi bi-trophy-fill ms-1"></i><?php endif; ?>
                            </div>
                            <div class="small opacity-75"><?php echo date(
                                "M j, g:i A",
                                strtotime($attempt["taken_at"]),
                            ); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="card border-0 shadow-lg rounded-4 mb-5 overflow-hidden score-display">
            <div class="card-body p-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="row text-white text-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="opacity-75 small text-uppercase mb-2" style="letter-spacing: 1px;">Current Score</div>
                        <div class="display-4 fw-bold"><?php echo $current_result[
                            "score"
                        ]; ?>%</div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="opacity-75 small text-uppercase mb-2" style="letter-spacing: 1px;">Best Score</div>
                        <div class="display-4 fw-bold"><?php echo $best_score; ?>%</div>
                    </div>
                    <div class="col-md-4">
                        <div class="opacity-75 small text-uppercase mb-2" style="letter-spacing: 1px;">Average</div>
                        <div class="display-4 fw-bold"><?php echo $average_score; ?>%</div>
                    </div>
                </div>

                <?php
                $correct_answers = 0;
                foreach ($student_answers as $ans) {
                    if ($ans["is_correct"]) {
                        $correct_answers++;
                    }
                }
                ?>

                <div class="text-center mt-4 text-white">
                    <p class="mb-0 fs-5 opacity-90">
                        You got <strong><?php echo $correct_answers; ?> out of <?php echo $total_questions; ?></strong> questions correct
                    </p>

                    <div class="mt-4">
                        <?php if ($current_result["score"] >= 80): ?>
                            <div class="badge bg-success bg-opacity-25 text-white px-4 py-2 fs-6">
                                <i class="bi bi-trophy-fill me-2"></i>Excellent Performance!
                            </div>
                        <?php elseif ($current_result["score"] >= 60): ?>
                            <div class="badge bg-warning bg-opacity-25 text-white px-4 py-2 fs-6">
                                <i class="bi bi-star-fill me-2"></i>Good Job!
                            </div>
                        <?php else: ?>
                            <div class="badge bg-danger bg-opacity-25 text-white px-4 py-2 fs-6">
                                <i class="bi bi-arrow-repeat me-2"></i>Keep Practicing!
                            </div>
                        <?php endif; ?>
                    </div>

                    <p class="small mt-3 mb-0 opacity-75">
                        <i class="bi bi-calendar3 me-2"></i>Taken on <?php echo date(
                            'F j, Y \a\t g:i A',
                            strtotime($current_result["taken_at"]),
                        ); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h4 class="fw-bold text-accent mb-3">
                <i class="bi bi-clipboard-check text-primary me-2"></i>Review Your Answers
                <?php if ($view_attempt !== null): ?>
                    <span class="badge bg-secondary ms-2">Attempt #<?php echo $total_attempts -
                        $view_attempt; ?></span>
                <?php endif; ?>
            </h4>
            <p class="text-muted">Review each question below to learn from your mistakes and understand the correct answers.</p>
        </div>
    <?php else: ?>
        <!-- Quiz Instructions -->
        <div class="alert alert-info rounded-4 border-0 shadow-sm mb-5" style="background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);">
            <div class="d-flex">
                <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                <div>
                    <h5 class="fw-bold text-info mb-2">Instructions</h5>
                    <ul class="mb-0 text-info small">
                        <li>Read each question carefully before selecting your answer</li>
                        <li>You must answer all questions before submitting</li>
                        <li>Once submitted, you can review your answers and score</li>
                        <?php if ($has_taken_quiz): ?>
                            <li><strong>This is attempt #<?php echo $total_attempts +
                                1; ?></strong> - Your previous scores are saved</li>
                        <?php endif; ?>
                        <li>Make sure to click the "Submit Quiz" button at the end</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quiz Form -->
    <form action="<?php echo $is_taking_quiz
        ? "../../actions/submit_quiz.php"
        : "#"; ?>" method="POST" id="quizForm">
        <?php if ($is_taking_quiz): ?>
            <input type="hidden" name="quiz_id" value="<?php echo $quiz[
                "id"
            ]; ?>">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <?php endif; ?>

        <!-- Questions -->
        <?php foreach ($questions as $index => $question): ?>
            <?php
            $user_answer = $current_result
                ? $student_answers[$question["id"]]["selected_option"] ?? null
                : null;
            $is_correct = $current_result
                ? $student_answers[$question["id"]]["is_correct"] ?? false
                : false;
            $correct_option = $question["correct_option"];
            ?>

            <div class="card border-0 shadow-sm rounded-4 mb-4 question-card" style="animation-delay: <?php echo $index *
                0.1; ?>s">
                <!-- Question Header -->
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                                <?php echo $index + 1; ?>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold text-accent"><?php echo htmlspecialchars(
                                $question["question_text"],
                            ); ?></h5>
                            <?php if ($current_result): ?>
                                <?php if ($is_correct): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>Correct Answer
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i>Incorrect Answer
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="card-body p-4 pt-2">
                    <?php foreach (["a", "b", "c", "d"] as $opt_key): ?>
                        <?php
                        $option_text = $question["option_$opt_key"];
                        $input_id = "q{$question["id"]}_opt_{$opt_key}";

                        $is_user_choice = $user_answer === $opt_key;
                        $is_correct_opt = $correct_option === $opt_key;

                        // Determine styling classes
                        $container_class = "quiz-option mb-3";
                        $label_class =
                            "btn btn-outline-secondary w-100 text-start p-3 rounded-3 d-flex align-items-center";
                        $icon_html = "";

                        if ($current_result) {
                            if ($is_correct_opt) {
                                $container_class .= " correct";
                                $icon_html =
                                    "<i class='bi bi-check-circle-fill fs-5 ms-auto'></i>";
                            } elseif ($is_user_choice && !$is_correct_opt) {
                                $container_class .= " incorrect";
                                $icon_html =
                                    "<i class='bi bi-x-circle-fill fs-5 ms-auto'></i>";
                            } else {
                                $container_class .= " disabled";
                            }
                        }
                        ?>

                        <div class="<?php echo $container_class; ?>">
                            <input type="radio"
                                   class="btn-check"
                                   name="answers[<?php echo $question[
                                       "id"
                                   ]; ?>]"
                                   value="<?php echo $opt_key; ?>"
                                   id="<?php echo $input_id; ?>"
                                   <?php echo $is_taking_quiz
                                       ? "required"
                                       : "disabled"; ?>
                                   <?php echo $is_user_choice
                                       ? "checked"
                                       : ""; ?>
                                   autocomplete="off">

                            <label class="<?php echo $label_class; ?>" for="<?php echo $input_id; ?>">
                                <span class="option-letter rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                    <?php echo strtoupper($opt_key); ?>
                                </span>
                                <span class="flex-grow-1"><?php echo htmlspecialchars(
                                    $option_text,
                                ); ?></span>
                                <?php echo $icon_html; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Show correct answer if user got it wrong -->
                <?php if ($current_result && !$is_correct): ?>
                    <div class="card-footer bg-success bg-opacity-10 border-0 p-3">
                        <div class="d-flex align-items-center text-success">
                            <i class="bi bi-lightbulb-fill me-2"></i>
                            <small class="fw-semibold">
                                The correct answer is: <strong>Option <?php echo strtoupper(
                                    $correct_option,
                                ); ?></strong> -
                                <?php echo htmlspecialchars(
                                    $question["option_$correct_option"],
                                ); ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Submit or Action Buttons -->
        <div class="text-center mt-5 pt-4 pb-5">
            <?php if ($is_taking_quiz): ?>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg" onclick="return confirmSubmit()">
                    <i class="bi bi-check2-circle me-2"></i>Submit Quiz
                </button>
                <p class="text-muted small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>Make sure you've answered all questions before submitting
                </p>
            <?php else: ?>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn btn-outline-primary btn-lg rounded-pill px-5 py-3 fw-bold">
                        <i class="bi bi-arrow-left me-2"></i>Back to Course
                    </a>
                    <a href="take_quiz.php?course_id=<?php echo $course_id; ?>&retake=1" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg" onclick="return confirmRetake()">
                        <i class="bi bi-arrow-repeat me-2"></i>Retake Quiz
                    </a>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>Retake the quiz to improve your score. Your previous attempts will be saved.
                </p>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($is_taking_quiz): ?>
<script>
function confirmSubmit() {
    // Check if all questions are answered
    const form = document.getElementById('quizForm');
    const questionGroups = form.querySelectorAll('[name^="answers"]');
    const uniqueQuestions = new Set();

    questionGroups.forEach(input => {
        const name = input.getAttribute('name');
        uniqueQuestions.add(name);
    });

    let answeredCount = 0;
    uniqueQuestions.forEach(name => {
        const checked = form.querySelector(`[name="${name}"]:checked`);
        if (checked) answeredCount++;
    });

    const totalQuestions = <?php echo $total_questions; ?>;

    if (answeredCount < totalQuestions) {
        alert(`Please answer all questions before submitting.\n\nAnswered: ${answeredCount}/${totalQuestions}`);
        return false;
    }

    // Confirm submission
    <?php if ($retake): ?>
    return confirm(`Are you sure you want to submit Attempt #<?php echo $total_attempts +
        1; ?>?\n\nYou have answered all ${totalQuestions} questions.\n\nClick OK to submit or Cancel to review your answers.`);
    <?php else: ?>
    return confirm(`Are you sure you want to submit your quiz?\n\nYou have answered all ${totalQuestions} questions.\n\nClick OK to submit or Cancel to review your answers.`);
    <?php endif; ?>
}

// Smooth scroll to top on page load if submitted
<?php if (isset($_GET["submitted"])): ?>
window.scrollTo({ top: 0, behavior: 'smooth' });
<?php endif; ?>
</script>
<?php else: ?>
<script>
function confirmRetake() {
    return confirm('Are you sure you want to retake this quiz?\n\nYour current score: <?php echo $current_result[
        "score"
    ]; ?>%\nYour best score: <?php echo $best_score; ?>%\n\nThis will be Attempt #<?php echo $total_attempts +
    1; ?>. All your previous attempts will be saved.');
}
</script>
<?php endif; ?>

<?php include "../../includes/footer.php"; ?>
