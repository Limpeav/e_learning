<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$course_id = $_GET['course_id'] ?? null;
$edit_id = $_GET['edit_id'] ?? null;

if (!$course_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch course details for context
$stmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();
$course_title = $course ? $course['title'] : 'Unknown Course';

$lesson = null;
$is_edit = false;
$title_val = '';
$content_val = '';

if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ? AND course_id = ?");
    $stmt->execute([$edit_id, $course_id]);
    $lesson = $stmt->fetch();

    if ($lesson) {
        $is_edit = true;
        $title_val = $lesson['title'];
        $content_val = $lesson['content'];
    }
}
?>

<!-- CKEditor 5 Superbuild -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/super-build/ckeditor.js"></script>

<div class="container-fluid bg-light py-5" style="min-height: calc(100vh - 60px);">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="view_course.php?id=<?php echo $course_id; ?>" class="text-decoration-none text-secondary"><?php echo htmlspecialchars($course_title); ?></a></li>
                <li class="breadcrumb-item active text-primary" aria-current="page"><?php echo $is_edit ? 'Edit Lesson' : 'New Lesson'; ?></li>
            </ol>
        </nav>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form action="../../actions/<?php echo $is_edit ? 'edit_lesson.php' : 'add_lesson.php'; ?>" method="POST" id="lessonForm" enctype="multipart/form-data">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="lesson_id" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="type" value="text">
                    
                    <div class="card shadow-lg border-0 rounded-4 overflow-hidden mb-5">
                        <!-- Modern Header -->
                        <div class="card-header bg-white p-4 p-md-5 border-bottom">
                            <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-1 rounded-pill">
                                            <i class="bi bi-journal-bookmark-fill me-1"></i> <?php echo htmlspecialchars($course_title); ?>
                                        </span>
                                        <?php if($is_edit): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10 px-3 py-1 rounded-pill">
                                                <i class="bi bi-pencil-square me-1"></i> Editing Mode
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h2 class="fw-bold text-accent mb-1"><?php echo $is_edit ? 'Edit Lesson Content' : 'Compose New Lesson'; ?></h2>
                                    <p class="text-muted small mb-0">Create comprehensive documentation and learning materials for your students.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light text-secondary border rounded-pill px-3" onclick="window.location.href='view_course.php?id=<?php echo $course_id; ?>'">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold">
                                        <i class="bi bi-cloud-upload me-2"></i><?php echo $is_edit ? 'Update Lesson' : 'Publish Lesson'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0 bg-light">
                            <div class="p-4 p-md-5">
                                <!-- Title Input Area -->
                                <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                                    <label for="title" class="form-label fw-bold small text-uppercase text-primary letter-spacing-1 mb-2">Lesson Title</label>
                                    <input type="text" 
                                           class="form-control form-control-lg border-0 px-0 fs-2 fw-bold text-accent" 
                                           id="title" 
                                           name="title" 
                                           placeholder="e.g., Introduction to Neural Networks" 
                                           required 
                                           style="box-shadow: none; background: transparent;" 
                                           value="<?php echo htmlspecialchars($title_val); ?>">
                                    <div class="progress mt-2" style="height: 3px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <!-- Material Upload Area -->
                                <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                                    <label for="material" class="form-label fw-bold small text-uppercase text-primary letter-spacing-1 mb-2">Lesson Material (Optional)</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="material" 
                                           name="material" 
                                           accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar">
                                    <div class="form-text text-muted">Supported formats: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR</div>
                                    <?php if ($is_edit && !empty($lesson['material_path'])): ?>
                                        <div class="mt-2">
                                            <span class="text-muted">Current file: </span>
                                            <a href="../../public/uploads/materials/<?php echo htmlspecialchars($lesson['material_path']); ?>" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i>Download/View
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>



                                <!-- Editor Area -->
                                <div class="bg-white rounded-4 shadow-sm border overflow-hidden position-relative">
                                    <div class="d-flex align-items-center justify-content-between px-4 py-3 bg-light border-bottom">
                                        <span class="fw-bold small text-uppercase text-secondary"><i class="bi bi-file-earmark-richtext me-2"></i>Content Editor</span>
                                        <div class="small text-muted"><i class="bi bi-shield-check me-1"></i> Auto-saved locally</div>
                                    </div>
                                    <div id="editor-container" class="word-editor-wrapper bg-light p-4">
                                        <textarea id="editor" name="content"><?php echo htmlspecialchars($content_val); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white p-4 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i> Content supports rich text, code blocks, and media embedding.
                            </small>
                            <div class="d-flex gap-3">
                                <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn btn-link text-decoration-none text-secondary fw-bold">Discard Changes</a>
                                <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold">
                                    <i class="bi bi-check-lg me-2"></i><?php echo $is_edit ? 'Save Changes' : 'Create Lesson'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Refined Editor Styles */
