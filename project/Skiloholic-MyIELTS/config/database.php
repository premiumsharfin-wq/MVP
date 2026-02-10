<?php
/**
 * Database Configuration
 * MyIELTS - Database Connection Handler
 */

// Database credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'mpkhydsc_myielts');
define('DB_USER', getenv('DB_USER') ?: 'mpkhydsc_sharfin');
define('DB_PASS', getenv('DB_PASS') ?: 'DevNerds@Sharfin9090'); // Production password

// Create PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

/**
 * Execute a prepared statement and return results
 *
 * @param PDO $pdo Database connection
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return PDOStatement
 */
function db_query($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
        throw $e;
    }
}

/**
 * Get single row
 */
function db_fetch($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetch();
}

/**
 * Get all rows
 */
function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Insert and get last insert ID
 */
function db_insert($sql, $params = []) {
    global $pdo;
    db_query($sql, $params);
    return $pdo->lastInsertId();
}

/**
 * Execute update/delete and get affected rows
 */
function db_execute($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->rowCount();
}
