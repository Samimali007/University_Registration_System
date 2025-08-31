<?php

session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if (!isset($_SESSION['usn'])) {
    header("Location: student-login.html");
    exit();
}

$usn = $_SESSION['usn'];

// DB Connection
$conn = new mysqli("localhost", "root", "", "university_system");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Get student's branch
$stmt = $conn->prepare("SELECT branch FROM students WHERE usn = ?");
$stmt->bind_param("s", $usn);
$stmt->execute();
$studentData = $stmt->get_result()->fetch_assoc();
$branch = $studentData['branch'];

// Get all subjects for the student's branch
$subjectsQuery = $conn->prepare("SELECT subject_code, subject_name FROM subjects WHERE branch = ?");
$subjectsQuery->bind_param("s", $branch);
$subjectsQuery->execute();
$subjects = $subjectsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Get uploaded files for this student
$uploadsQuery = $conn->prepare("SELECT file_name, upload_date FROM student_uploads WHERE usn = ? ORDER BY upload_date DESC");
$uploadsQuery->bind_param("s", $usn);
$uploadsQuery->execute();
$uploads = $uploadsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> - Student Portal</title>
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
      margin-bottom: 30px;
      color: var(--highlight);
    }

    .container {
      width: 100%;
      max-width: 800px;
    }

    .panel {
      background: var(--glass-bg);
      border-radius: 20px;
      padding: 30px;
      backdrop-filter: blur(14px);
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 30px;
    }

    .panel h2 {
      font-size: 1.8rem;
      margin-bottom: 25px;
      color: var(--highlight);
      text-align: center;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 8px;
      color: #ddd;
    }

    select {
      width: 100%;
      padding: 14px;
      font-size: 1rem;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: white;
      border-radius: 12px;
      transition: 0.3s;
    }

    select:focus {
      outline: none;
      border-color: var(--highlight);
      box-shadow: 0 0 0 2px rgba(0, 255, 213, 0.2);
    }

    select option {
      background: #1a1a1a;
      color: white;
    }

    .dropzone {
      border: 2px dashed rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      padding: 40px;
      text-align: center;
      background: rgba(255, 255, 255, 0.03);
      color: #aaa;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
      margin-bottom: 20px;
    }

    .dropzone:hover {
      border-color: var(--highlight);
      color: white;
      background: rgba(0, 255, 213, 0.05);
    }

    .dropzone.dragover {
      border-color: var(--highlight);
      background: rgba(0, 255, 213, 0.1);
    }

    .dropzone input[type="file"] {
      display: none;
    }

    .file-preview {
      margin-top: 15px;
      font-size: 14px;
      color: var(--success);
    }

    .submit-btn {
      width: 100%;
      padding: 14px;
      background: var(--highlight);
      color: black;
      font-weight: bold;
      font-size: 1rem;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: 0.3s;
      box-shadow: 0 0 15px rgba(0, 255, 213, 0.3);
    }

    .submit-btn:hover {
      background: #00e6c0;
      box-shadow: 0 0 25px rgba(0, 255, 213, 0.5);
    }

    .uploads-list {
      margin-top: 30px;
    }

    .uploads-list h3 {
      font-size: 1.2rem;
      margin-bottom: 15px;
      color: var(--highlight);
      border-bottom: 1px solid rgba(255,255,255,0.1);
      padding-bottom: 10px;
    }

    .upload-item {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 14px 18px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: 0.3s;
    }

    .upload-item:hover {
      background: rgba(255, 255, 255, 0.08);
      transform: translateY(-2px);
    }

    .upload-info {
      flex: 1;
    }

    .upload-name {
      font-size: 0.95rem;
      color: #eee;
      margin-bottom: 5px;
    }

    .upload-date {
      font-size: 0.8rem;
      color: #aaa;
    }

    .upload-actions button {
      background: rgba(0, 255, 213, 0.1);
      color: var(--highlight);
      border: 1px solid rgba(0, 255, 213, 0.3);
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      margin-left: 10px;
      transition: 0.3s;
    }

    .upload-actions button:hover {
      background: rgba(0, 255, 213, 0.2);
    }

    .upload-actions button.delete {
      background: rgba(255, 62, 62, 0.1);
      color: var(--danger);
      border-color: rgba(255, 62, 62, 0.3);
    }

    .upload-actions button.delete:hover {
      background: rgba(255, 62, 62, 0.2);
    }

    .no-uploads {
      color: #777;
      font-style: italic;
      text-align: center;
      padding: 20px;
    }
  </style>
