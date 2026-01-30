<?php
require_once '../../config/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Fetch all queries for courses taught by this teacher
$stmt = $pdo->prepare("
    SELECT q.*, u.username as student_name, c.title as course_title 
    FROM queries q 
    JOIN users u ON q.student_id = u.id 
    JOIN courses c ON q.course_id = c.id 
    WHERE c.teacher_id = ? 
    ORDER BY q.answered_at IS NULL DESC, q.created_at DESC
");
$stmt->execute([$teacher_id]);
$queries = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-accent fw-bold"><i class="bi bi-chat-left-dots-fill me-2 text-primary"></i>Student Inquiries</h2>
    <div class="text-muted small">Active discussions for your courses</div>
</div>

<?php if (empty($queries)): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-chat-square-text fs-1 text-muted opacity-25 d-block mb-3"></i>
            <h5 class="text-muted">No student queries found.</h5>
            <p class="text-muted small mb-0">Engagement will appear here once students start asking questions.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-accent text-white">
                    <tr>
                        <th class="ps-4 py-3">Course & Student</th>
                        <th class="py-3">Question</th>
                        <th class="py-3">Status</th>
                        <th class="text-end pe-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($queries as $q): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-accent"><?php echo htmlspecialchars($q['course_title']); ?></div>
                                <div class="text-primary small"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($q['student_name']); ?></div>
                            </td>
                            <td class="py-3">
                                <p class="mb-0 text-secondary small text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($q['question']); ?></p>
                            </td>
                            <td class="py-3">
                                <?php if ($q['answer']): ?>
                                    <span class="badge bg-primary rounded-pill px-3 fw-normal">RESOLVED</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-3 fw-normal">PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 py-3">
                                <button class="btn btn-sm <?php echo $q['answer'] ? 'btn-outline-primary' : 'btn-primary'; ?> rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#answerModal<?php echo $q['id']; ?>">
                                    <i class="bi <?php echo $q['answer'] ? 'bi-pencil-square' : 'bi-reply-fill'; ?> me-1"></i>
                                    <?php echo $q['answer'] ? 'Edit' : 'Answer'; ?>
                                </button>

                                <!-- Answer Modal -->
                                <div class="modal fade" id="answerModal<?php echo $q['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-accent text-white py-3">
                                                <h5 class="modal-title fw-bold small text-uppercase tracking-wider">
                                                    <i class="bi bi-reply-all-fill me-2 text-primary"></i>Student Support
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="../../actions/answer_query.php" method="POST">
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="query_id" value="<?php echo $q['id']; ?>">
                                                    <div class="bg-light p-3 rounded mb-4">
                                                        <label class="text-secondary small text-uppercase fw-bold opacity-50 mb-2 d-block">Student Question</label>
                                                        <p class="mb-0 text-accent fw-bold italic"><?php echo htmlspecialchars($q['question']); ?></p>
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="text-accent fw-bold small text-uppercase mb-2 d-block">Your Response</label>
                                                        <textarea class="form-control border-0 bg-light p-3 shadow-none" name="answer" rows="6" placeholder="Type your helpful response here..." required><?php echo htmlspecialchars($q['answer'] ?? ''); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-0 p-3">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Discard</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                                        <i class="bi bi-send-fill me-1"></i> Send Response
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
