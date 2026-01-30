<?php include '../../includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary p-4 text-center text-white">
                <i class="bi bi-person-plus fs-1"></i>
                <h3 class="mt-2">Join E-Learning</h3>
                <p class="small opacity-75">Join our learning community today</p>
            </div>
            <div class="card-body p-4">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger border-0 shadow-sm"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                
                <form action="../../actions/register.php" method="POST">
                    <!-- Forced Role -->
                    <input type="hidden" name="role" value="student">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label fw-bold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control bg-light border-0" id="username" name="username" placeholder="johndoe" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control bg-light border-0" id="email" name="email" placeholder="john@example.com" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control bg-light border-0" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-pill">Create My Account</button>
                </form>
            </div>
            <div class="card-footer text-center bg-transparent border-0 pb-4">
                <p class="text-muted small">Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Login Here</a></p>
            </div>
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>
