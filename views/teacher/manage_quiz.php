<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    header("Location: dashboard.php");
    exit;
}

// Ensure quiz exists for this course
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ?");
$stmt->execute([$course_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    // Create a default quiz if none exists
    $stmt = $pdo->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)");
    $stmt->execute([$course_id, "Final Assessment"]);
    $quiz_id = $pdo->lastInsertId();
} else {
    $quiz_id = $quiz['id'];
}

// Check for edit mode
$edit_mode = false;
$edit_question = null;
if (isset($_GET['edit_question_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ? AND quiz_id = ?");
    $stmt->execute([$_GET['edit_question_id'], $quiz_id]);
    $edit_question = $stmt->fetch();
    if ($edit_question) {
        $edit_mode = true;
    }
}

// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="view_course.php?id=<?php echo $course_id; ?>" class="text-decoration-none">Course</a></li>
        <li class="breadcrumb-item active">Manage Quiz</li>
      </ol>
    </nav>
    <h2 class="text-accent fw-bold"><i class="bi bi-patch-question-fill me-2 text-primary"></i>Manage Quiz: <?php echo htmlspecialchars($quiz['title'] ?? 'Final Assessment'); ?></h2>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card border-0 shadow-lg overflow-hidden position-sticky" style="top: 20px;">
            <div class="card-header bg-accent text-white py-4 px-4">
                <h5 class="card-title mb-0 fw-bold small text-uppercase tracking-wider">
                    <i class="bi <?php echo $edit_mode ? 'bi-pencil-square' : 'bi-plus-circle-fill'; ?> me-2 text-primary"></i>
                    <?php echo $edit_mode ? 'Edit Question' : 'Append Question'; ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="../../actions/<?php echo $edit_mode ? 'edit_question.php' : 'add_question.php'; ?>" method="POST">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="question_id" value="<?php echo $edit_question['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label class="form-label text-accent fw-bold small text-uppercase opacity-75">Question Hypothesis</label>
                        <textarea class="form-control border-0 bg-light p-3 shadow-none" name="question_text" rows="3" placeholder="State your inquiry here..." required><?php echo $edit_mode ? htmlspecialchars($edit_question['question_text']) : ''; ?></textarea>
                    </div>
                    
                    <label class="form-label text-accent fw-bold small text-uppercase opacity-75">Response Alternatives</label>
                    <div class="input-group mb-2 border rounded overflow-hidden">
                        <span class="input-group-text bg-white border-0 text-primary fw-bold">A</span>
                        <input type="text" class="form-control border-0 bg-light shadow-none" name="option_a" placeholder="Option A Content" value="<?php echo $edit_mode ? htmlspecialchars($edit_question['option_a']) : ''; ?>" required>
                    </div>
                    <div class="input-group mb-2 border rounded overflow-hidden">
                        <span class="input-group-text bg-white border-0 text-primary fw-bold">B</span>
                        <input type="text" class="form-control border-0 bg-light shadow-none" name="option_b" placeholder="Option B Content" value="<?php echo $edit_mode ? htmlspecialchars($edit_question['option_b']) : ''; ?>" required>
                    </div>
                    <div class="input-group mb-3 border rounded overflow-hidden">
                        <span class="input-group-text bg-white border-0 text-primary fw-bold">C</span>
                        <input type="text" class="form-control border-0 bg-light shadow-none" name="option_c" placeholder="Option C Content" value="<?php echo $edit_mode ? htmlspecialchars($edit_question['option_c']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-accent fw-bold small text-uppercase opacity-75">Validated Solution</label>
                        <select class="form-select border-0 bg-light py-3 shadow-none" name="correct_option" required>
                            <option value="a" <?php echo ($edit_mode && $edit_question['correct_option'] == 'a') ? 'selected' : ''; ?>>A is Correct</option>
                            <option value="b" <?php echo ($edit_mode && $edit_question['correct_option'] == 'b') ? 'selected' : ''; ?>>B is Correct</option>
                            <option value="c" <?php echo ($edit_mode && $edit_question['correct_option'] == 'c') ? 'selected' : ''; ?>>C is Correct</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                        <i class="bi <?php echo $edit_mode ? 'bi-check-lg' : 'bi-plus-lg'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Update Question' : 'Append to Assessment'; ?>
                    </button>
                    
                    <?php if ($edit_mode): ?>
                        <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-secondary w-100 mt-2 py-2 rounded-pill fw-bold border-0">
                            Cancel Edit
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-accent fw-bold mb-0">Instructional Sequence (<?php echo count($questions); ?>)</h4>
        </div>
        
        <?php if (empty($questions)): ?>
            <div class="card border-0 shadow-sm text-center py-5 bg-light">
                <div class="card-body">
                    <i class="bi bi-patch-question fs-1 text-muted opacity-25 d-block mb-3"></i>
                    <h5 class="text-muted">No items in this assessment yet.</h5>
                    <p class="text-muted small mb-0">Construct your quiz by adding your first question on the left.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="question-stream">
                <?php foreach ($questions as $index => $q): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="d-flex align-items-center">
                                    <span class="bg-primary text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center fw-bold small shadow-sm" style="width: 32px; height: 32px;">
                                        <?php echo ($index + 1); ?>
                                    </span>
                                    <h6 class="text-accent fw-bold mb-0 lh-base">
                                        <?php echo htmlspecialchars($q['question_text']); ?>
                                    </h6>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>&edit_question_id=<?php echo $q['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary border-0 rounded-circle p-2"
                                       title="Edit Question">
                                       <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="../../actions/delete_question.php?id=<?php echo $q['id']; ?>&course_id=<?php echo $course_id; ?>" 
                                       class="btn btn-sm btn-outline-danger border-0 rounded-circle p-2" 
                                       onclick="return confirm('Remove this question from the assessment?')"
                                       title="Delete Question">
                                       <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="row g-3">
                                <?php foreach(['a', 'b', 'c'] as $opt): ?>
                                    <?php $isCorrect = ($q['correct_option'] == $opt); ?>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded border shadow-none small d-flex align-items-center <?php echo $isCorrect ? 'bg-primary bg-opacity-10 border-primary text-primary fw-bold' : 'bg-light border-light-subtle text-secondary'; ?>">
                                            <span class="me-2 opacity-75"><?php echo strtoupper($opt); ?>.</span>
                                            <span class="flex-grow-1"><?php echo htmlspecialchars($q['option_'.$opt]); ?></span>
                                            <?php if($isCorrect): ?> <i class="bi bi-check-circle-fill"></i> <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
