<?php
session_start();

// Database configuration
$db_host = "localhost";
$db_name = "university_system";
$db_user = "root";
$db_pass = "";

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // Log the attempt for debugging
    error_log("Attempting faculty login with email: $email, password: $password");

    // Query the FACULTY table with plain-text password comparison
    $stmt = $db->prepare("SELECT * FROM faculty_login WHERE email_id = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Login successful
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_email"] = $user["email_id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["role"] = "faculty"; // Add role for consistency

        error_log("Login successful for email: $email");
        header("Location: admin-dashboard.php");
        exit();
    } else {
        // Login failed
        error_log("Login failed for email: $email - Invalid credentials");
        header("Location: faculty-login.html?error=invalid_credentials");
        exit();
    }
} else {
    // If someone tries to access this page directly
    header("Location: faculty-login.html");
    exit();
}
?>