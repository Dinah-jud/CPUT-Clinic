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

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    header("Location: list_appointments.php");
    exit();
}

$appointment_id = (int)$_GET['id'];

// Fetch appointment details - FIXED: using appointment_id instead of id
$appointment_sql = "SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name 
                    FROM appointment a 
                    LEFT JOIN students p ON a.patient_id = p.id 
                    LEFT JOIN students d ON a.doctor_id = d.id 
                    WHERE a.appointment_id = ?"; // CHANGED: a.id to a.appointment_id
$stmt = $conn->prepare($appointment_sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment_result = $stmt->get_result();

if ($appointment_result->num_rows === 0) {
    header("Location: list_appointments.php");
    exit();
}

$appointment = $appointment_result->fetch_assoc();

// Check permissions
if ($user_type === 'doctor' && $appointment['doctor_id'] != $user_id) {
    header("Location: list_appointments.php");
    exit();
}

if ($user_type === 'patient' && $appointment['patient_id'] != $user_id) {
    header("Location: list_appointments.php");
    exit();
}

// Fetch doctors for dropdown
$doctors = [];
$doctor_sql = "SELECT id, full_name FROM students WHERE user_type = 'doctor' ORDER BY full_name";
$doctor_result = $conn->query($doctor_sql);
if ($doctor_result && $doctor_result->num_rows > 0) {
    while ($row = $doctor_result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Handle form submission
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = (int)$_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $appointment_type = $_POST['appointment_type'];
    $status = $_POST['status'];
    
    // Check if the new time slot is available
    $check_sql = "SELECT * FROM appointment 
                  WHERE doctor_id = ? 
                  AND appointment_date = ? 
                  AND appointment_time = ? 
                  AND appointment_id != ?  -- CHANGED: id to appointment_id
                  AND status != 'Cancelled'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("issi", $doctor_id, $appointment_date, $appointment_time, $appointment_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $message = "Error: This time slot is already booked! Please choose a different time.";
        $message_type = "error";
    } else {
        // Update appointment - FIXED: using appointment_id in WHERE clause
        $update_sql = "UPDATE appointment 
                       SET doctor_id = ?, appointment_date = ?, appointment_time = ?, 
                           appointment_type = ?, status = ? 
                       WHERE appointment_id = ?"; // CHANGED: id to appointment_id
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("issssi", $doctor_id, $appointment_date, $appointment_time, 
                                $appointment_type, $status, $appointment_id);
        
        if ($update_stmt->execute()) {
            $message = "Appointment updated successfully!";
            $message_type = "success";
            // Refresh appointment data
            $appointment['doctor_id'] = $doctor_id;
            $appointment['appointment_date'] = $appointment_date;
            $appointment['appointment_time'] = $appointment_time;
            $appointment['appointment_type'] = $appointment_type;
            $appointment['status'] = $status;
        } else {
            $message = "Error updating appointment: " . $conn->error;
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Appointment - CPUT Clinic</title>
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

    .info-note {
      background: #e8f4fc;
      border-left: 4px solid #0072CE;
      padding: 1rem;
      margin-bottom: 1.5rem;
      border-radius: 4px;
      font-size: 0.9rem;
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

    .btn-secondary {
      background-color: #6c757d;
      color: white;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      text-decoration: none;
      display: inline-block;
      text-align: center;
      margin-top: 0.5rem;
      width: 100%;
    }

    .btn-secondary:hover {
      background-color: #545b62;
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

    .appointment-info {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 5px;
      margin-bottom: 1rem;
    }

    .appointment-info p {
      margin: 0.5rem 0;
    }
  </style>
</head>
<body>

  <header class="header">
    <div class="logo-container" style="display:flex;align-items:center;">
      <img src="../images/cput-logo.png" alt="CPUT Logo">
      <div class="header-title">CPUT Clinic - Edit Appointment</div>
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
      
      <?php if ($user_type === 'patient'): ?>
      <div class="nav-item" onclick="window.location.href='appointment_form.php'">
        <i class="fas fa-calendar-check"></i>
        <span>Book Appointment</span>
      </div>
      <?php endif; ?>
      
      <div class="nav-item active" onclick="window.location.href='list_appointments.php'">
        <i class="fas fa-list"></i>
        <span>View Appointments</span>
      </div>
      
      <?php if ($user_type === 'patient'): ?>
      <div class="nav-item" onclick="window.location.href='../availability/list_availability.php'">
        <i class="fas fa-eye"></i>
        <span>View Available Slots</span>
      </div>
      <?php endif; ?>
      
      <div class="nav-item" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
    </aside>

    <main class="main-content">
      <div class="form-container">
        <h2>Edit Appointment</h2>
        
        <div class="info-note">
          <strong>Editing Appointment #<?php echo $appointment_id; ?></strong><br>
          Patient: <?php echo htmlspecialchars($appointment['patient_name']); ?>
        </div>

        <?php if ($message): ?>
          <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
          </div>
        <?php endif; ?>

        <form action="edit_appointment.php?id=<?php echo $appointment_id; ?>" method="POST">
          <div class="form-group">
            <label for="doctor_id">Doctor</label>
            <select id="doctor_id" name="doctor_id" required>
              <option value="">-- Select Doctor --</option>
              <?php foreach ($doctors as $doc): ?>
                <option value="<?php echo $doc['id']; ?>" 
                  <?php echo ($appointment['doctor_id'] == $doc['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($doc['full_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="appointment_date">Appointment Date</label>
            <input type="date" id="appointment_date" name="appointment_date" required 
                   min="<?php echo date('Y-m-d'); ?>" 
                   value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>">
          </div>

          <div class="form-group">
            <label for="appointment_time">Appointment Time</label>
            <input type="time" id="appointment_time" name="appointment_time" required
                   value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>">
          </div>

          <div class="form-group">
            <label for="appointment_type">Appointment Type</label>
            <select id="appointment_type" name="appointment_type" required>
              <option value="General" <?php echo ($appointment['appointment_type'] == 'General') ? 'selected' : ''; ?>>General Consultation</option>
              <option value="Follow-up" <?php echo ($appointment['appointment_type'] == 'Follow-up') ? 'selected' : ''; ?>>Follow-up Visit</option>
              <option value="Emergency" <?php echo ($appointment['appointment_type'] == 'Emergency') ? 'selected' : ''; ?>>Emergency</option>
            </select>
          </div>

          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="Scheduled" <?php echo ($appointment['status'] == 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
              <option value="Completed" <?php echo ($appointment['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
              <option value="Cancelled" <?php echo ($appointment['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
          </div>

          <button type="submit" class="btn-primary">Update Appointment</button>
        </form>

        <a href="list_appointments.php" class="btn-secondary">Back to Appointments</a>
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
    document.getElementById('appointment_date').min = new Date().toISOString().split('T')[0];
  </script>

</body>
</html>
<?php
$conn->close();
?>