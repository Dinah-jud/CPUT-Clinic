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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --dark-blue: #003399;
      --mid-blue: #0099CC;
      --light-bg: #f4f7fb;
      --white: #ffffff;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background-color: var(--light-bg);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Top Navbar */
    .navbar {
      background-color: var(--dark-blue);
      color: var(--white);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .navbar h1 {
      font-size: 20px;
      margin: 0;
    }

    .navbar .nav-links a {
      color: var(--white);
      text-decoration: none;
      margin-left: 20px;
      font-size: 15px;
      transition: color 0.3s ease;
    }

    .navbar .nav-links a:hover {
      color: var(--mid-blue);
    }

    /* Profile Container */
    .profile-container {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-grow: 1;
      padding: 40px 20px;
    }

    .profile-card {
      background-color: var(--white);
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
      padding: 40px;
      width: 100%;
      max-width: 500px;
      text-align: center;
    }

    .profile-pic {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid var(--mid-blue);
      margin-bottom: 15px;
    }

    h2 {
      color: var(--dark-blue);
      margin-bottom: 15px;
    }

    .detail {
      text-align: left;
      font-size: 16px;
      margin: 8px 0;
    }

    .detail strong {
      color: var(--mid-blue);
    }

    .back-btn {
      margin-top: 25px;
      background-color: var(--dark-blue);
      color: var(--white);
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .back-btn:hover {
      background-color: var(--mid-blue);
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <h1><i class="fa-solid fa-user-graduate"></i> Student Profile</h1>
    <div class="nav-links">
      <a href="dashboard.html"><i class="fa-solid fa-home"></i> Dashboard</a>
      <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </div>

  <!-- Profile Section -->
  <div class="profile-container">
    <div class="profile-card">
      <img src="images/profileP.PNG" alt="Profile Picture" class="profile-pic">
      <h2><?php echo htmlspecialchars($student['full_name']); ?>'s Profile</h2>

      <p class="detail"><strong>Full Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
      <p class="detail"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
      <p class="detail"><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
      <p class="detail"><strong>Student Number:</strong> <?php echo htmlspecialchars($student['student_no']); ?></p>
      <p class="detail"><strong>Address:</strong> <?php echo htmlspecialchars($student['address'] ?? ''); ?></p>

      <button class="back-btn" onclick="window.location.href='dashboard.html'">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
      </button>
    </div>
  </div>

</body>
</html>
