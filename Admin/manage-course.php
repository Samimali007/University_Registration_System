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

// Fetch unique branches
$branches = $conn->query("SELECT DISTINCT branch FROM subjects");

// Fetch faculty's assigned subjects and all subjects
$selected_branch = isset($_POST['branch']) ? $_POST['branch'] : '';
$selected_semester = isset($_POST['semester']) ? $_POST['semester'] : '';
$assigned_subjects = [];
$all_subjects = [];

if ($selected_branch && $selected_semester) {
    // Assigned subjects for the faculty
    $stmt = $conn->prepare("SELECT s.subject_code, s.subject_name FROM faculty_subjects fs JOIN subjects s ON fs.subject_code = s.subject_code WHERE fs.faculty_email = ? AND s.branch = ? AND s.semester = ?");
    $stmt->bind_param("ssi", $email, $selected_branch, $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assigned_subjects[] = $row;
    }
    $stmt->close();

    // All subjects for the branch and semester
    $stmt = $conn->prepare("SELECT subject_code, subject_name FROM subjects WHERE branch = ? AND semester = ?");
    $stmt->bind_param("si", $selected_branch, $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $all_subjects[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Course</title>
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

    .subject-list {
      margin-top: 20px;
    }

    .subject-list h3 {
      font-size: 1.2rem;
      color: var(--success);
      margin-bottom: 10px;
    }

    .subject-list table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
      background: var(--card-bg);
      border-radius: 10px;
      overflow: hidden;
    }

    .subject-list th, .subject-list td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .subject-list th {
      background: #222;
      color: var(--highlight);
    }

    .subject-list td {
      color: #ccc;
    }

    .subject-list .assigned {
      color: var(--highlight);
      font-weight: 600;
    }

    .subject-list button {
      background: var(--highlight);
      color: var(--white);
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s;
    }

    .subject-list button:hover {
      background: #00b7a0;
    }

    .subject-list button:disabled {
      background: #555;
      cursor: not-allowed;
    }

    .subject-list .chosen button {
      background: #00b7a0;
      border: 2px solid var(--success);
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
      display: none;
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

      .subject-list th, .subject-list td {
        padding: 8px;
        font-size: 0.9rem;
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
    <h1>📊 Manage Course</h1>
    <p class="subheading">Add or Remove Subjects for the Curriculum</p>
  </div>

  <div class="content-container">
    <form method="POST" action="" id="courseForm">
      <label for="branch">Branch:</label>
      <select name="branch" id="branch" onchange="this.form.submit()">
        <option value="">Select Branch</option>
        <?php while ($row = $branches->fetch_assoc()) { ?>
          <option value="<?php echo htmlspecialchars($row['branch']); ?>" <?php echo $selected_branch === $row['branch'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($row['branch']); ?>
          </option>
        <?php } ?>
      </select>

      <?php if ($selected_branch) { ?>
        <div id="semesterDiv">
          <label for="semester">Semester:</label>
          <select name="semester" id="semester" onchange="this.form.submit()">
            <option value="">Select Semester</option>
            <?php for ($i = 1; $i <= 8; $i++) { ?>
              <option value="<?php echo $i; ?>" <?php echo $selected_semester == $i ? 'selected' : ''; ?>>
                Semester <?php echo $i; ?>
              </option>
            <?php } ?>
          </select>
        </div>
      <?php } ?>

      <?php if ($selected_branch && $selected_semester) { ?>
        <div id="subjectsDiv">
          <div class="subject-list" id="subjectList">
            <h3>All Subjects:</h3>
            <table>
              <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Action</th>
              </tr>
              <?php foreach ($all_subjects as $subject) { ?>
                <tr data-subject-code="<?php echo htmlspecialchars($subject['subject_code']); ?>">
                  <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                  <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                  <td>
                    <?php
                    $is_assigned = false;
                    foreach ($assigned_subjects as $assigned) {
                      if ($assigned['subject_code'] === $subject['subject_code']) {
                        $is_assigned = true;
                        break;
                      }
                    }
                    if ($is_assigned) {
                      echo '<span class="assigned">Assigned</span>';
                    } else {
                      $disable_button = count($assigned_subjects) >= 1 ? 'disabled' : '';
                      ?>
                      <button type="button" onclick="registerSubject('<?php echo htmlspecialchars($subject['subject_code']); ?>', this)" <?php echo $disable_button; ?>>Choose</button>
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
            </table>
            <?php if (count($assigned_subjects) < 1) { ?>
              <button type="button" id="submitBtn" onclick="submitChoices()">Submit</button>
            <?php } ?>
          </div>
        </div>
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
    let selectedSubject = '';

    function registerSubject(subjectCode, button) {
      selectedSubject = subjectCode;
      console.log(`Selected subject: ${selectedSubject}`);

      // Remove 'chosen' class from all rows
      document.querySelectorAll('.subject-list tr').forEach(row => {
        row.classList.remove('chosen');
        const btn = row.querySelector('button');
        if (btn) btn.classList.remove('chosen');
      });

      // Add 'chosen' class to the selected row and button
      const row = button.closest('tr');
      row.classList.add('chosen');
      button.classList.add('chosen');

      // Show the Submit button
      document.getElementById('submitBtn').style.display = 'block';
    }

    function submitChoices() {
      const branch = document.getElementById('branch').value;
      const semester = document.getElementById('semester').value;
      const facultyEmail = '<?php echo $email; ?>';

      if (!selectedSubject) {
        showPopup('Please select a subject first.', 'error');
        return;
      }

      if (confirm('Are you sure you want to register for the selected subject?')) {
        const body = `email=${encodeURIComponent(facultyEmail)}&subjectcode=${encodeURIComponent(selectedSubject)}&branch=${encodeURIComponent(branch)}&semester=${encodeURIComponent(semester)}`;
        console.log('POST Data:', body);

        fetch('register_subject.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body
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
            showPopup(data.message || 'Subject registered successfully!', 'success');
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else {
            showPopup(data.message || 'Failed to register subject: Unknown error', 'error');
          }
        })
        .catch(error => {
          console.error('Fetch Error:', error);
          showPopup('Error registering subject: ' + error.message, 'error');
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