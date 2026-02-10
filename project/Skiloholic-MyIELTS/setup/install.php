<?php
/**
 * MyIELTS Setup/Installation Script
 * Run this once to set up the database and admin account
 */

require_once __DIR__ . '/../config/config.php';

// Check if already installed
$installedFlag = __DIR__ . '/.installed';
if (file_exists($installedFlag)) {
    die('Installation already completed. Delete setup/.installed file to reinstall.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyIELTS Installation</title>
    <link rel="stylesheet" href="../assets/css/main.css?v=2.0">
</head>
<body class="auth-page">
    <div class="auth-container" style="max-width: 600px;">
        <div class="auth-box">
            <div class="auth-header">
                <h1>MyIELTS Installation</h1>
                <p>Set up your database and admin account</p>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $errors = [];
                $success = [];

                try {
                    // Connect to MySQL without database selection
                    $host = sanitize($_POST['db_host'] ?? 'localhost');
                    $user = sanitize($_POST['db_user'] ?? 'root');
                    $pass = $_POST['db_pass'] ?? '';
                    $dbname = sanitize($_POST['db_name'] ?? 'myielts');

                    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Create database
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $success[] = "Database '$dbname' created successfully";

                    $pdo->exec("USE `$dbname`");

                    // Read and execute schema
                    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
                    $schema = str_replace('CREATE DATABASE IF NOT EXISTS myielts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', '', $schema);
                    $schema = str_replace('USE myielts;', '', $schema);

                    $pdo->exec($schema);
                    $success[] = "Database tables created successfully";

                    // Create admin account with proper password
                    $adminPassword = password_hash('DevNerds@Sharfin9090', PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare("
                        INSERT INTO users (full_name, email, whatsapp_number, password_hash, role, email_verified, created_at)
                        VALUES ('Sharfin Hossain', 'sharfinhossain50@gmail.com', '+8801533869234', ?, 'admin', TRUE, NOW())
                        ON DUPLICATE KEY UPDATE role = 'admin', email_verified = TRUE
                    ");
                    $stmt->execute([$adminPassword]);

                    $success[] = "Admin account created successfully";
                    $success[] = "Email: sharfinhossain50@gmail.com";
                    $success[] = "Password: DevNerds@Sharfin9090";

                    // Update database configuration file
                    $configContent = file_get_contents(__DIR__ . '/../config/database.php');
                    $configContent = str_replace("define('DB_HOST', 'localhost');", "define('DB_HOST', '$host');", $configContent);
                    $configContent = str_replace("define('DB_NAME', 'myielts');", "define('DB_NAME', '$dbname');", $configContent);
                    $configContent = str_replace("define('DB_USER', 'root');", "define('DB_USER', '$user');", $configContent);
                    $configContent = str_replace("define('DB_PASS', '');", "define('DB_PASS', '$pass');", $configContent);

                    file_put_contents(__DIR__ . '/../config/database.php', $configContent);
                    $success[] = "Database configuration updated";

                    // Create upload directories
                    $dirs = [
                        __DIR__ . '/../uploads',
                        __DIR__ . '/../uploads/test-images',
                        __DIR__ . '/../uploads/submission-images',
                    ];

                    foreach ($dirs as $dir) {
                        if (!file_exists($dir)) {
                            mkdir($dir, 0755, true);
                        }
                    }
                    $success[] = "Upload directories created";

                    // Mark as installed
                    file_put_contents($installedFlag, date('Y-m-d H:i:s'));

                    $success[] = "<strong>Installation completed successfully!</strong>";
                    $success[] = "<a href='../auth/login.php' class='btn btn-primary'>Go to Login</a>";

                } catch (PDOException $e) {
                    $errors[] = "Database Error: " . $e->getMessage();
                } catch (Exception $e) {
                    $errors[] = "Error: " . $e->getMessage();
                }

                if (!empty($errors)) {
                    echo '<div class="alert alert-error">';
                    foreach ($errors as $error) {
                        echo '<p>' . htmlspecialchars($error) . '</p>';
                    }
                    echo '</div>';
                }

                if (!empty($success)) {
                    echo '<div class="alert alert-success">';
                    foreach ($success as $msg) {
                        echo '<p>' . $msg . '</p>';
                    }
                    echo '</div>';
                }
            } else {
                ?>
                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>

                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="myielts" required>
                    </div>

                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" value="root" required>
                    </div>

                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass">
                        <small>Leave empty if no password (XAMPP default)</small>
                    </div>

                    <div class="alert alert-info">
                        <p><strong>This will:</strong></p>
                        <ul style="margin: 10px 0 0 20px;">
                            <li>Create the database and tables</li>
                            <li>Set up the admin account</li>
                            <li>Configure database connection</li>
                            <li>Create upload directories</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Install MyIELTS</button>
                </form>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>
