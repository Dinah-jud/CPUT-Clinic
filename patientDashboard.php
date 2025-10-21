<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['student'])) {
  header("Location: login.html");
  exit();
}

// Store session data in variable
$student = $_SESSION['student'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Dashboard</title>
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

    /* Header */
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
      transition: opacity 0.3s;
    }

    .profile-section:hover {
      opacity: 0.8;
    }

    /* Layout */
    .container {
      display: flex;
      flex: 1;
      min-height: calc(100vh - 140px);
    }

    /* Sidebar */
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

    /* Main content area */
    .main-content {
      flex: 1;
      padding: 2rem;
    }

    .main-content h2 {
      font-size: 1.8rem;
      margin-bottom: 1rem;
      color: #003865;
    }

    .welcome-card {
      background: white;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 600px;
      margin: 0 auto;
    }

    .welcome-card img {
      width: 100px;
      margin-bottom: 1rem;
    }

    .welcome-card p {
      font-size: 1rem;
      color: #555;
    }

    /* Footer */
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

    .appointments-card {
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  padding: 20px;
  margin-top: 20px;
  width: 90%;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}

.appointments-card h3 {
  color: #003399;
  margin-bottom: 10px;
}

.appointments-card p {
  color: #555;
  margin-bottom: 15px;
}

.appointments-table {
  width: 100%;
  border-collapse: collapse;
}

.appointments-table th,
.appointments-table td {
  text-align: left;
  padding: 12px 15px;
  border-bottom: 1px solid #ddd;
}

.appointments-table th {
  background-color: #f2f6ff;
  color: #003399;
  font-weight: bold;
}

.status {
  padding: 5px 10px;
  border-radius: 20px;
  font-weight: bold;
  color: #fff;
}

.status.confirmed {
  background-color: #28a745;
}

.status.pending {
  background-color: #ffc107;
}
  </style>
</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="logo-container" style="display:flex;align-items:center;">
      <img src="images/cput-logo.png" alt="CPUT Logo">
      <div class="header-title">CPUT Clinic - Patient Dashboard</div>
    </div>

    <!-- Profile Section (Clickable) -->
    <div class="profile-section" onclick="window.location.href='profile.php'">
      <i class="fas fa-user-circle fa-2x"></i>
      <span>Welcome, <?php echo htmlspecialchars($student['full_name']); ?></span>

    </div>
  </header>

  <!-- Main Layout -->
  <div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="nav-item active" onclick="window.location.href='update-profile.html'">
        <i class="fas fa-user-edit"></i>
        <span>Update Profile</span>
      </div>
      <div class="nav-item" onclick="window.location.href='book-appointment.html'">
        <i class="fas fa-calendar-check"></i>
        <span>Book Appointment</span>
      </div>
    
      <div class="nav-item" onclick="window.location.href='prescriptionPatientView.php'">
        <i class="fas fa-prescription-bottle-alt"></i>
        <span>Prescription</span>
      </div>
     
      <div class="nav-item" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
      <div class="welcome-card">
        <img src="images/clinic-logo.jpg" alt="Clinic Logo">
        <h2>Welcome to the CPUT Clinic Portal</h2>
        <p>Here you can manage your profile, book appointments, view prescriptions, and access reports easily and securely.</p>
      </div>

      <!-- Upcoming Appointments Table -->
<div class="appointments-card">
  <h3>Upcoming Appointments</h3>
  <p>Here are your next scheduled visits.</p>
  <table class="appointments-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Doctor</th>
        <th>Department</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>15 Oct 2025</td>
        <td>Dr. NN Dlamini</td>
        <td>General Medicine</td>
        <td><span class="status confirmed">Confirmed</span></td>
      </tr>
      <tr>
        <td>22 Oct 2025</td>
        <td>Dr. Mhlawuli</td>
        <td>Dental</td>
        <td><span class="status pending">Pending</span></td>
      </tr>
    </tbody>
  </table>
</div>
    </main>

  </div>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <img src="images/clinic-logo.jpg" alt="Clinic Logo" class="footer-logo">
        <p><img src="images/email-icon.png" class="icon" alt="Email Icon"> info@cput.ac.za</p>
        <div class="social-icons">
          <a href="#" target="_blank">
            <img src="images/facebook-icon.png" class="icon" alt="Facebook Icon"> Facebook Page
          </a>
        </div>
      </div>

      <div class="footer-section">
        <h4>Clinic Contact Details</h4>
        <ul>
          <li><img src="images/phone-icon.png" class="icon" alt="Phone Icon"> Bellville: +27 21 959 6403</li>
          <li><img src="images/phone-icon.png" class="icon" alt="Phone Icon"> Cape Town (D6): +27 21 460 3405</li>
          <li><img src="images/phone-icon.png" class="icon" alt="Phone Icon"> Mowbray: +27 21 680 1555</li>
          <li><img src="images/phone-icon.png" class="icon" alt="Phone Icon"> Wellington: +27 21 864 5522</li>
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
      alert('Successfully logged out!');
      window.location.href = 'login.html';
    }
  </script>

</body>
</html>
