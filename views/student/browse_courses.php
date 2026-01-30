<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch all courses that the student is NOT enrolled in
$stmt = $pdo->prepare("
    SELECT c.*, u.username as teacher_name 
    FROM courses c 
    JOIN users u ON c.teacher_id = u.id 
    WHERE c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ?)
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-compass-fill me-2 text-primary"></i>Explore Courses</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
        <i class="bi bi-grid-fill me-1"></i>My Dashboard
    </a>
</div>

<div class="row">
    <?php if (empty($courses)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-emoji-smile fs-1 text-muted opacity-25 d-block mb-3"></i>
                    <h5 class="text-muted">You're all caught up!</h5>
                    <p class="text-muted small mb-0">You are already enrolled in all available courses. Check your dashboard to continue learning.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($courses as $course): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm overflow-hidden course-card card-hover">
                    <div class="position-relative">
                        <img src="../../public/uploads/<?php echo $course['thumbnail'] ?: 'default_course.png'; ?>" class="card-img-top" alt="Course" style="height: 220px; object-fit: cover;">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-primary rounded-pill px-3 shadow-sm">FREE</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light rounded-circle p-2 me-2">
                                <i class="bi bi-person-fill text-primary"></i>
                            </div>
                            <span class="text-secondary small fw-bold"><?php echo htmlspecialchars($course['teacher_name']); ?></span>
                        </div>
                        <h5 class="card-title text-accent fw-bold mb-3"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="card-text text-muted small mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                        
                        <div class="pt-3 border-top mt-auto">
                            <form action="../../actions/enroll.php" method="POST">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                                    <span>Enroll in Course</span>
                                    <i class="bi bi-arrow-right-short fs-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
