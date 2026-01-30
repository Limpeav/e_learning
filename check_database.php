<?php
/**
 * Database Structure Checker
 * This page checks if your database tables are set up correctly for quiz retakes
 */

require_once 'config/db.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Structure Checker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error { background: #fee; color: #c00; padding: 1rem; border-radius: 0.5rem; border: 2px solid #c00; }
        .success { background: #efe; color: #060; padding: 1rem; border-radius: 0.5rem; border: 2px solid #060; }
        .warning { background: #ffeaa7; color: #d63031; padding: 1rem; border-radius: 0.5rem; border: 2px solid #fdcb6e; }
        pre { background: #1f2937; color: #10b981; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; }
        .table-structure { font-family: monospace; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white p-4">
                <h2 class="mb-0"><i class="bi bi-database-check me-2"></i>Database Structure Checker</h2>
                <p class="mb-0 small mt-2">Checking tables for quiz retake functionality</p>
            </div>
            <div class="card-body p-4">

                <?php
                $errors = [];
                $warnings = [];
                $success = [];

                // Check 1: Database Connection
                try {
                    $pdo->query("SELECT 1");
                    $success[] = "✅ Database connection successful";
                } catch (Exception $e) {
                    $errors[] = "❌ Cannot connect to database: " . $e->getMessage();
                    echo "<div class='error'>" . implode("<br>", $errors) . "</div>";
                    exit;
                }

                // Check 2: student_answers table exists
                echo "<h4 class='mt-4'>Checking <code>student_answers</code> table...</h4>";

                try {
                    $stmt = $pdo->query("SHOW TABLES LIKE 'student_answers'");
                    $tableExists = $stmt->fetch();

                    if (!$tableExists) {
                        echo "<div class='error'>";
                        echo "<h5>❌ Table 'student_answers' does NOT exist!</h5>";
                        echo "<p>This is why your quiz submission is failing.</p>";
                        echo "<h6>Fix this by running:</h6>";
                        echo "<pre>cd /Applications/XAMPP/xamppfiles/htdocs/e_learning\nmysql -u root -p e_learning < fix_student_answers_table.sql</pre>";
                        echo "<p><strong>OR</strong> use phpMyAdmin to import the SQL file.</p>";
                        echo "</div>";
                        exit;
                    } else {
                        echo "<div class='success'>✅ Table 'student_answers' exists</div>";
                    }

                } catch (Exception $e) {
                    echo "<div class='error'>❌ Error checking table: " . htmlspecialchars($e->getMessage()) . "</div>";
                    exit;
                }

                // Check 3: Table structure
                echo "<h4 class='mt-4'>Checking table structure...</h4>";

                try {
                    $stmt = $pdo->query("DESCRIBE student_answers");
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo "<table class='table table-striped table-structure'>";
                    echo "<thead><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>";
                    echo "<tbody>";

                    $hasResultId = false;
                    $hasAnsweredAt = false;

                    foreach ($columns as $col) {
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
                        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
                        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
                        echo "</tr>";

                        if ($col['Field'] === 'result_id') $hasResultId = true;
                        if ($col['Field'] === 'answered_at') $hasAnsweredAt = true;
                    }

                    echo "</tbody></table>";

                    // Check for required columns
                    if (!$hasResultId) {
                        echo "<div class='error mt-3'>";
                        echo "<h5>❌ CRITICAL: Missing 'result_id' column!</h5>";
                        echo "<p>The 'result_id' column is required for quiz retakes to work.</p>";
                        echo "<h6>Fix this immediately:</h6>";
                        echo "<pre>mysql -u root -p e_learning < fix_student_answers_table.sql</pre>";
                        echo "</div>";
                    } else {
                        echo "<div class='success mt-3'>✅ Required column 'result_id' exists</div>";
                    }

                    if (!$hasAnsweredAt) {
                        echo "<div class='warning mt-2'>⚠️ Missing 'answered_at' column (recommended but not critical)</div>";
                    }

                } catch (Exception $e) {
                    echo "<div class='error'>❌ Error checking structure: " . htmlspecialchars($e->getMessage()) . "</div>";
                }

                // Check 4: Foreign keys
                echo "<h4 class='mt-4'>Checking foreign key constraints...</h4>";

                try {
                    $stmt = $pdo->query("
                        SELECT
                            CONSTRAINT_NAME,
                            COLUMN_NAME,
                            REFERENCED_TABLE_NAME,
                            REFERENCED_COLUMN_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = 'e_learning'
                        AND TABLE_NAME = 'student_answers'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($foreignKeys)) {
                        echo "<div class='warning'>⚠️ No foreign keys found (table structure might be incomplete)</div>";
                    } else {
                        echo "<div class='success'>✅ Found " . count($foreignKeys) . " foreign key constraint(s):</div>";
                        echo "<ul class='mt-2'>";
                        foreach ($foreignKeys as $fk) {
                            echo "<li><code>" . htmlspecialchars($fk['COLUMN_NAME']) . "</code> → ";
                            echo "<code>" . htmlspecialchars($fk['REFERENCED_TABLE_NAME']) . "." . htmlspecialchars($fk['REFERENCED_COLUMN_NAME']) . "</code></li>";
                        }
                        echo "</ul>";
                    }

                } catch (Exception $e) {
                    echo "<div class='warning'>⚠️ Could not check foreign keys: " . htmlspecialchars($e->getMessage()) . "</div>";
                }

                // Check 5: Sample data
                echo "<h4 class='mt-4'>Checking existing data...</h4>";

                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM student_answers");
                    $result = $stmt->fetch();
                    $count = $result['count'];

                    if ($count > 0) {
                        echo "<div class='success'>✅ Found $count record(s) in student_answers</div>";

                        // Show sample
                        $stmt = $pdo->query("SELECT * FROM student_answers ORDER BY id DESC LIMIT 3");
                        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (!empty($samples)) {
                            echo "<h5 class='mt-3'>Sample records:</h5>";
                            echo "<pre>" . print_r($samples, true) . "</pre>";
                        }
                    } else {
                        echo "<div class='warning'>ℹ️ No records yet in student_answers (table is empty but that's OK for new installations)</div>";
                    }

                } catch (Exception $e) {
                    echo "<div class='warning'>⚠️ Could not count records: " . htmlspecialchars($e->getMessage()) . "</div>";
                }

                // Check 6: Other required tables
                echo "<h4 class='mt-4'>Checking other required tables...</h4>";

                $requiredTables = ['users', 'courses', 'quizzes', 'questions', 'quiz_results', 'enrollments'];
                $missingTables = [];

                foreach ($requiredTables as $table) {
                    try {
                        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                        if (!$stmt->fetch()) {
                            $missingTables[] = $table;
                        }
                    } catch (Exception $e) {
                        $missingTables[] = $table . " (error checking)";
                    }
                }

                if (empty($missingTables)) {
                    echo "<div class='success'>✅ All required tables exist</div>";
                } else {
                    echo "<div class='error'>❌ Missing tables: " . implode(', ', $missingTables) . "</div>";
                    echo "<p class='mt-2'>Run the full database script:</p>";
                    echo "<pre>mysql -u root -p < database.sql</pre>";
                }

                // Final summary
                echo "<hr class='my-4'>";
                echo "<h3>📊 Summary</h3>";

                if ($hasResultId && $tableExists) {
                    echo "<div class='success'>";
                    echo "<h4>✅ Everything looks good!</h4>";
                    echo "<p>Your database structure is correct for quiz retakes.</p>";
                    echo "<p><strong>If you're still getting errors:</strong></p>";
                    echo "<ol>";
                    echo "<li>Clear your browser cache (Ctrl+Shift+Delete)</li>";
                    echo "<li>Make sure you're enrolled in the course</li>";
                    echo "<li>Try answering ALL questions before submitting</li>";
                    echo "<li>Check PHP error logs in XAMPP</li>";
                    echo "</ol>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>";
                    echo "<h4>❌ Issues Found</h4>";
                    echo "<p><strong>Your database needs to be fixed!</strong></p>";
                    echo "<h5>Run this command:</h5>";
                    echo "<pre>cd /Applications/XAMPP/xamppfiles/htdocs/e_learning\nmysql -u root -p e_learning < fix_student_answers_table.sql</pre>";
                    echo "<h5>Or use phpMyAdmin:</h5>";
                    echo "<ol>";
                    echo "<li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
                    echo "<li>Select 'e_learning' database</li>";
                    echo "<li>Click 'SQL' tab</li>";
                    echo "<li>Copy contents of <code>fix_student_answers_table.sql</code></li>";
                    echo "<li>Paste and click 'Go'</li>";
                    echo "</ol>";
                    echo "</div>";
                }

                ?>

            </div>
            <div class="card-footer p-3 bg-light">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="index.php" class="btn btn-secondary">← Home</a>
                    <a href="verify_quiz_setup.php" class="btn btn-primary">Full Verification</a>
                    <button onclick="location.reload()" class="btn btn-success">🔄 Refresh Check</button>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-white">Database: e_learning | Host: localhost | User: root</small>
        </div>
    </div>
</body>
</html>
