<?php
/**
 * Quiz System Verification Script
 * This script verifies that the database is properly set up for the quiz system
 * Run this file in your browser to check the setup
 */

require_once 'config/db.php';

// Set header
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz System Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        .verification-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            margin: 0 auto;
        }
        .check-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .status-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .status-success {
            background: #10b981;
            color: white;
        }
        .status-error {
            background: #ef4444;
            color: white;
        }
        .status-warning {
            background: #f59e0b;
            color: white;
        }
        pre {
            background: #1f2937;
            color: #10b981;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <div class="card-header bg-primary text-white p-4">
            <h2 class="mb-0"><i class="bi bi-shield-check me-2"></i>Quiz System Verification</h2>
            <p class="mb-0 small opacity-75 mt-2">Checking database setup and table structures</p>
        </div>
        <div class="card-body p-0">
            <?php
            $checks = [];
            $all_passed = true;

            // Check 1: Database Connection
            try {
                $pdo->query("SELECT 1");
                $checks[] = [
                    'name' => 'Database Connection',
                    'status' => 'success',
                    'message' => 'Successfully connected to database'
                ];
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'Database Connection',
                    'status' => 'error',
                    'message' => 'Failed to connect: ' . $e->getMessage()
                ];
                $all_passed = false;
            }

            // Check 2: Required Tables
            $required_tables = ['users', 'courses', 'lessons', 'enrollments', 'quizzes', 'questions', 'quiz_results', 'student_answers', 'queries'];

            try {
                $stmt = $pdo->query("SHOW TABLES");
                $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $missing_tables = array_diff($required_tables, $existing_tables);

                if (empty($missing_tables)) {
                    $checks[] = [
                        'name' => 'Required Tables',
                        'status' => 'success',
                        'message' => 'All ' . count($required_tables) . ' required tables exist',
                        'details' => implode(', ', $required_tables)
                    ];
                } else {
                    $checks[] = [
                        'name' => 'Required Tables',
                        'status' => 'error',
                        'message' => 'Missing tables: ' . implode(', ', $missing_tables),
                        'details' => 'Please run the database.sql or migration script'
                    ];
                    $all_passed = false;
                }
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'Required Tables',
                    'status' => 'error',
                    'message' => 'Error checking tables: ' . $e->getMessage()
                ];
                $all_passed = false;
            }

            // Check 3: student_answers Table Structure
            try {
                $stmt = $pdo->query("DESCRIBE student_answers");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $required_columns = ['id', 'student_id', 'quiz_id', 'question_id', 'selected_option', 'is_correct', 'answered_at'];
                $existing_columns = array_column($columns, 'Field');
                $missing_columns = array_diff($required_columns, $existing_columns);

                if (empty($missing_columns)) {
                    $checks[] = [
                        'name' => 'student_answers Table Structure',
                        'status' => 'success',
                        'message' => 'Table structure is correct with all ' . count($required_columns) . ' columns',
                        'show_structure' => true,
                        'structure' => $columns
                    ];
                } else {
                    $checks[] = [
                        'name' => 'student_answers Table Structure',
                        'status' => 'warning',
                        'message' => 'Missing columns: ' . implode(', ', $missing_columns)
                    ];
                    $all_passed = false;
                }
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'student_answers Table Structure',
                    'status' => 'error',
                    'message' => 'Table does not exist or error occurred: ' . $e->getMessage(),
                    'details' => 'Run: mysql -u root -p e_learning < add_student_answers_table.sql'
                ];
                $all_passed = false;
            }

            // Check 4: Foreign Key Constraints
            try {
                $stmt = $pdo->query("
                    SELECT
                        CONSTRAINT_NAME,
                        TABLE_NAME,
                        REFERENCED_TABLE_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'student_answers'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                $foreign_keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($foreign_keys) >= 3) {
                    $checks[] = [
                        'name' => 'Foreign Key Constraints',
                        'status' => 'success',
                        'message' => count($foreign_keys) . ' foreign key constraints found',
                        'details' => 'student_id → users, quiz_id → quizzes, question_id → questions'
                    ];
                } else {
                    $checks[] = [
                        'name' => 'Foreign Key Constraints',
                        'status' => 'warning',
                        'message' => 'Expected 3 foreign keys, found ' . count($foreign_keys)
                    ];
                }
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'Foreign Key Constraints',
                    'status' => 'error',
                    'message' => 'Error checking constraints: ' . $e->getMessage()
                ];
            }

            // Check 5: Sample Data
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM quizzes");
                $quiz_count = $stmt->fetch()['count'];

                $stmt = $pdo->query("SELECT COUNT(*) as count FROM questions");
                $question_count = $stmt->fetch()['count'];

                if ($quiz_count > 0 && $question_count > 0) {
                    $checks[] = [
                        'name' => 'Sample Data',
                        'status' => 'success',
                        'message' => "Found {$quiz_count} quiz(es) and {$question_count} question(s)"
                    ];
                } else {
                    $checks[] = [
                        'name' => 'Sample Data',
                        'status' => 'warning',
                        'message' => 'No quizzes or questions found. Create some via teacher dashboard.'
                    ];
                }
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'Sample Data',
                    'status' => 'error',
                    'message' => 'Error checking data: ' . $e->getMessage()
                ];
            }

            // Display Checks
            foreach ($checks as $check) {
                $icon_class = 'status-' . $check['status'];
                $icon = $check['status'] === 'success' ? 'check-lg' : ($check['status'] === 'error' ? 'x-lg' : 'exclamation-lg');
                ?>
                <div class="check-item">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold"><?php echo $check['name']; ?></h6>
                        <p class="mb-0 text-muted small"><?php echo $check['message']; ?></p>
                        <?php if (isset($check['details'])): ?>
                            <p class="mb-0 mt-1 small text-primary"><i class="bi bi-info-circle me-1"></i><?php echo $check['details']; ?></p>
                        <?php endif; ?>

                        <?php if (isset($check['show_structure']) && $check['show_structure']): ?>
                            <details class="mt-2">
                                <summary class="text-primary small" style="cursor: pointer;">View Table Structure</summary>
                                <pre class="mt-2 mb-0"><?php
                                    foreach ($check['structure'] as $col) {
                                        echo sprintf("%-20s %-20s %s\n",
                                            $col['Field'],
                                            $col['Type'],
                                            $col['Key'] ? '[' . $col['Key'] . ']' : ''
                                        );
                                    }
                                ?></pre>
                            </details>
                        <?php endif; ?>
                    </div>
                    <div class="status-icon <?php echo $icon_class; ?>">
                        <i class="bi bi-<?php echo $icon; ?>"></i>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="card-footer p-4 bg-light">
            <?php if ($all_passed): ?>
                <div class="alert alert-success mb-0 d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                    <div>
                        <div class="fw-bold">All Checks Passed! ✅</div>
                        <div class="small">Your quiz system is properly configured and ready to use.</div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <a href="index.php" class="btn btn-primary"><i class="bi bi-house-fill me-2"></i>Go to Homepage</a>
                    <a href="views/student/dashboard.php" class="btn btn-outline-primary"><i class="bi bi-speedometer2 me-2"></i>Student Dashboard</a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0 d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>
                        <div class="fw-bold">Action Required</div>
                        <div class="small">Some checks failed. Please review the issues above and fix them.</div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6 class="fw-bold mb-2">Quick Fix:</h6>
                    <pre>cd /Applications/XAMPP/xamppfiles/htdocs/e_learning
mysql -u root -p e_learning < add_student_answers_table.sql</pre>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="?refresh=1" class="btn btn-light"><i class="bi bi-arrow-clockwise me-2"></i>Refresh Verification</a>
    </div>
</body>
</html>
