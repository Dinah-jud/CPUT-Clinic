<?php
// Start session to store user data temporarily
session_start();

// Initialize users if not already set
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        'patients' => [],
        'doctors'  => [],
        'admins'   => [],
    ];
}

// Handle form submission for Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $phone = preg_replace('/\D/', '', $_POST['phone']); // digits only

    // Validation
    $errors = [];
    if (!$name) $errors[] = "Name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Phone must be 10 digits";

    if (empty($errors)) {
        if ($id) {
            // Edit user
            foreach ($_SESSION['users'][$role] as &$user) {
                if ($user['id'] == $id) {
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                }
            }
        } else {
            // Add new user
            $newId = strtoupper(substr($role,0,1)) . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT);
            $_SESSION['users'][$role][] = [
                'id' => $newId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
            ];
        }
    }
}

// Handle deletion via GET
if (isset($_GET['delete']) && isset($_GET['role'])) {
    $role = $_GET['role'];
    $id = $_GET['delete'];
    $_SESSION['users'][$role] = array_filter($_SESSION['users'][$role], function($u) use ($id) {
        return $u['id'] != $id;
    });
    header("Location: user-management.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management | Admin Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Include your CSS from HTML version here */
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
      <div class="nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</div>
      <div class="nav-item active"><i class="fas fa-users-cog"></i> User Management</div>
      <div class="nav-item"><i class="fas fa-calendar-check"></i> Shift Schedule</div>
      <div class="nav-item"><i class="fas fa-calendar-check"></i> Appointments</div>
      <div class="nav-item"><i class="fas fa-prescription-bottle-alt"></i> Report and Analytics</div>
      <div class="nav-item"><i class="fas fa-user-edit"></i> Update Profile</div>
  </aside>

  <main class="main-content">
    <h2>User Management</h2>

    <div class="tabs">
      <button class="tab-btn active" onclick="switchTab('patients')">Patients</button>
      <button class="tab-btn" onclick="switchTab('doctors')">Doctors</button>
      <button class="tab-btn" onclick="switchTab('admins')">Admins</button>
    </div>

    <!-- Loop tables for each role -->
    <?php foreach(['patients','doctors','admins'] as $role): ?>
    <section id="<?= $role ?>" class="tab-content" style="<?= $role=='patients'?'':'display:none;' ?>">
      <div class="actions">
        <button onclick="openModal('<?= $role ?>')"><i class="fas fa-plus"></i> Add <?= ucfirst($role) ?></button>
      </div>
      <table>
        <thead>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach($_SESSION['users'][$role] as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['phone']) ?></td>
            <td class="action-icons">
              <i class="fas fa-edit" onclick="openModal('<?= $role ?>','<?= $user['id'] ?>')"></i>
              <a href="?delete=<?= $user['id'] ?>&role=<?= $role ?>" onclick="return confirm('Delete <?= $user['id'] ?>?')">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
    <?php endforeach; ?>
  </main>
</div>

<!-- Modal -->
<div id="userModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h3 id="modalTitle">Add User</h3>
    <form id="userForm" method="post" action="">
      <input type="hidden" name="role" id="userRole">
      <input type="hidden" name="id" id="editId">

      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="phone">Phone</label>
      <input type="text" id="phone" name="phone" required oninput="formatPhone(this)">

      <div class="modal-buttons">
        <button type="submit">Save</button>
        <button type="button" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<footer>
  <div class="footer-content">
    <div class="footer-section">
      <p>© 2025 CPUT Clinic Dashboard</p>
    </div>
  </div>
</footer>

<script>
let currentRole = '';
let editing = false;

function switchTab(tabName) {
  document.querySelectorAll('.tab-content').forEach(s => s.style.display = 'none');
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(tabName).style.display = 'block';
  event.target.classList.add('active');
}

function openModal(role, id=null) {
  currentRole = role;
  editing = !!id;
  document.getElementById('userRole').value = role;
  document.getElementById('editId').value = id || '';
  document.getElementById('userForm').reset();
  document.getElementById('modalTitle').innerText = editing ? 'Edit ' + role : 'Add ' + role;
  document.getElementById('userModal').style.display = 'flex';

  if(editing){
    const row = document.querySelector(`#${role} table tbody tr td:first-child`);
    // Optional: pre-fill using JS for future AJAX
  }
}

function closeModal(){document.getElementById('userModal').style.display='none';}

function formatPhone(input){
  let digits = input.value.replace(/\D/g,'');
  if(digits.length>10) digits=digits.slice(0,10);
  if(digits.length>6) input.value = digits.slice(0,3)+'-'+digits.slice(3,6)+'-'+digits.slice(6);
  else if(digits.length>3) input.value = digits.slice(0,3)+'-'+digits.slice(3);
  else input.value=digits;
}
</script>

</body>
</html>
