<?php 
require_once 'config/db.php';
include 'includes/header.php'; 

// Fetch all courses with teacher name
$stmt = $pdo->query("
    SELECT c.*, u.username as teacher_name 
    FROM courses c 
    JOIN users u ON c.teacher_id = u.id 
    ORDER BY c.created_at DESC
");
$courses = $stmt->fetchAll();
?>


<div class="mt-3">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['info'])): ?>
        <div class="alert alert-info alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?php echo htmlspecialchars($_GET['info']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
    <!-- ADMIN HOME PAGE -->
    <div class="py-5">
        <div class="row align-items-center mb-5">
            <div class="col-lg-8">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill mb-3">
                    <i class="bi bi-shield-check me-2"></i>Administration Panel
                </span>
                <h1 class="display-4 fw-bold text-accent mb-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p class="lead text-muted mb-0">Here's what's happening on your platform today.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="views/admin/dashboard.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i>Full Dashboard
                </a>
            </div>
        </div>

        <?php
        // Quick Admin Stats
        $total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
        $total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
        $pending_queries_count = $pdo->query("SELECT COUNT(*) FROM queries WHERE answered_at IS NULL")->fetchColumn();
        ?>

        <div class="row mb-5">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-people-fill text-primary fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase tracking-wide">Total Students</div>
                            <div class="fs-2 fw-bold text-accent"><?php echo $total_students; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-journal-check text-success fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase tracking-wide">Active Courses</div>
                            <div class="fs-2 fw-bold text-accent"><?php echo $total_courses; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-chat-dots-fill text-warning fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase tracking-wide">Pending Inquiries</div>
                            <div class="fs-2 fw-bold text-accent"><?php echo $pending_queries_count; ?></div>
                        </div>
                        <?php if ($pending_queries_count > 0): ?>
                            <a href="views/teacher/manage_queries.php" class="btn btn-sm btn-warning rounded-pill ms-auto fw-bold px-3">View</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold text-accent mb-4"><i class="bi bi-lightning-fill text-warning me-2"></i>Quick Actions</h4>
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="views/admin/users.php" class="card card-hover h-100 border-0 shadow-sm rounded-4 text-decoration-none">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-person-plus-fill fs-1 text-primary mb-3 d-block"></i>
                        <h6 class="fw-bold text-accent mb-1">Manage Users</h6>
                        <span class="text-muted small">Add or Edit Accounts</span>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="views/teacher/add_course.php" class="card card-hover h-100 border-0 shadow-sm rounded-4 text-decoration-none">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-journal-plus fs-1 text-success mb-3 d-block"></i>
                        <h6 class="fw-bold text-accent mb-1">Add Course</h6>
                        <span class="text-muted small">Create New Content</span>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="views/admin/enrollments.php" class="card card-hover h-100 border-0 shadow-sm rounded-4 text-decoration-none">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-card-checklist fs-1 text-info mb-3 d-block"></i>
                        <h6 class="fw-bold text-accent mb-1">Enrollments</h6>
                        <span class="text-muted small">Track Progress</span>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="views/teacher/manage_queries.php" class="card card-hover h-100 border-0 shadow-sm rounded-4 text-decoration-none">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-question-circle-fill fs-1 text-danger mb-3 d-block"></i>
                        <h6 class="fw-bold text-accent mb-1">Student Q&A</h6>
                        <span class="text-muted small">Answer Questions</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- PUBLIC / STUDENT HERO SECTION -->
    <div class="text-center mb-5 hero-section">
        <div class="container">
          <h1 class="display-3 fw-bold mb-3">Master Your Future with E-Learning</h1>
          <p class="lead mb-5 opacity-90 mx-auto" style="max-width: 700px;">Access world-class education from anywhere in the world. Learn, grow, and succeed with our expert-led courses designed for your success.</p>
          <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="d-flex justify-content-center gap-3">
                <a class="btn btn-light btn-lg px-5 py-3 rounded-pill fw-bold shadow" href="views/auth/register.php" role="button">Start Learning Now</a>
                <a class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill fw-bold" href="views/auth/login.php" role="button">Sign In</a>
            </div>
          <?php else: ?>
            <div class="bg-white bg-opacity-10 d-inline-block p-4 rounded-4 backdrop-blur shadow-lg">
                <p class="fs-4 mb-4">Ready to continue your journey, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>?</p>
                <a class="btn btn-light btn-lg px-5 py-3 rounded-pill fw-bold shadow" href="views/student/dashboard.php" role="button">Go to Your Dashboard</a>
            </div>
          <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="pb-5">
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <h2 class="display-5 fw-bold text-accent mb-2">Explore Our Courses</h2>
            <p class="text-muted lead">Choose from our wide range of expert-led courses.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                <i class="bi bi-book me-2"></i><?php echo count($courses); ?> Courses Available
            </span>
        </div>
    </div>

    <div class="row">
        <?php if (empty($courses)): ?>
            <div class="col-12 text-center py-5">
                <div class="card border-0 shadow-sm py-5 rounded-4">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <i class="bi bi-journal-x display-1 text-muted opacity-25"></i>
                        </div>
                        <h3 class="text-accent fw-bold">No courses available yet</h3>
                        <p class="text-muted mx-auto" style="max-width: 400px;">Our instructors are currently preparing amazing content for you. Please check back later!</p>
                        <a href="index.php" class="btn btn-outline-primary rounded-pill px-4 mt-3">Refresh Page</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($courses as $course): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden course-card card-hover rounded-4">
                        <div class="position-relative">
                            <?php 
                                $thumbnail = $course['thumbnail'] ? 'public/uploads/' . $course['thumbnail'] : 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?auto=format&fit=crop&q=80&w=1000';
                            ?>
                            <img src="<?php echo $thumbnail; ?>" class="card-img-top" alt="Course" style="height: 220px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-white text-primary rounded-pill px-3 py-2 shadow-sm fw-bold">FREE</span>
                            </div>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="bi bi-person-fill text-primary"></i>
                                </div>
                                <span class="text-secondary small fw-bold">By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                            </div>
                            <h5 class="card-title text-accent fw-bold mb-3"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text text-muted small mb-4 flex-grow-1">
                                <?php 
                                    $desc = htmlspecialchars($course['description']);
                                    echo strlen($desc) > 120 ? substr($desc, 0, 120) . '...' : $desc;
                                ?>
                            </p>
                            
                            <div class="pt-3 border-top mt-auto">
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <a href="views/auth/login.php" class="btn btn-primary w-100 py-3 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                                        <span>Login to Enroll</span>
                                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                                    </a>
                                <?php elseif ($_SESSION['role'] === 'student'): ?>
                                    <?php
                                    // Check if enrolled
                                    $check_stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
                                    $check_stmt->execute([$_SESSION['user_id'], $course['id']]);
                                    $is_enrolled = $check_stmt->fetch();
                                    ?>
                                    <?php if ($is_enrolled): ?>
                                        <a href="views/student/view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-accent w-100 py-3 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2 text-white">
                                            <span>Continue Learning</span>
                                            <i class="bi bi-play-circle fs-5"></i>
                                        </a>
                                    <?php else: ?>
                                        <form action="actions/enroll.php" method="POST" class="m-0">
                                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                                                <span>Enroll in Course</span>
                                                <i class="bi bi-plus-circle fs-5"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center">
                                        <span class="badge bg-light text-secondary p-3 w-100 rounded-pill border">
                                            <i class="bi bi-info-circle me-2"></i>Logged in as <?php echo ucfirst($_SESSION['role']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<?php include 'includes/footer.php'; ?>
