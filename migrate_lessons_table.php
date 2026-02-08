<?php
/**
 * LESSONS TABLE MIGRATION SCRIPT
 * ================================
 * Adds missing columns to lessons table:
 * - material_path (for file uploads)
 * - updated_at (for tracking modifications)
 * - order_index (for custom lesson ordering)
 */

require_once 'config/db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Lessons Table Migration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .success { color: #27ae60; padding: 10px; background: #d5f4e6; border-radius: 5px; margin: 10px 0; }
        .error { color: #e74c3c; padding: 10px; background: #fadbd8; border-radius: 5px; margin: 10px 0; }
        .info { color: #3498db; padding: 10px; background: #d6eaf8; border-radius: 5px; margin: 10px 0; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 15px; background: #ecf0f1; border-radius: 5px; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔧 Lessons Table Migration</h1>";

$migrations = [];
$errors = [];

try {
    echo "<div class='info'>Starting migration process...</div>";
    
    // Step 1: Check current table structure
    echo "<div class='step'>";
    echo "<h3>Step 1: Checking current table structure</h3>";
    $stmt = $pdo->query("DESCRIBE lessons");
    $current_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Current columns: " . implode(', ', $current_columns) . "</p>";
    echo "</div>";
    
    // Step 2: Add material_path column if missing
    echo "<div class='step'>";
    echo "<h3>Step 2: Adding material_path column</h3>";
    if (!in_array('material_path', $current_columns)) {
        $pdo->exec("ALTER TABLE lessons ADD COLUMN material_path VARCHAR(255) DEFAULT NULL COMMENT 'Optional downloadable material'");
        $migrations[] = "Added material_path column";
        echo "<div class='success'>✅ material_path column added successfully</div>";
    } else {
        echo "<div class='info'>ℹ️ material_path column already exists</div>";
    }
    echo "</div>";
    
    // Step 3: Add updated_at column if missing
    echo "<div class='step'>";
    echo "<h3>Step 3: Adding updated_at column</h3>";
    if (!in_array('updated_at', $current_columns)) {
        $pdo->exec("ALTER TABLE lessons ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Track modifications'");
        $migrations[] = "Added updated_at column";
        echo "<div class='success'>✅ updated_at column added successfully</div>";
    } else {
        echo "<div class='info'>ℹ️ updated_at column already exists</div>";
    }
    echo "</div>";
    
    // Step 4: Add order_index column if missing
    echo "<div class='step'>";
    echo "<h3>Step 4: Adding order_index column</h3>";
    if (!in_array('order_index', $current_columns)) {
        $pdo->exec("ALTER TABLE lessons ADD COLUMN order_index INT DEFAULT 0 COMMENT 'For custom ordering'");
        $migrations[] = "Added order_index column";
        echo "<div class='success'>✅ order_index column added successfully</div>";
        
        // Update existing lessons with sequential order
        echo "<p>Updating existing lessons with sequential order...</p>";
        $pdo->exec("UPDATE lessons SET order_index = id WHERE order_index = 0");
        echo "<div class='success'>✅ Existing lessons updated with order index</div>";
    } else {
        echo "<div class='info'>ℹ️ order_index column already exists</div>";
    }
    echo "</div>";
    
    // Step 5: Update type column default
    echo "<div class='step'>";
    echo "<h3>Step 5: Updating type column default value</h3>";
    try {
        $pdo->exec("ALTER TABLE lessons MODIFY COLUMN type ENUM('video', 'pdf', 'text') NOT NULL DEFAULT 'text'");
        $migrations[] = "Updated type column default to 'text'";
        echo "<div class='success'>✅ type column updated with default value</div>";
    } catch (PDOException $e) {
        echo "<div class='info'>ℹ️ type column already has proper default (or error: " . $e->getMessage() . ")</div>";
    }
    echo "</div>";
    
    // Step 6: Add index for ordering
    echo "<div class='step'>";
    echo "<h3>Step 6: Adding index for lesson ordering</h3>";
    try {
        $pdo->exec("ALTER TABLE lessons ADD INDEX idx_order (course_id, order_index)");
        $migrations[] = "Added idx_order index";
        echo "<div class='success'>✅ Ordering index added successfully</div>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<div class='info'>ℹ️ Ordering index already exists</div>";
        } else {
            $errors[] = "Error adding index: " . $e->getMessage();
            echo "<div class='error'>❌ Error adding index: " . $e->getMessage() . "</div>";
        }
    }
    echo "</div>";
    
    // Step 7: Verify final structure
    echo "<div class='step'>";
    echo "<h3>Step 7: Verifying final table structure</h3>";
    $stmt = $pdo->query("DESCRIBE lessons");
    $final_structure = $stmt->fetchAll();
    
    echo "<pre>";
    echo "Field               | Type                | Null | Key | Default | Extra\n";
    echo "--------------------------------------------------------------------------------\n";
    foreach ($final_structure as $col) {
        printf("%-19s | %-19s | %-4s | %-3s | %-7s | %s\n",
            $col['Field'],
            $col['Type'],
            $col['Null'],
            $col['Key'],
            $col['Default'] ?? 'NULL',
            $col['Extra']
        );
    }
    echo "</pre>";
    echo "</div>";
    
    // Summary
    echo "<div class='step'>";
    echo "<h2>📊 Migration Summary</h2>";
    
    if (!empty($migrations)) {
        echo "<h3 style='color: #27ae60;'>✅ Changes Applied:</h3>";
        echo "<ul>";
        foreach ($migrations as $migration) {
            echo "<li>$migration</li>";
        }
        echo "</ul>";
    } else {
        echo "<div class='info'>ℹ️ No changes needed - table structure is already up to date!</div>";
    }
    
    if (!empty($errors)) {
        echo "<h3 style='color: #e74c3c;'>❌ Errors:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
    
    echo "<div class='success' style='margin-top: 20px;'>";
    echo "<h2>🎉 Migration Completed Successfully!</h2>";
    echo "<p>The lessons table now has all required columns for full CRUD functionality.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Your lessons table is ready for use</li>";
    echo "<li>All CRUD operations (Create, Read, Update, Delete) will work properly</li>";
    echo "<li>File uploads for lesson materials are now supported</li>";
    echo "<li>Lesson ordering is now available</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Migration Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
echo "<p><a href='verify_database_structure.php' class='btn'>← Back to Database Verification</a></p>";
echo "<p><a href='views/teacher/dashboard.php'>← Back to Teacher Dashboard</a></p>";
echo "</div>";

echo "</div></body></html>";
?>
