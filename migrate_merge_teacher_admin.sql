-- ================================================================
-- MIGRATION: Merge Teacher Role into Admin Role
-- ================================================================
-- This migration combines the teacher and admin roles into a single admin role
-- Run this SQL to update your e_learning database
-- ================================================================

USE e_learning;

-- Step 1: Update all existing teacher users to admin role
UPDATE users SET role = 'admin' WHERE role = 'teacher';

-- Step 2: Modify the role enum to remove 'teacher' option
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'student') NOT NULL DEFAULT 'student';

-- ================================================================
-- MIGRATION COMPLETE
-- ================================================================
-- All teachers are now admins
-- The role column now only accepts 'admin' or 'student'
-- ================================================================
