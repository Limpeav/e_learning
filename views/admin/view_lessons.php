<?php
require_once '../../config/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    header("Location: courses.php");
    exit;
}

// Get course info
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

// Get lessons
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Lessons: <?php echo htmlspecialchars($course['title']); ?></h2>
    <a href="courses.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-accent text-white">
                <tr>
                    <th class="ps-4">Lesson Title</th>
                    <th>Type</th>
                    <th>Content Preview</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lessons)): ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted">No lessons found for this course.</td></tr>
                <?php else: ?>
                    <?php foreach ($lessons as $lesson): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-accent"><?php echo htmlspecialchars($lesson['title']); ?></div>
                                <div class="text-muted small">ID: #<?php echo $lesson['id']; ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border fw-normal">DOCUMENTATION</span>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 300px;">
                                    <?php echo htmlspecialchars(strip_tags($lesson['content'])); ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="../../actions/delete_lesson.php?id=<?php echo $lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Delete this lesson?')">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
