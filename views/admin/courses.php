<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->query("SELECT c.*, u.username as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id ORDER BY c.created_at DESC");
$courses = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Manage Courses</h2>
    <div>
        <a href="../teacher/add_course.php" class="btn btn-primary rounded-pill px-4 me-2">
            <i class="bi bi-plus-lg me-1"></i>New Course
        </a>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-accent text-white">
                <tr>
                    <th class="ps-4">Course Info</th>
                    <th>Lecturer</th>
                    <th>Students</th>
                    <th>Created</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <?php
                        // Fetch enrollment count for this course
                        $estmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
                        $estmt->execute([$course['id']]);
                        $student_count = $estmt->fetchColumn();
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img src="../../public/uploads/<?php echo $course['thumbnail'] ?: 'default_course.png'; ?>" 
                                         class="rounded shadow-sm" width="60" height="45" style="object-fit: cover;">
                                </div>
                                <div>
                                    <div class="fw-bold text-accent"><?php echo htmlspecialchars($course['title']); ?></div>
                                    <div class="text-muted small">ID: #<?php echo $course['id']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-accent"><i class="bi bi-person-badge me-2 text-primary"></i><?php echo htmlspecialchars($course['teacher_name']); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border rounded-pill px-3 fw-normal">
                                <i class="bi bi-people me-1"></i><?php echo $student_count; ?>
                            </span>
                        </td>
                        <td class="text-muted small">
                            <?php echo date('M d, Y', strtotime($course['created_at'])); ?>
                        </td>
                        <td class="text-end pe-4">
                            <a href="../teacher/view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-1">
                                <i class="bi bi-gear me-1"></i>Manage
                            </a>
                            <a href="../teacher/edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-light border text-secondary rounded-circle ms-1 mb-1" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="../../actions/delete_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-light border text-danger rounded-circle ms-1 mb-1" onclick="return confirm('Delete this course?')" title="Delete">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
