-- ================================================================
-- LESSONS TABLE MIGRATION SCRIPT
-- ================================================================
-- This script adds missing columns to the lessons table
-- Run this if you already have an existing lessons table
-- ================================================================

USE e_learning;

-- Check if columns exist before adding them
-- Add material_path column if it doesn't exist
ALTER TABLE lessons 
ADD COLUMN IF NOT EXISTS material_path VARCHAR(255) COMMENT 'Optional downloadable material (PDF, DOC, PPT, etc.)';

-- Add updated_at column if it doesn't exist
ALTER TABLE lessons 
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Track lesson modifications';

-- Add order_index column if it doesn't exist  
ALTER TABLE lessons 
ADD COLUMN IF NOT EXISTS order_index INT DEFAULT 0 COMMENT 'For custom ordering of lessons';

-- Add index for ordering
ALTER TABLE lessons 
ADD INDEX IF NOT EXISTS idx_order (course_id, order_index);

-- Update type column to have default value
ALTER TABLE lessons 
MODIFY COLUMN type ENUM('video', 'pdf', 'text') NOT NULL DEFAULT 'text';

-- Update existing lessons to set order_index based on current IDs
-- This gives existing lessons sequential order
UPDATE lessons 
SET order_index = id 
WHERE order_index = 0;

SELECT 'Lessons table migration completed successfully!' as Status;

-- ================================================================
-- VERIFICATION QUERY
-- ================================================================
-- Run this to verify all columns exist
DESCRIBE lessons;
