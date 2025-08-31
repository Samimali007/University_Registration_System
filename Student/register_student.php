<?php
header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 0); // Disable on production
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php_errors.log'); // Update this path
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'error' => 'Connection failed']);
    exit();
}

$usn = $_POST['usn'] ?? '';
$email = $_POST['email'] ?? '';
$mobile = $_POST['mobile'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($mobile) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Email, mobile, and password are required']);
    $conn->close();
    exit();
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if email already exists
$stmt = $conn->prepare("SELECT email FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already registered']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// If USN is provided → Update existing student
if (!empty($usn)) {
    $stmt = $conn->prepare("UPDATE students SET email = ?, mobile = ?, password = ? WHERE usn = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Query preparation failed']);
        $conn->close();
        exit();
    }
    $stmt->bind_param("ssss", $email, $mobile, $hashed_password, $usn);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Registration successful (USN matched)!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to register: USN not found']);
    }
    $stmt->close();
}
// If USN not provided → Insert a new student (manual registration)
else {
    // Optional: You can auto-generate a temporary USN
    $manual_usn = 'M' . time(); // Example: M1715768392

    $stmt = $conn->prepare("INSERT INTO students (usn, email, mobile, password) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Query preparation failed']);
        $conn->close();
        exit();
    }
    $stmt->bind_param("ssss", $manual_usn, $email, $mobile, $hashed_password);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Manual registration successful!', 'usn_assigned' => $manual_usn]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to register manually: ' . $conn->error]);
    }
    $stmt->close();
}

$conn->close();
?>
