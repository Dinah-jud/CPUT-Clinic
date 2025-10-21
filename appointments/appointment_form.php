<?php
session_start();
include '../db.php';

// Ensure user is logged in
if (!isset($_SESSION['student'])) {
  header("Location: ../login.html");
  exit();
}

$patient = $_SESSION['student'];
$patient_id = $patient['id'];
$full_name = $patient['full_name'] ?? 'Patient';
$user_type = $patient['user_type'] ?? 'patient';

// Only patients can book appointments
if ($user_type !== 'patient') {
  header("Location: ../dashboard.html");
  exit();
}

// Fetch doctors from DB (user_type = 'doctor')
$doctors = [];
$sql = "SELECT id, full_name FROM students WHERE user_type = 'doctor'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
  }
}

// Get available slots for selected doctor (if doctor is selected)
$available_slots = [];
$selected_doctor = $_GET['doctor_id'] ?? ''; // Changed to GET
$selected_date = $_GET['appointment_date'] ?? ''; // Changed to GET

if ($selected_doctor && $selected_date) {
  // Get doctor's availability for selected date
  $availability_sql = "SELECT start_time, end_time 
                       FROM availabilityschedule 
                       WHERE doctor_id = ? AND available_date = ?";
  $stmt = $conn->prepare($availability_sql);
  $stmt->bind_param("is", $selected_doctor, $selected_date);
  $stmt->execute();
  $availability_result = $stmt->get_result();
  
  // Get already booked appointments for selected doctor and date
  $booked_sql = "SELECT appointment_time 
                 FROM appointment 
                 WHERE doctor_id = ? AND appointment_date = ? AND status != 'Cancelled'";
  $stmt2 = $conn->prepare($booked_sql);
  $stmt2->bind_param("is", $selected_doctor, $selected_date);
  $stmt2->execute();
  $booked_result = $stmt2->get_result();
  
  $booked_times = [];
  while ($booked_row = $booked_result->fetch_assoc()) {
    $booked_times[] = substr($booked_row['appointment_time'], 0, 5); // Get HH:MM format
  }
  
  // Generate available time slots (30-minute intervals)
  while ($avail_row = $availability_result->fetch_assoc()) {
    $start = new DateTime($avail_row['start_time']);
    $end = new DateTime($avail_row['end_time']);
    
    $current = clone $start;
    while ($current < $end) {
      $time_slot = $current->format('H:i');
      $next_slot = (clone $current)->modify('+30 minutes');
      
      // Check if this time slot is available (not booked)
      if (!in_array($time_slot, $booked_times) && $next_slot <= $end) {
        $available_slots[] = [
          'time' => $time_slot,
          'display' => $current->format('g:i A')
        ];
      }
      
      $current->modify('+30 minutes');
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Appointment - CPUT Clinic</title>
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
      transition: opacity 0.3s;
    }

    .profile-section:hover {
      opacity: 0.8;
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

    .btn-primary:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.9rem;
      margin-left: 0.5rem;
    }

    .btn-secondary:hover {
      background-color: #545b62;
    }

    .time-slots {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
      gap: 0.5rem;
      margin-top: 1rem;
    }

    .time-slot {
      padding: 0.75rem;
      border: 2px solid #0072CE;
      border-radius: 5px;
      background: white;
      color: #0072CE;
      cursor: pointer;
      text-align: center;
      font-weight: bold;
      transition: all 0.3s;
    }

    .time-slot:hover {
      background: #0072CE;
      color: white;
    }

    .time-slot.selected {
      background: #0072CE;
      color: white;
    }

    .time-slot.unavailable {
      border-color: #ccc;
      background: #f8f9fa;
      color: #999;
      cursor: not-allowed;
    }

    .no-slots {
      text-align: center;
      padding: 1rem;
      color: #666;
      background: #f8f9fa;
      border-radius: 5px;
      margin-top: 1rem;
    }

    .loading {
      text-align: center;
      padding: 1rem;
      color: #0072CE;
    }

    .time-error {
      color: #dc3545;
      font-size: 0.9rem;
      margin-top: 0.5rem;
      display: none;
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

  <!-- Header -->
  <header class="header">
    <div class="logo-container" style="display:flex;align-items:center;">
      <img src="../images/cput-logo.png" alt="CPUT Logo">
      <div class="header-title">CPUT Clinic - Book Appointment</div>
    </div>

    <!-- Profile Section -->
    <div class="profile-section" onclick="window.location.href='../profile.php'">
      <i class="fas fa-user-circle fa-2x"></i>
      <span>Welcome, <?php echo htmlspecialchars($full_name); ?></span>
      <span class="user-role-badge"><?php echo htmlspecialchars($user_type); ?></span>
    </div>
  </header>

  <!-- Main Layout -->
  <div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="nav-item" onclick="window.location.href='../dashboard.html'">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </div>
      <div class="nav-item active" onclick="window.location.href='appointment_form.php'">
        <i class="fas fa-calendar-check"></i>
        <span>Book Appointment</span>
      </div>
      <div class="nav-item" onclick="window.location.href='list_appointments.php'">
        <i class="fas fa-list"></i>
        <span>View Appointments</span>
      </div>
      <div class="nav-item" onclick="window.location.href='../availability/list_availability.php'">
        <i class="fas fa-eye"></i>
        <span>View Available Slots</span>
      </div>
      <div class="nav-item" onclick="logout()">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
      <div class="form-container">
        <h2>Book an Appointment</h2>
        
        <div class="info-note">
          <strong>💡 Smart Booking:</strong> Select a doctor and date to see available time slots. 
          Only available slots that haven't been booked yet will be shown.
        </div>

        <form action="add_appointment.php" method="POST" id="appointmentForm" novalidate>
          <!-- patient hidden from user and taken from session -->
          <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>">

          <div class="form-group">
            <label for="doctor_id">Select Doctor</label>
            <select id="doctor_id" name="doctor_id" required>
              <option value="">-- Select Doctor --</option>
              <?php foreach ($doctors as $doc): ?>
                <option value="<?php echo (int)$doc['id']; ?>" <?php echo ($selected_doctor == $doc['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($doc['full_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="appointment_date">Appointment Date</label>
            <input type="date" id="appointment_date" name="appointment_date" required 
                   min="<?php echo date('Y-m-d'); ?>" 
                   value="<?php echo htmlspecialchars($selected_date); ?>">
          </div>

          <div class="form-group">
            <button type="button" id="checkAvailability" class="btn-secondary">Check Available Slots</button>
          </div>

          <?php if ($selected_doctor && $selected_date): ?>
          <div class="form-group">
            <label>Available Time Slots</label>
            <?php if (!empty($available_slots)): ?>
              <div class="time-slots" id="timeSlots">
                <?php foreach ($available_slots as $slot): ?>
                  <div class="time-slot" data-time="<?php echo $slot['time']; ?>">
                    <?php echo $slot['display']; ?>
                  </div>
                <?php endforeach; ?>
              </div>
              <input type="hidden" id="selected_time" name="appointment_time" value="" required>
              <div id="time-error" class="time-error">
                Please select a time slot before booking.
              </div>
            <?php else: ?>
              <div class="no-slots">
                <i class="fas fa-calendar-times"></i>
                <p>No available time slots for this date.</p>
                <p>Please select a different date or check <a href="../availability/list_availability.php">available slots</a>.</p>
              </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <div class="form-group">
            <label for="appointment_type">Appointment Type</label>
            <select id="appointment_type" name="appointment_type" required>
              <option value="General">General Consultation</option>
              <option value="Follow-up">Follow-up Visit</option>
              <option value="Emergency">Emergency</option>
            </select>
          </div>

          <button type="submit" class="btn-primary" id="submitBtn" 
                  <?php echo (empty($available_slots) && $selected_doctor && $selected_date) ? 'disabled' : ''; ?>>
            Book Appointment
          </button>
        </form>
      </div>
    </main>

  </div>

  <!-- Footer -->
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

    document.addEventListener('DOMContentLoaded', function() {
      const doctorSelect = document.getElementById('doctor_id');
      const dateInput = document.getElementById('appointment_date');
      const checkAvailabilityBtn = document.getElementById('checkAvailability');
      const timeSlots = document.getElementById('timeSlots');
      const selectedTimeInput = document.getElementById('selected_time');
      const submitBtn = document.getElementById('submitBtn');
      const timeError = document.getElementById('time-error');
      
      // Set minimum date to today
      if (dateInput) {
        dateInput.min = new Date().toISOString().split('T')[0];
      }

      // Check availability button
      if (checkAvailabilityBtn) {
        checkAvailabilityBtn.addEventListener('click', function() {
          const doctorId = doctorSelect.value;
          const appointmentDate = dateInput.value;
          
          if (!doctorId || !appointmentDate) {
            alert('Please select both a doctor and a date.');
            return;
          }
          
          // Redirect to same page with GET parameters
          window.location.href = `appointment_form.php?doctor_id=${doctorId}&appointment_date=${appointmentDate}`;
        });
      }

      // Time slot selection
      if (timeSlots) {
        timeSlots.addEventListener('click', function(e) {
          if (e.target.classList.contains('time-slot')) {
            // Remove selected class from all slots
            document.querySelectorAll('.time-slot').forEach(slot => {
              slot.classList.remove('selected');
            });
            
            // Add selected class to clicked slot
            e.target.classList.add('selected');
            
            // Set the hidden input value
            selectedTimeInput.value = e.target.dataset.time;
            
            // Hide error message
            if (timeError) timeError.style.display = 'none';
            
            // Enable submit button
            if (submitBtn) {
              submitBtn.disabled = false;
            }
          }
        });
      }

      // Form validation
      const appointmentForm = document.getElementById('appointmentForm');
      if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
          const selectedTime = document.getElementById('selected_time');
          if (selectedTime && !selectedTime.value) {
            e.preventDefault();
            if (timeError) timeError.style.display = 'block';
            // Scroll to error
            timeError.scrollIntoView({ behavior: 'smooth' });
            return false;
          }
        });
      }
    });
  </script>

</body>
</html>
<?php
$conn->close();
?>