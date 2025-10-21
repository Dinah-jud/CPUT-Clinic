<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { font-family: Arial, sans-serif; background-color: #f5f7fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
.header { background-color: #003865; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
.sidebar { width: 250px; background-color: #fff; box-shadow: 2px 0 5px rgba(0,0,0,0.1); padding: 1rem 0; height: calc(100vh - 70px); position: fixed; }
.nav-item { padding: 1rem 2rem; display: flex; align-items: center; gap: 10px; cursor: pointer; color: #003865; font-weight: bold; transition: background 0.3s; }
.nav-item:hover, .nav-item.active { background-color: #e8f4fc; border-left: 4px solid #0072CE; }
.nav-item i { color: #0072CE; width: 20px; text-align: center; }
.main-content { margin-left: 250px; padding: 2rem; flex:1; }
table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius:10px; overflow:hidden; }
th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; }
th { background-color: #f2f6ff; color: #003399; font-weight: bold; }
.status { padding: 5px 10px; border-radius: 20px; font-weight: bold; color: #fff; }
.status.confirmed { background-color: #28a745; }
.status.pending { background-color: #ffc107; color:#000; }
.status.cancelled { background-color: #dc3545; }
footer { background-color: #012773; color: white; padding: 30px 0; margin-top: auto; }
</style>
</head>
<body>

<header class="header">
  <div>CPUT Clinic - Admin</div>
  <div onclick="logout()" style="cursor:pointer;"><i class="fas fa-sign-out-alt"></i> Logout</div>
</header>

<aside class="sidebar">
  <div class="nav-item" onclick="window.location.href='admin-dashboard.php'"><i class="fas fa-tachometer-alt"></i> Dashboard</div>
  <div class="nav-item" onclick="window.location.href='user-management.php'"><i class="fas fa-users-cog"></i> User Management</div>
  <div class="nav-item" onclick="window.location.href='shift-schedule.php'"><i class="fas fa-calendar-check"></i> Shift Schedule</div>
  <div class="nav-item active"><i class="fas fa-calendar-check"></i> Appointments</div>
  <div class="nav-item" onclick="window.location.href='reporting.php'"><i class="fas fa-prescription-bottle-alt"></i> Report and Analytics</div>
  <div class="nav-item" onclick="window.location.href='update-profile.php'"><i class="fas fa-user-edit"></i> Update Profile</div>
</aside>

<main class="main-content">
  <h2>Manage Appointments</h2>
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Patient</th>
        <th>Doctor</th>
        <th>Department</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
        // Example static data (you can replace this with database data later)
        $appointments = [
          ["date" => "15 Oct 2025", "patient" => "John Peterson", "doctor" => "Dr. NN Dlamini", "department" => "General Medicine", "status" => "confirmed"],
          ["date" => "22 Oct 2025", "patient" => "Emma Madonsela", "doctor" => "Dr. Mhlawuli", "department" => "Dental", "status" => "pending"],
        ];

        foreach ($appointments as $appt) {
          $statusClass = $appt['status'];
          $statusText = ucfirst($appt['status']);
          echo "<tr>
                  <td>{$appt['date']}</td>
                  <td>{$appt['patient']}</td>
                  <td>{$appt['doctor']}</td>
                  <td>{$appt['department']}</td>
                  <td><span class='status {$statusClass}'>{$statusText}</span></td>
                </tr>";
        }
      ?>
    </tbody>
  </table>
</main>

<footer>
  <p style="text-align:center;">CPUT Clinic &copy; 2025</p>
</footer>

<script>
function logout() {
  alert('Successfully logged out!');
  window.location.href='login.php';
}
</script>

</body>
</html>
