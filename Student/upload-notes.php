<?php
session_start();

if (!isset($_SESSION['usn'])) {
    echo "Unauthorized";
    exit();
}

$usn = $_SESSION['usn'];
$subject = $_POST['subject'] ?? '';
$semester = $_POST['semester'] ?? '';
$section = $_POST['section'] ?? '';

$uploadDir = "uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_FILES['note_file']) && $subject && $semester && $section) {
    $filename = basename($_FILES['note_file']['name']);
    $targetFile = $uploadDir . time() . "_" . $filename;

    if (move_uploaded_file($_FILES['note_file']['tmp_name'], $targetFile)) {
        $conn = new mysqli("localhost", "root", "", "university_system");
        $stmt = $conn->prepare("INSERT INTO student_notes (usn, subject, semester, section, file_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $usn, $subject, $semester, $section, $targetFile);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        echo "success";
    } else {
        echo "error";
    }
}
?>
