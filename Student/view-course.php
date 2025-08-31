<?php
session_start();

if (!isset($_SESSION['usn'])) {
    echo "<script>alert('Please login first'); window.location.href='student-login.html';</script>";
    exit();
}

$usn = $_SESSION['usn'];
$elective1 = "";
$elective2 = "";

// DB Connection
$conn = new mysqli("localhost", "root", "", "university_system");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Fetch student info and subjects
$studentInfo = $conn->prepare("SELECT branch, semester FROM students WHERE usn = ?");
$studentInfo->bind_param("s", $usn);
$studentInfo->execute();
$studentResult = $studentInfo->get_result();

if ($studentResult->num_rows > 0) {
    $studentData = $studentResult->fetch_assoc();
    $branch = $studentData['branch'];
    $semester = $studentData['semester'];
    
    // Get subjects with full details including description
    $subjectsQuery = $conn->prepare("SELECT s.id, s.subject_code as code, s.subject_name as name, 
                                    f.faculty_name, f.faculty_email
                                    FROM subjects s
                                    LEFT JOIN faculty_subjects f ON s.subject_code = f.subject_code
                                    WHERE s.branch = ? AND s.semester = ?");
    $subjectsQuery->bind_param("ss", $branch, $semester);
    $subjectsQuery->execute();
    $subjectsResult = $subjectsQuery->get_result();
    $subjects = [];
    while ($row = $subjectsResult->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Fetch electives
$stmt = $conn->prepare("SELECT elective1, elective2 FROM registered_courses WHERE usn = ?");
$stmt->bind_param("s", $usn);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
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
  <title>View Registered Courses</title>
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
      cursor: pointer;
    }

    .subject-card:hover {
      background: rgba(255, 255, 255, 0.08);
      transform: translateY(-4px);
      box-shadow: 0 5px 15px rgba(0, 255, 213, 0.2);
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
      margin-bottom: 4px;
    }

    .subject-faculty {
      font-size: 0.85rem;
      color: #aaa;
      font-style: italic;
    }

    .elective-section {
      margin-top: 30px;
    }

    .elective-heading {
      font-weight: 600;
      margin: 20px 0 8px;
      color: var(--highlight);
    }

    .placeholder {
      color: #777;
      font-style: italic;
      padding: 10px;
      background: rgba(255,255,255,0.03);
      border-radius: 8px;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.8);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
      border-radius: 16px;
      padding: 30px;
      width: 90%;
      max-width: 600px;
      border: 1px solid var(--highlight);
      box-shadow: 0 0 30px rgba(0, 255, 213, 0.3);
      position: relative;
    }

    .close-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      color: var(--highlight);
      font-size: 28px;
      cursor: pointer;
    }

    .modal-subject-code {
      font-size: 1.8rem;
      color: var(--highlight);
      margin-bottom: 5px;
    }

    .modal-subject-name {
      font-size: 1.4rem;
      margin-bottom: 20px;
    }

    .modal-info-item {
      margin-bottom: 12px;
    }

    .modal-info-label {
      font-weight: 600;
      color: var(--highlight);
      display: inline-block;
      width: 120px;
    }

    .modal-description {
      margin-top: 25px;
      padding: 15px;
      background: rgba(255,255,255,0.05);
      border-radius: 10px;
      line-height: 1.6;
    }
  </style>
</head>
<body>

  <h1>Your Registered Courses</h1>
  <h2><?php echo htmlspecialchars($semester); ?> Semester (<?php echo htmlspecialchars($branch); ?>)</h2>

  <div class="container">
    <div class="subject-box">
      <div class="subject-grid">
        <?php foreach ($subjects as $subject): ?>
          <div class="subject-card" 
               onclick="openSubjectModal(
                 '<?php echo htmlspecialchars($subject['code']); ?>',
                 '<?php echo htmlspecialchars($subject['name']); ?>',
                 '<?php echo htmlspecialchars($subject['faculty_name'] ?? 'Not assigned'); ?>',
                 '<?php echo htmlspecialchars($subject['faculty_email'] ?? ''); ?>',
                 '<?php echo htmlspecialchars($subject['faculty_phone'] ?? ''); ?>',
                 '<?php echo htmlspecialchars($subject['description'] ?? 'No description available'); ?>'
               )">
            <div class="subject-code"><?php echo htmlspecialchars($subject['code']); ?></div>
            <div class="subject-name"><?php echo htmlspecialchars($subject['name']); ?></div>
            <div class="subject-faculty">
              <?php echo isset($subject['faculty_name']) ? 'Faculty: '.htmlspecialchars($subject['faculty_name']) : 'Faculty: To be assigned'; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="elective-section">
      <div class="elective-heading">1st Elective (1 Credit)</div>
      <?php if (!empty($elective1)): ?>
        <div class="subject-card">
          <div class="subject-name"><?php echo htmlspecialchars($elective1); ?></div>
          <div class="subject-faculty">Faculty: To be assigned</div>
        </div>
      <?php else: ?>
        <div class="placeholder">No elective selected yet</div>
      <?php endif; ?>
    </div>

    <div class="elective-section">
      <div class="elective-heading">2nd Elective (1 Credit)</div>
      <?php if (!empty($elective2)): ?>
        <div class="subject-card">
          <div class="subject-name"><?php echo htmlspecialchars($elective2); ?></div>
          <div class="subject-faculty">Faculty: To be assigned</div>
        </div>
      <?php else: ?>
        <div class="placeholder">No elective selected yet</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Subject Detail Modal -->
  <div id="subjectModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">&times;</span>
      <div class="modal-subject-code" id="modalSubjectCode"></div>
      <div class="modal-subject-name" id="modalSubjectName"></div>
      
      <div class="modal-info-item">
        <span class="modal-info-label">Faculty:</span>
        <span id="modalFacultyName"></span>
      </div>
      
      <div class="modal-info-item">
        <span class="modal-info-label">Email:</span>
        <span id="modalFacultyEmail"></span>
      </div>
      
      <div class="modal-info-item">
        <span class="modal-info-label">Contact:</span>
        <span id="modalFacultyPhone"></span>
      </div>
      
      <div class="modal-description">
        <h4>About This Subject</h4>
        <p id="modalDescription"></p>
      </div>
    </div>
  </div>

  <script>
    function openSubjectModal(code, name, faculty, email, phone, description) {
      document.getElementById('modalSubjectCode').textContent = code;
      document.getElementById('modalSubjectName').textContent = name;
      document.getElementById('modalFacultyName').textContent = faculty;
      document.getElementById('modalFacultyEmail').textContent = email || 'Not available';
      document.getElementById('modalFacultyPhone').textContent = phone || 'Not available';
      document.getElementById('modalDescription').textContent = description;
      
      document.getElementById('subjectModal').style.display = 'flex';
      document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
    }

    function closeModal() {
      document.getElementById('subjectModal').style.display = 'none';
      document.body.style.overflow = 'auto'; // Re-enable scrolling
    }

    // Close modal when clicking outside content
    window.onclick = function(event) {
      if (event.target == document.getElementById('subjectModal')) {
        closeModal();
      }
    }

    // Close with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal();
      }
    });
  </script>
</body>
</html>