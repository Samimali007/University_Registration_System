<?php
session_start();
if (!isset($_SESSION['usn'])) {
    header("Location: login.php");
    exit();
}

$usn = $_SESSION['usn'];
$conn = new mysqli("localhost", "root", "", "university_system"); // Update if needed

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student's branch and semester
$stmt = $conn->prepare("SELECT branch, semester FROM students WHERE usn = ?");
$stmt->bind_param("s", $usn);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) {
    die("Student not found for USN: $usn");
}
$branch = $student['branch'];
$semester = $student['semester'];
$stmt->close();

// Fetch attendance for student's subjects
$stmt = $conn->prepare("
    SELECT a.subject_id AS subject_code, s.subject_name, a.conducted, a.attended, a.excused
    FROM attendance a
    JOIN subjects s ON a.subject_id = s.subject_code
    WHERE a.usn = ? AND s.branch = ? AND s.semester = ?
");
$stmt->bind_param("ssi", $usn, $branch, $semester);
$stmt->execute();
$result = $stmt->get_result();

$total_conducted = 0;
$total_attended = 0;
$total_excused = 0;

// Loop through fetched data and calculate totals
$subjects_data = [];
while ($row = $result->fetch_assoc()) {
    $conducted = $row['conducted'];
    $attended = $row['attended'];
    $excused = $row['excused'];
    $percentage = $conducted > 0 ? round((($attended + $excused) / $conducted) * 100, 2) : 0;

    // Push each subject
    $subjects_data[] = [
        'subject_code' => $row['subject_code'],
        'subject_name' => $row['subject_name'],
        'conducted' => $conducted,
        'attended' => $attended,
        'excused' => $excused,
        'attendance_percentage' => $percentage
    ];

    // Add to totals
    $total_conducted += $conducted;
    $total_attended += $attended;
    $total_excused += $excused;
}

// Calculate total percentage
$total_percentage = $total_conducted > 0
    ? round((($total_attended + $total_excused) / $total_conducted) * 100, 2)
    : 0;


$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Attendance</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --highlight: #00ffd5;
      --danger: #ff3e3e;
      --success: #7fff7f;
      --popup-bg: rgba(0, 0, 0, 0.7);
      --white: #fff;
      --close-color: #fff;
      --popup-bg-color: #1a1a1a;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 20px;
      color: white;
    }

    /* Animated Bubbles */
    .bubble {
      position: absolute;
      border-radius: 50%;
      background: rgba(0, 255, 213, 0.2);
      animation: float 15s infinite ease-in-out;
    }

    .bubble:nth-child(1) {
      width: 20px;
      height: 20px;
      left: 10%;
      top: 10%;
      animation-duration: 12s;
    }

    .bubble:nth-child(2) {
      width: 30px;
      height: 30px;
      left: 70%;
      top: 20%;
      animation-duration: 18s;
    }

    .bubble:nth-child(3) {
      width: 15px;
      height: 15px;
      left: 30%;
      top: 70%;
      animation-duration: 10s;
    }

    .bubble:nth-child(4) {
      width: 25px;
      height: 25px;
      left: 85%;
      top: 50%;
      animation-duration: 14s;
    }

    .bubble:nth-child(5) {
      width: 18px;
      height: 18px;
      left: 5%;
      top: 40%;
      animation-duration: 16s;
    }

    .bubble:nth-child(6) {
      width: 35px;
      height: 35px;
      left: 60%;
      top: 80%;
      animation-duration: 13s;
    }

    .bubble:nth-child(7) {
      width: 22px;
      height: 22px;
      left: 45%;
      top: 15%;
      animation-duration: 11s;
    }

    .bubble:nth-child(8) {
      width: 28px;
      height: 28px;
      left: 20%;
      top: 60%;
      animation-duration: 17s;
    }

    .bubble:nth-child(9) {
      width: 17px;
      height: 17px;
      left: 75%;
      top: 30%;
      animation-duration: 15s;
    }

    .bubble:nth-child(10) {
      width: 23px;
      height: 23px;
      left: 40%;
      top: 90%;
      animation-duration: 19s;
    }

    @keyframes float {
      0% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-100px) scale(1.2); opacity: 0.7; }
      100% { transform: translateY(0) scale(1); opacity: 1; }
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 30px;
      color: var(--highlight);
      position: relative;
      z-index: 1;
    }

    .dashboard-container {
      width: 100%;
      max-width: 1200px;
      display: flex;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }

    .attendance-box {
      background: var(--popup-bg-color);
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: 0.3s;
      width: 30%;
      margin: 20px;
      cursor: pointer;
      text-align: center;
      z-index: 1;
    }

    .attendance-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 25px rgba(0, 255, 213, 0.2);
    }

    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--popup-bg);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .popup-content {
      background: var(--popup-bg-color);
      border-radius: 15px;
      padding: 30px;
      max-width: 90%;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
      position: relative;
      z-index: 1001;
    }

    .popup-close {
      position: absolute;
      top: 10px;
      right: 10px;
      background: var(--danger);
      color: var(--white);
      border: none;
      padding: 5px 10px;
      cursor: pointer;
      border-radius: 5px;
      font-size: 1.5rem;
    }

    table {
      width: 100%;
      margin-top: 20px;
      border-collapse: collapse;
      table-layout: auto;
      min-width: 600px;
    }

    table th, table td {
      padding: 12px;
      text-align: center;
      border: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 0.9rem;
      white-space: nowrap;
    }

    table th {
      background-color: #222;
      position: sticky;
      top: 0;
    }

    .attendance-status {
      padding: 5px 10px;
      border-radius: 5px;
      font-weight: bold;
    }

    .low-attendance {
      color: var(--danger);
    }

    .high-attendance {
      color: var(--success);
    }

    .total-row {
      font-weight: bold;
      background-color: #333;
      color: #fff;
    }

    .no-data {
      text-align: center;
      padding: 20px;
      color: #aaa;
    }
  </style>
