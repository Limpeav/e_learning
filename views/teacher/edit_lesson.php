<?php 
require_once '../../config/db.php';
include '../../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$lesson_id = $_GET['id'] ?? null;
if (!$lesson_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch lesson details and verify ownership
$stmt = $pdo->prepare("
    SELECT l.*, c.teacher_id 
    FROM lessons l 
    JOIN courses c ON l.course_id = c.id 
    WHERE l.id = ? AND c.teacher_id = ?
");
$stmt->execute([$lesson_id, $_SESSION['user_id']]);
$lesson = $stmt->fetch();

if (!$lesson) {
    echo "Lesson not found or access denied.";
    exit;
}

$course_id = $lesson['course_id'];
?>

<!-- CKEditor 5 Superbuild -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/super-build/ckeditor.js"></script>

<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card shadow-lg border-0 overflow-hidden">
            <div class="card-header bg-accent text-white p-4 d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bold h4"><i class="bi bi-pencil-square me-2 text-primary"></i>Document Revision</h3>
                <div>
                    <span class="badge bg-primary px-3 py-2 rounded-pill me-2">Edit Mode</span>
                    <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn btn-sm btn-outline-light rounded-pill border-0">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-5">
                <form action="../../actions/edit_lesson.php" method="POST" id="editLessonForm">
                    <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    
                    <div class="mb-5">
                        <label for="title" class="form-label fw-bold small text-uppercase text-secondary opacity-75">Document Title</label>
                        <input type="text" class="form-control form-control-lg border-0 border-bottom rounded-0 px-0 fs-3 fw-bold mb-2" id="title" name="title" value="<?php echo htmlspecialchars($lesson['title']); ?>" required style="box-shadow: none;">
                        <hr class="mt-0 opacity-10">
                    </div>

                    <div class="mb-5">
                        <label class="form-label fw-bold small text-uppercase text-secondary opacity-75 mb-3">Lesson Documentation</label>
                        <div id="editor-container" class="word-editor-wrapper">
                            <textarea id="editor" name="content"><?php echo $lesson['content']; ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-5 pt-4 border-top">
                        <button type="submit" class="btn btn-primary px-5 py-3 rounded-pill shadow-sm fw-bold">
                            <i class="bi bi-check2-all me-2"></i>Commit Changes
                        </button>
                        <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn btn-outline-secondary px-5 py-3 rounded-pill fw-bold">Cancel Revisions</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styling to make CKEditor look like Microsoft Word */
.word-editor-wrapper {
    background-color: #f8fafc;
    padding: 50px 20px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.ck-editor__editable {
    min-height: 800px !important;
    max-width: 850px !important;
    margin: 0 auto !important;
    background-color: white !important;
    padding: 50px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #dee2e6 !important;
    border-top: 5px solid var(--primary-color) !important;
}
.ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
    border-color: #dee2e6 !important;
}
.ck.ck-toolbar {
    background: white !important;
    border-color: #e2e8f0 !important;
    padding: 10px !important;
    border-radius: 8px 8px 0 0 !important;
    margin-bottom: 0 !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.ck.ck-editor__top {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>

<script>
    let editorInstance;
    
    CKEDITOR.ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: {
                items: [
                    'exportPDF','exportWord', '|',
                    'findAndReplace', 'selectAll', '|',
                    'heading', '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'outdent', 'indent', '|',
                    'undo', 'redo',
                    '-',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                    'alignment', '|',
                    'link', 'insertImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', 'htmlEmbed', '|',
                    'specialCharacters', 'horizontalLine', 'pageBreak', '|',
                    'textPartLanguage', '|',
                    'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            list: {
                properties: {
                    styles: true,
                    startIndex: true,
                    reversed: true
                }
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                    { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                    { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                ]
            },
            placeholder: 'Continue typing your lesson documentation here...',
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Courier New, Courier, monospace',
                    'Georgia, serif',
                    'Lucida Sans Unicode, Lucida Grande, sans-serif',
                    'Tahoma, Geneva, sans-serif',
                    'Times New Roman, Times, serif',
                    'Trebuchet MS, Helvetica, sans-serif',
                    'Verdana, Geneva, sans-serif'
                ],
                supportAllValues: true
            },
            fontSize: {
                options: [ 10, 12, 14, 'default', 18, 20, 22 ],
                supportAllValues: true
            },
            htmlSupport: {
                allow: [
                    {
                        name: /.*/,
                        attributes: true,
                        classes: true,
                        styles: true
                    }
                ]
            },
            htmlEmbed: {
                showPreviews: true
            },
            link: {
                decorators: {
                    addTargetToExternalLinks: true,
                    defaultProtocol: 'https://',
                    toggleDownloadable: {
                        mode: 'manual',
                        label: 'Downloadable',
                        attributes: {
                            download: 'file'
                        }
                    }
                }
            },
            mention: {
                feeds: [
                    {
                        marker: '@',
                        feed: [
                            '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                            '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                            '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                            '@sugar', '@sweet', '@topping', '@wafer'
                        ],
                        minimumCharacters: 1
                    }
                ]
            },
            removePlugins: [
                'CKBox',
                'CKFinder',
                'EasyImage',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
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
                'PasteFromOfficeEnhanced'
            ]
        })
        .then(editor => {
            editorInstance = editor;
            
            // Sync CKEditor content with textarea before form submission
            document.getElementById('editLessonForm').addEventListener('submit', function(e) {
                const textarea = document.getElementById('editor');
                textarea.value = editorInstance.getData();
            });
        })
        .catch(error => {
            console.error(error);
        });
</script>

<?php include '../../includes/footer.php'; ?>
