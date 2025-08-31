<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "university_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$usn = $_SESSION['usn'];
$isRegistered = false;
$elective1 = "";
$elective2 = "";

// First, get student's branch and semester
$studentInfo = $conn->prepare("SELECT branch, semester FROM students WHERE usn = ?");
$studentInfo->bind_param("s", $usn);
$studentInfo->execute();
$studentResult = $studentInfo->get_result();

// First, get student's branch and semester
$studentInfo = $conn->prepare("SELECT branch, semester FROM students WHERE usn = ?");
$studentInfo->bind_param("s", $usn);
$studentInfo->execute();
$studentResult = $studentInfo->get_result();

if ($studentResult->num_rows > 0) {
    $studentData = $studentResult->fetch_assoc();
    $branch = $studentData['branch'];
    $semester = $studentData['semester'];
    
    // Verify table structure or use correct column names
    try {
        // Try with possible column name variations
        $subjectsQuery = $conn->prepare("SELECT 
                                        subject_code as code, 
                                        subject_name as name 
                                        FROM subjects 
                                        WHERE branch = ? AND semester = ?");
        $subjectsQuery->bind_param("ss", $branch, $semester);
        $subjectsQuery->execute();
        $subjectsResult = $subjectsQuery->get_result();
        $subjects = [];
        while ($row = $subjectsResult->fetch_assoc()) {
            $subjects[] = $row;
        }
        $subjectsQuery->close();
    } catch (mysqli_sql_exception $e) {
        die("Error loading subjects: " . $e->getMessage() . 
            ". Please verify the column names in your subjects table.");
    }
} else {
    die("Student information not found");
}
$studentInfo->close();

// Check if already registered
$stmt = $conn->prepare("SELECT elective1, elective2 FROM registered_courses WHERE usn = ?");
$stmt->bind_param("s", $usn);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $isRegistered = true;
    $row = $result->fetch_assoc();
    $elective1 = $row['elective1'];
    $elective2 = $row['elective2'];
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select Elective Subject</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --glass-bg: rgba(255, 255, 255, 0.05);
      --highlight: #00ffd5;
      --danger: #ff3e3e;
      --success: #7fff7f;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 20px;
      color: white;
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 8px;
    }

    h2 {
      font-size: 1.1rem;
      color: #aaa;
      margin-bottom: 25px;
    }

    .container {
      background: var(--glass-bg);
      border-radius: 20px;
      padding: 30px;
      max-width: 760px;
      width: 100%;
      backdrop-filter: blur(14px);
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .subject-box {
      background: rgba(255, 255, 255, 0.03);
      padding: 20px;
      border-radius: 16px;
      margin-bottom: 30px;
      border-left: 3px solid var(--highlight);
    }

    .subject-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 16px;
    }

    .subject-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 14px 18px;
      transition: 0.3s;
    }

    .subject-card:hover {
      background: rgba(255, 255, 255, 0.08);
      transform: translateY(-4px);
    }

    .subject-code {
      font-weight: 700;
      color: var(--highlight);
      font-size: 1rem;
      margin-bottom: 6px;
    }

    .subject-name {
      font-size: 0.95rem;
      color: #eee;
    }

    label {
      display: block;
      font-weight: 600;
      margin: 20px 0 8px;
    }

    select {
      width: 100%;
      padding: 14px;
      font-size: 1rem;
      background: rgba(255, 255, 255, 0.07);
      border: none;
      color: black;
      border-radius: 12px;
      transition: 0.3s;
    }

    select:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.12);
    }

    .submit-btn {
      width: 100%;
      padding: 14px;
      background: var(--highlight);
      color: black;
      font-weight: bold;
      font-size: 1rem;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      transition: 0.3s;
      box-shadow: 0 0 15px #00ffd577;
      margin-top: 20px;
    }

    .submit-btn:hover {
      background: #00e6c0;
      box-shadow: 0 0 25px #00ffd5aa;
    }

    ul {
      margin-top: 15px;
      padding-left: 20px;
    }

    ul li {
      margin-bottom: 8px;
    }

    p {
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <h1>Select an Elective Subject</h1>
  <h2><?php echo htmlspecialchars($semester); ?> Semester Subject Selection (<?php echo htmlspecialchars($branch); ?>)</h2>

  <div class="container">
    <div class="subject-box">
      <div class="subject-grid">
        <?php foreach ($subjects as $subject): ?>
          <div class="subject-card">
            <div class="subject-code"><?php echo htmlspecialchars($subject['code']); ?></div>
            <div class="subject-name"><?php echo htmlspecialchars($subject['name']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($isRegistered): ?>
      <p>You have already registered for:</p>
      <ul>
        <li><strong>1st Elective:</strong> <?php echo htmlspecialchars($elective1); ?></li>
        <li><strong>2nd Elective:</strong> <?php echo htmlspecialchars($elective2); ?></li>
      </ul>
      <p style="color:#ff9999;">If you need to update your choices, please contact admin.</p>
    <?php else: ?>
      <form action="register-course.php" method="POST" id="electiveForm">
        <label for="elective1">1st Elective (1 Credit)</label>
        <select name="elective1" id="elective1" required onchange="checkDuplicateSelection()">
          <option value="">-- Select --</option>
          <option value="UI/UX">UI/UX</option>
          <option value="Human Environment">Human Environment</option>
        </select>

        <label for="elective2">2nd Elective (1 Credit)</label>
        <select name="elective2" id="elective2" required onchange="checkDuplicateSelection()">
          <option value="">-- Select --</option>
          <option value="CS">CS</option>
          <option value="GIT">GIT</option>
        </select>

        <button type="submit" class="submit-btn">Submit</button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    function checkDuplicateSelection() {
      const e1 = document.getElementById("elective1").value;
      const e2 = document.getElementById("elective2").value;

      if (e1 && e2 && e1 === e2) {
        alert("⚠️ Same elective selected twice. Please choose different electives.");
        document.getElementById("elective2").value = "";
      }
    }
  </script>

</body>
</html>