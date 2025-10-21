<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Doctor ID: Using 1 as a fallback if session is not fully configured, replace with appropriate user management logic
$doctor_id = $_SESSION['student']['id'] ?? 1; 
$message = "";


$doctor_name = "Guest Doctor"; // Default
$avatar_initials = "??";
if ($doctor_id) {
    $stmt = $conn->prepare("SELECT full_name FROM students WHERE id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $doctor_row = $res->fetch_assoc();
    if ($doctor_row) {
        $doctor_name = "Dr. " . htmlspecialchars($doctor_row['full_name']);
        
       
        $words = explode(' ', $doctor_row['full_name']);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        $avatar_initials = substr($initials, 0, 2);
    }
    $stmt->close();
}


// Handle new prescription submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_prescription'])) {
    $patient_id = $_POST['patient_id'] ?? null;
    if ($patient_id && $doctor_id) {
        // Sanitize inputs
        $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
        $treatment = $conn->real_escape_string($_POST['treatment']);
        $medication = $conn->real_escape_string($_POST['medication']);
        $dosage = $conn->real_escape_string($_POST['dosage']);
        $follow_up = $conn->real_escape_string($_POST['follow_up']);

        $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, diagnosis, treatment, medication, dosage, follow_up) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $patient_id, $doctor_id, $diagnosis, $treatment, $medication, $dosage, $follow_up);
        
        if ($stmt->execute()) {
             $message = "Prescription added successfully!";
        } else {
             $message = "Error adding prescription: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Cannot add prescription: patient or doctor not found.";
    }
}

