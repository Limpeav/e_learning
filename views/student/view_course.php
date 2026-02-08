<?php
require_once "../../config/db.php";
include "../../includes/header.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit();
}

$course_id = $_GET["id"] ?? null;
$lesson_id = $_GET["lesson_id"] ?? null;

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
    echo "You are not enrolled in this course.";
    exit();
}

// Fetch course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

// Fetch lessons
$stmt = $pdo->prepare(
    "SELECT * FROM lessons WHERE course_id = ? ORDER BY id ASC",
);
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();

// Fetch queries
$stmt = $pdo->prepare(
    "SELECT q.*, u.username as student_name FROM queries q JOIN users u ON q.student_id = u.id WHERE q.course_id = ? ORDER BY q.created_at DESC",
);
$stmt->execute([$course_id]);
$queries = $stmt->fetchAll();

// Fetch latest quiz result and attempt count
$stmt = $pdo->prepare(
    "SELECT qr.*, q.title as quiz_title FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.student_id = ? AND q.course_id = ? ORDER BY qr.taken_at DESC LIMIT 1",
);
$stmt->execute([$_SESSION["user_id"], $course_id]);
$latest_result = $stmt->fetch();

// Get total quiz attempts
$quiz_attempts = 0;
$best_score = 0;
if ($latest_result) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as attempts, MAX(score) as best_score FROM quiz_results WHERE student_id = ? AND quiz_id = ?",
    );
    $stmt->execute([$_SESSION["user_id"], $latest_result["quiz_id"]]);
    $attempt_data = $stmt->fetch();
    $quiz_attempts = $attempt_data["attempts"];
    $best_score = $attempt_data["best_score"];
}

// Fetch current lesson
$current_lesson = null;
$current_lesson_index = 0;

if ($lesson_id) {
    foreach ($lessons as $k => $l) {
        if ($l["id"] == $lesson_id) {
            $current_lesson = $l;
            $current_lesson_index = $k;
            break;
        }
    }
} elseif (!empty($lessons)) {
    $current_lesson = $lessons[0];
    $current_lesson_index = 0;
}

// Calculate Progress (based on current lesson position)
$total_lessons = count($lessons);
$progress_percent =
    $total_lessons > 0
        ? (($current_lesson_index + 1) / $total_lessons) * 100
        : 0;
?>

