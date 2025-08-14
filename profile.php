<?php
session_start();
if (!isset($_SESSION['student'])) {
  header("Location: login.html");
  exit();
}
$student = $_SESSION['student'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Profile</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      min-height: 100vh;
      background: linear-gradient(to bottom,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,0) 50%,
        #f0f4f8 50%,
        #f0f4f8 100%);
      position: relative;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 40px 20px;
    }
    body::before {
      content: "";
      background: url('images/background.png') no-repeat center center;
      background-size: cover;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 50vh;
      z-index: -1;
    }
    .container {
      background-color: rgba(255, 255, 255, 0.95);
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 56, 101, 0.2);
      max-width: 500px;
      width: 100%;
      margin-top: 40px;
      z-index: 1;
      position: relative;
      text-align: center;
    }
    .profile-pic {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 20px;
      border: 3px solid #003865;
    }
    h2 {
      color: #003865;
      margin-bottom: 20px;
    }
    .detail {
      text-align: left;
      margin: 8px 0;
      font-size: 16px;
    }
    .detail strong {
      color: #003865;
    }
    .back-btn {
      margin-top: 25px;
      background-color: #e0e0e0;
      color: #003865;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .back-btn:hover {
      background-color: #c7c7c7;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Profile Image -->
    <img src="images/profileP.PNG" alt="Profile Picture" class="profile-pic">

    <h2><?php echo htmlspecialchars($student['full_name']); ?>'s Profile</h2>

    <!-- Student Details -->
    <p class="detail"><strong>Full Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
    <p class="detail"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
    <p class="detail"><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
    <p class="detail"><strong>Student Number:</strong> <?php echo htmlspecialchars($student['student_no']); ?></p>
    <p class="detail"><strong>Address:</strong> <?php echo htmlspecialchars($student['address'] ?? ''); ?></p>

    <!-- Back Button -->
    <button class="back-btn" onclick="window.location.href='dashboard.html'">Back to Dashboard</button>
    
  </div>
</body>
</html>