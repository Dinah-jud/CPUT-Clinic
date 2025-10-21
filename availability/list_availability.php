<?php
session_start();
include '../db.php';

// Ensure user is logged in
if (!isset($_SESSION['student'])) {
    header("Location: ../login.html");
    exit();
}

$user = $_SESSION['student'];
$user_id = $user['id'];
$full_name = $user['full_name'] ?? 'User';
$user_type = $user['user_type'] ?? 'patient';

// Build query based on user role
if ($user_type === 'admin') {
    // Admin sees all availability with doctor names
    $sql = "SELECT a.*, d.full_name as doctor_name 
            FROM availabilityschedule a 
            LEFT JOIN students d ON a.doctor_id = d.id 
            ORDER BY a.available_date DESC, a.start_time ASC";
    $page_title = "All Doctor Availability (Admin View)";
} elseif ($user_type === 'doctor') {
    // Doctors see only their own availability
    $sql = "SELECT a.*, d.full_name as doctor_name 
            FROM availabilityschedule a 
            LEFT JOIN students d ON a.doctor_id = d.id 
            WHERE a.doctor_id = $user_id 
            ORDER BY a.available_date DESC, a.start_time ASC";
    $page_title = "My Availability Schedule";
} else {
    // Patients see all doctor availability
    $sql = "SELECT a.*, d.full_name as doctor_name 
            FROM availabilityschedule a 
            LEFT JOIN students d ON a.doctor_id = d.id 
            WHERE a.available_date >= CURDATE() 
            ORDER BY a.available_date ASC, a.start_time ASC";
    $page_title = "Available Doctor Slots";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor Availability - CPUT Clinic</title>
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

    .user-role-badge {
      display: inline-block;
      background: #0072CE;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
      margin-left: 10px;
      text-transform: capitalize;
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

    .content-card {
      background: white;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .content-card h2 {
      color: #003865;
      margin-bottom: 1.5rem;
    }

    .info-note {
      background: #e8f4fc;
      border-left: 4px solid #0072CE;
      padding: 1rem;
      margin-bottom: 1.5rem;
      border-radius: 4px;
    }

    .availability-table {
      width: 100%;
      border-collapse: collapse;
      margin: 1rem 0;
    }

    .availability-table th,
    .availability-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .availability-table th {
      background-color: #003865;
      color: white;
      font-weight: bold;
    }

    .availability-table tr:hover {
      background-color: #f5f5f5;
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

    .no-availability {
      text-align: center;
      padding: 2rem;
      color: #666;
    }

    .date-badge {
      background: #28a745;
      color: white;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: bold;
    }

<<<<<<< Updated upstream
=======
    /* Back button styles */
    .back-button-container {
      background-color: transparent;
      padding: 10px 30px;
      display: flex;
      align-items: center;
    }

    .back-button {
      background-color: #0072CE;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      font-size: 15px;
      font-weight: bold;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      cursor: pointer;
      transition: background-color 0.3s ease;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .back-button:hover {
      background-color: #005fa3;
    }

>>>>>>> Stashed changes
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
      <div class="header-title">CPUT Clinic - Doctor Availability</div>
    </div>

    <div class="profile-section" onclick="window.location.href='../profile.php'">
      <i class="fas fa-user-circle fa-2x"></i>
      <span>Welcome, <?php echo htmlspecialchars($full_name); ?></span>
      <span class="user-role-badge"><?php echo htmlspecialchars($user_type); ?></span>
    </div>
  </header>

<<<<<<< Updated upstream
  <div class="container">
=======
  <div class="back-button-container">
    <a href="../patientDashboard.php" class="back-button">
      <i class="fas fa-arrow-left"></i> Back to Patient Dashboard
    </a>
  </div>

  <!-- <div class="container">
>>>>>>> Stashed changes
    <aside class="sidebar">
      <div class="nav-item" onclick="window.location.href='../dashboard.html'">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </div>
      
      <?php if ($user_type === 'patient'): ?>
      <div class="nav-item" onclick="window.location.href='../appointments/appointment_form.php'">
        <i class="fas fa-calendar-check"></i>
        <span>Book Appointment</span>
      </div>
      <?php endif; ?>
      
      <?php if ($user_type === 'patient' || $user_type === 'admin'): ?>
      <div class="nav-item" onclick="window.location.href='../appointments/list_appointments.php'">
        <i class="fas fa-list"></i>
        <span>View Appointments</span>
      </div>
      <?php endif; ?>
      
      <?php if ($user_type === 'admin' || $user_type === 'doctor'): ?>
      <div class="nav-item" onclick="window.location.href='availability_form.php'">
        <i class="fas fa-clock"></i>
        <span>Manage Availability</span>
      </div>
      <?php endif; ?>
      
      <div class="nav-item active" onclick="window.location.href='list_availability.php'">
        <i class="fas fa-eye"></i>
        <span>View Availability</span>
      </div>
      
      <div class="nav-item" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
<<<<<<< Updated upstream
    </aside>
=======
    </aside> -->
>>>>>>> Stashed changes

    <main class="main-content">
      <div class="content-card">
        <h2><?php echo $page_title; ?></h2>
        
        <?php if ($user_type === 'patient'): ?>
        <div class="info-note">
          <strong>📅 Available Slots:</strong> This shows all available time slots from doctors. 
          You can book appointments during these available times.
        </div>
        <?php elseif ($user_type === 'doctor'): ?>
        <div class="info-note">
          <strong>👨‍⚕️ Your Schedule:</strong> This shows your available time slots that patients can book.
        </div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
        <table class="availability-table">
          <thead>
            <tr>
              <th>Doctor</th>
              <th>Date</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Duration</th>
              <?php if ($user_type === 'admin'): ?>
              <th>Schedule ID</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): 
              $start = new DateTime($row['start_time']);
              $end = new DateTime($row['end_time']);
              $duration = $start->diff($end);
              $duration_str = $duration->h . 'h ' . $duration->i . 'm';
              
              $is_today = $row['available_date'] == date('Y-m-d');
              $is_future = $row['available_date'] > date('Y-m-d');
            ?>
            <tr>
              <td>
                <strong><?php echo htmlspecialchars($row['doctor_name'] ?? 'Doctor #' . $row['doctor_id']); ?></strong>
              </td>
              <td>
                <?php echo $row['available_date']; ?>
                <?php if ($is_today): ?>
                  <span class="date-badge">Today</span>
                <?php elseif ($is_future): ?>
                  <span class="date-badge">Upcoming</span>
                <?php endif; ?>
              </td>
              <td><?php echo substr($row['start_time'], 0, 5); ?></td>
              <td><?php echo substr($row['end_time'], 0, 5); ?></td>
              <td><?php echo $duration_str; ?></td>
              <?php if ($user_type === 'admin'): ?>
              <td><?php echo $row['schedule_id']; ?></td>
              <?php endif; ?>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div class="no-availability">
          <i class="fas fa-calendar-times fa-3x" style="color: #ccc; margin-bottom: 1rem;"></i>
          <h3>No Availability Found</h3>
          <p>
            <?php if ($user_type === 'patient'): ?>
            There are currently no available doctor slots. Please check back later.
            <?php elseif ($user_type === 'doctor'): ?>
            You haven't set any availability slots yet.
            <?php else: ?>
            No availability schedules have been created yet.
            <?php endif; ?>
          </p>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: center;">
          <?php if ($user_type === 'admin' || $user_type === 'doctor'): ?>
          <a href="availability_form.php" class="btn-action">Add New Availability</a>
          <?php endif; ?>
          
          <?php if ($user_type === 'patient'): ?>
          <a href="../appointments/appointment_form.php" class="btn-action">Book Appointment</a>
          <?php endif; ?>
          
          <a href="../appointments/list_appointments.php" class="btn-action">View Appointments</a>
        </div>
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
