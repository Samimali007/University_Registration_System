<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode([]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "university_system");
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

$branch = $_GET['branch'] ?? '';
$semester = $_GET['semester'] ?? '';
$faculty = $_GET['faculty'] ?? '';

$stmt = $conn->prepare("SELECT s.subject_code, s.subject_name, fs.faculty_email as assigned_to 
                        FROM subjects s 
                        LEFT JOIN faculty_subjects fs ON s.subject_code = fs.subjectcode AND fs.faculty_email = ? 
                        WHERE s.branch = ? AND s.semester = ?");
$stmt->bind_param("ssi", $faculty, $branch, $semester);
$stmt->execute();
$result = $stmt->get_result();
$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode($subjects);
?>