<?php include '../../includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 overflow-hidden">
            <div class="row g-0">
                <div class="col-12 bg-primary p-4 text-center text-white">
                    <i class="bi bi-shield-lock fs-1"></i>
                    <h3 class="mt-2">Welcome Back</h3>
                    <p class="small opacity-75">Universal Login for Students, Lecturers, & Admins</p>
                </div>
                <div class="col-12 p-4">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger border-0 shadow-sm"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success border-0 shadow-sm"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php endif; ?>
                    
                    <form action="../../actions/login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control bg-light border-0" id="email" name="email" placeholder="example@elearning.com" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control bg-light border-0" id="password" name="password" placeholder="••••••••" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-pill">Sign In</button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p class="text-muted small">Need an account? <a href="register.php" class="text-primary fw-bold text-decoration-none">Create Account</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>
