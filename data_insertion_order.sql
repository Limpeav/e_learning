-- ================================================================
-- DATA INSERTION ORDER REFERENCE GUIDE
-- ================================================================
-- Quick reference for inserting data in the correct order
-- to avoid foreign key constraint violations
-- ================================================================

-- ================================================================
-- STEP 1: CREATE USERS
-- ================================================================
-- Must be done FIRST - No dependencies
-- Creates: Admin, Teachers, and Students

INSERT INTO users (username, email, password, role, bio, avatar) VALUES
-- Admin
('admin', 'admin@elearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', NULL),

-- Teachers
('john_teacher', 'john@elearning.com', '$2y$10$hashed_password_here', 'teacher', 'Experienced programming instructor', NULL),
('mary_teacher', 'mary@elearning.com', '$2y$10$hashed_password_here', 'teacher', 'Web development expert', NULL),

-- Students
('alice_student', 'alice@elearning.com', '$2y$10$hashed_password_here', 'student', 'Passionate learner', NULL),
('bob_student', 'bob@elearning.com', '$2y$10$hashed_password_here', 'student', 'Tech enthusiast', NULL);

-- ================================================================
-- STEP 2: CREATE COURSES
-- ================================================================
-- Requires: teacher_id from users table

INSERT INTO courses (teacher_id, title, description, thumbnail) VALUES
(2, 'HTML Fundamentals', 'Master the basics of HTML5', 'html_thumb.jpg'),
(2, 'CSS Mastery', 'Learn modern CSS techniques', 'css_thumb.jpg'),
(3, 'JavaScript Essentials', 'Complete JavaScript guide', 'js_thumb.jpg'),
(3, 'PHP Backend Development', 'Server-side programming with PHP', 'php_thumb.jpg');

-- ================================================================
-- STEP 3: CREATE LESSONS, ENROLLMENTS, QUERIES, QUIZZES
-- ================================================================
-- These can be created in any order as they're at the same dependency level

-- 3A. LESSONS
-- Requires: course_id from courses table

INSERT INTO lessons (course_id, title, type, content) VALUES
-- HTML Course Lessons
(1, 'Introduction to HTML', 'text', 'HTML stands for HyperText Markup Language...'),
(1, 'HTML Tags and Elements', 'text', 'HTML uses tags to structure content...'),
(1, 'HTML Forms', 'text', 'Forms are used to collect user input...'),

-- CSS Course Lessons
(2, 'CSS Basics', 'text', 'CSS controls the visual presentation...'),
(2, 'CSS Selectors', 'text', 'Selectors target HTML elements...'),

-- JavaScript Course Lessons
(3, 'JavaScript Intro', 'text', 'JavaScript adds interactivity...'),
(3, 'Variables and Data Types', 'text', 'Learn about var, let, and const...');

-- 3B. ENROLLMENTS
-- Requires: student_id from users, course_id from courses

INSERT INTO enrollments (student_id, course_id) VALUES
(4, 1), -- alice enrolls in HTML
(4, 2), -- alice enrolls in CSS
(4, 3), -- alice enrolls in JavaScript
(5, 1), -- bob enrolls in HTML
(5, 4); -- bob enrolls in PHP

-- 3C. QUERIES
-- Requires: student_id from users, course_id from courses

INSERT INTO queries (student_id, course_id, question, answer, answered_at) VALUES
(4, 1, 'What is the difference between div and span?', 'Div is block-level, span is inline', NOW()),
(5, 1, 'How do I create a table in HTML?', NULL, NULL), -- Unanswered
(4, 3, 'What is the difference between let and const?', 'const cannot be reassigned', NOW());

-- 3D. QUIZZES
-- Requires: course_id from courses

INSERT INTO quizzes (course_id, title) VALUES
(1, 'HTML Fundamentals Quiz'),
(2, 'CSS Basics Quiz'),
(3, 'JavaScript Variables Quiz'),
(4, 'PHP Syntax Quiz');

-- ================================================================
-- STEP 4: CREATE QUESTIONS
-- ================================================================
-- Requires: quiz_id from quizzes table

INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES
-- HTML Quiz Questions
(1, 'What does HTML stand for?', 'HyperText Markup Language', 'Home Tool Markup Language', 'Hyperlinks and Text Markup Language', 'None of the above', 'a'),
(1, 'Which tag is used for the largest heading?', '<h1>', '<h6>', '<heading>', '<head>', 'a'),
(1, 'Which tag is used to create a hyperlink?', '<link>', '<a>', '<href>', '<url>', 'b'),

-- CSS Quiz Questions
(2, 'What does CSS stand for?', 'Cascading Style Sheets', 'Computer Style Sheets', 'Creative Style Sheets', 'Colorful Style Sheets', 'a'),
(2, 'Which property is used to change text color?', 'font-color', 'text-color', 'color', 'fgcolor', 'c'),

