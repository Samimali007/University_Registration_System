<?php
header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

$usn = $_POST['usn'] ?? '';

if (empty($usn)) {
    error_log("USN not provided in request");
    echo json_encode(['success' => false, 'error' => 'USN is required']);
    $conn->close();
    exit();
}

// Sanitize and standardize USN
$usn = trim(strtoupper($usn));
error_log("Searching for USN: $usn");

// Check new students table
$stmt = $conn->prepare("SELECT name, semester, branch, college FROM students WHERE usn = ?");
if (!$stmt) {
    error_log("Prepare failed (students): " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Query prepare failed: ' . $conn->error]);
    $conn->close();
    exit();
}
$stmt->bind_param("s", $usn);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    error_log("Found in students: " . json_encode($student));
    echo json_encode(['success' => true, 'student' => $student]);
} else {
    // Check old student table
    $stmt = $conn->prepare("SELECT name, semester, branch, college FROM student_old WHERE usn = ?");
    if (!$stmt) {
        error_log("Prepare failed (student_old): " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Query prepare failed: ' . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("s", $usn);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        error_log("Found in student_old: " . json_encode($student));
        echo json_encode(['success' => true, 'student' => $student]);
    } else {
        error_log("Student not found for USN: $usn");
        echo json_encode(['success' => false, 'error' => 'Student not found']);
    }
}

$stmt->close();
$conn->close();
?>