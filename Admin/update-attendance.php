<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_email"]) || !isset($_SESSION["user_name"])) {
    header("Location: faculty-login.html");
    exit();
}

$email = htmlspecialchars($_SESSION["user_email"]);
$name = htmlspecialchars($_SESSION["user_name"]);

$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch subjects assigned to the faculty
$subjects = [];
$stmt = $conn->prepare("SELECT s.subject_code, s.subject_name, fs.branch, fs.semester 
                        FROM faculty_subjects fs 
                        JOIN subjects s ON fs.subject_code = s.subject_code 
                        WHERE fs.faculty_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();

// Fetch students and existing attendance for the selected subject
$selected_subject = isset($_POST['subject_code']) ? $_POST['subject_code'] : '';
$students = [];
$attendance_data = [];
$total_classes_conducted = 0;

if ($selected_subject) {
    // Find branch and semester for the selected subject
    $stmt = $conn->prepare("SELECT branch, semester FROM faculty_subjects WHERE faculty_email = ? AND subject_code = ?");
    $stmt->bind_param("ss", $email, $selected_subject);
    $stmt->execute();
    $result = $stmt->get_result();
    $subject_info = $result->fetch_assoc();
    $stmt->close();

    if ($subject_info) {
        $branch = $subject_info['branch'];
        $semester = $subject_info['semester'];

        // Fetch students in the same branch and semester
        $stmt = $conn->prepare("SELECT usn, name FROM students WHERE branch = ? AND semester = ?");
        $stmt->bind_param("si", $branch, $semester);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $stmt->close();

        // Fetch existing attendance for the selected subject
        $stmt = $conn->prepare("SELECT usn, conducted, attended, excused FROM attendance WHERE subject_id = ?");
        $stmt->bind_param("s", $selected_subject);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $attendance_data[$row['usn']] = [
                'conducted' => $row['conducted'],
                'attended' => $row['attended'],
                'excused' => $row['excused']
            ];
            
            // Set total classes conducted from the first record
            if ($total_classes_conducted == 0) {
                $total_classes_conducted = $row['conducted'];
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Attendance</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --highlight: #00ffd5;
      --danger: #ff3e3e;
      --success: #7fff7f;
      --popup-bg: rgba(0, 0, 0, 0.7);
      --white: #fff;
      --close-color: #fff;
      --card-bg: rgba(26, 26, 26, 0.9);
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
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 20px;
      color: var(--white);
    }

    /* Back Arrow */
    .back-arrow {
      position: absolute;
      top: 20px;
      left: 20px;
      font-size: 1.5rem;
      color: var(--highlight);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: color 0.3s;
      z-index: 2;
    }

    .back-arrow:hover {
      color: #00b7a0;
    }

    .back-arrow span {
      font-size: 1rem;
      font-weight: 600;
    }

    /* Animated Bubbles */
    .bubble {
      position: absolute;
      border-radius: 50%;
      background: rgba(0, 255, 213, 0.2);
      animation: float 15s infinite ease-in-out;
    }

    .bubble:nth-child(1) { width: 20px; height: 20px; left: 10%; top: 10%; animation-duration: 12s; }
    .bubble:nth-child(2) { width: 30px; height: 30px; left: 70%; top: 20%; animation-duration: 18s; }
    .bubble:nth-child(3) { width: 15px; height: 15px; left: 30%; top: 70%; animation-duration: 10s; }
    .bubble:nth-child(4) { width: 25px; height: 25px; left: 85%; top: 50%; animation-duration: 14s; }
    .bubble:nth-child(5) { width: 18px; height: 18px; left: 5%; top: 40%; animation-duration: 16s; }
    .bubble:nth-child(6) { width: 35px; height: 35px; left: 60%; top: 80%; animation-duration: 13s; }
    .bubble:nth-child(7) { width: 22px; height: 22px; left: 45%; top: 15%; animation-duration: 11s; }
    .bubble:nth-child(8) { width: 28px; height: 28px; left: 20%; top: 60%; animation-duration: 17s; }
    .bubble:nth-child(9) { width: 17px; height: 17px; left: 75%; top: 30%; animation-duration: 15s; }
    .bubble:nth-child(10) { width: 23px; height: 23px; left: 40%; top: 90%; animation-duration: 19s; }

    @keyframes float {
      0% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-100px) scale(1.2); opacity: 0.7; }
      100% { transform: translateY(0) scale(1); opacity: 1; }
    }

    .title-container {
      text-align: center;
      margin-bottom: 40px;
      position: relative;
      z-index: 1;
    }

    h1 {
      font-size: 2.8rem;
      color: var(--highlight);
      margin-bottom: 10px;
    }

    .subheading {
      font-size: 1.2rem;
      color: #ccc;
    }

    .content-container {
      width: 100%;
      max-width: 1200px;
      padding: 20px;
      position: relative;
      z-index: 1;
    }

    .content-container label {
      display: block;
      font-size: 1.1rem;
      color: var(--highlight);
      margin-bottom: 5px;
    }

    .content-container select {
      width: 100%;
      max-width: 400px;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #2c2c2c;
      color: var(--white);
      border-radius: 5px;
      font-size: 0.95rem;
    }

    .attendance-list {
      margin-top: 20px;
    }

    .attendance-list h3 {
      font-size: 1.2rem;
      color: var(--success);
      margin-bottom: 10px;
    }

    .classes-conducted-container {
      background: var(--card-bg);
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
    }

    .classes-conducted-container label {
      margin-bottom: 0;
      white-space: nowrap;
    }

    .classes-conducted-container input[type="number"] {
      width: 100px;
      padding: 8px;
      background: #2c2c2c;
      color: var(--white);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 5px;
    }

    .attendance-list table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
      background: var(--card-bg);
      border-radius: 10px;
      overflow: hidden;
    }

    .attendance-list th,
    .attendance-list td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .attendance-list th {
      background: #222;
      color: var(--highlight);
    }

    .attendance-list td {
      color: #ccc;
    }

    .attendance-list input[type="number"] {
      width: 80px;
      padding: 5px;
      background: #2c2c2c;
      color: var(--white);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 5px;
    }

    #submitBtn {
      background: var(--success);
      color: var(--white);
      border: none;
      padding: 12px 25px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s;
      margin-top: 20px;
      width: 100%;
      max-width: 200px;
    }

    #submitBtn:hover {
      background: #66cc66;
    }

    /* Pop-up Styling */
    .popup-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--popup-bg);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .popup-content {
      background: var(--card-bg);
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      width: 90%;
      max-width: 400px;
      position: relative;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    }

    .popup-content p {
      font-size: 1rem;
      color: var(--white);
      margin-bottom: 20px;
    }

    .popup-content.success p {
      color: var(--success);
    }

    .popup-content.error p {
      color: var(--danger);
    }

    .popup-content button {
      background: var(--highlight);
      color: var(--white);
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
    }

    .popup-content button:hover {
      background: #00b7a0;
    }

    @media (max-width: 768px) {
      .content-container {
        padding: 15px;
      }

      .attendance-list th,
      .attendance-list td {
        padding: 8px;
        font-size: 0.9rem;
      }

      .attendance-list input[type="number"] {
        width: 60px;
      }

      .back-arrow {
        top: 15px;
        left: 15px;
        font-size: 1.2rem;
      }

      .back-arrow span {
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>
  <!-- Back Arrow -->
  <a href="admin-dashboard.php" class="back-arrow">
    ← <span>Back to Dashboard</span>
  </a>

  <!-- 10 bubble elements -->
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

  <div class="title-container">
    <h1>📅 Update Attendance</h1>
    <p class="subheading">Update Cumulative Attendance for Your Assigned Subjects</p>
  </div>

  <div class="content-container">
    <form method="POST" action="" id="attendanceForm">
      <label for="subject_code">Subject:</label>
      <select name="subject_code" id="subject_code" onchange="this.form.submit()">
        <option value="">Select Subject</option>
        <?php foreach ($subjects as $subject) { ?>
          <option value="<?php echo htmlspecialchars($subject['subject_code']); ?>" <?php echo $selected_subject === $subject['subject_code'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($subject['subject_name']) . ' (' . htmlspecialchars($subject['branch']) . ' - Sem ' . $subject['semester'] . ')'; ?>
          </option>
        <?php } ?>
      </select>

      <?php if ($selected_subject) { ?>
        <?php if ($students) { ?>
          <div class="attendance-list">
            <h3>Total Classes Information:</h3>
            <div class="classes-conducted-container">
              <label for="total_classes_conducted">Total Classes Conducted:</label>
              <input type="number" name="total_classes_conducted" id="total_classes_conducted" 
                value="<?php echo $total_classes_conducted; ?>" min="0" 
                onchange="updateAllConducted(this.value)">
            </div>
            
            <h3>Students:</h3>
            <table>
              <tr>
                <th>USN</th>
                <th>Name</th>
                <th>Attended</th>
                <th>Excused</th>
                <th>Percentage</th>
              </tr>
              <?php foreach ($students as $student) { ?>
                <?php
                $usn = $student['usn'];
                $conducted = isset($attendance_data[$usn]['conducted']) ? $attendance_data[$usn]['conducted'] : $total_classes_conducted;
                $attended = isset($attendance_data[$usn]['attended']) ? $attendance_data[$usn]['attended'] : 0;
                $excused = isset($attendance_data[$usn]['excused']) ? $attendance_data[$usn]['excused'] : 0;
                
                // Fix percentage calculation
                $effective_classes = max(0, $conducted - $excused);
                $percentage = $effective_classes > 0 ? round(($attended / $effective_classes) * 100, 2) : 0;
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($usn); ?></td>
                  <td><?php echo htmlspecialchars($student['name']); ?></td>
                  <input type="hidden" name="attendance[<?php echo htmlspecialchars($usn); ?>][conducted]" 
                    value="<?php echo $conducted; ?>" class="conducted-input">
                  <td>
                    <input type="number" name="attendance[<?php echo htmlspecialchars($usn); ?>][attended]" 
                      value="<?php echo $attended; ?>" min="0" max="<?php echo $conducted; ?>"
                      onchange="updatePercentage('<?php echo htmlspecialchars($usn); ?>')">
                  </td>
                  <td>
                    <input type="number" name="attendance[<?php echo htmlspecialchars($usn); ?>][excused]" 
                      value="<?php echo $excused; ?>" min="0" max="<?php echo $conducted; ?>"
                      onchange="updatePercentage('<?php echo htmlspecialchars($usn); ?>')">
                  </td>
                  <td id="percentage-<?php echo htmlspecialchars($usn); ?>"><?php echo $percentage; ?>%</td>
                </tr>
              <?php } ?>
            </table>
            <button type="button" id="submitBtn" onclick="submitAttendance()">Submit Attendance</button>
          </div>
        <?php } else { ?>
          <p>No students found for this subject.</p>
        <?php } ?>
      <?php } ?>
    </form>
  </div>

  <!-- Pop-up for result -->
  <div class="popup-overlay" id="resultPopup">
    <div class="popup-content" id="popupContent">
      <p id="popupMessage"></p>
      <button onclick="closePopup()">Close</button>
    </div>
  </div>

  <script>
    function updateAllConducted(value) {
      const conductedInputs = document.querySelectorAll('.conducted-input');
      conductedInputs.forEach(input => {
        input.value = value;
      });
      
      // Update all percentages
      const rows = document.querySelectorAll('table tr:not(:first-child)');
      rows.forEach(row => {
        const usn = row.querySelector('input[type="hidden"]').name.match(/attendance\[(.*?)\]/)[1];
        updatePercentage(usn);
      });
    }
    
    function updatePercentage(usn) {
      const conductedInput = document.querySelector(`input[name="attendance[${usn}][conducted]"]`);
      const attendedInput = document.querySelector(`input[name="attendance[${usn}][attended]"]`);
      const excusedInput = document.querySelector(`input[name="attendance[${usn}][excused]"]`);
      const percentageCell = document.getElementById(`percentage-${usn}`);
      
      const conducted = parseInt(conductedInput.value) || 0;
      const attended = parseInt(attendedInput.value) || 0;
      const excused = parseInt(excusedInput.value) || 0;
      
      // Fix percentage calculation
      const effectiveClasses = Math.max(0, conducted - excused);
      let percentage = 0;
      if (effectiveClasses > 0) {
        percentage = (attended / effectiveClasses) * 100;
      }
      
      percentageCell.textContent = percentage.toFixed(2) + '%';
      
      // Update max values
      attendedInput.max = conducted;
      excusedInput.max = conducted;
    }

    function submitAttendance() {
      const form = document.getElementById('attendanceForm');
      const formData = new FormData(form);
      const subjectCode = document.getElementById('subject_code').value;

      if (!subjectCode) {
        showPopup('Please select a subject.', 'error');
        return;
      }

      if (confirm('Are you sure you want to submit the attendance?')) {
        fetch('save_attendance.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          console.log('Response Status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Response Data:', data);
          if (data.success) {
            showPopup(data.message || 'Attendance updated successfully!', 'success');
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else {
            showPopup(data.message || 'Failed to update attendance: Unknown error', 'error');
          }
        })
        .catch(error => {
          console.error('Fetch Error:', error);
          showPopup('Error updating attendance: ' + error.message, 'error');
        });
      }
    }

    function showPopup(message, type) {
      const popup = document.getElementById('resultPopup');
      const popupContent = document.getElementById('popupContent');
      const popupMessage = document.getElementById('popupMessage');
      popupMessage.textContent = message;
      popupContent.className = `popup-content ${type}`;
      popup.style.display = 'flex';
    }

    function closePopup() {
      document.getElementById('resultPopup').style.display = 'none';
    }
  </script>
</body>
</html>