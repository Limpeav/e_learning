<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch course details
// Fetch course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    echo "Course not found or access denied.";
    exit;
}

$lesson_id = $_GET['lesson_id'] ?? null;

// Fetch lessons
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY id ASC");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();

// Fetch current lesson if selected, otherwise first lesson if any
$current_lesson = null;
if ($lesson_id) {
    foreach ($lessons as $l) {
        if ($l['id'] == $lesson_id) {
            $current_lesson = $l;
            break;
        }
    }
} elseif (!empty($lessons)) {
    $current_lesson = $lessons[0];
}
?>

<div class="row g-4">
    <!-- Sticky Sidebar -->
    <div class="col-lg-4">
        <div class="sticky-top" style="top: 80px; z-index: 900;">
            
            <!-- Quick Actions Card -->
             <div class="card border-0 shadow-sm mb-3 rounded-4 bg-primary text-white overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="position-absolute top-0 end-0 opacity-10" style="transform: translate(30%, -30%);">
                         <i class="bi bi-journal-bookmark-fill" style="font-size: 8rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-white"><?php echo htmlspecialchars($course['title']); ?></h5>
                    <p class="small opacity-75 mb-3">Manage your course content</p>
                    
                    <div class="d-grid gap-2">
                        <a href="add_lesson.php?course_id=<?php echo $course_id; ?>" class="btn btn-light fw-bold text-primary rounded-pill shadow-sm">
                             <i class="bi bi-plus-lg me-2"></i>New Lesson
                        </a>
                        <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-light rounded-pill btn-sm">
                             <i class="bi bi-gear-fill me-2"></i>Settings & Quiz
                        </a>
                    </div>
                </div>
            </div>

            <!-- Curriculum List -->
            <div class="card border-0 shadow-lg mb-4 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-accent mb-0">Curriculum</h6>
                    <span class="badge bg-light text-secondary rounded-pill"><?php echo count($lessons); ?></span>
                </div>
                <div class="list-group list-group-flush" style="max-height: 50vh; overflow-y: auto;">
                    <?php if (empty($lessons)): ?>
                        <div class="text-center py-5 px-4">
                            <i class="bi bi-journal-plus text-primary opacity-25" style="font-size: 3rem;"></i>
                            <p class="text-muted small mt-2 mb-0">No lessons yet. Start adding content!</p>
                        </div>
                    <?php else: ?>
                    <?php foreach ($lessons as $index => $lesson): ?>
                        <?php $is_active = ($current_lesson && $current_lesson['id'] == $lesson['id']); ?>
                        <div class="list-group-item p-3 border-0 border-bottom section-item d-flex align-items-center justify-content-between <?php echo $is_active ? 'active-lesson' : ''; ?>" <?php echo $is_active ? 'id="active-lesson-item"' : ''; ?>>
                            <a href="view_course.php?id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson['id']; ?>" class="d-flex align-items-center text-decoration-none flex-grow-1 text-truncate pe-2">
                                <span class="badge <?php echo $is_active ? '' : 'bg-light text-secondary'; ?> rounded-pill me-3" style="min-width: 25px;"><?php echo $index + 1; ?></span>
                                <span class="<?php echo $is_active ? 'text-white fw-bold force-white' : 'text-accent'; ?> text-truncate">
                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                </span>
                            </a>
                            <div class="d-flex gap-1">
                                <a href="add_lesson.php?course_id=<?php echo $course_id; ?>&edit_id=<?php echo $lesson['id']; ?>" 
                                   class="btn btn-icon btn-sm <?php echo $is_active ? 'text-white' : 'text-muted hover-primary'; ?>" 
                                   title="Edit Lesson">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="../../actions/delete_lesson.php?id=<?php echo $lesson['id']; ?>&course_id=<?php echo $course_id; ?>" 
                                   class="btn btn-icon btn-sm <?php echo $is_active ? 'text-white' : 'text-muted hover-danger'; ?>" 
                                   title="Delete Lesson"
                                   onclick="return confirm('Are you sure you want to delete this lesson?\n\nTitle: <?php echo addslashes($lesson['title']); ?>\n\nThis action cannot be undone.');">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

             <!-- Students Widget (Mini) -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden collapsed-widget">
                 <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#studentCollapse">
                    <h6 class="fw-bold text-accent mb-0 small text-uppercase"><i class="bi bi-people me-2"></i>Students</h6>
                    <i class="bi bi-chevron-down text-muted small"></i>
                 </div>
                 <div class="collapse" id="studentCollapse">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 200px;">
                             <!-- Reuse existing logic for students briefly -->
                             <?php
                                // Re-using the query from before (simplified)
                                $stmt = $pdo->prepare("
                                    SELECT u.username, 
                                    (SELECT score FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.student_id = u.id AND q.course_id = ? ORDER BY qr.taken_at DESC LIMIT 1) as latest_score
                                    FROM users u
                                    JOIN enrollments e ON u.id = e.student_id
                                    WHERE e.course_id = ? LIMIT 20
                                ");
                                $stmt->execute([$course_id, $course_id]);
                                $students = $stmt->fetchAll();
                             ?>
                             <table class="table table-sm mb-0 small">
                                 <tbody>
                                    <?php if(empty($students)): ?>
                                        <tr><td class="text-center text-muted py-3">No students yet</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $s): ?>
                                            <tr>
                                                <td class="ps-4 border-0 text-muted"><?php echo htmlspecialchars($s['username']); ?></td>
                                                <td class="pe-4 border-0 text-end">
                                                    <?php if ($s['latest_score'] !== null): ?>
                                                        <span class="badge <?php echo $s['latest_score'] >= 50 ? 'bg-success' : 'bg-warning'; ?>"><?php echo $s['latest_score']; ?>%</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                 </tbody>
                             </table>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-lg-8">
        <?php if ($current_lesson): ?>
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
                <!-- Lesson Header -->
                <div class="card-header bg-white p-5 border-bottom">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb mb-0 small uppercase font-monospace">
                            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary"><?php echo htmlspecialchars($course['title']); ?></li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-start gap-4">
                        <div>
                            <h1 class="fw-bold text-accent mb-2 display-6"><?php echo htmlspecialchars($current_lesson['title']); ?></h1>
                             <div class="d-flex align-items-center gap-2 text-muted small">
                                <i class="bi bi-clock"></i> Last updated: <?php echo date('M d, Y', strtotime($current_lesson['updated_at'] ?? $current_lesson['created_at'])); ?>
                            </div>
                        </div>
                        <a href="add_lesson.php?course_id=<?php echo $course_id; ?>&edit_id=<?php echo $current_lesson['id']; ?>" 
                           class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center flex-shrink-0">
                            <i class="bi bi-pencil-square me-2"></i> Edit Content
                        </a>
                    </div>
                </div>

                <!-- Lesson Body -->
                <div class="card-body bg-light p-0">
                     <div class="p-5">
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

                        <article class="ms-word-document shadow-sm mx-auto p-5 bg-white rounded-3 border">
                            <?php echo $current_lesson['content']; ?>
                        </article>
                     </div>
                </div>
            </div>
            
            <!-- About Course (Moved to bottom) -->
            <div class="bg-white rounded-4 p-4 border border-light opacity-75">
                <h6 class="fw-bold text-muted text-uppercase small mb-3">About this Course</h6>
                <p class="text-muted mb-0 small"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            </div>

        <?php else: ?>
            <div class="text-center py-5 mt-5">
                <div class="mb-4">
                    <img src="../../public/images/empty_state.svg" alt="No Content" style="width: 200px; opacity: 0.5;" onerror="this.style.display='none'">
                    <i class="bi bi-journal-plus display-1 text-light-gray" onerror="this.style.display='block'"></i>
                </div>
                <h3 class="fw-bold text-accent">Start Building Your Course</h3>
                <p class="text-muted mb-4 col-lg-8 mx-auto">This course is empty using the menu on the left to add your first lesson.</p>
                <a href="add_lesson.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                    <i class="bi bi-plus-circle me-2"></i>Create First Lesson
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Custom Scrollbar for Sidebar */
.list-group::-webkit-scrollbar {
    width: 6px;
}
.list-group::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.list-group::-webkit-scrollbar-thumb {
    background: #d1d5db; 
    border-radius: 3px;
}
.list-group::-webkit-scrollbar-thumb:hover {
    background: #9ca3af; 
}

