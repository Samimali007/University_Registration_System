<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Welcome | Course Portal</title>
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
      color: var(--white);
    }

    .bubble {
      position: absolute;
      border-radius: 50%;
      background: rgba(0, 255, 213, 0.4);
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

    header {
      position: fixed;
      top: 0;
      width: 100%;
      padding: 20px 40px;
      background: rgba(0, 0, 0, 0.8);
      display: flex;
      justify-content: center;
      z-index: 100;
    }

    header h1 {
      font-size: 1.5rem;
      color: var(--highlight);
      font-weight: 700;
    }

    .hero {
      min-height: 80vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 100px 20px 40px;
      position: relative;
    }

    .hero h1 {
      font-size: 3rem;
      margin-bottom: 20px;
      color: var(--highlight);
      font-weight: 700;
      text-shadow: 0 0 10px rgba(0, 255, 213, 0.5);
    }

    .hero h2 {
      font-size: 1.5rem;
      margin-bottom: 30px;
      color: var(--white);
      font-weight: 600;
    }

    .hero p {
      font-size: 1.1rem;
      margin-bottom: 40px;
      color: var(--white);
      font-weight: 400;
      max-width: 600px;
    }

    .btn-group {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .btn {
      padding: 12px 30px;
      border: 2px solid var(--highlight);
      border-radius: 5px;
      font-size: 1.1rem;
      cursor: pointer;
      color: var(--white);
      font-weight: 600;
      background: transparent;
      transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
      text-decoration: none;
      display: inline-block;
    }

    .btn:hover {
      background: rgba(0, 255, 213, 0.2);
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(0, 255, 213, 0.3);
    }

    footer {
      margin-top: auto;
      padding: 20px;
      text-align: center;
      font-size: 0.75rem;
      opacity: 0.8;
      color: var(--highlight);
      font-weight: 400;
      background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
      width: 100%;
    }

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }
      .hero h2 {
        font-size: 1.2rem;
      }
      .hero p {
        font-size: 1rem;
      }
      .btn {
        padding: 10px 20px;
        font-size: 1rem;
      }
    }

    @media (max-width: 480px) {
      header {
        padding: 15px 20px;
      }
      .hero {
        padding: 80px 10px 20px;
      }
      .btn-group {
        flex-direction: column;
        gap: 10px;
      }
      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>
  <div class="bubble"></div>

  
  <div class="hero">
    <h1>Welcome to University Management System</h1>
    <h2>Your Smart Academic Companion</h2>
    <p>Unlock your academic journey with personalized tools and resources. Choose your role to get started.</p>
    <div class="btn-group">
      <a class="btn" href="Student/student-login.html">Student Login</a>
      <a class="btn" href="Admin/faculty-login.html">Faculty / Admin Login</a>
      <a class="btn" href="Student/student-register.html">New Student? Register</a>
    </div>
  </div>

  <footer>
    Designed By Sami | Developed by None
  </footer>
</body>
</html>