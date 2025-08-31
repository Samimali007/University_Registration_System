<?php
session_start();

// Make sure user is logged in
if (!isset($_SESSION['usn'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

$usn = $_SESSION['usn'];

// Check if it's a blob sent as FormData
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = $usn . '_profile.jpg';
    $uploadPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
        echo "Profile updated!";
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "No file received.";
}
?>
