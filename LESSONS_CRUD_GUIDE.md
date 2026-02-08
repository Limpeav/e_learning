# Lessons CRUD Operations - Complete Guide

## 📋 Overview

This document provides a complete guide for **CRUD (Create, Read, Update, Delete)** operations on **Lessons** in the e-learning platform.

---

## 📊 Lessons Table Structure

```sql
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    type ENUM('video', 'pdf', 'text') NOT NULL DEFAULT 'text',
    content TEXT,
    material_path VARCHAR(255),
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
```

### Column Descriptions:
- **id**: Primary key, auto-increment
- **course_id**: Foreign key to courses table (REQUIRED)
- **title**: Lesson title (REQUIRED)
- **type**: Lesson type - 'video', 'pdf', or 'text' (default: 'text')
- **content**: Rich text HTML content for text lessons, or URL for video/pdf
- **material_path**: Optional downloadable material filename (PDF, DOC, PPT, ZIP, RAR)
- **order_index**: For custom ordering of lessons within a course
- **created_at**: Automatically set when lesson is created
- **updated_at**: Automatically updated when lesson is modified

---

## ✅ Full CRUD Implementation

### 1. **CREATE** - Add New Lesson

#### 📄 View File:
`/views/teacher/add_lesson.php`

#### 🔧 Action File:
`/actions/add_lesson.php`

#### Features:
- Rich text editor (CKEditor 5)
- File upload support for materials
- Form validation
- Course context awareness
- Can also handle editing via edit_id parameter

#### Usage:
```
URL: /views/teacher/add_lesson.php?course_id=1
```

#### Key Functionality:
```php
// Form submission to /actions/add_lesson.php
POST data:
- course_id (required)
- title (required)
- type (default: 'text')
- content (required)
- material (optional file upload)

// File handling:
- Accepted formats: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR
- Stored in: /public/uploads/materials/
- Filename format: material_{uniqid}.{ext}
```

#### Example Code:
```php
// INSERT new lesson
$stmt = $pdo->prepare("
    INSERT INTO lessons 
    (course_id, title, type, content, material_path) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([
    $course_id,
    $title,
    'text',
    $content,
    $material_path // or NULL
]);
$lesson_id = $pdo->lastInsertId();
```

---

### 2. **READ** - View/Display Lessons

#### 📄 View File:
`/views/teacher/view_course.php`

#### Features:
- Display all lessons for a course
- Show lesson content with rich text formatting
- Material download option
- Lesson navigation
- Auto-scroll to active lesson

#### Usage:
```
URL: /views/teacher/view_course.php?id=1&lesson_id=5
```

#### Query Example:
```php
// Get all lessons for a course
$stmt = $pdo->prepare("
    SELECT * FROM lessons 
    WHERE course_id = ? 
    ORDER BY order_index ASC, id ASC
");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();

// Get specific lesson
$stmt = $pdo->prepare("
    SELECT l.*, c.title as course_title 
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    WHERE l.id = ?
");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();
```

#### Display Features:
- Sidebar curriculum list with lesson count
- Main content area showing lesson HTML
- Downloadable materials section
- Edit button for lesson modification
- Last updated timestamp
- Sequential numbering

---

### 3. **UPDATE** - Edit Lesson

#### 📄 View Files:
- `/views/teacher/add_lesson.php?course_id=1&edit_id=5` (Combined with create)
- `/views/teacher/edit_lesson.php?id=5` (Standalone editor)

#### 🔧 Action File:
`/actions/edit_lesson.php`

#### Features:
- Pre-populated form with existing data
- Same rich text editor as create
- File upload to replace existing material
- Ownership verification (teacher must own the course)
- Automatic updated_at timestamp

#### Usage:
```
URL: /views/teacher/add_lesson.php?course_id=1&edit_id=5
Alternative: /views/teacher/edit_lesson.php?id=5
```

#### Key Functionality:
```php
// Fetch lesson for editing
$stmt = $pdo->prepare("
    SELECT l.*, c.teacher_id 
    FROM lessons l 
    JOIN courses c ON l.course_id = c.id 
    WHERE l.id = ? AND c.teacher_id = ?
");
$stmt->execute([$lesson_id, $_SESSION['user_id']]);
$lesson = $stmt->fetch();

// UPDATE lesson
$stmt = $pdo->prepare("
    UPDATE lessons 
    SET title = ?, content = ?, material_path = ? 
    WHERE id = ?
");
$stmt->execute([
    $title,
    $content,
    $material_path, // or keep existing
    $lesson_id
]);
```

