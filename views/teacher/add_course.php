<?php 
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="card-header bg-accent text-white py-4 px-5">
                <h3 class="mb-0 fw-bold h4"><i class="bi bi-journal-plus me-2 text-primary"></i>Launch New Course</h3>
                <p class="small opacity-75 mb-0">Fill in the details below to publish your curriculum.</p>
            </div>
            <div class="card-body p-5">
                <form action="../../actions/add_course.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="title" class="form-label text-accent fw-bold small text-uppercase">Course Title</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-type-h1 text-primary"></i></span>
                            <input type="text" class="form-control border-0 bg-light py-3 shadow-none" id="title" name="title" placeholder="e.g. Advanced PHP Development" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label text-accent fw-bold small text-uppercase">Description</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-blockquote-left text-primary"></i></span>
                            <textarea class="form-control border-0 bg-light py-3 shadow-none" id="description" name="description" rows="5" placeholder="Provide a detailed overview of what students will learn..." required></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <label for="thumbnail" class="form-label text-accent fw-bold small text-uppercase">Cover Image</label>
                        <div class="p-4 border-2 border-dashed rounded bg-light text-center">
                            <i class="bi bi-image text-muted fs-1 mb-2 d-block"></i>
                            <input type="file" class="form-control bg-white shadow-none" id="thumbnail" name="thumbnail" accept="image/*">
                            <small class="text-muted d-block mt-2">Recommended size: 1200x800px. JPG, PNG or WEBP only.</small>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3 pt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold flex-grow-1 shadow">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i>Publish Course
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 py-3 fw-bold">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .border-dashed {
        border-style: dashed !important;
        border-color: #cbd5e1 !important;
    }
</style>

<?php include '../../includes/footer.php'; ?>