-- JavaScript Quiz Questions
(3, 'Which keyword is used to declare a variable?', 'var', 'let', 'const', 'All of the above', 'd'),
(3, 'What is the correct syntax for a comment?', '// comment', '/* comment */', '<!-- comment -->', 'Both A and B', 'd'),

-- PHP Quiz Questions
(4, 'PHP stands for?', 'Personal Home Page', 'PHP: Hypertext Preprocessor', 'Private Homepage', 'Public Hypertext Protocol', 'b');

-- ================================================================
-- STEP 5: CREATE QUIZ RESULTS
-- ================================================================
-- Requires: student_id from users, quiz_id from quizzes

INSERT INTO quiz_results (student_id, quiz_id, score, taken_at) VALUES
(4, 1, 3, '2026-01-28 10:30:00'), -- Alice took HTML quiz, scored 3/3
(4, 3, 2, '2026-01-29 14:15:00'), -- Alice took JavaScript quiz, scored 2/2
(5, 1, 2, '2026-01-28 16:45:00'); -- Bob took HTML quiz, scored 2/3

-- ================================================================
-- STEP 6: CREATE STUDENT ANSWERS
-- ================================================================
-- Requires: student_id, quiz_id, result_id, question_id
-- MUST BE LAST - Has the most dependencies

-- Alice's HTML Quiz Answers (result_id = 1)
INSERT INTO student_answers (student_id, quiz_id, result_id, question_id, selected_option, is_correct) VALUES
(4, 1, 1, 1, 'a', 1), -- Question 1: Correct
(4, 1, 1, 2, 'a', 1), -- Question 2: Correct
(4, 1, 1, 3, 'b', 1); -- Question 3: Correct

-- Alice's JavaScript Quiz Answers (result_id = 2)
INSERT INTO student_answers (student_id, quiz_id, result_id, question_id, selected_option, is_correct) VALUES
(4, 3, 2, 6, 'd', 1), -- Question 6: Correct
(4, 3, 2, 7, 'd', 1); -- Question 7: Correct

-- Bob's HTML Quiz Answers (result_id = 3)
INSERT INTO student_answers (student_id, quiz_id, result_id, question_id, selected_option, is_correct) VALUES
(5, 1, 3, 1, 'a', 1), -- Question 1: Correct
(5, 1, 3, 2, 'a', 1), -- Question 2: Correct
(5, 1, 3, 3, 'a', 0); -- Question 3: Wrong (selected 'a' instead of 'b')

-- ================================================================
-- VERIFICATION QUERIES
-- ================================================================
-- Run these to verify your data was inserted correctly

-- Check all users
SELECT id, username, role FROM users;

-- Check courses with teacher names
SELECT c.id, c.title, u.username as teacher 
FROM courses c 
JOIN users u ON c.teacher_id = u.id;

-- Check enrollments
SELECT e.id, u.username as student, c.title as course 
FROM enrollments e 
JOIN users u ON e.student_id = u.id 
JOIN courses c ON e.course_id = c.id;

-- Check quiz results with scores
SELECT 
    u.username as student, 
    c.title as course,
    q.title as quiz,
    qr.score,
    COUNT(ques.id) as total_questions
FROM quiz_results qr
JOIN users u ON qr.student_id = u.id
JOIN quizzes q ON qr.quiz_id = q.id
JOIN courses c ON q.course_id = c.id
JOIN questions ques ON ques.quiz_id = q.id
GROUP BY qr.id;

-- Check student answers with correctness
SELECT 
    u.username as student,
    q.title as quiz,
    ques.question_text,
    sa.selected_option,
    ques.correct_option,
    sa.is_correct
FROM student_answers sa
JOIN users u ON sa.student_id = u.id
JOIN quizzes q ON sa.quiz_id = q.id
JOIN questions ques ON sa.question_id = ques.id
ORDER BY sa.student_id, sa.quiz_id, sa.question_id;

-- ================================================================
-- IMPORTANT NOTES
-- ================================================================
-- 
-- 1. ALWAYS insert in this order:
--    users → courses → (lessons/enrollments/queries/quizzes) → 
--    questions → quiz_results → student_answers
--
-- 2. Use transactions for complex operations:
--    START TRANSACTION;
--    -- your inserts here
--    COMMIT;
--
-- 3. Verify parent records exist before inserting:
--    SELECT id FROM users WHERE id = 5;
--
-- 4. Handle errors gracefully:
--    Use INSERT IGNORE for duplicate prevention
--    Use ON DUPLICATE KEY UPDATE for upserts
--
-- 5. Remember: Deleting a parent record will CASCADE delete all children
--
-- ================================================================
