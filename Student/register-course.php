<?php
session_start();

if (!isset($_SESSION['usn'])) {
    echo "<script>alert('Please login first.'); window.location.href='student-login.html';</script>";
    exit();
}

$usn = $_SESSION['usn'];

$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$already_registered = false;
$registered_courses = [];

$check = $conn->prepare("SELECT elective1, elective2 FROM registered_courses WHERE usn = ?");
$check->bind_param("s", $usn);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $already_registered = true;
    $row = $result->fetch_assoc();
    $registered_courses[] = $row['elective1'];
    $registered_courses[] = $row['elective2'];
}
$check->close();

// All electives list
$all_electives = [
    "ML101" => "Machine Learning",
    "WT102" => "Web Technology",
    "BC103" => "Blockchain",
    "CC104" => "Cloud Computing",
    "AI105" => "Artificial Intelligence",
    "UIUX"  => "UI/UX",
    "CS"    => "Cyber Security"
];

// Handle form POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$already_registered) {
    $elective1 = $_POST['elective1'];
    $elective2 = $_POST['elective2'];

    if ($elective1 === $elective2) {
        echo "<script>alert('Both electives must be different!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO registered_courses (usn, elective1, elective2) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $usn, $elective1, $elective2);

        if ($stmt->execute()) {
            echo "<script>alert('Courses registered successfully!'); window.location.href='register-course.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error registering courses.');</script>";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Your Electives</title>
    <style>
        select:disabled {
            background-color: #f0f0f0;
            color: #555;
        }
    </style>
</head>
<body>
    <h2>Register Your Electives</h2>

    <form method="post">
        <label for="elective1">Elective 1:</label>
        <select name="elective1" <?= $already_registered ? 'disabled' : 'required' ?>>
            <option disabled <?= !$already_registered ? 'selected' : '' ?>>-- Select Elective --</option>
            <?php foreach ($all_electives as $code => $name): ?>
                <option value="<?= $code ?>"
                    <?= ($already_registered && $registered_courses[0] === $code) ? 'selected' : '' ?>>
                    <?= $name ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="elective2">Elective 2:</label>
        <select name="elective2" <?= $already_registered ? 'disabled' : 'required' ?>>
            <option disabled <?= !$already_registered ? 'selected' : '' ?>>-- Select Elective --</option>
            <?php foreach ($all_electives as $code => $name): ?>
                <option value="<?= $code ?>"
                    <?= ($already_registered && $registered_courses[1] === $code) ? 'selected' : '' ?>>
                    <?= $name ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <?php if (!$already_registered): ?>
            <button type="submit">Register</button>
        <?php else: ?>
            <p><em>You have already registered. To make changes, contact admin.</em></p>
        <?php endif; ?>
    </form>
</body>
</html>