/* Hover effects */
.hover-primary:hover {
    color: var(--primary-color) !important;
    background: rgba(37, 99, 235, 0.1);
    border-radius: 50%;
}

.hover-danger:hover {
    color: #dc3545 !important;
    background: rgba(220, 53, 69, 0.1);
    border-radius: 50%;
}

.section-item {
    transition: all 0.2s;
}
.section-item:hover {
    background-color: #f8fafc;
    padding-left: 1.25rem !important; /* Slight nudge effect */
}

/* Active Lesson Style */
.active-lesson {
    background-color: var(--primary-color) !important;
    color: white !important;
    border-left: 4px solid #ffffff !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.active-lesson a {
    color: white !important;
}

.active-lesson .text-accent {
    color: white !important;
}

.active-lesson .btn-icon {
    color: rgba(255, 255, 255, 0.7) !important;
}

.active-lesson .btn-icon:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: white !important;
}

/* Explicit Override class */
.force-white {
    color: #ffffff !important;
}

/* Fix for badge in active state */
.active-lesson .badge {
    background-color: white !important;
    color: var(--primary-color) !important;
    box-shadow: none;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        // 1. Auto-scroll sidebar to active lesson
        const activeItem = document.getElementById('active-lesson-item');
        const scrollContainer = activeItem ? activeItem.parentElement : null;
        
        if (activeItem && scrollContainer) {
            // Calculate offset to center it or put it near top
            const offsetTop = activeItem.offsetTop;
            const containerHeight = scrollContainer.clientHeight;
            const itemHeight = activeItem.clientHeight;
            
            // Scroll to show the item
            scrollContainer.scrollTo({
                top: offsetTop - (containerHeight / 2) + (itemHeight / 2),
                behavior: 'smooth'
            });
        }

        // 2. Ensure main window is scrolled to top
        window.scrollTo({ top: 0, behavior: 'instant' });
    }, 100); // Small delay to ensure layout is final
});
</script>

<?php include '../../includes/footer.php'; ?>
