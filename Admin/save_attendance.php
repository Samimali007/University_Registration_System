<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_email"]) || !isset($_SESSION["user_name"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if subject code is provided
if (!isset($_POST['subject_code']) || empty($_POST['subject_code'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Subject code is required']);
    exit();
}

// Check if total_classes_conducted is provided
if (!isset($_POST['total_classes_conducted']) || !is_numeric($_POST['total_classes_conducted'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Total classes conducted is required']);
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$faculty_email = $_SESSION["user_email"];
$subject_code = $_POST['subject_code'];
$total_classes_conducted = intval($_POST['total_classes_conducted']);

// Validate if the faculty is assigned to this subject
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM faculty_subjects WHERE faculty_email = ? AND subject_code = ?");
$stmt->bind_param("ss", $faculty_email, $subject_code);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['count'] == 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You are not authorized to update attendance for this subject']);
    exit();
}

// Process attendance data
if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
    $conn->begin_transaction();
    try {
        foreach ($_POST['attendance'] as $usn => $data) {
            // Ensure data types are correct
            $conducted = $total_classes_conducted;
            $attended = isset($data['attended']) ? intval($data['attended']) : 0;
            $excused = isset($data['excused']) ? intval($data['excused']) : 0;
            
            // Validate attendance data
            if ($attended > $conducted) {
                throw new Exception("Attended classes cannot be more than conducted classes for USN: $usn");
            }
            
            if ($excused > $conducted) {
                throw new Exception("Excused classes cannot be more than conducted classes for USN: $usn");
            }

            // Check if record exists
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM attendance WHERE usn = ? AND subject_id = ?");
            $stmt->bind_param("ss", $usn, $subject_code);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc()['count'] > 0;
            $stmt->close();

            if ($exists) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE attendance SET conducted = ?, attended = ?, excused = ? WHERE usn = ? AND subject_id = ?");
                $stmt->bind_param("iiiss", $conducted, $attended, $excused, $usn, $subject_code);
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO attendance (usn, subject_id, conducted, attended, excused) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiii", $usn, $subject_code, $conducted, $attended, $excused);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to save attendance for USN: $usn. Error: " . $stmt->error);
            }
            $stmt->close();
        }
        
        $conn->commit();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No attendance data provided']);
}

$conn->close();
?>