<div class="row transition-all" id="main-content-row">
    <!-- Curriculum Sidebar -->
    <div class="col-lg-4 mb-4" id="course-sidebar">
        <div class="sticky-sidebar">
            <div class="card border-0 shadow-lg mb-4 overflow-hidden rounded-4">
                <div class="card-header bg-accent text-white py-4 px-4 border-0">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-20 rounded-3 p-2">
                            <i class="bi bi-collection-play-fill text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 fw-bold">Curriculum</h5>
                            <small class="opacity-75 text-white-50"><?php echo $total_lessons; ?> Lessons</small>
                        </div>
                    </div>

                    <!-- Course Progress -->
                    <div class="mt-2">
                        <div class="d-flex justify-content-between text-white-50 small mb-1">
                            <span>Course Progress</span>
                            <span><?php echo round(
                                $progress_percent,
                            ); ?>%</span>
                        </div>
                        <div class="progress bg-white bg-opacity-10" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $progress_percent; ?>%" aria-valuenow="<?php echo $progress_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="list-group list-group-flush custom-scrollbar" style="max-height: 60vh; overflow-y: auto;">
                    <?php foreach ($lessons as $idx => $lesson): ?>
                        <?php
                        $is_active =
                            $current_lesson &&
                            $current_lesson["id"] == $lesson["id"];
                        // Simple logic: if index < current index, it's "completed" (visually)
                        $is_completed = $idx < $current_lesson_index;
                        ?>
                        <a href="view_course.php?id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson[
    "id"
]; ?>"
                           class="list-group-item list-group-item-action py-3 px-4 border-start-0 border-end-0 <?php echo $is_active
                               ? "border-primary border-start-4"
                               : ""; ?>"
                           style="<?php echo $is_active
                               ? "background-color: rgba(37, 99, 235, 0.05);"
                               : ""; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle <?php echo $is_active
                                        ? "bg-primary text-white"
                                        : ($is_completed
                                            ? "bg-success text-white"
                                            : "bg-light text-secondary"); ?> d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: 700;">
                                        <?php if ($is_completed): ?>
                                            <i class="bi bi-check"></i>
                                        <?php else: ?>
                                            <?php echo $idx + 1; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="<?php echo $is_active
                                        ? "text-primary fw-bold"
                                        : "text-accent"; ?>" style="font-size: 0.95rem; line-height: 1.4;">
                                        <?php echo htmlspecialchars(
                                            $lesson["title"],
                                        ); ?>
                                    </div>
                                </div>
                                <?php if ($is_active): ?>
                                    <i class="bi bi-play-circle-fill text-primary"></i>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                                    <div class="p-4 bg-light border-top">
                                        <?php if ($latest_result): ?>
                                            <div class="mb-3 text-center">
                                                <div class="small text-muted mb-1">Quiz Performance</div>
                                                <div class="d-flex justify-content-around align-items-center">
                                                    <div>
                                                        <div class="fs-4 fw-bold text-<?php echo $best_score >=
                                                        80
                                                            ? "success"
                                                            : ($best_score >= 60
                                                                ? "warning"
                                                                : "danger"); ?>"><?php echo $best_score; ?>%</div>
                                                        <div class="small text-muted">Best Score</div>
                                                    </div>
                                                    <div>
                                                        <div class="fs-4 fw-bold text-primary"><?php echo $quiz_attempts; ?></div>
                                                        <div class="small text-muted">Attempt<?php echo $quiz_attempts >
                                                        1
                                                            ? "s"
                                                            : ""; ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="take_quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-primary w-100 py-2 rounded-pill fw-bold mb-2">
                                                <i class="bi bi-eye me-2"></i>View Results
                                            </a>
                                            <a href="take_quiz.php?course_id=<?php echo $course_id; ?>&retake=1" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
                                                <i class="bi bi-arrow-repeat me-2"></i>Retake Quiz
                                            </a>
                                        <?php else: ?>
                                            <a href="take_quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                                                <i class="bi bi-patch-check-fill me-2"></i>Take Final Quiz
                                            </a>
                                        <?php endif; ?>
                                    </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 shadow-sm mb-4 rounded-4 bg-primary text-white overflow-hidden">
                <div class="card-body p-4 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-3" style="opacity: 0.15; z-index: 0;">
                        <i class="bi bi-chat-dots-fill display-1"></i>
                    </div>
                    <div class="position-relative" style="z-index: 1;">
                        <h5 class="fw-bold mb-2">Need Assistance?</h5>
                        <p class="small opacity-75 mb-4" style="max-width: 85%;">Stuck on a topic? Ask your lecturer directly in the Q&A section.</p>
                        <a href="#qa-section" class="btn btn-white bg-white text-primary rounded-pill px-4 fw-bold btn-sm shadow-sm">Post a Query</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="col-lg-8" id="course-content">
        <?php if ($latest_result): ?>
            <div class="card border-0 shadow-sm mb-5 rounded-4 overflow-hidden" style="background: linear-gradient(45deg, #10b981 0%, #059669 100%); color: white;">
                <div class="card-body p-4 text-center position-relative">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-start flex-grow-1">
                            <span class="badge bg-white bg-opacity-20 rounded-pill px-3 py-1 mb-2">
                                <i class="bi bi-clock-history me-1"></i>ATTEMPT #<?php echo $quiz_attempts; ?>
                            </span>
                            <h4 class="fw-bold mb-1">Latest Score: <?php echo htmlspecialchars(
                                $latest_result["score"],
                            ); ?>%</h4>
                            <p class="mb-0 small opacity-75">Best Score: <?php echo $best_score; ?>% | Total Attempts: <?php echo $quiz_attempts; ?></p>
                        </div>
                        <div class="text-end">
                            <a href="take_quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-white bg-white text-success rounded-pill fw-bold shadow-sm btn-sm mb-2 d-block">
                                <i class="bi bi-eye me-1"></i>View Results
                            </a>
                            <a href="take_quiz.php?course_id=<?php echo $course_id; ?>&retake=1" class="btn btn-outline-light rounded-pill fw-bold btn-sm d-block">
                                <i class="bi bi-arrow-repeat me-1"></i>Retake Quiz
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($current_lesson): ?>
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 fade-in">
                <!-- Header -->
                <div class="card-header bg-white py-4 px-4 px-md-5 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-1 rounded-pill">
                            Lesson <?php echo $current_lesson_index +
                                1; ?> of <?php echo $total_lessons; ?>
                        </span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light btn-sm rounded-circle text-secondary" title="Bookmark"><i class="bi bi-bookmark"></i></button>
                            <button class="btn btn-light btn-sm rounded-circle text-secondary" title="Share"><i class="bi bi-share"></i></button>
                        </div>
                    </div>
                    <h1 class="text-accent fw-bold mb-0 display-6"><?php echo htmlspecialchars(
                        $current_lesson["title"],
                    ); ?></h1>
                </div>

                <!-- Breadcrumb & Top Nav -->
                <div class="px-4 px-md-5 py-3 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 sticky-top shadow-sm" style="top: 0; z-index: 1000;">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-secondary">My Learning</a></li>
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-secondary"><?php echo htmlspecialchars(
                                $course["title"],
                            ); ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Lesson <?php echo $current_lesson_index +
                                1; ?></li>
                        </ol>
                    </nav>
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="toggleFocusMode()" id="focusModeBtn">
                        <i class="bi bi-arrows-fullscreen me-2"></i>Focus Mode
                    </button>
                </div>

                <!-- Lesson Content -->
                <div class="card-body p-0" style="background-color: #f1f5f9;">
                    <div class="py-5 px-3">
                        <?php if (!empty($current_lesson['material_path'])): ?>
                            <div class="mx-auto mb-4" style="max-width: 850px;">
                                <div class="card border-0 shadow-sm bg-primary bg-opacity-10 rounded-3">
                                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-white p-2 rounded-circle me-3 text-primary">
                                                <i class="bi bi-file-earmark-text-fill fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-primary">Lesson Material</h6>
                                                <small class="text-muted">Download attached resource</small>
                                            </div>
                                        </div>
                                        <a href="../../public/uploads/materials/<?php echo htmlspecialchars($current_lesson['material_path']); ?>" 
                                           class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" 
                                           download>
                                            <i class="bi bi-download me-2"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <article class="ms-word-document ck-content shadow-lg mx-auto p-4 p-md-5 bg-white rounded-2" style="max-width: 850px; border-top: 5px solid var(--primary-color) !important; min-height: 800px;">
                            <?php echo $current_lesson["content"]; ?>
                        </article>
                    </div>
                </div>

                <!-- Footer Navigation -->
                <div class="card-footer bg-white py-4 px-4 px-md-5 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <?php if ($current_lesson_index > 0): ?>
                            <a href="view_course.php?id=<?php echo $course_id; ?>&lesson_id=<?php echo $lessons[
    $current_lesson_index - 1
][
    "id"
]; ?>" class="btn btn-light rounded-pill px-4 text-secondary fw-bold shadow-sm hover-primary">
                                <i class="bi bi-arrow-left me-2"></i> Previous
                            </a>
                        <?php else: ?>
                            <button class="btn btn-light rounded-pill px-4 text-muted border-0" disabled>
                                <i class="bi bi-arrow-left me-2"></i> Previous
                            </button>
                        <?php endif; ?>

                        <?php if (
                            $current_lesson_index <
                            $total_lessons - 1
                        ): ?>
                            <a href="view_course.php?id=<?php echo $course_id; ?>&lesson_id=<?php echo $lessons[
    $current_lesson_index + 1
]["id"]; ?>" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                Next Lesson <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        <?php else: ?>
                            <a href="take_quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                                Take Quiz <i class="bi bi-patch-check-fill ms-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm p-5 text-center rounded-4">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="bi bi-journal-x display-1 text-muted opacity-25"></i>
                    </div>
                    <h3 class="text-accent fw-bold">Course Content Missing</h3>
                    <p class="text-muted mx-auto" style="max-width: 400px;">The lecturer hasn't added any lessons to this course curriculum yet. Please check back later.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-5" id="qa-section">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-4 px-4 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                        <i class="bi bi-chat-left-text-fill"></i>
                    </div>
                    <h4 class="text-accent fw-bold mb-0">Course Discussion</h4>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <!-- Post Question Form -->
                    <div class="col-lg-4 border-end bg-light">
                        <div class="p-4 sticky-top" style="top: 20px; z-index: 1;">
                            <h5 class="text-accent fw-bold mb-3">Ask a Question</h5>
                            <p class="text-muted small mb-4">Have a doubt? Post it here and the lecturer or other students might help.</p>
                            <form action="../../actions/post_query.php" method="POST">
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                <div class="mb-3">
                                    <textarea class="form-control border-0 shadow-sm" name="question" rows="5" placeholder="Type your question clearly..." required style="resize: none;"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                                    <i class="bi bi-send-fill me-2"></i>Post Question
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Discussion Feed -->
                    <div class="col-lg-8">
                        <div class="p-4 p-md-5">
                            <h5 class="text-accent fw-bold mb-4">Recent Discussions (<?php echo count(
                                $queries,
                            ); ?>)</h5>

                            <?php if (empty($queries)): ?>
                                <div class="text-center py-5 text-muted bg-light rounded-4 border border-dashed">
                                    <i class="bi bi-chat-quote fs-1 opacity-25 d-block mb-3"></i>
                                    <p class="mb-0">No questions asked yet. Be the first!</p>
                                </div>
                            <?php else: ?>
                                <div class="discussion-feed">
                                    <?php foreach ($queries as $q): ?>
                                        <div class="d-flex align-items-start mb-4">
                                            <div class="bg-gradient bg-primary text-white rounded-circle p-2 me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <span class="fw-bold"><?php echo strtoupper(
                                                    substr(
                                                        $q["student_name"],
                                                        0,
                                                        1,
                                                    ),
                                                ); ?></span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="card border-0 bg-light shadow-sm rounded-4 rounded-top-0">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars(
                                                                $q[
                                                                    "student_name"
                                                                ],
                                                            ); ?></h6>
                                                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo date(
                                                                "M d, Y",
                                                                strtotime(
                                                                    $q[
                                                                        "created_at"
                                                                    ],
                                                                ),
                                                            ); ?></small>
                                                        </div>
                                                        <p class="text-secondary mb-0"><?php echo htmlspecialchars(
                                                            $q["question"],
                                                        ); ?></p>
                                                    </div>
                                                </div>

                                                <?php if ($q["answer"]): ?>
                                                    <div class="d-flex align-items-start mt-3 ms-4">
                                                        <div class="bg-accent text-white rounded-circle p-2 me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                            <i class="bi bi-award-fill small"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="card border-0 bg-primary border-start border-white border-3 rounded-end rounded-bottom shadow-sm text-white">
                                                                <div class="card-body p-3">
                                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                                        <span class="badge bg-white text-primary rounded-pill px-2">Lecturer</span>
                                                                        <small class="text-white-50" style="font-size: 0.75rem;"><?php echo date(
                                                                            "M d",
                                                                            strtotime(
                                                                                $q[
                                                                                    "answered_at"
                                                                                ],
                                                                            ),
                                                                        ); ?></small>
                                                                    </div>
                                                                    <p class="mb-0 text-white small"><?php echo htmlspecialchars(
                                                                        $q[
                                                                            "answer"
                                                                        ],
                                                                    ); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // Prism Highlighting
        document.querySelectorAll('pre').forEach((pre) => {
            pre.classList.add('line-numbers');
        });
        if (typeof Prism !== 'undefined') {
            Prism.highlightAll();
        }

        // Auto-scroll to active lesson
        const activeItem = document.querySelector('.list-group-item.border-primary.border-start-4');
        if (activeItem) {
            activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Wrap Iframes for Responsiveness
        const article = document.querySelector('.ck-content');
        if (article) {
            const iframes = article.querySelectorAll('iframe');
            iframes.forEach(iframe => {
                if (!iframe.parentElement.classList.contains('media')) {
                    const wrapper = document.createElement('div');
                    wrapper.classList.add('media');
                    iframe.parentNode.insertBefore(wrapper, iframe);
                    wrapper.appendChild(iframe);
                }
            });
        }

        // Keyboard Shortcuts for Navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === "ArrowLeft") {
                const prevBtn = document.querySelector('a[title="Previous Lesson (Left Arrow)"]'); // Check specific selector
                if(!prevBtn) {
                     // Fallback to footer button if top nav is hidden/changed
                     const footerPrev = document.querySelector('.card-footer a.text-secondary');
                     if(footerPrev && !footerPrev.classList.contains('disabled')) footerPrev.click();
                } else {
                    prevBtn.click();
                }
            } else if (e.key === "ArrowRight") {
                 const nextBtn = document.querySelector('a[title="Next Lesson (Right Arrow)"]');
                 if(!nextBtn) {
                     const footerNext = document.querySelector('.card-footer a.btn-primary');
                     if(footerNext) footerNext.click();
                 } else {
                     nextBtn.click();
                 }
            }
        });
    });

    function toggleFocusMode() {
        const sidebar = document.getElementById('course-sidebar');
        const content = document.getElementById('course-content');
        const btn = document.getElementById('focusModeBtn');
        const mainRow = document.getElementById('main-content-row');

        if (sidebar.style.display === 'none') {
            // Exit Focus Mode
            sidebar.style.display = 'block';
            content.classList.remove('col-lg-12');
            content.classList.add('col-lg-8');
            btn.innerHTML = '<i class="bi bi-arrows-fullscreen me-2"></i>Focus Mode';
            // Smooth scroll back to position might be needed
        } else {
            // Enter Focus Mode
            sidebar.style.display = 'none';
            content.classList.remove('col-lg-8');
            content.classList.add('col-lg-12');
            btn.innerHTML = '<i class="bi bi-fullscreen-exit me-2"></i>Exit Focus';
        }
    }
</script>

<?php include "../../includes/footer.php"; ?>
