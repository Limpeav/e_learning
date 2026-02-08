<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Get stats
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$course_count = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$enrollment_count = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

// --- FETCH RECENT ADMIN ACTIVITY ---
// 1. New Users
// 2. New Courses
// 3. New Lessons
$admin_activity = [];

// New Users
$stmt = $pdo->query("SELECT id, username, 'joined the platform' as action, created_at, 'user' as type FROM users ORDER BY created_at DESC LIMIT 5");
while($row = $stmt->fetch()) {
    $admin_activity[] = $row;
}

// New Courses
$stmt = $pdo->query("SELECT id, title as username, 'was created' as action, created_at, 'course' as type FROM courses ORDER BY created_at DESC LIMIT 5");
while($row = $stmt->fetch()) {
    $admin_activity[] = $row;
}

// Sort combined admin/system activity by date
usort($admin_activity, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$admin_activity = array_slice($admin_activity, 0, 7);


// --- FETCH RECENT STUDENT ACTIVITY ---
// 1. Enrollments
// 2. Quiz Results
$student_activity = [];

// Enrollments
$sql_enrollments = "SELECT u.username, c.title as course_title, e.enrolled_at as created_at, 'enrollment' as type 
                    FROM enrollments e 
                    JOIN users u ON e.student_id = u.id 
                    JOIN courses c ON e.course_id = c.id 
                    ORDER BY e.enrolled_at DESC LIMIT 5";
$stmt = $pdo->query($sql_enrollments);
while($row = $stmt->fetch()) {
    $student_activity[] = [
        'username' => $row['username'],
        'action' => "enrolled in <strong>{$row['course_title']}</strong>",
        'created_at' => $row['created_at'],
        'type' => 'enrollment'
    ];
}

// Quiz Results
$sql_quizzes = "SELECT u.username, q.title as quiz_title, qr.score, qr.taken_at as created_at, 'quiz' as type 
                FROM quiz_results qr 
                JOIN users u ON qr.student_id = u.id 
                JOIN quizzes q ON qr.quiz_id = q.id 
                ORDER BY qr.taken_at DESC LIMIT 5";
$stmt = $pdo->query($sql_quizzes);
while($row = $stmt->fetch()) {
    $student_activity[] = [
        'username' => $row['username'],
        'action' => "completed quiz <strong>{$row['quiz_title']}</strong> (Score: {$row['score']}%)",
        'created_at' => $row['created_at'],
        'type' => 'quiz'
    ];
}

// Sort student activity
usort($student_activity, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$student_activity = array_slice($student_activity, 0, 7);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard</h2>
    <div class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?php echo date('F d, Y'); ?></div>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm bg-primary text-white p-2">
            <div class="card-body d-flex align-items-center">
                <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                    <i class="bi bi-people fs-2"></i>
                </div>
                <div>
                    <h5 class="mb-0 opacity-75 small">Total Users</h5>
                    <div class="fs-2 fw-bold"><?php echo $user_count; ?></div>
                    <a href="users.php" class="text-white text-decoration-none small opacity-75 hover-opacity-100">
                        Manage Users <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm bg-secondary text-white p-2">
            <div class="card-body d-flex align-items-center">
                <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                    <i class="bi bi-journal-bookmark fs-2"></i>
                </div>
                <div>
                    <h5 class="mb-0 opacity-75 small">Active Courses</h5>
                    <div class="fs-2 fw-bold"><?php echo $course_count; ?></div>
                    <a href="courses.php" class="text-white text-decoration-none small opacity-75 hover-opacity-100">
                        Manage Courses <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12 mb-4">
        <div class="card border-0 shadow-sm bg-accent text-white p-2">
            <div class="card-body d-flex align-items-center">
                <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                    <i class="bi bi-graph-up-arrow fs-2 text-primary"></i>
                </div>
                <div>
                    <h5 class="mb-0 opacity-75 small">Total Enrollments</h5>
                    <div class="fs-2 fw-bold"><?php echo $enrollment_count; ?></div>
                    <a href="enrollments.php" class="text-white text-decoration-none small opacity-75 hover-opacity-100">
                        View Details <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<h4 class="text-accent fw-bold mb-3 mt-2"><i class="bi bi-grid-fill me-2 text-primary"></i>Management Console</h4>
<div class="row">
    <div class="col-xl-3 col-sm-6 mb-4">
        <a href="users.php" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center py-4">
                    <div class="bg-light rounded-circle p-3 d-inline-block mb-3">
                        <i class="bi bi-person-gear fs-2 text-primary"></i>
                    </div>
                    <h5 class="text-accent fw-bold mb-1">Users</h5>
                    <p class="text-muted small mb-0">Manage roles & access</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6 mb-4">
        <a href="courses.php" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center py-4">
                    <div class="bg-light rounded-circle p-3 d-inline-block mb-3">
                        <i class="bi bi-journal-check fs-2 text-secondary"></i>
                    </div>
                    <h5 class="text-accent fw-bold mb-1">Courses</h5>
                    <p class="text-muted small mb-0">Curriculum oversight</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6 mb-4">
        <a href="enrollments.php" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center py-4">
                    <div class="bg-light rounded-circle p-3 d-inline-block mb-3">
                        <i class="bi bi-card-checklist fs-2 text-accent"></i>
                    </div>
                    <h5 class="text-accent fw-bold mb-1">Enrollments</h5>
                    <p class="text-muted small mb-0">Track student progress</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6 mb-4">
        <a href="users.php" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-hover">
                <div class="card-body text-center py-4">
                    <div class="bg-light rounded-circle p-3 d-inline-block mb-3">
                        <i class="bi bi-plus-circle-dotted fs-2 text-primary"></i>
                    </div>
                    <h5 class="text-accent fw-bold mb-1">New Account</h5>
                    <p class="text-muted small mb-0">Add admin/lecturer</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="row mt-4">
    <!-- Student Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="bi bi-mortarboard-fill text-success fs-5 me-2"></i>
                <h5 class="card-title mb-0 text-accent fw-bold">Recent Student Activity</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($student_activity)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        No recent student activity found.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($student_activity as $activity): ?>
                            <div class="list-group-item px-4 py-3 border-light">
                                <div class="d-flex align-items-start">
                                    <div class="me-3 mt-1">
                                        <?php if ($activity['type'] == 'enrollment'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-circle p-2">
                                                <i class="bi bi-journal-plus"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-circle p-2">
                                                <i class="bi bi-pencil-square"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1 text-accent fw-semibold"><?php echo htmlspecialchars($activity['username']); ?></h6>
                                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></small>
                                        </div>
                                        <div class="small text-muted mb-0">
                                            <?php echo $activity['action']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Admin/System Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="bi bi-shield-lock-fill text-primary fs-5 me-2"></i>
                <h5 class="card-title mb-0 text-accent fw-bold">Admin & System Activity</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($admin_activity)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        No recent system activity found.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($admin_activity as $activity): ?>
                            <div class="list-group-item px-4 py-3 border-light">
                                <div class="d-flex align-items-start">
                                    <div class="me-3 mt-1">
                                        <?php if ($activity['type'] == 'user'): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-circle p-2">
                                                <i class="bi bi-person-plus"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                                                <i class="bi bi-journal-plus"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1 text-accent fw-semibold"><?php echo htmlspecialchars($activity['username']); ?></h6>
                                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></small>
                                        </div>
                                        <div class="small text-muted mb-0">
                                            <?php echo $activity['action']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