// Handle new medical history submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_history'])) {
    $patient_id = $_POST['patient_id'] ?? null;
    if ($patient_id) {
        // Sanitize inputs
        $condition = $conn->real_escape_string($_POST['condition']);
        $details = $conn->real_escape_string($_POST['details']);

        $stmt = $conn->prepare("INSERT INTO medical_history (patient_id, `condition`, details) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $patient_id, $condition, $details);
        
        if ($stmt->execute()) {
             $message = "Medical history added successfully!";
        } else {
             $message = "Error adding medical history: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Patient search
$searchResults = [];
if (!isset($_GET['patient_id']) && isset($_GET['search'])) {
    $search = "%" . $conn->real_escape_string($_GET['search']) . "%";
    $stmt = $conn->prepare("SELECT * FROM students WHERE full_name LIKE ? OR idNumber LIKE ?");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $searchResults[] = $row;
    }
    $stmt->close();
}

// Load selected patient
$selectedPatient = null;
$patientPrescriptions = [];
$patientHistory = [];
if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    
    // Fetch patient details 
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $selectedPatient = $res->fetch_assoc();
    $stmt->close();

    if ($selectedPatient) {
        // Prescriptions
        $stmt = $conn->prepare("SELECT * FROM prescriptions WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $patientPrescriptions[] = $row;
        }
        $stmt->close();

        // Medical history
        $stmt = $conn->prepare("SELECT * FROM medical_history WHERE patient_id = ? ORDER BY visit_date DESC");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $patientHistory[] = $row;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Dashboard - Prescription</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Global Styles */
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Arial', sans-serif; background-color: #f5f7fa; color: #333; line-height: 1.6; min-height: 100vh; display: flex; flex-direction: column; }
/* Header Styles */
.header { background-color: #003865; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
.logo-container { display: flex; align-items: center; }
.logo { width: 50px; height: 50px; margin-right: 15px; border-radius: 5px; background-color: #0072CE; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold; }
.header-title { font-size: 1.5rem; font-weight: bold; }
.user-info { display: flex; align-items: center; }
.user-avatar { width: 40px; height: 40px; border-radius: 50%; background-color: #0072CE; display: flex; align-items: center; justify-content: center; margin-right: 10px; color: white; font-weight: bold; }
/* Main Content Layout */
.container { display: flex; flex: 1; } 
/* Sidebar Styles */
.sidebar { width: 250px; background-color: white; padding: 1.5rem 0; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); flex-shrink: 0; }
.nav-item { padding: 1rem 2rem; cursor: pointer; transition: background-color 0.3s; display: flex; align-items: center; }
.nav-item:hover, .nav-item.active { background-color: #e8f4fc; border-left: 4px solid #0072CE; }
.nav-item i { margin-right: 10px; color: #0072CE; }
/* Main Content Styles */
.main-content { flex: 1; padding: 2rem; }
/* Card Styling  */
.card { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); padding: 2.5rem; max-width: 1000px; margin: 0 auto; }

/* Search/Form Specific Styles */
#search-interface h1, .patient-profile h2, .patient-profile h1 { color: #003865; margin-bottom: 1rem; border-bottom: 2px solid #e9ecef; padding-bottom: 0.5rem; }
.search-container { display: flex; gap: 1rem; margin-bottom: 1rem; }
.search-container input { flex: 1; padding: 0.8rem; border: 1px solid #ced4da; border-radius: 5px; }
.search-results { margin-top: 1rem; }
.search-results ul { list-style: none; padding: 0; margin-top: 1rem; border: 1px solid #e9ecef; border-radius: 5px; max-height: 250px; overflow-y: auto; }
.search-results li { padding: 0.8rem 1rem; border-bottom: 1px solid #f0f0f0; }
.search-results li a { text-decoration: none; color: #0072CE; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
.search-results li:hover { background-color: #e8f4fc; }
.search-results span { font-size: 0.9em; color: #6c757d; }

.btn { padding: 0.8rem 1.8rem; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background-color 0.3s; }
.btn-primary { background-color: #0072CE; color: white; }
.btn-primary:hover { background-color: #005fa3; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn-secondary:hover { background-color: #5a6268; }
.message { padding: 1rem; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #c3e6cb;}

/* Patient Profile Tabs */
.tabs { display: flex; margin-top: 2rem; border: 1px solid #ccc; border-radius: 8px; overflow: hidden; }
.tab-buttons { flex: 0 0 200px; background-color: #f8f9fa; padding: 1rem 0; }
.tab-buttons button { background: none; border: none; padding: 1rem; width: 100%; text-align: left; cursor: pointer; border-left: 4px solid transparent; transition: background-color 0.3s; color: #333; font-weight: bold; }
.tab-buttons button i { margin-right: 8px; }
.tab-buttons button.active, .tab-buttons button:hover { background-color: white; border-left: 4px solid #0072CE; }
.tab-content { flex: 1; padding: 1.5rem; background: white; }
.tab-content > div { display: none; }
.tab-content > div.active { display: block; }
.tab-content h3 { color: #0072CE; margin-top: 1.5rem; margin-bottom: 0.5rem; border-bottom: 1px dashed #eee; padding-bottom: 5px; }


/* Form/Table Styling */
form label { display: block; margin-top: 10px; font-weight: bold; color: #0072CE; }
form input[type="text"], form textarea { width:100%; padding:10px; margin:5px 0 10px 0; border-radius:6px; border:1px solid #ccc; }
table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
th, td { border: 1px solid #eee; padding: 10px; text-align: left; font-size: 0.9em;}
th { background: #0072CE; color: white; }

/* Allergy Styles */
.allergy-warning { color: red; font-weight: bold; }

/* Footer Styles */
.custom-footer { background-color: #012773; color: white; padding: 30px 0; margin-top: auto; }
.footer-content { display: flex; justify-content: space-around; flex-wrap: wrap; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.footer-section { flex: 1; min-width: 250px; margin: 10px; }
.footer-section h4 { margin-bottom: 10px; text-decoration: underline; }
.footer-logo { width: 100px; height: 50px; margin-bottom: 10px; border-radius: 5px; background-color: #0072CE; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold; }
.icon { width: 16px; height: 16px; margin-right: 8px; vertical-align: middle; color: white; }
.social-icons a { color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; margin-top: 10px; }
.footer-section ul { list-style: none; padding-left: 0; }
.footer-section ul li { margin-bottom: 4px; line-height: 1.4; }
</style>
</head>
<body>
<header class="header">
    <div class="logo-container">
        <div class="logo">CPUT</div>
        <div class="header-title">CPUT Clinic - Doctor Dashboard</div>
    </div>
    <div class="user-info">
        <div class="user-avatar"><?= $avatar_initials ?></div>
        <div><?= $doctor_name ?></div>
    </div>
    </header>

<div class="container">

    <aside class="sidebar">
        <div class="nav-item" onclick="window.location.href='update-profile.html'">
            <i class="fas fa-user-edit"></i>
            <span>Update Profile</span>
        </div>
        <div class="nav-item" onclick="window.location.href='book-appointment.html'">
            <i class="fas fa-calendar-check"></i>
            <span>View Appointment</span>
        </div>
        <div class="nav-item active">
            <i class="fas fa-prescription-bottle-alt"></i>
            <span>Prescription</span>
        </div>
        <div class="nav-item" onclick="window.location.href='user-management.html'">
            <i class="fas fa-users-cog"></i>
            <span>User Management</span>
        </div>
        <div class="nav-item" onclick="window.location.href='reporting.html'">
            <i class="fas fa-chart-line"></i>
            <span>Reporting & Analytics</span>
        </div>
        <div class="nav-item" onclick="window.location.href='logout.php'">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </div>
        </aside>

    <main class="main-content">
        <div class="card">
            <?php if(!$selectedPatient): ?>
                <div id="search-interface">
                    <h1><i class="fas fa-search"></i> Start New Prescription</h1>
                    
                    <form method="GET">
                        <div class="search-container">
                            <input type="text" name="search" placeholder="Enter Patient Name or ID Number" 
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" required>
                            <button type="submit" class="btn btn-primary" style="flex: 0 0 150px;">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>

                    <p id="search-feedback" style="margin-top: 1rem; color: #6c757d;">
                        <?php if(!isset($_GET['search'])): ?>
                            Enter patient name or ID above to begin.
                        <?php elseif(empty($searchResults)): ?>
                            No patient found matching "<?= htmlspecialchars($_GET['search']) ?>".
                        <?php else: ?>
                            <?= count($searchResults) ?> patient(s) found. Select one to proceed.
                        <?php endif; ?>
                    </p>

                    <?php if(!empty($searchResults)): ?>
                        <div class="search-results">
                            <ul>
                            <?php foreach($searchResults as $patient): ?>
                                <li>
                                    <a href="?patient_id=<?= $patient['id'] ?>">
                                        <?= htmlspecialchars($patient['full_name']) ?>
                                        <span>ID: <?= htmlspecialchars($patient['idNumber']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div id="prescription-card" class="patient-profile">
                    <h1><i class="fas fa-notes-medical"></i> Patient Profile & Records</h1>

                    <?php if($message) echo "<div class='message'>{$message}</div>"; ?>
                    
                    <h2>Patient: <?= htmlspecialchars($selectedPatient['full_name']) ?></h2>
                    <p><strong>ID:</strong> <?= htmlspecialchars($selectedPatient['idNumber']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($selectedPatient['email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($selectedPatient['phone']) ?></p>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($selectedPatient['gender']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($selectedPatient['address']) ?></p>
                    
                    <p>
                        <strong>Allergies:</strong> 
                        <span class="allergy-warning">
                            <?= htmlspecialchars($selectedPatient['allergies'] ?? 'None Reported') ?>
                        </span>
                    </p>
                    
                    <div style="margin-top: 1rem; margin-bottom: 2rem;">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='prescription.php'">
                            <i class="fas fa-arrow-left"></i> Start New Search
                        </button>
                    </div>

                    <div class="tabs">
                        <div class="tab-buttons">
                            <button class="active" onclick="showTab('prescriptions')"><i class="fas fa-pills"></i> Prescriptions</button>
                            <button onclick="showTab('medical_history')"><i class="fas fa-notes-medical"></i> Medical History</button>
                        </div>
                        <div class="tab-content">
                            
                            <div id="prescriptions" class="active">
                                <h3>Past Prescriptions</h3>
                                <?php if(!empty($patientPrescriptions)): ?>
                                    <table>
                                        <tr><th>Date</th><th>Diagnosis</th><th>Treatment</th><th>Medication</th><th>Dosage</th><th>Follow-up</th></tr>
                                        <?php foreach($patientPrescriptions as $presc): ?>
                                            <tr>
                                                <td><?= date('Y-m-d', strtotime($presc['created_at'])) ?></td>
                                                <td><?= nl2br(htmlspecialchars($presc['diagnosis'])) ?></td>
                                                <td><?= nl2br(htmlspecialchars($presc['treatment'])) ?></td>
                                                <td><?= nl2br(htmlspecialchars($presc['medication'])) ?></td>
                                                <td><?= htmlspecialchars($presc['dosage']) ?></td>
                                                <td><?= htmlspecialchars($presc['follow_up']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                <?php else: ?>
                                    <p>No prescriptions yet for this patient.</p>
                                <?php endif; ?>

                                <h3 style="margin-top: 2rem;">Add New Prescription</h3>
                                <form method="POST">
                                    <input type="hidden" name="patient_id" value="<?= $selectedPatient['id'] ?>">
                                    <label>Diagnosis</label>
                                    <input type="text" name="diagnosis" required>
                                    <label>Treatment (Instructions)</label>
                                    <textarea name="treatment" required></textarea>
                                    <label>Medication</label>
                                    <input type="text" name="medication" required>
                                    <label>Dosage (e.g., 500mg TID for 7 days)</label>
                                    <input type="text" name="dosage" required>
                                    <label>Follow-up Date (Optional)</label>
                                    <input type="text" name="follow_up" placeholder="DD/MM/YYYY or 'As Needed'">
                                    <button type="submit" name="add_prescription" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Issue Prescription</button>
                                </form>
                            </div>

                            <div id="medical_history">
                                <h3>Medical History Records</h3>
                                <?php if(!empty($patientHistory)): ?>
                                    <table>
                                        <tr><th>Date</th><th>Condition</th><th>Details</th></tr>
                                        <?php foreach($patientHistory as $mh): ?>
                                            <tr>
                                                <td><?= date('Y-m-d', strtotime($mh['visit_date'] ?? $mh['created_at'])) ?></td>
                                                <td><?= htmlspecialchars($mh['condition']) ?></td>
                                                <td><?= nl2br(htmlspecialchars($mh['details'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                <?php else: ?>
                                    <p>No medical history records yet for this patient.</p>
                                <?php endif; ?>

                                <h3 style="margin-top: 2rem;">Add Medical History</h3>
                                <form method="POST">
                                    <input type="hidden" name="patient_id" value="<?= $selectedPatient['id'] ?>">
                                    <label>Condition</label>
                                    <input type="text" name="condition" required placeholder="e.g., Follow-up, Cold, or New Allergy Reported">
                                    <label>Details</label>
                                    <textarea name="details" required placeholder="Detailed notes on condition or visit"></textarea>
                                    <button type="submit" name="add_history" class="btn btn-primary">Add History</button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<footer class="custom-footer">
    <div class="footer-content">
        <div class="footer-section">
            <div class="footer-logo">CLINIC</div>
            <p><i class="fas fa-envelope icon"></i> info@cput.ac.za</p>
            <div class="social-icons">
                <a href="#" target="_blank">
                    <i class="fab fa-facebook-f icon"></i> Facebook Page
                </a>
            </div>
        </div>

        <div class="footer-section">
            <h4>Clinic Contact Details</h4>
            <ul>
                <li><i class="fas fa-phone icon"></i> Bellville: +27 21 959 6403</li>
                <li><i class="fas fa-phone icon"></i> Cape Town (D6): +27 21 460 3405</li>
                <li><i class="fas fa-phone icon"></i> Mowbray: +27 21 680 1555</li>
                <li><i class="fas fa-phone icon"></i> Wellington: +27 21 864 5522</li>
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
    function showTab(tabName) {
        // Remove 'active' class from all buttons
        let buttons = document.querySelectorAll('.tab-buttons button');
        buttons.forEach(button => button.classList.remove('active'));

        // Add 'active' class to the clicked button
        let clickedButton = document.querySelector(`.tab-buttons button[onclick="showTab('${tabName}')"]`);
        if (clickedButton) {
            clickedButton.classList.add('active');
        }

        // Hide all content tabs
        let tabs = document.querySelectorAll('.tab-content > div');
        tabs.forEach(tab => tab.classList.remove('active'));
        
        // Show the selected content tab
        document.getElementById(tabName).classList.add('active');
    }

   
    
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('prescriptions')) {
            showTab('prescriptions');
        }
    });
</script>
</body>
</html>