-- Migration Script: Create/Update student_answers table with result_id
-- This script creates the student_answers table with improved attempt tracking
-- Run this if you already have an existing e_learning database

USE e_learning;

-- IMPORTANT: This will drop the existing student_answers table if it exists
-- Uncomment the next line ONLY if you want to recreate the table from scratch
-- WARNING: This will delete all existing answer data!
-- DROP TABLE IF EXISTS student_answers;

-- Create student_answers table with result_id for better attempt tracking
CREATE TABLE IF NOT EXISTS student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    quiz_id INT NOT NULL,
    result_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option ENUM('a', 'b', 'c', 'd') NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (result_id) REFERENCES quiz_results(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_student_quiz (student_id, quiz_id),
    INDEX idx_result (result_id),
    INDEX idx_question (question_id),
    UNIQUE KEY unique_answer (result_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify table creation
SELECT 'student_answers table created/verified successfully!' AS status;

-- Show table structure
DESCRIBE student_answers;

-- Show existing records count
SELECT COUNT(*) as total_records FROM student_answers;

-- Show sample data (if any exists)
SELECT * FROM student_answers LIMIT 5;

-- Verify foreign keys
SELECT
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'e_learning'
AND TABLE_NAME = 'student_answers'
AND REFERENCED_TABLE_NAME IS NOT NULL;
