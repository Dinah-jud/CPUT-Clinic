<?php
session_start();
include '../db.php';

// Ensure user is logged in and is admin/doctor
if (!isset($_SESSION['student'])) {
    header("Location: ../login.html");
    exit();
}

$user = $_SESSION['student'];
$user_id = $user['id'];
$full_name = $user['full_name'] ?? 'User';
$user_type = $user['user_type'] ?? 'patient';

// Only admin and doctors can manage availability
if ($user_type !== 'admin' && $user_type !== 'doctor') {
    header("Location: ../dashboard.html");
    exit();
}

// Fetch doctors from database
$doctors = [];
if ($user_type === 'admin') {
    // Admin can set availability for any doctor
    $sql = "SELECT id, full_name FROM students WHERE user_type = 'doctor'";
} else {
    // Doctors can only set their own availability
    $sql = "SELECT id, full_name FROM students WHERE id = $user_id AND user_type = 'doctor'";
}

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Availability - CPUT Clinic</title>
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

    .form-container {
      background: white;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      max-width: 600px;
      margin: 0 auto;
    }

    .form-container h2 {
      color: #003865;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: #003865;
      font-weight: bold;
    }

    .form-group input, .form-group select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }

    .btn-primary {
      background-color: #0072CE;
      color: white;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      width: 100%;
      margin-top: 1rem;
    }

    .btn-primary:hover {
      background-color: #005fa3;
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

    .access-note {
      background: #e8f4fc;
      border-left: 4px solid #0072CE;
      padding: 1rem;
      margin-bottom: 1.5rem;
      border-radius: 4px;
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
      <div class="header-title">CPUT Clinic - Manage Availability</div>
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
      
      <?php if ($user_type === 'admin' || $user_type === 'doctor'): ?>
      <div class="nav-item active">
        <i class="fas fa-clock"></i>
        <span>Manage Availability</span>
      </div>
      <div class="nav-item" onclick="window.location.href='list_availability.php'">
        <i class="fas fa-eye"></i>
        <span>View Availability</span>
      </div>
      <?php endif; ?>
      
      <?php if ($user_type === 'admin'): ?>
      <div class="nav-item" onclick="window.location.href='../appointments/list_appointments.php'">
        <i class="fas fa-list"></i>
        <span>View All Appointments</span>
      </div>
      <?php endif; ?>
      
      <div class="nav-item" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
    </aside>

    <main class="main-content">
      <div class="form-container">
        <h2>Add Doctor Availability</h2>
        
        <div class="access-note">
          <strong>Access Level:</strong> 
          <?php if ($user_type === 'admin'): ?>
            You can set availability for any doctor.
          <?php else: ?>
            You can only set your own availability.
          <?php endif; ?>
        </div>

        <form action="add_availability.php" method="POST">
          <div class="form-group">
            <label for="doctor_id">Doctor</label>
            <select id="doctor_id" name="doctor_id" required <?php echo ($user_type === 'doctor') ? 'disabled' : ''; ?>>
              <option value="">-- Select Doctor --</option>
              <?php foreach ($doctors as $doc): ?>
                <option value="<?php echo $doc['id']; ?>" <?php echo ($user_type === 'doctor' && $doc['id'] == $user_id) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($doc['full_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if ($user_type === 'doctor'): ?>
              <input type="hidden" name="doctor_id" value="<?php echo $user_id; ?>">
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="available_date">Date</label>
            <input type="date" id="available_date" name="available_date" required min="<?php echo date('Y-m-d'); ?>">
          </div>

          <div class="form-group">
            <label for="start_time">Start Time</label>
            <input type="time" id="start_time" name="start_time" required>
          </div>

          <div class="form-group">
            <label for="end_time">End Time</label>
            <input type="time" id="end_time" name="end_time" required>
          </div>

          <button type="submit" class="btn-primary">Add Availability</button>
        </form>

        <div style="margin-top: 20px; text-align: center;">
          <a href="list_availability.php" class="btn-action">View Availability Schedule</a>
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

    // Set minimum date to today
    document.getElementById('available_date').min = new Date().toISOString().split('T')[0];
  </script>

</body>
</html>
<?php
$conn->close();
?>
