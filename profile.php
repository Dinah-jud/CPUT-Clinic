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
</head>
<body>
  <div class="container">
    <img src="images/cput-logo.png" alt="CPUT Logo" class="logo">
    
    <h2>Welcome <?php echo htmlspecialchars($student['full_name']); ?>!</h2>
    
    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
    <p><strong>Student Number:</strong> <?php echo htmlspecialchars($student['student_no']); ?></p>

    <div class="button-row">
      <button onclick="window.location.href='update-profile.html'">Update Profile</button>
      <button onclick="window.location.href='book-appointment.html'">Book Appointment</button>
      <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
  </div>
</body>
</html>