<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Database Connection
$servername = "localhost";
$username = "root";
$password = ""; // Try "root" if blank doesn't work
$database = "university_system";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("<h2 style='color: red;'>Database Connection Failed: " . mysqli_connect_error() . "</h2>");
}

// 2. Validate and Process Form Data
$errors = [];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<h2 style='color: red;'>Error: Form not submitted</h2>");
}

// Check required fields
$required = ['name', 'dob', 'usn', 'branch', 'semester', 'email', 'mobile', 'password', 'confirm_password'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $errors[] = ucfirst($field) . " is required";
    }
}

// Validate email format
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

// Check password match
if ($_POST['password'] !== $_POST['confirm_password']) {
    $errors[] = "Passwords do not match";
}

// Check password strength (optional)
if (strlen($_POST['password']) < 8) {
    $errors[] = "Password must be at least 8 characters";
}

// 3. Process if no errors
if (empty($errors)) {
    // Sanitize data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $usn = mysqli_real_escape_string($conn, $_POST['usn']);
    $College = mysqli_real_escape_string($conn, $_POST['College']);
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);

    // Hash password
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 4. Insert into database using prepared statement
    $sql = "INSERT INTO students (name, dob, usn, branch, semester, email, mobile, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssss", $name, $dob, $usn, $branch, $semester, $email, $mobile, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        // Success message
        echo "<div style='max-width: 500px; margin: 20px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
        echo "<h2 style='color: green; text-align: center;'>Registration Successful!</h2>";
        echo "<p style='text-align: center;'>You can now <a href='student-login.html' style='color: #4facfe;'>login</a> with your credentials.</p>";
        echo "</div>";
    } else {
        echo "<h2 style='color: red;'>Database Error: " . mysqli_error($conn) . "</h2>";
    }

    mysqli_stmt_close($stmt);
} else {
    // Display errors
    echo "<div style='max-width: 500px; margin: 20px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
    echo "<h2 style='color: red;'>Please fix these errors:</h2>";
    echo "<ul style='margin: 20px 0;'>";
    foreach ($errors as $error) {
        echo "<li style='margin-bottom: 5px;'>$error</li>";
    }
    echo "</ul>";
    echo "<a href='javascript:history.back()' style='display: inline-block; padding: 8px 15px; background: #4facfe; color: white; text-decoration: none; border-radius: 5px;'>Go back to form</a>";
    echo "</div>";
}

// Close connection
mysqli_close($conn);
?>
