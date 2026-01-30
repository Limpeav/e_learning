<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-grid-1x2-fill me-2 text-primary"></i>Lecturer Portal</h2>
    <div class="text-muted small">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></div>
</div>

<div class="row mb-5 text-center">
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm border-0 bg-primary text-white p-3 card-hover">
            <div class="card-body">
                <div class="mb-3"><i class="fs-1 bi bi-person-badge"></i></div>
                <h5 class="fw-bold">Profile</h5>
                <p class="small opacity-75">Update bio & avatar</p>
                <a href="../auth/profile.php" class="btn btn-light btn-sm rounded-pill mt-2 text-primary fw-bold px-3">Manage</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm border-0 bg-secondary text-white p-3 card-hover">
            <div class="card-body">
                <div class="mb-3"><i class="fs-1 bi bi-chat-left-text"></i></div>
                <h5 class="fw-bold">Queries</h5>
                <p class="small opacity-75">Answer students</p>
                <a href="manage_queries.php" class="btn btn-light btn-sm rounded-pill mt-2 text-secondary fw-bold px-3">View</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm border-0 bg-accent text-white p-3 card-hover">
            <div class="card-body">
                <div class="mb-3"><i class="fs-1 bi bi-journal-plus"></i></div>
                <h5 class="fw-bold">Resources</h5>
                <p class="small opacity-75">Course materials</p>
                <a href="#my-courses" class="btn btn-light btn-sm rounded-pill mt-2 text-accent fw-bold px-3 shadow-none border-primary text-primary bg-white">Add File</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm border-0 bg-primary text-white p-3 card-hover">
            <div class="card-body">
                <div class="mb-3"><i class="fs-1 bi bi-patch-question"></i></div>
                <h5 class="fw-bold">Quizzes</h5>
                <p class="small opacity-75">Create assessments</p>
                <a href="#my-courses" class="btn btn-light btn-sm rounded-pill mt-2 text-primary fw-bold px-3">Go to Quiz</a>
            </div>
        </div>
    </div>
</div>


<div class="d-flex justify-content-between align-items-center mb-4" id="my-courses">
    <h4 class="text-accent fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>My Active Courses</h4>
    <a href="add_course.php" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-plus-lg me-1"></i>New Course
    </a>
</div>


<div class="row">
    <?php if (empty($courses)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-journal-x fs-1 text-muted opacity-25 d-block mb-3"></i>
                    <h5 class="text-muted">No courses created yet.</h5>
                    <p class="text-muted small mb-0">Start sharing your knowledge by creating your first course today!</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($courses as $course): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm overflow-hidden course-card card-hover">
                    <div class="position-relative">
                        <img src="../../public/uploads/<?php echo $course['thumbnail'] ?: 'default_course.png'; ?>" class="card-img-top" alt="Course" style="height: 200px; object-fit: cover;">
                        <div class="position-absolute bottom-0 start-0 m-3">
                            <span class="badge bg-white text-primary rounded-pill px-3 shadow-sm fw-bold">Active Course</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="card-title text-accent fw-bold mb-2"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="card-text text-muted small mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between align-items-center mt-auto pt-3 border-top">
                            <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary rounded-pill px-4">Manage</a>
                            <div class="d-flex gap-1">
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-light border text-secondary rounded-circle" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                <a href="../../actions/delete_course.php?id=<?php echo $course['id']; ?>" class="btn btn-light border text-danger rounded-circle" title="Delete" onclick="return confirm('Are you sure?')"><i class="bi bi-trash-fill"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
