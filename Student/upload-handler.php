<?php
header('Content-Type: application/json'); // Tells browser to expect JSON

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
session_start();

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
}

// Database connection
require_once 'db-config.php'; // Create this file with your DB credentials

// Validate inputs
$required = ['usn', 'subject'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die(json_encode(['success' => false, 'message' => "Missing required field: $field"]));
    }
}

$usn = $conn->real_escape_string($_POST['usn']);
$subject_code = $conn->real_escape_string($_POST['subject']);

// File upload handling
$uploadDir = __DIR__.'/uploads/';
$allowedTypes = ['application/pdf', 'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$maxSize = 10 * 1024 * 1024; // 10MB

// Create uploads directory if it doesn't exist
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        die(json_encode(['success' => false, 'message' => 'Failed to create upload directory']));
    }
}

try {
    // Validate file
    if (!isset($_FILES['note_file'])) {
        throw new Exception("No file uploaded");
    }

    $file = $_FILES['note_file'];
    
    // Check errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload error: ".$file['error']);
    }

    // Verify file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedTypes)) {
        throw new Exception("Invalid file type. Only PDF/DOC/DOCX allowed");
    }

    // Check size
    if ($file['size'] > $maxSize) {
        throw new Exception("File too large. Max 10MB allowed");
    }

    // Generate safe filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $usn.'_'.time().'.'.$extension);
    $targetPath = $uploadDir.$safeName;

    // Move file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception("Failed to move uploaded file");
    }

    // Store in database
    $stmt = $conn->prepare("INSERT INTO student_uploads (usn, subject_code, file_name, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $usn, $subject_code, $file['name'], $safeName);
    
    if (!$stmt->execute()) {
        unlink($targetPath); // Clean up if DB fails
        throw new Exception("Database error: ".$stmt->error);
    }

    $response = [
        'success' => true,
        'message' => 'File uploaded successfully!',
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'No file uploaded or upload error.',
    ];
}
} else {
$response = [
    'success' => false,
    'message' => 'Invalid request method.',
];
}

echo json_encode($response);
exit;
?>