<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $appointment_type = $_POST['appointment_type'];

    // Check if doctor is available at that time
    $check_availability = $conn->query("
        SELECT * FROM availabilityschedule 
        WHERE doctor_id = '$doctor_id' 
        AND available_date = '$appointment_date'
        AND start_time <= '$appointment_time'
        AND end_time >= '$appointment_time'
    ");
    
    // Check if time slot is already booked
    $check_booked = $conn->query("
        SELECT * FROM appointment 
        WHERE doctor_id = '$doctor_id'
        AND appointment_date = '$appointment_date'
        AND appointment_time = '$appointment_time'
    ");
    
    if ($check_availability->num_rows == 0) {
        $message = "Error: Doctor is not available at this time! Please check the availability schedule.";
        $message_type = "error";
    } elseif ($check_booked->num_rows > 0) {
        $message = "Error: This time slot is already booked! Please choose a different time.";
        $message_type = "error";
    } else {
        // If available, book the appointment
        $sql = "INSERT INTO appointment (patient_id, doctor_id, appointment_date, appointment_time, appointment_type) 
                VALUES ('$patient_id', '$doctor_id', '$appointment_date', '$appointment_time', '$appointment_type')";

        if ($conn->query($sql) === TRUE) {
            $message = "Appointment booked successfully!";
            $message_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Appointment - CPUT Clinic</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .header {
      background-color: #003865;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .header img {
      width: 60px;
      margin-right: 15px;
    }

    .header-title {
      font-size: 1.4rem;
      font-weight: bold;
    }

    .profile-section {
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
    }

    .container {
      display: flex;
      flex: 1;
      min-height: calc(100vh - 140px);
    }

    .sidebar {
      width: 250px;
      background-color: #fff;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
      padding: 1rem 0;
    }

    .nav-item {
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      color: #003865;
      font-weight: bold;
      transition: background 0.3s;
    }

    .nav-item:hover, .nav-item.active {
      background-color: #e8f4fc;
      border-left: 4px solid #0072CE;
    }

    .nav-item i {
      color: #0072CE;
      width: 20px;
      text-align: center;
    }

    .main-content {
      flex: 1;
      padding: 2rem;
    }

    .message {
      padding: 15px;
      margin: 20px 0;
      border-radius: 5px;
      text-align: center;
      font-weight: bold;
    }

    .success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .content-card {
      background: white;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 500px;
      margin: 0 auto;
    }

    .btn-action {
      background-color: #0072CE;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin: 10px;
    }

    .btn-action:hover {
      background-color: #005fa3;
    }

    footer {
      background-color: #012773;
      color: white;
      padding: 30px 0;
      margin-top: auto;
    }

    .footer-content {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .footer-section {
      flex: 1;
      min-width: 250px;
      margin: 10px;
    }

    .footer-section h4 {
      margin-bottom: 10px;
      text-decoration: underline;
    }

    .footer-logo {
      width: 100px;
      margin-bottom: 10px;
    }

    .icon {
      width: 16px;
      height: 16px;
      margin-right: 8px;
      vertical-align: middle;
    }

    .social-icons a {
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 10px;
    }

    .footer-section ul {
      list-style: none;
      padding-left: 0;
    }

    .footer-section ul li {
      margin-bottom: 4px;
      line-height: 1.4;
    }
  </style>
</head>
<body>

  <header class="header">
    <div class="logo-container" style="display:flex;align-items:center;">
      <img src="../images/cput-logo.png" alt="CPUT Logo">
      <div class="header-title">CPUT Clinic - Appointment Confirmation</div>
    </div>

    <div class="profile-section" onclick="window.location.href='../profile.php'">
      <i class="fas fa-user-circle fa-2x"></i>
      <span>Welcome, Patient</span>
    </div>
  </header>

  <div class="container">
    <aside class="sidebar">
      <div class="nav-item" onclick="window.location.href='../dashboard.html'">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </div>
      <div class="nav-item" onclick="window.location.href='../update-profile.html'">
        <i class="fas fa-user-edit"></i>
        <span>Update Profile</span>
      </div>
      <div class="nav-item" onclick="window.location.href='appointment_form.php'">
        <i class="fas fa-calendar-check"></i>
        <span>Book Appointment</span>
      </div>
      <div class="nav-item" onclick="window.location.href='list_appointments.php'">
        <i class="fas fa-list"></i>
        <span>View Appointments</span>
      </div>
      <div class="nav-item" onclick="window.location.href='../availability/availability_form.html'">
        <i class="fas fa-clock"></i>
        <span>Manage Availability</span>
      </div>
      <div class="nav-item" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
    </aside>

    <main class="main-content">
      <div class="content-card">
        <?php if (isset($message)): ?>
          <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
          </div>
          
          <?php if ($message_type == 'success'): ?>
            <script>
              setTimeout(function() {
                window.location.href = 'list_appointments.php';
              }, 2000);
            </script>
            <p>Redirecting to appointments list...</p>
          <?php else: ?>
            <div style="margin-top: 20px;">
              <a href="appointment_form.php" class="btn-action">Try Again</a>
              <a href="list_appointments.php" class="btn-action">View Appointments</a>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="message error">
            No appointment data submitted. Please use the booking form.
          </div>
          <div style="margin-top: 20px;">
            <a href="appointment_form.php" class="btn-action">Book Appointment</a>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <img src="../images/clinic-logo.jpg" alt="Clinic Logo" class="footer-logo">
        <p><img src="../images/email-icon.png" class="icon" alt="Email Icon"> info@cput.ac.za</p>
        <div class="social-icons">
          <a href="#" target="_blank">
            <img src="../images/facebook-icon.png" class="icon" alt="Facebook Icon"> Facebook Page
          </a>
        </div>
      </div>

      <div class="footer-section">
        <h4>Clinic Contact Details</h4>
        <ul>
          <li><img src="../images/phone-icon.png" class="icon" alt="Phone Icon"> Bellville: +27 21 959 6403</li>
          <li><img src="../images/phone-icon.png" class="icon" alt="Phone Icon"> Cape Town (D6): +27 21 460 3405</li>
          <li><img src="../images/phone-icon.png" class="icon" alt="Phone Icon"> Mowbray: +27 21 680 1555</li>
          <li><img src="../images/phone-icon.png" class="icon" alt="Phone Icon"> Wellington: +27 21 864 5522</li>
        </ul>
      </div>

      <div class="footer-section">
        <h4>Group EZ2</h4>
        <ul>
          <li>Yvvonne Mthiyane – 222530723</li>
          <li>Anwill Jacobs – 219423202</li>
          <li>Judina Malefu Moleko – 221630597</li>
          <li>Nothile Cele – 230894356</li>
          <li>Njabulo Nicco Mathabela – 212061208</li>
          <li>Thabiso Kama – 218017421</li>
        </ul>
      </div>
    </div>
  </footer>

  <script>
    function logout() {
      if(confirm('Are you sure you want to logout?')) {
        window.location.href = '../login.html';
      }
    }
  </script>

</body>
</html>
<?php
$conn->close();
?>