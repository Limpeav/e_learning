<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch limit/offset for pagination could be added, but listing all for now
$stmt = $pdo->query("
    SELECT e.id as enrollment_id, e.enrolled_at, u.username, u.email, c.title as course_title, c.id as course_id
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
");
$enrollments = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-card-checklist me-2 text-primary"></i>Student Enrollments</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-accent text-white">
                <tr>
                    <th class="ps-4">Student</th>
                    <th>Course</th>
                    <th>Enrolled Date</th>
                    <th class="text-end pe-4">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enrollments)): ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted">No enrollments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-accent"><?php echo htmlspecialchars($enrollment['username']); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($enrollment['email']); ?></div>
                            </td>
                            <td>
                                <div class="text-primary fw-bold"><?php echo htmlspecialchars($enrollment['course_title']); ?></div>
                                <div class="text-muted small">Course ID: #<?php echo $enrollment['course_id']; ?></div>
                            </td>
                            <td class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?>
                            </td>
                            <td class="text-end pe-4">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
