<?php
include '../../includes/header.php';
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Update Profile</h3>
            </div>
            <div class="card-body">
                <form action="../../actions/update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <?php if ($user['avatar']): ?>
                            <img src="../../public/uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="rounded-circle img-thumbnail profile-img shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border border-primary text-primary shadow-sm mb-2" style="width: 150px; height: 150px;">
                                <i class="bi bi-person-fill" style="font-size: 5rem;"></i>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Biography</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        <div class="form-text">Choose a new profile picture.</div>
                    </div>

                    <hr>
                    <h5>Change Password (Optional)</h5>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
