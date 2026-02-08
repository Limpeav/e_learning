<?php
require_once '../../config/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: users.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="card-header bg-accent text-white py-4 px-5 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 fw-bold h4"><i class="bi bi-person-vcard me-2 text-primary"></i>User Intelligence</h2>
                    <p class="small opacity-75 mb-0">Reviewing details for <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                </div>
                <button type="button" class="btn btn-warning rounded-pill px-4 btn-sm me-2" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                    <i class="bi bi-key me-1"></i>Reset Password
                </button>
                <a href="users.php" class="btn btn-outline-light rounded-pill px-4 btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
            <div class="card-body p-5">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="row mb-5">
                    <div class="col-md-4 text-center border-end">
                        <div class="mb-4">
                            <?php if ($user['avatar']): ?>
                                <img src="../../public/uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="rounded-circle shadow-sm border border-4 border-white" style="width: 160px; height: 160px; object-fit: cover;">
                            <?php else: ?>
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-primary shadow-sm border border-4 border-white" style="width: 160px; height: 160px;">
                                    <i class="bi bi-person-fill" style="font-size: 5rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-2">
                            <span class="badge bg-primary px-3 py-2 rounded-pill text-uppercase tracking-wider small">
                                <i class="bi bi-shield-lock me-1"></i><?php echo strtoupper($user['role']); ?>
                            </span>
                        </div>
                        <div class="text-muted small">ID: #USR-<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="col-md-8 ps-md-5">
                        <h5 class="text-accent fw-bold mb-4 border-bottom pb-2">Identification & Bio</h5>
                        <div class="row g-4 mb-4">
                            <div class="col-sm-6">
                                <label class="text-secondary small text-uppercase fw-bold opacity-50">Username</label>
                                <div class="text-accent fw-bold fs-5"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-secondary small text-uppercase fw-bold opacity-50">Email Address</label>
                                <div class="text-accent"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-secondary small text-uppercase fw-bold opacity-50">Membership Date</label>
                                <div class="text-accent"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="text-secondary small text-uppercase fw-bold opacity-50">Biography</label>
                            <p class="text-secondary mt-1"><?php echo nl2br(htmlspecialchars($user['bio'] ?? 'This user hasn\'t provided a biography yet.')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="platform-activity mt-5">
                    <?php if ($user['role'] === 'student'): ?>
                        <h5 class="text-accent fw-bold mb-4 d-flex align-items-center">
                            <i class="bi bi-book-half me-2 text-primary"></i>Learning Progress
                        </h5>
                        <?php
                        $stmt = $pdo->prepare("SELECT c.title, c.id FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = ?");
                        $stmt->execute([$user_id]);
                        $enrollments = $stmt->fetchAll();
                        ?>
                        <div class="row g-3">
                            <?php if ($enrollments): ?>
                                <?php foreach ($enrollments as $e): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded border border-white shadow-sm d-flex align-items-center">
                                            <i class="bi bi-journal-check text-primary me-3 fs-4"></i>
                                            <div class="text-accent fw-bold"><?php echo htmlspecialchars($e['title']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert bg-light border-0 py-3 px-4 text-muted small">Not enrolled in any courses yet.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($user['role'] === 'admin'): ?>
                        <h5 class="text-accent fw-bold mb-4 d-flex align-items-center">
                            <i class="bi bi-briefcase-fill me-2 text-primary"></i>Instructional Catalog
                        </h5>
                        <?php
                        $stmt = $pdo->prepare("SELECT title, id FROM courses WHERE teacher_id = ?");
                        $stmt->execute([$user_id]);
                        $teaching = $stmt->fetchAll();
                        ?>
                        <div class="row g-3">
                            <?php if ($teaching): ?>
                                <?php foreach ($teaching as $t): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded border border-white shadow-sm d-flex align-items-center">
                                            <i class="bi bi-mortarboard text-primary me-3 fs-4"></i>
                                            <div class="text-accent fw-bold"><?php echo htmlspecialchars($t['title']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert bg-light border-0 py-3 px-4 text-muted small">No courses published yet.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-key-fill me-2"></i>Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../actions/reset_user_password.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <p class="text-muted mb-4">You are resetting the password for <strong><?php echo htmlspecialchars($user['username']); ?></strong>.</p>
                    
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        The new password will be known to you. Please share it securely with the user and instruct them to <strong>change it immediately</strong> after logging in.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required minlength="6">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
