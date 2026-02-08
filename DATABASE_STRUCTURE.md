# E-Learning Database Structure Guide

## 📋 Table of Contents
1. [Database Overview](#database-overview)
2. [Table Creation Order](#table-creation-order)
3. [Table Relationships](#table-relationships)
4. [Data Insertion Order](#data-insertion-order)
5. [Foreign Key Constraints](#foreign-key-constraints)
6. [Usage Examples](#usage-examples)

---

## 🗄️ Database Overview

**Database Name:** `e_learning`

**Total Tables:** 9

The database consists of 9 interconnected tables that manage:
- User accounts (admin, teachers, students)
- Course content and structure
- Student enrollments
- Quizzes and assessments
- Student progress tracking
- Q&A system

---

## 📊 Table Creation Order

**CRITICAL:** Tables MUST be created in this exact order to respect foreign key dependencies:

```
1. users          (Level 0 - No dependencies)
2. courses        (Level 1 - Depends on: users)
3. lessons        (Level 2 - Depends on: courses)
4. enrollments    (Level 2 - Depends on: users, courses)
5. queries        (Level 2 - Depends on: users, courses)
6. quizzes        (Level 2 - Depends on: courses)
7. questions      (Level 3 - Depends on: quizzes)
8. quiz_results   (Level 3 - Depends on: users, quizzes)
9. student_answers (Level 4 - Depends on: users, quizzes, quiz_results, questions)
```

### Dependency Levels Explained:
- **Level 0:** Base tables with no foreign keys
- **Level 1:** Tables that reference only Level 0 tables
- **Level 2:** Tables that reference Level 0 and/or Level 1 tables
- **Level 3:** Tables that reference up to Level 2 tables
- **Level 4:** Tables that reference multiple higher-level tables

---

## 🔗 Table Relationships

### Visual Dependency Map

```
users (👥)
  ├─→ courses (teacher_id)
  │     ├─→ lessons
  │     ├─→ enrollments (student_id also from users)
  │     ├─→ queries (student_id also from users)
  │     └─→ quizzes
  │           ├─→ questions
  │           └─→ quiz_results (student_id also from users)
  │                 └─→ student_answers (also references: users, quizzes, questions)
  └─→ enrollments (student_id)
      queries (student_id)
      quiz_results (student_id)
      student_answers (student_id)
```

---

## 📝 Detailed Table Specifications

### 1. **users** (Base Table - Level 0)
**Purpose:** Stores all user accounts (admin, teacher, student)

**Dependencies:** None

**Columns:**
- `id` - Primary Key
- `username` - Unique username
- `email` - Unique email address
- `password` - Hashed password
- `role` - ENUM('admin', 'teacher', 'student')
- `bio` - User biography
- `avatar` - Profile picture path
- `created_at` - Account creation timestamp

**Indexes:**
- PRIMARY KEY on `id`
- UNIQUE on `username`
- UNIQUE on `email`
- INDEX on `role`

---

### 2. **courses** (Level 1)
**Purpose:** Stores courses created by teachers

**Dependencies:**
- `teacher_id` → `users(id)`

**Columns:**
- `id` - Primary Key
- `teacher_id` - Foreign Key to users
- `title` - Course title
- `description` - Course description
- `thumbnail` - Course thumbnail image
- `created_at` - Course creation timestamp

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `teacher_id`
- FOREIGN KEY `teacher_id` REFERENCES `users(id)` ON DELETE CASCADE

---

### 3. **lessons** (Level 2)
**Purpose:** Stores lesson content for each course

**Dependencies:**
- `course_id` → `courses(id)`

**Columns:**
- `id` - Primary Key
- `course_id` - Foreign Key to courses
- `title` - Lesson title
- `type` - ENUM('video', 'pdf', 'text')
- `content` - Lesson content (URL or text)
- `created_at` - Lesson creation timestamp

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `course_id`
- INDEX on `type`
- FOREIGN KEY `course_id` REFERENCES `courses(id)` ON DELETE CASCADE

---

### 4. **enrollments** (Level 2)
**Purpose:** Tracks student enrollments in courses

**Dependencies:**
- `student_id` → `users(id)`
- `course_id` → `courses(id)`

**Columns:**
- `id` - Primary Key
- `student_id` - Foreign Key to users
- `course_id` - Foreign Key to courses
- `enrolled_at` - Enrollment timestamp

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `student_id`
- INDEX on `course_id`
- UNIQUE KEY on (`student_id`, `course_id`) - Prevents duplicate enrollments
- FOREIGN KEY `student_id` REFERENCES `users(id)` ON DELETE CASCADE
- FOREIGN KEY `course_id` REFERENCES `courses(id)` ON DELETE CASCADE

---

### 5. **queries** (Level 2)
**Purpose:** Stores student questions about courses

**Dependencies:**
- `student_id` → `users(id)`
- `course_id` → `courses(id)`

**Columns:**
- `id` - Primary Key
- `student_id` - Foreign Key to users
- `course_id` - Foreign Key to courses
- `question` - Student's question
- `answer` - Teacher's answer (nullable)
- `created_at` - Question creation timestamp
- `answered_at` - Answer timestamp (nullable)

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `student_id`
- INDEX on `course_id`
- INDEX on `answered_at` (for filtering answered/unanswered)
- FOREIGN KEY `student_id` REFERENCES `users(id)` ON DELETE CASCADE
- FOREIGN KEY `course_id` REFERENCES `courses(id)` ON DELETE CASCADE

---

### 6. **quizzes** (Level 2)
**Purpose:** Stores quizzes associated with courses

**Dependencies:**
- `course_id` → `courses(id)`

**Columns:**
- `id` - Primary Key
- `course_id` - Foreign Key to courses
- `title` - Quiz title
- `created_at` - Quiz creation timestamp

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `course_id`
- FOREIGN KEY `course_id` REFERENCES `courses(id)` ON DELETE CASCADE

---

### 7. **questions** (Level 3)
**Purpose:** Stores individual questions for each quiz

**Dependencies:**
- `quiz_id` → `quizzes(id)`

**Columns:**
- `id` - Primary Key
- `quiz_id` - Foreign Key to quizzes
- `question_text` - The question text
- `option_a` - First option
- `option_b` - Second option
- `option_c` - Third option
- `option_d` - Fourth option
- `correct_option` - ENUM('a', 'b', 'c', 'd')

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `quiz_id`
- FOREIGN KEY `quiz_id` REFERENCES `quizzes(id)` ON DELETE CASCADE

---

### 8. **quiz_results** (Level 3)
**Purpose:** Stores overall quiz attempt results

**Dependencies:**
- `student_id` → `users(id)`
- `quiz_id` → `quizzes(id)`

**Columns:**
- `id` - Primary Key
- `student_id` - Foreign Key to users
- `quiz_id` - Foreign Key to quizzes
- `score` - Student's score
- `taken_at` - Quiz completion timestamp

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on `student_id`
- INDEX on `quiz_id`
- INDEX on (`student_id`, `quiz_id`)
- FOREIGN KEY `student_id` REFERENCES `users(id)` ON DELETE CASCADE
- FOREIGN KEY `quiz_id` REFERENCES `quizzes(id)` ON DELETE CASCADE

**Note:** Students can retake quizzes, so there's no unique constraint on (student_id, quiz_id)

---

### 9. **student_answers** (Level 4) ⚠️ MOST COMPLEX
**Purpose:** Stores individual answers for each question in a quiz attempt

**Dependencies:** (REQUIRES ALL OF THE FOLLOWING)
- `student_id` → `users(id)`
- `quiz_id` → `quizzes(id)`
- `result_id` → `quiz_results(id)`
- `question_id` → `questions(id)`

**Columns:**
- `id` - Primary Key
- `student_id` - Foreign Key to users
- `quiz_id` - Foreign Key to quizzes
- `result_id` - Foreign Key to quiz_results
- `question_id` - Foreign Key to questions
- `selected_option` - ENUM('a', 'b', 'c', 'd')
- `is_correct` - TINYINT(1) - 0 or 1
- `answered_at` - Answer timestamp

**Indexes:**
- PRIMARY KEY on `id`
- INDEX on (`student_id`, `quiz_id`)
- INDEX on `result_id`
- INDEX on `question_id`
- UNIQUE KEY on (`result_id`, `question_id`) - One answer per question per attempt
- FOREIGN KEY `student_id` REFERENCES `users(id)` ON DELETE CASCADE
- FOREIGN KEY `quiz_id` REFERENCES `quizzes(id)` ON DELETE CASCADE
- FOREIGN KEY `result_id` REFERENCES `quiz_results(id)` ON DELETE CASCADE
- FOREIGN KEY `question_id` REFERENCES `questions(id)` ON DELETE CASCADE

---

## 🔄 Data Insertion Order

**CRITICAL:** Always insert data in this order to avoid foreign key constraint violations:

### Step-by-Step Insertion Guide

#### 1️⃣ Insert Users First
```sql
INSERT INTO users (username, email, password, role) 
VALUES ('john_teacher', 'john@example.com', 'hashed_password', 'teacher');
-- Returns: user_id = 5
```

#### 2️⃣ Insert Courses
```sql
INSERT INTO courses (teacher_id, title, description) 
VALUES (5, 'Introduction to PHP', 'Learn PHP from scratch');
-- Returns: course_id = 10
```

#### 3️⃣ Insert Lessons, Enrollments, Queries, or Quizzes (any order)

**Lessons:**
```sql
INSERT INTO lessons (course_id, title, type, content) 
VALUES (10, 'PHP Basics', 'text', 'Content here...');
```

**Enrollments:**
```sql
INSERT INTO enrollments (student_id, course_id) 
VALUES (12, 10); -- student_id=12 must exist in users
```

**Queries:**
```sql
INSERT INTO queries (student_id, course_id, question) 
VALUES (12, 10, 'How do I start with PHP?');
```

**Quizzes:**
```sql
INSERT INTO quizzes (course_id, title) 
VALUES (10, 'PHP Basics Quiz');
-- Returns: quiz_id = 3
```

#### 4️⃣ Insert Questions
```sql
INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
VALUES (3, 'What is PHP?', 'A programming language', 'A database', 'An OS', 'A browser', 'a');
-- Returns: question_id = 25
```

#### 5️⃣ Insert Quiz Results
```sql
INSERT INTO quiz_results (student_id, quiz_id, score, taken_at) 
VALUES (12, 3, 8, NOW());
-- Returns: result_id = 7
```

#### 6️⃣ Insert Student Answers (MUST BE LAST)
```sql
INSERT INTO student_answers (student_id, quiz_id, result_id, question_id, selected_option, is_correct) 
VALUES (12, 3, 7, 25, 'a', 1);
```

---

## ⚠️ Common Mistakes to Avoid

### ❌ DON'T:
1. **Don't insert child records before parent records:**
   ```sql
   -- WRONG: This will fail!
   INSERT INTO courses (teacher_id, title) VALUES (999, 'Course'); 
   -- teacher_id=999 doesn't exist in users
   ```

2. **Don't try to insert student_answers without quiz_results:**
   ```sql
   -- WRONG: This will fail!
   INSERT INTO student_answers (...) VALUES (...);
   -- Must create quiz_result first
   ```

3. **Don't delete parent records without considering CASCADE:**
   ```sql
   -- This will CASCADE DELETE all related data!
   DELETE FROM users WHERE id = 5;
   -- Will also delete: courses, enrollments, queries, quiz_results, student_answers
   ```

### ✅ DO:
1. **Always verify parent records exist first:**
   ```sql
   SELECT id FROM users WHERE id = 5; -- Verify user exists
   INSERT INTO courses (teacher_id, ...) VALUES (5, ...);
   ```

2. **Use transactions for complex operations:**
   ```php
   $pdo->beginTransaction();
   try {
       // Insert quiz_result
       $stmt = $pdo->prepare("INSERT INTO quiz_results ...");
       $stmt->execute([...]);
       $result_id = $pdo->lastInsertId();
       
       // Insert all student_answers
       foreach ($answers as $answer) {
           $stmt = $pdo->prepare("INSERT INTO student_answers ...");
           $stmt->execute(['result_id' => $result_id, ...]);
       }
       
       $pdo->commit();
   } catch (Exception $e) {
       $pdo->rollBack();
       throw $e;
   }
   ```

---

## 🔍 Verification

### Run the Verification Script
Access the verification script via browser:
```
http://localhost/e_learning/verify_database_structure.php
```

This will show:
- ✅ All tables and their row counts
- ✅ Foreign key relationships
- ✅ Index optimization status
- ✅ Sample data queries
- ⚠️ Any issues found

### Manual Verification Queries

**Check all tables exist:**
```sql
SHOW TABLES FROM e_learning;
```

**Check foreign keys for a table:**
```sql
SELECT 
    COLUMN_NAME, 
    REFERENCED_TABLE_NAME, 
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'e_learning'
AND TABLE_NAME = 'student_answers'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

---

## 📚 Usage Examples

### Example 1: Creating a Complete Course with Quiz

```php
// 1. Teacher creates a course
$stmt = $pdo->prepare("INSERT INTO courses (teacher_id, title, description) VALUES (?, ?, ?)");
$stmt->execute([5, 'JavaScript Basics', 'Learn JavaScript']);
$course_id = $pdo->lastInsertId();

// 2. Add lessons
$stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, type, content) VALUES (?, ?, ?, ?)");
$stmt->execute([$course_id, 'Introduction', 'text', 'Welcome to JavaScript...']);
$stmt->execute([$course_id, 'Variables', 'text', 'Let and Const...']);

// 3. Create a quiz
$stmt = $pdo->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)");
$stmt->execute([$course_id, 'JavaScript Fundamentals Quiz']);
$quiz_id = $pdo->lastInsertId();

// 4. Add questions
$stmt = $pdo->prepare("
    INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$quiz_id, 'What is JavaScript?', 'A programming language', 'A database', 'An OS', 'None', 'a']);
$question_id_1 = $pdo->lastInsertId();

$stmt->execute([$quiz_id, 'Which keyword declares a constant?', 'var', 'let', 'const', 'constant', 'c']);
$question_id_2 = $pdo->lastInsertId();
```

### Example 2: Student Takes Quiz

```php
// 1. Student enrolls in course
$student_id = 12;
$stmt = $pdo->prepare("INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (?, ?)");
$stmt->execute([$student_id, $course_id]);

// 2. Student submits quiz
$answers = [
    ['question_id' => $question_id_1, 'selected' => 'a', 'correct' => true],
    ['question_id' => $question_id_2, 'selected' => 'c', 'correct' => true]
];

$score = array_sum(array_column($answers, 'correct'));

// 3. Create quiz result
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO quiz_results (student_id, quiz_id, score) VALUES (?, ?, ?)");
    $stmt->execute([$student_id, $quiz_id, $score]);
    $result_id = $pdo->lastInsertId();
    
    // 4. Insert each answer
    $stmt = $pdo->prepare("
        INSERT INTO student_answers 
        (student_id, quiz_id, result_id, question_id, selected_option, is_correct) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($answers as $answer) {
        $stmt->execute([
            $student_id,
            $quiz_id,
            $result_id,
            $answer['question_id'],
            $answer['selected'],
            $answer['correct'] ? 1 : 0
        ]);
    }
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## 🛠️ Database Setup

### Fresh Installation

1. **Import the database schema:**
   ```bash
   mysql -u root -p < database.sql
   ```

2. **Verify the structure:**
   ```bash
   mysql -u root -p e_learning -e "SHOW TABLES;"
   ```

3. **Run the verification script:**
   ```
   http://localhost/e_learning/verify_database_structure.php
   ```

### Reset Database (⚠️ WARNING: This deletes all data!)

```sql
DROP DATABASE IF EXISTS e_learning;
CREATE DATABASE e_learning;
USE e_learning;
SOURCE database.sql;
```

---

## 📞 Troubleshooting

### Issue: "Cannot add foreign key constraint"
**Solution:** You're trying to create a table that references a table that doesn't exist yet. Check the table creation order.

### Issue: "Duplicate entry" error
**Solution:** You're trying to insert a record that violates a UNIQUE constraint. Check for:
- Duplicate usernames/emails in users
- Duplicate enrollments (same student + course)
- Duplicate answers (same result_id + question_id)

### Issue: "Cannot delete or update a parent row"
**Solution:** You're trying to delete a record that has dependent records. Either delete children first, or the CASCADE DELETE will handle it automatically.

---

## ✅ Best Practices

1. **Always use transactions** for operations involving multiple related tables
2. **Check parent records exist** before inserting foreign keys
3. **Use prepared statements** to prevent SQL injection
4. **Respect the insertion order** documented in this guide
5. **Run the verification script** after any schema changes
6. **Backup your data** before making structural changes

---

**Last Updated:** 2026-02-03
**Database Version:** MySQL 5.7+ / MariaDB 10.2+
