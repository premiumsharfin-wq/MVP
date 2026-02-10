<?php
/**
 * Database Schema Fixer
 * Run this script to update your database tables with missing columns.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>MyIELTS Database Schema Fixer</h1>";
echo "<pre>";

try {
    // 1. Fix Evaluations Table
    echo "Checking 'evaluations' table...\n";
    $columns = db_fetch_all("DESCRIBE evaluations");
    $colNames = array_column($columns, 'Field');

    if (!in_array('assigned_at', $colNames)) {
        echo "- Adding missing column 'assigned_at'...\n";
        $pdo->exec("ALTER TABLE evaluations ADD COLUMN assigned_at TIMESTAMP NULL AFTER estimated_completion_time");
        echo "  Done.\n";
    } else {
        echo "- 'assigned_at' already exists.\n";
    }

    // 2. Fix Submissions Table
    echo "\nChecking 'submissions' table...\n";
    $columns = db_fetch_all("DESCRIBE submissions");
    $colNames = array_column($columns, 'Field');

    $colsToAdd = [
        'custom_task1_image' => "VARCHAR(255) NULL",
        'custom_task1_question' => "TEXT NULL",
        'custom_task2_question' => "TEXT NULL"
    ];

    foreach ($colsToAdd as $col => $def) {
        if (!in_array($col, $colNames)) {
            echo "- Adding missing column '$col'...\n";
            $pdo->exec("ALTER TABLE submissions ADD COLUMN $col $def");
            echo "  Done.\n";
        } else {
            echo "- '$col' already exists.\n";
        }
    }

    // 3. Fix Users Table (just in case)
    echo "\nChecking 'users' table...\n";
    $columns = db_fetch_all("DESCRIBE users");
    $colNames = array_column($columns, 'Field');

    if (!in_array('password_hash', $colNames)) {
        echo "- Adding missing column 'password_hash'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NULL");
        echo "  Done.\n";
    }

    if (!in_array('email_verified', $colNames)) {
        echo "- Adding missing column 'email_verified'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE");
        echo "  Done.\n";
    }

    echo "\n---------------------------------------------------\n";
    echo "✅ Database schema update completed successfully!\n";
    echo "---------------------------------------------------\n";
    echo "You can now verify users and assign submissions without errors.";

} catch (PDOException $e) {
    echo "❌ Error updating database: " . $e->getMessage();
}

echo "</pre>";
echo '<br><a href="index.php" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Home</a>';
?>
