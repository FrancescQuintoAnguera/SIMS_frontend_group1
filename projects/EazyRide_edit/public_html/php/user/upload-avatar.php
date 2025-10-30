<?php
/**
 * Upload Avatar Handler
 * Handles profile picture uploads with image validation, resizing, and secure file storage
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Start session and check authentication
startSecureSession();
requireLogin();

// Set JSON response header
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Verify CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrf_token)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token'
    ]);
    exit;
}

$user_id = getCurrentUserId();

// Check if file was uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded'
    ]);
    exit;
}

$file = $_FILES['avatar'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds maximum upload size',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum upload size',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $message = $error_messages[$file['error']] ?? 'Unknown upload error';
    
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] > $max_size) {
    echo json_encode([
        'success' => false,
        'message' => 'File size must be less than 5MB'
    ]);
    exit;
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type. Only JPEG, PNG, and GIF images are allowed'
    ]);
    exit;
}

// Validate that it's actually an image
$image_info = getimagesize($file['tmp_name']);
if ($image_info === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid image file'
    ]);
    exit;
}

// Create image resource based on type
switch ($mime_type) {
    case 'image/jpeg':
    case 'image/jpg':
        $source_image = imagecreatefromjpeg($file['tmp_name']);
        $extension = 'jpg';
        break;
    case 'image/png':
        $source_image = imagecreatefrompng($file['tmp_name']);
        $extension = 'png';
        break;
    case 'image/gif':
        $source_image = imagecreatefromgif($file['tmp_name']);
        $extension = 'gif';
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Unsupported image format'
        ]);
        exit;
}

if ($source_image === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process image'
    ]);
    exit;
}

// Get original dimensions
$original_width = imagesx($source_image);
$original_height = imagesy($source_image);

// Set target dimensions (square avatar)
$target_size = 300;

// Calculate crop dimensions to make it square
$crop_size = min($original_width, $original_height);
$crop_x = ($original_width - $crop_size) / 2;
$crop_y = ($original_height - $crop_size) / 2;

// Create new image
$new_image = imagecreatetruecolor($target_size, $target_size);

// Preserve transparency for PNG and GIF
if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
    imagealphablending($new_image, false);
    imagesavealpha($new_image, true);
    $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
    imagefilledrectangle($new_image, 0, 0, $target_size, $target_size, $transparent);
}

// Resize and crop image
imagecopyresampled(
    $new_image,
    $source_image,
    0, 0,
    $crop_x, $crop_y,
    $target_size, $target_size,
    $crop_size, $crop_size
);

// Define upload directory and file path
$upload_dir = __DIR__ . '/../../images/avatars/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Delete old avatar files for this user (all formats)
$old_files = glob($upload_dir . "user_{$user_id}.*");
foreach ($old_files as $old_file) {
    if (file_exists($old_file)) {
        unlink($old_file);
    }
}

// Save new avatar
$filename = "user_{$user_id}.jpg";
$filepath = $upload_dir . $filename;

// Always save as JPEG for consistency and smaller file size
$save_success = imagejpeg($new_image, $filepath, 90);

// Free up memory
imagedestroy($source_image);
imagedestroy($new_image);

if ($save_success) {
    // Set proper permissions
    chmod($filepath, 0644);
    
    echo json_encode([
        'success' => true,
        'message' => 'Avatar uploaded successfully',
        'avatar_url' => "/images/avatars/{$filename}?t=" . time()
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save avatar image'
    ]);
}
?>
