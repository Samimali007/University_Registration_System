<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['usn'])) {
    echo "<script>alert('Please login first'); window.location.href='student-login.html';</script>";
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$usn = $_SESSION['usn'];

// Fetch student details
$stmt = $conn->prepare("SELECT name, usn, branch, semester, College FROM students WHERE usn = ?");
$stmt->bind_param("s", $usn);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $studentName = $row['name'];
    $studentUSN = $row['usn'];
    $studentBranch = $row['branch'];
    $studentsemester = $row['semester']; 
} else {
    $studentName = "Unknown";
    $studentUSN = $usn;
    $studentBranch = "Unknown";
    $studentsemester = $semester; 
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Google Fonts and Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
    }

    .header {
      text-align: center;
      margin-top: 40px;
    }

    .header h1 {
      font-size: 28px;
      margin-bottom: 5px;
    }

    .header p {
      font-size: 16px;
      margin: 0;
    }

    .dashboard {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 30px;
      margin-top: 60px;
      max-width: 1000px;
    }
    .profile-container {
      position: absolute;
      top: 20px;
      right: 30px;
      display: inline-block;
      z-index: 1000;
    }

    .profile-toggle {
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      padding: 8px 12px;
      border-radius: 8px;
      transition: background 0.2s;
      background: transparent;
    }

    .profile-toggle:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .profile-pic {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid white;
    }

    .profile-container:hover .dropdown-menu {
      display: flex;
      flex-direction: column;
    }

    .dropdown-menu {
      display: none;
      position: absolute;
      top: 50px;
      right: 0;
      background-color: white;
      padding: 12px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      width: 160px;
      z-index: 999;
      transition: all 0.3s ease-in-out;
    }

    .dropdown-menu a,
    .dropdown-menu button {
      text-decoration: none;
      color: black;
      padding: 10px;
      font-weight: 500;
      border: none;
      background: none;
      text-align: left;
      width: 80%;
      border-radius: 8px;
      transition: background 0.2s;
      font-family: 'Poppins', sans-serif;
    }

    .dropdown-menu a:hover,
    .dropdown-menu button:hover {
      background-color: #f0f4ff;
    }
    .modal {
  display: none;
  position: fixed;
  z-index: 999;
  padding-top: 80px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.6);
}

.modal-content {
  background-color: #fff;
  margin: auto;
  padding: 20px;
  border-radius: 10px;
  width: 400px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  text-align: center;
}

.close {
  color: #aaa;
  float: right;
  font-size: 24px;
  cursor: pointer;
}
.close:hover {
  color: #000;
}

    .card-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 15px;
  border: 3px solid #00e2c0; /* Blue stroke */
  box-shadow: 0 4px 12px rgba(47, 128, 237, 0.2); /* Soft modern shadow */
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.dashboard {
  display: flex;
  justify-content: center;
  flex-wrap: wrap; /* Mobile ke liye wrap allow */
  gap: 20px;
  margin-top: 60px;
  max-width: 1000px;
}

@media (min-width: 992px) {
  .dashboard {
    flex-wrap: nowrap; /* Desktop pe single row */
  }
}

.card {
  background-color: #ffffff;
  color: #2F80ED;
  width: 220px; /* 👈 Thoda chhota size */
  height: auto;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  text-align: center;
  padding: 25px 15px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
  overflow: hidden;
  flex-shrink: 0; /* 👈 Prevent shrinking in flex */
}




    .card {
      position: relative;
      width: 260px;
      padding: 30px 20px;
      border-radius: 15px;
      backdrop-filter: blur(15px);
      background: rgba(30, 30, 46, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
      z-index: 10;
    }
    .card {
    background: rgba(255, 255, 255, 0.08); /* Less opacity = more glass */
    backdrop-filter: blur(20px); /* Increase blur for more glass feel */
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.15); /* Soft glass border */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); /* Deep shadow */
    transition: all 0.3s ease;
    color : #00e2c0;
}

/* Optional hover effect for glow */
.card:hover {
    box-shadow: 0 12px 40px rgba(0, 123, 255, 0.2);
    transform: scale(1.02);
}


    .card::before {
      content: "";
      position: absolute;
      top: -40px;
      right: -40px;
      width: 100px;
      height: 100px;
      background: rgba(47,128,237,0.1);
      border-radius: 50%;
      
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    }

    .card i {
      font-size: 40px;
      margin-bottom: 15px;
      color: #00e2c0;
    }

    .card h3 {
      margin-bottom: 10px;
      font-size: 20px;
      font-weight: 600;
    }

    .card p {
      font-size: 14px;
      color:rgb(219, 214, 214);
      margin-bottom: 15px;
      line-height: 1.4;
    }

    .card a {
      text-decoration: none;
      color: black;
      background-color: #00e2c0;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 500;
      font-size: 14px;
      color : #00e2c0
      display: inline-block;
      transition: background-color 0.3s ease;
    }

    .card a:hover {
      background-color: #1e60c2;
      color:rgb(0, 0, 0);
    }
    @media (max-width: 768px) {
  .dashboard {
    overflow-x: auto;
    flex-wrap: nowrap;
    padding: 10px;
  }

  .card {
    flex: 0 0 auto;
    color: #00e2c0;
  }
}
.bubble {
      position: absolute;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.05);
      animation: float 20s linear infinite;
      pointer-events: none;
      z-index: 0;
}
@keyframes float {
      0% { transform: translateY(0) rotate(0deg); }
      100% { transform: translateY(-1000px) rotate(720deg); }
    }

    @media (max-width: 420px) {
      .container {
        width: 90%;
        padding: 30px 20px;
      }
    }

    .logout-btn {
      margin-top: 50px;
      background-color: white;
      color: #2F80ED;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    .logout-btn:hover {
      background-color: #f2f2f2;
    }

    
  </style>
