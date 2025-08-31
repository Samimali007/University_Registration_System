<?php
session_start();

header('Content-Type: application/json');

// Verify CSRF token
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
}

// Database connection
$conn = new mysqli("localhost", "root", "", "university_system");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "DB Connection failed"]));
}

$data = json_decode(file_get_contents('php://input'), true);
$filename = $conn->real_escape_string($data['filename']);
$usn = $conn->real_escape_string($data['usn']);

try {
    // Get file path from DB
    $stmt = $conn->prepare("SELECT file_path FROM student_uploads WHERE file_name = ? AND usn = ?");
    $stmt->bind_param("ss", $filename, $usn);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("File not found");
    }
    
    $row = $result->fetch_assoc();
    $filePath = __DIR__.'/uploads/'.$row['file_path'];
    
    // Delete from filesystem
    if (file_exists($filePath) && !unlink($filePath)) {
        throw new Exception("Could not delete file from server");
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM student_uploads WHERE file_name = ? AND usn = ?");
    $stmt->bind_param("ss", $filename, $usn);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: ".$stmt->error);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>