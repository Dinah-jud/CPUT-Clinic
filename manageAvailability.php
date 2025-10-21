<?php
// shiftSchedule.php
// Example PHP file to render shift schedule dynamically

// Database connection (adjust credentials)
$host = 'localhost';
$db   = 'clinic_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch doctors
$stmt = $pdo->query("SELECT id, name FROM doctors");
$doctors = $stmt->fetchAll();

// Fetch shifts
$stmt = $pdo->query("SELECT * FROM shifts ORDER BY date ASC, start_time ASC");
$shifts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shift Schedule | Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { box-sizing: border-box; margin:0; padding:0; }
    body { font-family: Arial, sans-serif; background-color: #f5f7fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
    .header { background-color: #003865; color: white; padding:1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
    .header img { width: 60px; margin-right: 15px; }
    .header-title { font-size:1.4rem; font-weight:bold; }
    .profile-section { display:flex; align-items:center; gap:10px; cursor:pointer; }
    .container { display:flex; flex:1; min-height: calc(100vh - 140px); }
    .sidebar { width:250px; background-color:#fff; box-shadow:2px 0 5px rgba(0,0,0,0.1); padding:1rem 0; }
    .nav-item { padding:1rem 2rem; display:flex; align-items:center; gap:10px; cursor:pointer; color:#003865; font-weight:bold; }
    .nav-item:hover, .nav-item.active { background-color:#e8f4fc; border-left:4px solid #0072CE; }
    .nav-item i { color:#0072CE; width:20px; text-align:center; }
    .main-content { flex:1; padding:2rem; }
    .main-content h2 { font-size:1.8rem; margin-bottom:1.5rem; color:#003865; }
    .actions { display:flex; justify-content:flex-end; margin-bottom:1rem; gap:10px; flex-wrap:wrap; }
    .actions button { padding:0.5rem 1rem; border-radius:5px; border:none; font-size:1rem; cursor:pointer; background-color:#0072CE; color:white; }
    .actions button:hover { background-color:#005fa3; }
    table { width:100%; border-collapse: collapse; background:white; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-radius:10px; overflow:hidden; }
    th, td { text-align:left; padding:1rem; border-bottom:1px solid #eee; }
    th { background-color:#0072CE; color:white; }
    tr:hover { background-color:#f1f9ff; }
    .action-icons i { margin-right:10px; cursor:pointer; color:#0072CE; }
    .action-icons i:hover { color:#005fa3; }
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; }
    .modal-content { background:white; padding:2rem; border-radius:10px; width:400px; position:relative; }
    .modal-content h3 { margin-bottom:1rem; color:#003865; }
    .modal-content label { display:block; margin-top:10px; font-weight:bold; }
    .modal-content input, .modal-content select { width:100%; padding:0.5rem; border:1px solid #ccc; border-radius:5px; margin-top:5px; }
    .modal-buttons { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
    .close-btn { position:absolute; top:10px; right:15px; font-size:1.3rem; cursor:pointer; color:#333; }
    footer { background-color:#012773; color:white; padding:30px 0; margin-top:auto; }
    .footer-content { display:flex; justify-content:space-around; flex-wrap:wrap; max-width:1200px; margin:0 auto; padding:0 20px; }
  </style>
</head>
<body>

<header class="header">
  <div class="logo-container">
    <img src="images/cput-logo.png" alt="CPUT Logo">
    <div class="header-title">CPUT Clinic - Admin Dashboard</div>
  </div>
  <div class="profile-section">
    <i class="fas fa-user-circle fa-2x"></i>
    <span>Admin Name</span>
    <i class="fas fa-sign-out-alt" style="margin-left:10px; cursor:pointer;" onclick="alert('Logging out...')"></i>
  </div>
</header>

<div class="container">
  <aside class="sidebar">
  <div class="nav-item"><a href="adminDashboard.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
  <div class="nav-item"><a href="userManagement.html"><i class="fas fa-users-cog"></i> User Management</a></div>
  <div class="nav-item active"><a href="manageAvailability.html"><i class="fas fa-calendar-check"></i> Manage Availability</a></div>
  <div class="nav-item"><a href="update-profile.html"><i class="fas fa-user-edit"></i> Update Profile</a></div>
  </aside>

  <main class="main-content">
    <h2>Shift Schedule</h2>
    <div class="actions">
      <button onclick="openShiftModal()"><i class="fas fa-plus"></i> Add Shift</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Doctor</th>
          <th>Date</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Availability</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($shifts as $shift): ?>
        <tr data-id="<?= htmlspecialchars($shift['id']) ?>">
          <td><?= htmlspecialchars($shift['id']) ?></td>
          <td><?= htmlspecialchars($shift['doctor_name']) ?></td>
          <td><?= htmlspecialchars($shift['date']) ?></td>
          <td><?= htmlspecialchars($shift['start_time']) ?></td>
          <td><?= htmlspecialchars($shift['end_time']) ?></td>
          <td><?= htmlspecialchars($shift['availability']) ?></td>
          <td class="action-icons">
            <a href="edit_shift.php?id=<?= $shift['id'] ?>"><i class="fas fa-edit"></i></a>
            <a href="delete_shift.php?id=<?= $shift['id'] ?>" onclick="return confirm('Delete this shift?')"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>

<footer>
  <div class="footer-content">
    <div class="footer-section">
      <p>© 2025 CPUT Clinic Dashboard</p>
    </div>
  </div>
</footer>

</body>
</html>