</head>
<body>
  <!-- Add 10 bubble elements -->
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>

  <h1>Track Your Progress</h1>

  <div class="dashboard-container">
    <div class="attendance-box" onclick="openPopup()">
      <h3>Attendance</h3>
      <p>Click to view your attendance details.</p>
    </div>
  </div>

  <!-- Popup Window -->
  <div class="popup-overlay" id="popupOverlay">
    <div class="popup-content">
      <button class="popup-close" onclick="closePopup()">X</button>
      <h2>📈 Attendance Details</h2>
      <table>
        <thead>
          <tr>
            <th>Course</th>
            <th>Conducted</th>
            <th>Attended</th>
            <th>Excused</th>
            <th>Attendance %</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($subjects_data)) { ?>
            <tr>
              <td colspan="5" class="no-data">No subjects found for your branch/semester.</td>
            </tr>
          <?php } else { ?>
            <?php foreach ($subjects_data as $index => $row) { ?>
              <tr>
                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                <td><?php echo htmlspecialchars($row['conducted']); ?></td>
                <td><?php echo htmlspecialchars($row['attended']); ?></td>
                <td><?php echo htmlspecialchars($row['excused']); ?></td>
                <td class="attendance-status" id="attendance-<?php echo $index; ?>">
                  <?php echo number_format($row['attendance_percentage'], 1); ?>%
                </td>
              </tr>
            <?php } ?>
          <?php } ?>
          <!-- Total Row -->
          <tr class="total-row">
            <td>Total</td>
            <td><?php echo htmlspecialchars($total_conducted); ?></td>
            <td><?php echo htmlspecialchars($total_attended); ?></td>
            <td><?php echo htmlspecialchars($total_excused); ?></td>
            <td class="attendance-status" id="total-attendance">
              <?php echo number_format($total_percentage, 1); ?>%
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function openPopup() {
      document.getElementById("popupOverlay").style.display = "flex";
      updateAttendanceColors();
    }

    function closePopup() {
      document.getElementById("popupOverlay").style.display = "none";
    }

    function updateAttendanceColors() {
      const attendanceElements = document.querySelectorAll('.attendance-status');
      attendanceElements.forEach(element => {
        const percentage = parseFloat(element.textContent) || 0;
        element.classList.remove("low-attendance", "high-attendance");
        if (percentage < 75 && percentage > 0) {
          element.classList.add("low-attendance");
        } else if (percentage >= 75) {
          element.classList.add("high-attendance");
        }
      });
    }
  </script>
</body>
</html>