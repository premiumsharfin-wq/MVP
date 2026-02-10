<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

require_login();
$user = get_authenticated_user();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['avatar'];

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error']);
    exit;
}

// Check file size (2 MB max)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 2 MB)']);
    exit;
}

// Check file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type (JPG or PNG only)']);
    exit;
}

// Create uploads directory if needed
$uploadDir = __DIR__ . '/../uploads/avatars/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $user['id'] . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

// Update database
$avatarUrl = BASE_URL . 'uploads/avatars/' . $filename;
db_execute("UPDATE users SET avatar_url = ? WHERE id = ?", [$avatarUrl, $user['id']]);

// Delete old avatar if exists
if ($user['avatar_url'] && $user['avatar_url'] !== $avatarUrl) {
    $oldFile = str_replace(BASE_URL, __DIR__ . '/../', $user['avatar_url']);
    if (file_exists($oldFile)) {
        unlink($oldFile);
    }
}

echo json_encode(['success' => true, 'url' => $avatarUrl]);