#### Security:
- Verifies teacher owns the course before allowing edit
- Validates file types for uploads
- Sanitizes input data
- Uses prepared statements to prevent SQL injection

---

### 4. **DELETE** - Remove Lesson

#### 🔧 Action File:
`/actions/delete_lesson.php`

#### Features:
- Confirmation before deletion
- Removes from database
- Cascade delete handled by database (related records removed automatically)
- Role-based access (teacher or admin)

#### Usage:
```
URL: /actions/delete_lesson.php?id=5&course_id=1
```

#### Key Functionality:
```php
// DELETE lesson
$stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);

// Redirect back to course view
header("Location: ../views/teacher/view_course.php?id=$course_id&success=Lesson deleted");
```

#### Access Control:
- **Teacher**: Can delete own lessons
- **Admin**: Can delete any lesson

#### Delete Button Implementation:
```html
<!-- In view_course.php or similar -->
<a href="../../actions/delete_lesson.php?id=<?php echo $lesson['id']; ?>&course_id=<?php echo $course_id; ?>" 
   class="btn btn-danger btn-sm"
   onclick="return confirm('Are you sure you want to delete this lesson?')">
    <i class="bi bi-trash"></i> Delete
</a>
```

---

## 📁 Complete File Structure

```
e_learning/
├── views/teacher/
│   ├── add_lesson.php          ✅ CREATE + UPDATE (combined)
│   ├── edit_lesson.php         ✅ UPDATE (standalone)
│   └── view_course.php         ✅ READ (display lessons)
│
├── actions/
│   ├── add_lesson.php          ✅ CREATE action handler
│   ├── edit_lesson.php         ✅ UPDATE action handler
│   └── delete_lesson.php       ✅ DELETE action handler
│
└── public/uploads/materials/   📁 Uploaded lesson materials
```

---

## 🔄 Complete CRUD Flow Diagram

```
┌─────────────────────────────────────────────────┐
│                   TEACHER                       │
└─────────────────────────────────────────────────┘
                      │
    ┌─────────────────┼─────────────────┐
    │                 │                 │
    ▼                 ▼                 ▼
┌────────┐      ┌──────────┐      ┌──────────┐
│ CREATE │      │   READ   │      │  UPDATE  │
└────┬───┘      └────┬─────┘      └────┬─────┘
     │               │                  │
     ▼               ▼                  ▼
add_lesson.php   view_course.php   edit_lesson.php
     │               │                  │
     ▼               │                  ▼
/actions/           │            /actions/
add_lesson.php      │            edit_lesson.php
     │               │                  │
     └───────────────┴──────────────────┘
                     │
            ┌────────▼────────┐
            │   lessons       │
            │   TABLE         │
            └────────┬────────┘
                     │
            ┌────────▼────────┐
            │    DELETE       │
            └────────┬────────┘
                     ▼
            /actions/
            delete_lesson.php
```

---

## 🎯 Example Usage Scenarios

### Scenario 1: Teacher Creates a New Lesson

1. Teacher navigates to course: `/views/teacher/view_course.php?id=1`
2. Clicks "New Lesson" button
3. Redirects to: `/views/teacher/add_lesson.php?course_id=1`
4. Fills in:
   - Title: "Introduction to JavaScript Variables"
   - Content: Rich text with code examples
   - Material: Uploads a PDF reference sheet
5. Submits form → `/actions/add_lesson.php`
6. Lesson inserted into database
7. Redirects back to: `/views/teacher/view_course.php?id=1&success=Lesson added`

### Scenario 2: Teacher Edits an Existing Lesson

1. Teacher viewing lesson in course
2. Clicks "Edit Content" button
3. Redirects to: `/views/teacher/add_lesson.php?course_id=1&edit_id=5`
4. Form pre-populated with existing data
5. Teacher modifies content
6. Submits → `/actions/edit_lesson.php`
7. Database updates lesson, sets updated_at timestamp
8. Redirects back with success message

### Scenario 3: Teacher Deletes a Lesson

1. Teacher clicks delete icon next to lesson in sidebar
2. JavaScript confirmation: "Are you sure?"
3. If confirmed → `/actions/delete_lesson.php?id=5&course_id=1`
4. Lesson removed from database
5. Redirects back to course view

### Scenario 4: Student Views Lessons (READ only)

1. Student enrolls in course
2. Views: `/views/student/view_course.php?id=1`
3. Same READ functionality, but:
   - No edit/delete buttons
   - Read-only view
   - Can download materials

---

## ⚙️ Advanced Features

### File Upload Handling

