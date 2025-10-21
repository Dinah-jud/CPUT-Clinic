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

// Build query based on user type
if ($user_type === 'patient') {
    // Patients see only their own appointments
    $sql = "SELECT a.*, d.full_name as doctor_name 
            FROM appointment a 
            LEFT JOIN students d ON a.doctor_id = d.id 
            WHERE a.patient_id = $user_id 
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $page_title = "My Appointments";
    $can_edit_delete = true;
    
} elseif ($user_type === 'doctor') {
    // Doctors see only appointments booked with them
    $sql = "SELECT a.*, p.full_name as patient_name 
            FROM appointment a 
            LEFT JOIN students p ON a.patient_id = p.id 
            WHERE a.doctor_id = $user_id 
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $page_title = "My Patient Appointments";
    $can_edit_delete = false;
    
} elseif ($user_type === 'admin') {
    // Admins see all appointments with both patient and doctor names
    $sql = "SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name 
            FROM appointment a 
            LEFT JOIN students p ON a.patient_id = p.id 
            LEFT JOIN students d ON a.doctor_id = d.id 
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $page_title = "All Appointments (Admin View)";
    $can_edit_delete = true;
    
} else {
    // Default fallback
    $sql = "SELECT * FROM appointment WHERE patient_id = $user_id";
    $page_title = "My Appointments";
    $can_edit_delete = true;
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Appointments - CPUT Clinic</title>
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

    .appointments-table {
      width: 100%;
      border-collapse: collapse;
      margin: 1rem 0;
    }

    .appointments-table th,
    .appointments-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .appointments-table th {
      background-color: #003865;
      color: white;
      font-weight: bold;
    }

    .appointments-table tr:hover {
      background-color: #f5f5f5;
    }

    .btn-action {
      background-color: #0072CE;
      color: white;
      padding: 5px 10px;
      border: none;
      border-radius: 3px;
      cursor: pointer;
      text-decoration: none;
      font-size: 0.875rem;
      margin-right: 5px;
      display: inline-block;
    }

    .btn-action:hover {
      background-color: #005fa3;
    }

    .btn-danger {
      background-color: #dc3545;
    }

    .btn-danger:hover {
      background-color: #c82333;
    }

    .status-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: bold;
    }

    .status-scheduled {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-completed {
      background-color: #d4edda;
      color: #155724;
    }

    .status-cancelled {
      background-color: #f8d7da;
      color: #721c24;
    }

    .no-appointments {
      text-align: center;
      padding: 2rem;
      color: #666;
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
      <div class="header-title">CPUT Clinic - Appointments</div>
    </div>

    <div class="profile-section" onclick="window.location.href='../profile.php'">
      <i class="fas fa-user-circle fa-2x"></i>
      <span>Welcome, <?php echo htmlspecialchars($full_name); ?></span>
      <span class="user-role-badge"><?php echo htmlspecialchars($user_type); ?></span>
    </div>
  </header>

  <div class="container">
    <aside class="sidebar">
      <div class="nav-item" onclick="window.location.href='../dashboard.html'">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </div>
      
      <?php if ($user_type === 'patient' || $user_type === 'admin'): ?>
      <div class="nav-item" onclick="window.location.href='appointment_form.php'">
        <i class="fas fa-calendar-check"></i>
        <span>Book Appointment</span>
      </div>
      <?php endif; ?>
      
      <div class="nav-item active" onclick="window.location.href='list_appointments.php'">
        <i class="fas fa-list"></i>
        <span>View Appointments</span>
      </div>
      
      <?php if ($user_type === 'admin' || $user_type === 'doctor'): ?>
      <div class="nav-item" onclick="window.location.href='../availability/availability_form.html'">
        <i class="fas fa-clock"></i>
        <span>Manage Availability</span>
      </div>
      <?php endif; ?>
      
      <div class="nav-item" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
    </aside>

    <main class="main-content">
      <div class="content-card">
        <h2><?php echo $page_title; ?></h2>
        
        <?php if ($result->num_rows > 0): ?>
        <table class="appointments-table">
          <thead>
            <tr>
              <th>ID</th>
              <?php if ($user_type === 'admin'): ?>
              <th>Patient</th>
              <th>Doctor</th>
              <?php elseif ($user_type === 'doctor'): ?>
              <th>Patient</th>
              <?php else: ?>
              <th>Doctor</th>
              <?php endif; ?>
              <th>Date</th>
              <th>Time</th>
              <th>Type</th>
              <th>Status</th>
              <?php if ($can_edit_delete): ?>
              <th>Actions</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['appointment_id']; ?></td>
              
              <?php if ($user_type === 'admin'): ?>
              <td><?php echo htmlspecialchars($row['patient_name'] ?? 'N/A'); ?> (ID: <?php echo $row['patient_id']; ?>)</td>
              <td><?php echo htmlspecialchars($row['doctor_name'] ?? 'N/A'); ?> (ID: <?php echo $row['doctor_id']; ?>)</td>
              <?php elseif ($user_type === 'doctor'): ?>
              <td><?php echo htmlspecialchars($row['patient_name'] ?? 'N/A'); ?></td>
              <?php else: ?>
              <td><?php echo htmlspecialchars($row['doctor_name'] ?? 'N/A'); ?></td>
              <?php endif; ?>
              
              <td><?php echo $row['appointment_date']; ?></td>
              <td><?php echo substr($row['appointment_time'], 0, 5); ?></td>
              <td><?php echo $row['appointment_type']; ?></td>
              <td>
                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                  <?php echo $row['status']; ?>
                </span>
              </td>
              
              <?php if ($can_edit_delete): ?>
              <td>
                <a href="edit_appointment.php?id=<?php echo $row['appointment_id']; ?>" class="btn-action">Edit</a>
                <a href="delete_appointment.php?id=<?php echo $row['appointment_id']; ?>" class="btn-action btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?')">Delete</a>
              </td>
              <?php endif; ?>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div class="no-appointments">
          <i class="fas fa-calendar-times fa-3x" style="color: #ccc; margin-bottom: 1rem;"></i>
          <h3>No Appointments Found</h3>
          <p>You don't have any appointments scheduled yet.</p>
          <?php if ($user_type === 'patient'): ?>
          <a href="appointment_form.php" class="btn-action" style="display: inline-block; padding: 10px 20px; margin-top: 1rem;">Book Your First Appointment</a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($user_type === 'patient' || $user_type === 'admin'): ?>
        <div style="margin-top: 20px; text-align: center;">
          <a href="appointment_form.php" class="btn-action" style="display: inline-block; padding: 10px 20px;">Book New Appointment</a>
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
