<?php
/**
 * DATABASE VERIFICATION AND SETUP SCRIPT
 * ======================================
 * This script verifies that all database tables are properly created
 * in the correct order with all foreign key constraints intact.
 * 
 * Usage: Access this file via browser or run via command line
 */

require_once 'config/db.php';

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>E-Learning Database Verification</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #3498db; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-success { background: #27ae60; color: white; }
        .badge-error { background: #e74c3c; color: white; }
        .section { margin: 20px 0; padding: 15px; background: #ecf0f1; border-radius: 5px; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🗄️ E-Learning Database Verification Report</h1>";
echo "<p class='info'>Generated on: " . date('Y-m-d H:i:s') . "</p>";

// ================================================================
// SECTION 1: Database Connection Test
// ================================================================
echo "<h2>1. Database Connection</h2>";
echo "<div class='section'>";
try {
    $pdo->query("SELECT 1");
    echo "<p class='success'>✅ Database connection successful!</p>";
    
    // Get database info
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $db_info = $stmt->fetch();
    echo "<p>Connected to database: <strong>{$db_info['db_name']}</strong></p>";
    
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "<p>MySQL Version: <strong>{$version['version']}</strong></p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}
echo "</div>";

// ================================================================
// SECTION 2: Table Existence Check (in correct order)
// ================================================================
echo "<h2>2. Table Structure Verification</h2>";
echo "<div class='section'>";
echo "<p>Checking if all tables exist in the correct dependency order...</p>";

$required_tables = [
    '1. users' => 'users',
    '2. courses' => 'courses',
    '3. lessons' => 'lessons',
    '4. enrollments' => 'enrollments',
    '5. queries' => 'queries',
    '6. quizzes' => 'quizzes',
    '7. questions' => 'questions',
    '8. quiz_results' => 'quiz_results',
    '9. student_answers' => 'student_answers'
];

echo "<table>";
echo "<tr><th>Order</th><th>Table Name</th><th>Status</th><th>Row Count</th><th>Created At</th></tr>";

$all_tables_exist = true;
foreach ($required_tables as $order => $table) {
    try {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            // Get row count
            $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $count_stmt->fetch()['count'];
            
            // Get table creation time
            $info_stmt = $pdo->query("SHOW TABLE STATUS LIKE '$table'");
            $table_info = $info_stmt->fetch();
            $created = $table_info['Create_time'] ?? 'N/A';
            
            echo "<tr>";
            echo "<td>$order</td>";
            echo "<td><strong>$table</strong></td>";
            echo "<td><span class='badge badge-success'>EXISTS</span></td>";
            echo "<td>$count rows</td>";
            echo "<td>$created</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td>$order</td>";
            echo "<td><strong>$table</strong></td>";
            echo "<td><span class='badge badge-error'>MISSING</span></td>";
            echo "<td colspan='2'>-</td>";
            echo "</tr>";
            $all_tables_exist = false;
        }
    } catch (PDOException $e) {
        echo "<tr>";
        echo "<td>$order</td>";
        echo "<td><strong>$table</strong></td>";
        echo "<td><span class='badge badge-error'>ERROR</span></td>";
        echo "<td colspan='2'>" . $e->getMessage() . "</td>";
        echo "</tr>";
        $all_tables_exist = false;
    }
}
echo "</table>";

if ($all_tables_exist) {
    echo "<p class='success'>✅ All required tables exist!</p>";
} else {
    echo "<p class='error'>❌ Some tables are missing. Please run the database.sql script.</p>";
}
echo "</div>";

// ================================================================
// SECTION 3: Foreign Key Constraints Verification
// ================================================================
echo "<h2>3. Foreign Key Constraints</h2>";
echo "<div class='section'>";
echo "<p>Verifying that all foreign key relationships are properly established...</p>";

$expected_fks = [
    'courses' => ['teacher_id → users(id)'],
    'lessons' => ['course_id → courses(id)'],
    'enrollments' => ['student_id → users(id)', 'course_id → courses(id)'],
    'queries' => ['student_id → users(id)', 'course_id → courses(id)'],
    'quizzes' => ['course_id → courses(id)'],
    'questions' => ['quiz_id → quizzes(id)'],
    'quiz_results' => ['student_id → users(id)', 'quiz_id → quizzes(id)'],
    'student_answers' => [
        'student_id → users(id)', 
        'quiz_id → quizzes(id)', 
        'result_id → quiz_results(id)', 
        'question_id → questions(id)'
    ]
];

echo "<table>";
echo "<tr><th>Table</th><th>Foreign Keys</th><th>Status</th></tr>";

foreach ($expected_fks as $table => $fks) {
    try {
        // Get foreign keys for this table
        $stmt = $pdo->query("
            SELECT 
                COLUMN_NAME, 
                REFERENCED_TABLE_NAME, 
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = '$table'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $actual_fks = $stmt->fetchAll();
        
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td>";
        
        if (count($actual_fks) > 0) {
            echo "<ul style='margin: 0; padding-left: 20px;'>";
            foreach ($actual_fks as $fk) {
                echo "<li>{$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}({$fk['REFERENCED_COLUMN_NAME']})</li>";
            }
            echo "</ul>";
        } else {
            echo "No foreign keys found";
        }
        
        echo "</td>";
        echo "<td>";
        if (count($actual_fks) == count($fks)) {
            echo "<span class='badge badge-success'>OK (" . count($actual_fks) . ")</span>";
        } else {
            echo "<span class='badge badge-error'>Expected: " . count($fks) . ", Found: " . count($actual_fks) . "</span>";
        }
        echo "</td>";
        echo "</tr>";
    } catch (PDOException $e) {
        echo "<tr><td><strong>$table</strong></td><td colspan='2' class='error'>" . $e->getMessage() . "</td></tr>";
    }
}
echo "</table>";
echo "</div>";

// ================================================================
// SECTION 4: Index Verification
// ================================================================
echo "<h2>4. Index Optimization</h2>";
echo "<div class='section'>";
echo "<p>Checking database indexes for query performance...</p>";

echo "<table>";
echo "<tr><th>Table</th><th>Index Name</th><th>Columns</th><th>Type</th></tr>";

foreach ($required_tables as $order => $table) {
    try {
        $stmt = $pdo->query("SHOW INDEX FROM $table");
        $indexes = $stmt->fetchAll();
        
        $index_count = 0;
        foreach ($indexes as $index) {
            if ($index_count === 0) {
                echo "<tr>";
                echo "<td rowspan='" . count($indexes) . "'><strong>$table</strong></td>";
            } else {
                echo "<tr>";
            }
            
            $key_type = $index['Key_name'] == 'PRIMARY' ? 'PRIMARY KEY' : 
                       ($index['Non_unique'] == 0 ? 'UNIQUE' : 'INDEX');
            
            echo "<td>{$index['Key_name']}</td>";
            echo "<td>{$index['Column_name']}</td>";
            echo "<td>$key_type</td>";
            echo "</tr>";
            
            $index_count++;
        }
        
        if ($index_count === 0) {
            echo "<tr><td><strong>$table</strong></td><td colspan='3'>No indexes</td></tr>";
        }
    } catch (PDOException $e) {
        echo "<tr><td><strong>$table</strong></td><td colspan='3' class='error'>" . $e->getMessage() . "</td></tr>";
    }
}
echo "</table>";
echo "</div>";

// ================================================================
// SECTION 5: Data Insertion Order Test
// ================================================================
echo "<h2>5. Data Insertion Order Guide</h2>";
echo "<div class='section'>";
echo "<p class='warning'>⚠️ When inserting data, ALWAYS follow this order to respect foreign key constraints:</p>";

echo "<ol style='line-height: 2;'>";
echo "<li><strong>users</strong> - Must be inserted first (no dependencies)</li>";
echo "<li><strong>courses</strong> - Requires: teacher_id from users</li>";
echo "<li><strong>lessons</strong> - Requires: course_id from courses</li>";
echo "<li><strong>enrollments</strong> - Requires: student_id from users, course_id from courses</li>";
echo "<li><strong>queries</strong> - Requires: student_id from users, course_id from courses</li>";
echo "<li><strong>quizzes</strong> - Requires: course_id from courses</li>";
echo "<li><strong>questions</strong> - Requires: quiz_id from quizzes</li>";
echo "<li><strong>quiz_results</strong> - Requires: student_id from users, quiz_id from quizzes</li>";
echo "<li><strong>student_answers</strong> - Requires: ALL of the above (student_id, quiz_id, result_id, question_id)</li>";
echo "</ol>";

echo "<h3>Example Data Flow:</h3>";
echo "<pre>";
echo "1. Create User (student)        → Returns user_id = 5
2. Create Course (teacher)      → Returns course_id = 10
3. Enroll Student in Course     → Uses user_id=5, course_id=10
4. Create Quiz for Course       → Uses course_id=10, returns quiz_id=3
5. Add Questions to Quiz        → Uses quiz_id=3, returns question_ids
6. Student Takes Quiz           → Creates quiz_result, returns result_id=7
7. Record Student Answers       → Uses user_id=5, quiz_id=3, result_id=7, question_id";
echo "</pre>";
echo "</div>";

// ================================================================
// SECTION 6: Sample Queries
// ================================================================
echo "<h2>6. Sample Data Queries</h2>";
echo "<div class='section'>";

// User statistics
try {
    $stmt = $pdo->query("
        SELECT 
            role,
            COUNT(*) as count
        FROM users
        GROUP BY role
    ");
    $user_stats = $stmt->fetchAll();
    
    echo "<h3>User Statistics:</h3>";
    echo "<table>";
    echo "<tr><th>Role</th><th>Count</th></tr>";
    foreach ($user_stats as $stat) {
        echo "<tr><td>{$stat['role']}</td><td>{$stat['count']}</td></tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p class='error'>Error fetching user statistics: " . $e->getMessage() . "</p>";
}

// Course statistics
try {
    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.title,
            u.username as teacher,
            COUNT(DISTINCT l.id) as lesson_count,
            COUNT(DISTINCT q.id) as quiz_count,
            COUNT(DISTINCT e.id) as enrollment_count
        FROM courses c
        LEFT JOIN users u ON c.teacher_id = u.id
        LEFT JOIN lessons l ON c.id = l.course_id
        LEFT JOIN quizzes q ON c.id = q.course_id
        LEFT JOIN enrollments e ON c.id = e.course_id
        GROUP BY c.id
        LIMIT 10
    ");
    $courses = $stmt->fetchAll();
    
    echo "<h3>Course Overview (Top 10):</h3>";
    if (count($courses) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Teacher</th><th>Lessons</th><th>Quizzes</th><th>Enrollments</th></tr>";
        foreach ($courses as $course) {
            echo "<tr>";
            echo "<td>{$course['id']}</td>";
            echo "<td>{$course['title']}</td>";
            echo "<td>{$course['teacher']}</td>";
            echo "<td>{$course['lesson_count']}</td>";
            echo "<td>{$course['quiz_count']}</td>";
            echo "<td>{$course['enrollment_count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No courses found in database.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>Error fetching course data: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ================================================================
// SECTION 7: Final Summary
// ================================================================
echo "<h2>7. Summary & Recommendations</h2>";
echo "<div class='section'>";

$issues = [];
if (!$all_tables_exist) {
    $issues[] = "Some tables are missing from the database";
}

if (count($issues) == 0) {
    echo "<p class='success'>✅ Your database is properly configured!</p>";
    echo "<p>All tables exist in the correct order with proper foreign key constraints.</p>";
} else {
    echo "<p class='error'>❌ Issues found:</p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "<p class='warning'><strong>Recommendation:</strong> Run the database.sql file to create missing tables:</p>";
    echo "<pre>mysql -u root -p e_learning < database.sql</pre>";
}

echo "</div>";

echo "</div></body></html>";
?>
