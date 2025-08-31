<?php
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get POST data
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $subject_code = isset($_POST['subjectcode']) ? $_POST['subjectcode'] : null;
    $branch = isset($_POST['branch']) ? $_POST['branch'] : null;
    $semester = isset($_POST['semester']) ? (int)$_POST['semester'] : null;

    // Validate inputs
    if (!$email || !$subject_code || !$branch || !$semester) {
        $response['message'] = 'Missing required fields.';
        echo json_encode($response);
        exit;
    }

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "university_system");

    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed: ' . $conn->connect_error;
        echo json_encode($response);
        exit;
    }

    // Check if the faculty already has a subject for this branch and semester
    $stmt = $conn->prepare("SELECT COUNT(*) FROM faculty_subjects WHERE faculty_email = ? AND branch = ? AND semester = ?");
    $stmt->bind_param("ssi", $email, $branch, $semester);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $response['message'] = 'You have already assigned a subject for this branch and semester.';
        echo json_encode($response);
        $conn->close();
        exit;
    }

    // Check if the subject exists in the subjects table
    $stmt = $conn->prepare("SELECT COUNT(*) FROM subjects WHERE subject_code = ? AND branch = ? AND semester = ?");
    $stmt->bind_param("ssi", $subject_code, $branch, $semester);
    $stmt->execute();
    $stmt->bind_result($subject_exists);
    $stmt->fetch();
    $stmt->close();

    if ($subject_exists == 0) {
        $response['message'] = 'Subject does not exist for the selected branch and semester.';
        echo json_encode($response);
        $conn->close();
        exit;
    }

    // Insert the new subject assignment
    $stmt = $conn->prepare("INSERT INTO faculty_subjects (faculty_email, subject_code, branch, semester) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $email, $subject_code, $branch, $semester);
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Subject registered successfully!';
    } else {
        $response['message'] = 'Failed to register subject: ' . $stmt->error;
    }
    $stmt->close();

    $conn->close();
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
}

echo json_encode($response);
?>