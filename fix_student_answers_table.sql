-- ============================================================================
-- QUICK FIX SCRIPT: Update student_answers table for Quiz Retake Feature
-- ============================================================================
-- This script fixes the student_answers table structure to support retakes
-- Run this script if you're getting errors about missing columns
-- ============================================================================

USE e_learning;

-- Backup existing data (if table exists)
CREATE TABLE IF NOT EXISTS student_answers_backup AS
SELECT * FROM student_answers WHERE 1=0;

-- Check if table exists and has data
SET @table_exists = (SELECT COUNT(*) FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = 'e_learning' AND TABLE_NAME = 'student_answers');

-- If table exists, backup the data
INSERT INTO student_answers_backup
SELECT * FROM student_answers WHERE @table_exists > 0;

-- Drop the old table
DROP TABLE IF EXISTS student_answers;

-- Create the new table with correct structure
CREATE TABLE student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    quiz_id INT NOT NULL,
    result_id INT NOT NULL COMMENT 'Links to quiz_results.id for attempt tracking',
    question_id INT NOT NULL,
    selected_option ENUM('a', 'b', 'c', 'd') NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (result_id) REFERENCES quiz_results(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,

    -- Indexes for performance
    INDEX idx_student_quiz (student_id, quiz_id),
    INDEX idx_result (result_id),
    INDEX idx_question (question_id),

    -- Ensure one answer per question per attempt
    UNIQUE KEY unique_answer (result_id, question_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores individual student answers for each quiz attempt';

-- ============================================================================
-- Verification
-- ============================================================================

SELECT '✅ Table created successfully!' AS Status;
DESCRIBE student_answers;

SELECT CONCAT('✅ Found ', COUNT(*), ' foreign key constraints') AS ForeignKeys
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'e_learning'
AND TABLE_NAME = 'student_answers'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Check if backup has data
SELECT CONCAT('ℹ️  Backup contains ', COUNT(*), ' records') AS BackupInfo
FROM student_answers_backup;

-- ============================================================================
-- Instructions
-- ============================================================================
--
-- HOW TO RUN THIS SCRIPT:
--
-- Option 1 - Command Line:
--   mysql -u root -p e_learning < fix_student_answers_table.sql
--
-- Option 2 - phpMyAdmin:
--   1. Open phpMyAdmin
--   2. Select 'e_learning' database
--   3. Click 'SQL' tab
--   4. Paste this entire file
--   5. Click 'Go'
--
-- Option 3 - MySQL Workbench:
--   1. Open MySQL Workbench
--   2. File > Run SQL Script
--   3. Select this file
--   4. Execute
--
-- IMPORTANT NOTES:
-- - This script will DROP the old student_answers table
-- - A backup is created in student_answers_backup
-- - Existing answer data will be lost (students need to retake quizzes)
-- - This is necessary because the old structure can't support retakes
-- - Quiz results are NOT affected, only detailed answers
--
-- After running this script:
-- - Clear your browser cache
-- - Refresh the quiz page
-- - Students can now retake quizzes unlimited times!
--
-- ============================================================================
