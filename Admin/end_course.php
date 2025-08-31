<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "university_system");
if ($conn->connect_error) {
    echo json_encode(['success' => false]);
    exit();
}

$branch = $_GET['branch'] ?? '';
$semester = $_GET['semester'] ?? '';
$faculty = $_GET['faculty'] ?? '';

$stmt = $conn->prepare("UPDATE subjects SET is_active = FALSE WHERE branch = ? AND semester = ? AND assigned_to = ? AND is_active = TRUE");
$stmt->bind_param("sis", $branch, $semester, $faculty);
$stmt->execute();
$success = $stmt->affected_rows > 0;
$stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>