</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<body>
  <!-- Trigger Button -->

  <div class="bubble" style="width: 100px; height: 100px; left: 10%; top: 90%;"></div>
  <div class="bubble" style="width: 150px; height: 150px; left: 70%; top: 95%;"></div>
  <div class="bubble" style="width: 80px; height: 80px; left: 30%; top: 85%;"></div>
  <div class="bubble" style="width: 120px; height: 120px; left: 80%; top: 100%;"></div>


<div class="profile-container">
  <div class="profile-toggle">

  <img src="uploads/<?php echo $studentUSN; ?>_profile.jpg?ts=<?php echo time(); ?>" class="profile-pic" onerror="this.src='default.jpg';">

    <?php echo htmlspecialchars($studentName); ?>
    <i class="fas fa-caret-down"></i>
  </div>
  <div class="dropdown-menu">
  <a href="#" onclick="openModal()">Add Profile Pic</a>

<!-- Upload Modal -->
<!-- Profile Pic Modal -->
<div id="profileModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2>Update Profile Picture</h2>
    <input type="file" id="inputImage" accept="image/*"><br><br>
    <div style="width: 300px; height: 300px; margin: auto;">
      <img id="imagePreview" style="max-width: 100%; max-height: 300px;">
    </div>
    <br>
    <button onclick="cropAndUpload()">Crop & Upload</button>
  </div>
</div>



    <a href="#">Edit Details</a>
    <form action="logout.php" method="post">
      <button type="submit">Logout</button>
    </form>
  </div>
</div>

<div class="header">
  <h1>Welcome, <?php echo htmlspecialchars($studentName); ?> 👋</h1>
  <p><strong>USN:</strong> <?php echo htmlspecialchars($studentUSN); ?> | 
     <strong>Branch:</strong> <?php echo htmlspecialchars($studentBranch); ?></p>
     <strong>Semester:</strong> <?php echo htmlspecialchars($studentsemester); ?></p>
</div>


<div class="dashboard">
  <div class="card">
    <img src="assests/Register-1.png" alt="View Course Icon" class="card-icon">
    <h3>Register Course</h3>
    <p>Enroll into your preferred courses and electives as per your branch.</p>
    <a href="Register-course1.php">Register</a>
  </div>

  <div class="card">
    <img src="assests/viewcourse-1.png" alt="Register Course Icon" class="card-icon">
    <h3>View Course</h3>
    <p>Check all available courses offered this semester with full details.</p>
    <a href="view-course.php">view</a>
  </div>

  <div class="card">
    <img src="assests/Drive.png" alt="Drop Course Icon" class="card-icon">
    <h3>Notes Sharing Drive</h3>
    <p>Access notes shared by your faculty and manage your personal notes privately.</p>
    <a href="Notes-sharing-drive.php">View</a>
  </div>

  <!-- ✅ New Track Progress Card -->
  <div class="card">
    <img src="assests/track.png" alt="Track Progress Icon" class="card-icon">
    <h3>Track Progress</h3>
    <p>Monitor your course completion status, grades, and academic performance.</p>
    <a href="attendance.php">Track</a>
  </div>
</div>
<script>
  let cropper;
const input = document.getElementById('inputImage');
const preview = document.getElementById('imagePreview');

function openModal() {
  document.getElementById('profileModal').style.display = 'block';
}

function closeModal() {
  document.getElementById('profileModal').style.display = 'none';
  if (cropper) cropper.destroy();
  preview.src = '';
}

input.addEventListener('change', function (e) {
  const file = e.target.files[0];
  if (file && /^image\/\w+/.test(file.type)) {
    const reader = new FileReader();
    reader.onload = function () {
      preview.src = reader.result;
      if (cropper) cropper.destroy();
      cropper = new Cropper(preview, {
        aspectRatio: 1,
        viewMode: 1
      });
    };
    reader.readAsDataURL(file);
  }
});

function cropAndUpload() {
  if (cropper) {
    cropper.getCroppedCanvas().toBlob(function (blob) {
      const formData = new FormData();
      formData.append('profile_image', blob, 'profile.jpg');

      fetch('profile-upload.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        alert('Profile updated!');
        closeModal();
        location.reload();
      })
      .catch(err => {
        alert('Upload failed!');
      });
    });
  }
}
</script>

<footer class="footer">

</footer>

</body>
</html>
