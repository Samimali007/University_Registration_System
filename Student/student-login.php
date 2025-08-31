<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // DB connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "university_system";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $usn = $_POST['usn'];
    $inputPassword = $_POST['password'];

    $stmt = $conn->prepare("SELECT usn, password FROM students WHERE usn = ?");
    $stmt->bind_param("s", $usn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Check password (assumes password is hashed in DB)
        if (password_verify($inputPassword, $row['password'])) {
            $_SESSION['usn'] = $usn;

            // Redirect to dashboard
            header("Location: student-dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid USN or Password'); window.location.href='student-login.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='student-login.html';</script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}

ob_end_flush();
?>
