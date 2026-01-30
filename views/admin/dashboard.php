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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard</h2>
    <div class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?php echo date('F d, Y'); ?></div>
</div>

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

<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 text-accent fw-bold">System Integrity Overview</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small text-uppercase text-secondary">
                        <tr>
                            <th class="ps-4">Subsystem</th>
                            <th>Current Status</th>
                            <th>Uptime</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-accent">Authentication Module</div>
                                <div class="text-muted small">Standard OAuth/JWT Logic</div>
                            </td>
                            <td><span class="badge bg-primary rounded-pill px-3 fw-normal">OPERATIONAL</span></td>
                            <td class="text-muted">99.9%</td>
                            <td class="text-end pe-4"><a href="../auth/login.php" class="btn btn-sm btn-outline-primary rounded-pill">Verify</a></td>
                        </tr>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-accent">Course Catalog Engine</div>
                                <div class="text-muted small">Database-driven listing</div>
                            </td>
                            <td><span class="badge bg-primary rounded-pill px-3 fw-normal">ACTIVE</span></td>
                            <td class="text-muted">100%</td>
                            <td class="text-end pe-4"><a href="courses.php" class="btn btn-sm btn-outline-primary rounded-pill">Audit</a></td>
                        </tr>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-accent">Resource Storage</div>
                                <div class="text-muted small">Local / Assets directory</div>
                            </td>
                            <td><span class="badge bg-primary rounded-pill px-3 fw-normal">STABLE</span></td>
                            <td class="text-muted">98.5%</td>
                            <td class="text-end pe-4"><a href="courses.php" class="btn btn-sm btn-outline-primary rounded-pill">Sync</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<?php include '../../includes/footer.php'; ?>
