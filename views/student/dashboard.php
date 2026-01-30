<?php
require_once "../../config/db.php";
include "../../includes/header.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION["user_id"];

// Fetch enrolled courses
$stmt = $pdo->prepare("
    SELECT c.*, u.username as teacher_name
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON c.teacher_id = u.id
    WHERE e.student_id = ?
");
$stmt->execute([$student_id]);
$enrolled_courses = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Student Portal</h2>
    <div class="text-muted small">Learning Path of <strong><?php echo htmlspecialchars(
        $_SESSION["username"],
    ); ?></strong></div>
</div>




<div class="row" id="my-courses">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-accent fw-bold mb-0"><i class="bi bi-play-circle-fill me-2 text-primary"></i>My Enrolled Courses</h4>
            <a href="browse_courses.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-search me-1"></i>Find More
            </a>
        </div>


        <div class="row">
            <?php if (empty($enrolled_courses)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-journal-x fs-1 text-muted opacity-25 d-block mb-3"></i>
                            <h5 class="text-muted">No enrollments yet.</h5>
                            <p class="text-muted small mb-0">Discover new skills by <a href="browse_courses.php" class="text-primary fw-bold">browsing our course catalog</a>.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($enrolled_courses as $course): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm overflow-hidden">
                            <img src="../../public/uploads/<?php echo $course[
                                "thumbnail"
                            ] ?:
                                "default_course.png"; ?>" class="card-img-top" alt="Course" style="height: 160px; object-fit: cover;">
                            <div class="card-body p-4">
                                <h6 class="text-primary small fw-bold text-uppercase mb-2"><?php echo htmlspecialchars(
                                    $course["teacher_name"],
                                ); ?></h6>
                                <h5 class="card-title text-accent fw-bold mb-3"><?php echo htmlspecialchars(
                                    $course["title"],
                                ); ?></h5>
                                <a href="view_course.php?id=<?php echo $course[
                                    "id"
                                ]; ?>" class="btn btn-primary w-100 rounded-pill">
                                    <i class="bi bi-book-half me-2"></i>Continue Learning
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <h4 class="text-accent fw-bold mb-4"><i class="bi bi-graph-up me-2 text-primary"></i>Assessment Results</h4>
        <?php
        $stmt = $pdo->prepare("
            SELECT qr.*, q.title as quiz_title, c.title as course_title
            FROM quiz_results qr
            JOIN quizzes q ON qr.quiz_id = q.id
            JOIN courses c ON q.course_id = c.id
            WHERE qr.student_id = ?
            AND qr.taken_at = (
                SELECT MAX(qr2.taken_at)
                FROM quiz_results qr2
                JOIN quizzes q2 ON qr2.quiz_id = q2.id
                WHERE qr2.student_id = qr.student_id
                AND q2.course_id = q.course_id
            )
            ORDER BY qr.taken_at DESC
        ");
        $stmt->execute([$student_id]);
        $results = $stmt->fetchAll();
        ?>
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="list-group list-group-flush">
                <?php if (empty($results)): ?>
                    <div class="list-group-item text-center py-5 text-muted small">
                        No assessments completed yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($results as $res): ?>
                        <div class="list-group-item p-3 border-bottom-0">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-accent fw-bold text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars(
                                    $res["course_title"],
                                ); ?></h6>
                                <span class="badge py-2 px-3 rounded-pill <?php echo $res[
                                    "score"
                                ] >= 70
                                    ? "bg-primary"
                                    : "bg-secondary"; ?>">
                                    <?php echo $res["score"]; ?>%
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted"><?php echo htmlspecialchars(
                                    $res["quiz_title"],
                                ); ?></small>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo date(
                                    "M d",
                                    strtotime($res["taken_at"]),
                                ); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>
