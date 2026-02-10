<?php
/**
 * Upload Handler
 * MyIELTS - Secure file upload functions
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Validate uploaded image
 *
 * @param array $file $_FILES array element
 * @return array ['success' => bool, 'error' => string|null]
 */
function validate_image($file) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $maxMB = MAX_FILE_SIZE / (1024 * 1024);
        return ['success' => false, 'error' => "File size exceeds {$maxMB}MB limit"];
    }

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed'];
    }

    // Verify it's actually an image
    if (!getimagesize($file['tmp_name'])) {
        return ['success' => false, 'error' => 'File is not a valid image'];
    }

    return ['success' => true, 'error' => null];
}

/**
 * Generate unique filename
 *
 * @param string $originalName Original filename
 * @return string Unique filename
 */
function generate_unique_filename($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $uniqueId = uniqid('', true);
    $randomStr = bin2hex(random_bytes(8));
    return $uniqueId . '_' . $randomStr . '.' . $extension;
}

/**
 * Upload test question image
 *
 * @param array $file $_FILES array element
 * @return array ['success' => bool, 'filename' => string|null, 'url' => string|null, 'error' => string|null]
 */
function upload_test_image($file) {
    $validation = validate_image($file);

    if (!$validation['success']) {
        return array_merge($validation, ['filename' => null, 'url' => null]);
    }

    // Create directory if it doesn't exist
    if (!file_exists(TEST_IMAGES_DIR)) {
        mkdir(TEST_IMAGES_DIR, 0755, true);
    }

    $filename = generate_unique_filename($file['name']);
    $destination = TEST_IMAGES_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $filename,
            'url' => TEST_IMAGES_URL . $filename,
            'error' => null
        ];
    } else {
        return [
            'success' => false,
            'filename' => null,
            'url' => null,
            'error' => 'Failed to save file'
        ];
    }
}

/**
 * Upload submission screenshot
 *
 * @param array $file $_FILES array element
 * @return array ['success' => bool, 'filename' => string|null, 'url' => string|null, 'error' => string|null]
 */
function upload_submission_image($file) {
    $validation = validate_image($file);

    if (!$validation['success']) {
        return array_merge($validation, ['filename' => null, 'url' => null]);
    }

    // Create directory if it doesn't exist
    if (!file_exists(SUBMISSION_IMAGES_DIR)) {
        mkdir(SUBMISSION_IMAGES_DIR, 0755, true);
    }

    $filename = generate_unique_filename($file['name']);
    $destination = SUBMISSION_IMAGES_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $filename,
            'url' => SUBMISSION_IMAGES_URL . $filename,
            'error' => null
        ];
    } else {
        return [
            'success' => false,
            'filename' => null,
            'url' => null,
            'error' => 'Failed to save file'
        ];
    }
}

/**
 * Upload multiple submission screenshots
 *
 * @param array $files $_FILES array with multiple files
 * @return array ['success' => bool, 'urls' => array, 'errors' => array]
 */
function upload_multiple_submission_images($files) {
    $urls = [];
    $errors = [];

    // Reformat the files array for easier processing
    $fileCount = count($files['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];

        $result = upload_submission_image($file);

        if ($result['success']) {
            $urls[] = $result['url'];
        } else {
            $errors[] = $result['error'];
        }
    }

    return [
        'success' => count($urls) > 0,
        'urls' => $urls,
        'errors' => $errors
    ];
}

/**
 * Delete file
 *
 * @param string $filePath Full file path
 * @return bool Success status
 */
function delete_file($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}
