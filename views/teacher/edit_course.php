<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch course details and verify ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    echo "Course not found or you don't have permission to edit it.";
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="card-header bg-accent text-white py-4 px-5 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0 fw-bold h4"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Course</h3>
                    <p class="small opacity-75 mb-0">Update your course details and settings.</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
            <div class="card-body p-5">
                <form action="../../actions/edit_course.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    
                    <div class="mb-4">
                        <label for="title" class="form-label text-accent fw-bold small text-uppercase">Course Title</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-type-h1 text-primary"></i></span>
                            <input type="text" class="form-control border-0 bg-light py-3 shadow-none" id="title" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label text-accent fw-bold small text-uppercase">Description</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-blockquote-left text-primary"></i></span>
                            <textarea class="form-control border-0 bg-light py-3 shadow-none" id="description" name="description" rows="5" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <label for="thumbnail" class="form-label text-accent fw-bold small text-uppercase">Course Thumbnail</label>
                        <div class="row align-items-center">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <img src="../../public/uploads/<?php echo $course['thumbnail'] ?: 'default_course.png'; ?>" class="img-fluid rounded shadow-sm" alt="Current Thumbnail">
                            </div>
                            <div class="col-md-8">
                                <div class="p-3 border-2 border-dashed rounded bg-light text-center">
                                    <input type="file" class="form-control bg-white shadow-none" id="thumbnail" name="thumbnail" accept="image/*">
                                    <small class="text-muted d-block mt-2">Leave blank to keep current image. Recommended: 1200x800px.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3 pt-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold flex-grow-1 shadow">
                            <i class="bi bi-check-circle-fill me-2"></i>Save Changes
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