</head>
<body>


  <div class="container">
    <!-- Upload Panel -->
    <div class="panel">
      <h2>Upload Materials</h2>
      <form id="uploadForm" method="POST" action="upload-handler.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-group">
          <label for="subject">Subject</label>
          <select id="subject" name="subject" required>
            <option value="">Select Subject</option>
            <?php foreach ($subjects as $subject): ?>
              <option value="<?= htmlspecialchars($subject['subject_code']) ?>">
                <?= htmlspecialchars($subject['subject_code']) ?> - <?= htmlspecialchars($subject['subject_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="dropzone" id="drop-area">
          <p>📁 Drag & Drop your file here or <strong>Click</strong> to select</p>
          <input type="file" name="note_file" id="fileElem" required />
          <div class="file-preview" id="file-preview">No file selected</div>
        </div>
        <input type="hidden" name="usn" value="<?= htmlspecialchars($usn) ?>">
        <button type="submit" class="submit-btn">Upload</button>
      </form>
    </div>

    <!-- Uploads List Panel -->
    <div class="panel">
      <h2>Your Uploads</h2>
      <div class="uploads-list">
        <h3>Recently Uploaded Files</h3>
        
        <?php if (count($uploads) > 0): ?>
          <?php foreach ($uploads as $upload): ?>
            <div class="upload-item">
              <div class="upload-info">
                <div class="upload-name"><?= htmlspecialchars($upload['file_name']) ?></div>
                <div class="upload-date">Uploaded on <?= date('M d, Y H:i', strtotime($upload['upload_date'])) ?></div>
              </div>
              <div class="upload-actions">
                <button onclick="viewFile('<?= htmlspecialchars($upload['file_name']) ?>')">View</button>
                <button class="delete" onclick="deleteFile('<?= htmlspecialchars($upload['file_name']) ?>')">Delete</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-uploads">You haven't uploaded any files yet</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    // Dropzone functionality
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('fileElem');
    const filePreview = document.getElementById('file-preview');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, preventDefaults, false);
      document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
      dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
      dropArea.classList.add('dragover');
    }

    function unhighlight() {
      dropArea.classList.remove('dragover');
    }

    // Handle dropped files
    dropArea.addEventListener('drop', handleDrop, false);
    dropArea.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', handleFiles);

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      handleFiles({ target: { files } });
    }

    function handleFiles(e) {
      const files = e.target.files;
      if (files.length > 0) {
        filePreview.textContent = files[0].name;
      }
    }

    // File actions
    function viewFile(filename) {
      // In a real implementation, this would open the file
      alert(`Viewing file: ${filename}`);
      // window.open(`uploads/${filename}`, '_blank');
    }

    function deleteFile(filename) {
      if (confirm(`Are you sure you want to delete ${filename}?`)) {
        // AJAX call to delete the file
        fetch('delete-file.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            filename: filename,
            usn: '<?= $usn ?>'
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload(); // Refresh to show updated list
          } else {
            alert('Error deleting file: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while deleting the file');
        });
      }
    }

// Form submission with AJAX
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('upload-handler.php', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('File uploaded successfully!');
            location.reload();
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Upload failed: ' + error.message);
    });
    function handleFiles(e) {
    const files = e.target.files;
    if (files.length > 0) {
        const allowedTypes = ['application/pdf', 'application/msword', 
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!allowedTypes.includes(files[0].type)) {
            alert('Only PDF, DOC, and DOCX files are allowed');
            fileInput.value = '';
            filePreview.textContent = 'No file selected';
            return;
        }
        
        filePreview.textContent = files[0].name;
    }
}
});

  </script>
</body>
</html>