.word-editor-wrapper {
    background-color: #f1f5f9; /* Slate 100 */
    min-height: 600px;
}

.ck-editor__editable {
    min-height: 800px !important;
    max-width: 850px !important;
    margin: 0 auto !important;
    background-color: white !important;
    padding: 60px !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0 !important;
    border-radius: 4px !important;
}

/* Specific focus state */
.ck.ck-editor__main>.ck-editor__editable.ck-focused {
    border-color: var(--primary-color) !important;
    box-shadow: 0 10px 40px rgba(37, 99, 235, 0.15);
}

.ck.ck-toolbar {
    background: white !important;
    border-color: #e2e8f0 !important;
    padding: 12px 20px !important;
    border-bottom: 1px solid #e2e8f0 !important;
}

.ck.ck-editor__top {
    position: sticky !important;
    top: 60px !important; /* Matches the navbar height exactly */
    z-index: 999 !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    width: 100%;
}

.letter-spacing-1 {
    letter-spacing: 1px;
}
</style>

<script>
    let editorInstance;
    
    CKEDITOR.ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                    'fontColor', 'fontBackgroundColor', '|',
                    'code', 'codeBlock', 'insertTable', 'mediaEmbed', '|',
                    'undo', 'redo', '|',
                    'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            placeholder: 'Start writing your lesson content here. You can paste images directly...',
            codeBlock: {
                languages: [
                    { language: 'plaintext', label: 'Plain text' },
                    { language: 'html', label: 'HTML' },
                    { language: 'javascript', label: 'JavaScript' },
                    { language: 'php', label: 'PHP' },
                    { language: 'css', label: 'CSS' },
                    { language: 'sql', label: 'SQL' },
                    { language: 'python', label: 'Python' },
                    { language: 'java', label: 'Java' }
                ]
            },
            removePlugins: [
                'CKBox',
                'CKFinder',
                'EasyImage',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'RealTimeCollaborativeEditing',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'TrackChangesData',
                'TrackChangesDataFacade',
                'RevisionHistory',
                'Pagination',
                'WProofreader',
                'MathType',
                'SlashCommand',
                'Template',
                'DocumentOutline',
                'FormatPainter',
                'TableOfContents',
                'PasteFromOfficeEnhanced',
                'AIAssistant'
            ]
        })
        .then(editor => {
            editorInstance = editor;
            // Custom Key handling for Indent/Outdent (Mac/Windows)
            editor.keystrokes.set('Tab', (data, cancel) => {
                const command = editor.commands.get('indent');
                if (command.isEnabled) {
                    command.execute();
                    cancel(); // Stop focus navigation
                }
            });

            editor.keystrokes.set('Shift+Tab', (data, cancel) => {
                const command = editor.commands.get('outdent');
                if (command.isEnabled) {
                    command.execute();
                    cancel();
                }
            });
            
            // Function to sync CKEditor content
            function syncEditorContent() {
                const textarea = document.getElementById('editor');
                if (editorInstance && typeof editorInstance.getData === 'function') {
                    textarea.value = editorInstance.getData();
                    console.log('CKEditor content synced successfully');
                    return true;
                } else {
                    console.warn('CKEditor not initialized, using textarea content directly');
                    return false;
                }
            }
            
            // Sync CKEditor content with textarea before form submission
            const form = document.getElementById('lessonForm');
            form.addEventListener('submit', function(e) {
                console.log('Form submission triggered');
                syncEditorContent();
                // Allow form to submit
                return true;
            });
            
            // Also attach to all submit buttons explicitly to ensure sync happens
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    console.log('Submit button clicked:', this.innerText);
                    // Small delay to ensure CKEditor has finished any pending updates
                    setTimeout(() => {
                        syncEditorContent();
                    }, 10);
                });
            });
        })
        .catch(error => {
            console.error('CKEditor initialization error:', error);
            // Fallback: allow form submission with textarea content if editor fails
            console.log('Editor failed to load. Form will use textarea content directly.');
        });
</script>

<?php include '../../includes/footer.php'; ?>
