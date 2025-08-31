<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: faculty-login.html");
    exit();
}

$name = htmlspecialchars($_SESSION["user_name"]);

// Handle logout
if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: faculty-login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Dashboard</title>
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

    .header {
      width: 100%;
      max-width: 1200px;
      display: flex;
      justify-content: center; /* Center the entire header content */
      align-items: center;
      margin-bottom: 40px;
      position: relative;
      z-index: 1;
    }

    .title-container {
      flex-grow: 1;
      text-align: center;
    }

    .greeting {
      font-size: 2.8rem;
      color: var(--highlight);
      margin-bottom: 10px;
    }

    .welcome-message {
      font-size: 1.5rem;
      color: var(--highlight);
    }

    .logout-form {
      position: absolute;
      right: 0;
    }

    .logout-btn {
      background: var(--danger);
      color: var(--white);
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s;
    }

    .logout-btn:hover {
      background: #cc2f2f;
    }

    .dashboard-container {
      width: 100%;
      max-width: 1200px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      position: relative;
      z-index: 1;
    }

    .card {
      background: var(--card-bg);
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      transition: transform 0.3s, box-shadow 0.3s;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 250px;
      position: relative;
      z-index: 1;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0, 255, 213, 0.2);
    }

    .card h2 {
      font-size: 1.8rem;
      margin-bottom: 15px;
      color: var(--highlight);
      text-align: center;
    }

    .card p {
      font-size: 1rem;
      color: #ccc;
      text-align: center;
      margin-bottom: 20px;
      flex-grow: 1;
    }

    .card button {
      background: var(--highlight);
      color: var(--white);
      border: none;
      padding: 12px 25px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      width: 100%;
      transition: background 0.3s;
    }

    .card button:hover {
      background: #00b7a0;
    }

    @media (max-width: 768px) {
      .greeting {
        font-size: 2rem;
      }
      .welcome-message {
        font-size: 1.2rem;
      }
    }

    @media (max-width: 480px) {
      .header {
        flex-direction: column;
        gap: 20px;
      }
      .logout-form {
        position: static;
      }
      .logout-btn {
        width: 100%;
        padding: 12px;
      }
    }
  </style>
</head>
<body>
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

  <div class="header">
    <div class="title-container">
      <h1 class="greeting">Hello, <?php echo $name; ?>!</h1>
      <h2 class="welcome-message">Welcome to Your Dashboard</h2>
    </div>
    <form method="POST" class="logout-form">
      <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
  </div>

  <div class="dashboard-container">
    <!-- Manage Course Card -->
    <div class="card">
      <h2>Manage Course</h2>
      <p>Add or remove subjects for the curriculum.</p>
      <a href="manage-course.php"><button>Manage</button></a>
    </div>

    <!-- Attendance Card -->
    <div class="card">
      <h2>Attendance</h2>
      <p>Update attendance records for students.</p>
      <a href="update-attendance.php"><button>Update</button></a>
    </div>

    <!-- Assign Grades Card -->
    <div class="card">
      <h2>Assign Grades</h2>
      <p>Assign grades to students for their subjects.</p>
      <a href="assign-grades.php"><button>Assign</button></a>
    </div>

    <!-- Share Notes Card -->
    <div class="card">
      <h2>Share Notes</h2>
      <p>Upload and share notes with students.</p>
      <a href="share-notes.php"><button>Share</button></a>
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