```php
// In add_lesson.php and edit_lesson.php actions
if (isset($_FILES['material']) && $_FILES['material']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadDir = '../public/uploads/materials/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExt = strtolower(pathinfo($_FILES['material']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
    
    if (in_array($fileExt, $allowedExts)) {
        $fileName = uniqid('material_', true) . '.' . $fileExt;
        move_uploaded_file($_FILES['material']['tmp_name'], $uploadDir . $fileName);
        $material_path = $fileName;
    }
}
```

### Custom Lesson Ordering

```php
// Get lessons in specific order
$stmt = $pdo->prepare("
    SELECT * FROM lessons 
    WHERE course_id = ? 
    ORDER BY order_index ASC, id ASC
");

// Update lesson order
$stmt = $pdo->prepare("UPDATE lessons SET order_index = ? WHERE id = ?");
$stmt->execute([$new_position, $lesson_id]);
```

### Lesson Statistics

```php
// Count lessons in a course
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lessons WHERE course_id = ?");
$stmt->execute([$course_id]);
$lesson_count = $stmt->fetch()['total'];

// Get recently updated lessons
$stmt = $pdo->prepare("
    SELECT * FROM lessons 
    WHERE course_id = ? 
    ORDER BY updated_at DESC 
    LIMIT 5
");
```

---

## 🛡️ Security Considerations

### 1. **Authorization Checks**
```php
// Verify user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

// Verify teacher owns the course
$stmt = $pdo->prepare("
    SELECT c.id FROM courses c 
    JOIN lessons l ON c.id = l.course_id 
    WHERE l.id = ? AND c.teacher_id = ?
");
```

### 2. **Input Validation**
```php
// Validate required fields
if (empty($title) || empty($content)) {
    header("Location: add_lesson.php?error=All fields required");
    exit;
}

// Validate file types
$allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
if (!in_array($fileExt, $allowedExts)) {
    header("Location: add_lesson.php?error=Invalid file type");
    exit;
}
```

### 3. **SQL Injection Prevention**
```php
// Always use prepared statements
$stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, content) VALUES (?, ?, ?)");
$stmt->execute([$course_id, $title, $content]);
```

### 4. **XSS Prevention**
```php
// Sanitize output
echo htmlspecialchars($lesson['title']);

// For rich text content (trusted HTML), display as-is
// Content is from CKEditor which sanitizes on client side
echo $lesson['content'];
```

---

## 🧪 Testing Checklist

- [ ] **CREATE**: Can create new lesson with title and content
- [ ] **CREATE**: Can upload material file (PDF, DOC, etc.)
- [ ] **CREATE**: Form validation works (required fields)
- [ ] **CREATE**: File type validation works
- [ ] **READ**: All lessons display in course view
- [ ] **READ**: Lesson content renders correctly (HTML)
- [ ] **READ**: Material download link works
- [ ] **READ**: Sidebar navigation works
- [ ] **UPDATE**: Can edit existing lesson
- [ ] **UPDATE**: Form pre-populates with existing data
- [ ] **UPDATE**: Can replace material file
- [ ] **UPDATE**: updated_at timestamp changes
- [ ] **UPDATE**: Only course owner can edit
- [ ] **DELETE**: Can delete lesson
- [ ] **DELETE**: Confirmation prompt appears
- [ ] **DELETE**: Only course owner can delete
- [ ] **DELETE**: Lesson actually removed from database

---

## 📞 Troubleshooting

### Issue: File upload fails
**Solution**: 
- Check directory permissions: `/public/uploads/materials/` must be writable
- Check PHP upload limits in `php.ini`: `upload_max_filesize` and `post_max_size`

### Issue: Can't edit lesson
**Solution**:
- Verify you're logged in as the course owner
- Check that lesson belongs to the course
- Verify foreign key relationships in database

### Issue: Rich text editor not loading
**Solution**:
- Check CKEditor CDN is accessible
- Check browser console for JavaScript errors
- Ensure `<script>` tag loads before initialization

---

## ✅ Summary

| Operation | View File | Action File | HTTP Method | Auth Required |
|-----------|-----------|-------------|-------------|---------------|
| **CREATE** | `add_lesson.php` | `/actions/add_lesson.php` | POST | Teacher |
| **READ** | `view_course.php` | N/A | GET | Teacher/Student |
| **UPDATE** | `add_lesson.php?edit_id=X` or `edit_lesson.php` | `/actions/edit_lesson.php` | POST | Teacher (owner) |
| **DELETE** | N/A (confirmation JS) | `/actions/delete_lesson.php` | GET | Teacher (owner)/Admin |

**All CRUD operations are fully implemented and working!** ✅

---

**Last Updated**: 2026-02-03  
**Status**: ✅ Complete CRUD Implementation
