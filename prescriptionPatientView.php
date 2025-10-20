<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 🛑 CRITICAL: Get the logged-in patient's ID
$patient_id = $_SESSION['student']['id'] ?? null; 
$message = "";

// Redirect to login if not logged in
if (!isset($_SESSION['student']) || !$patient_id) {
    header("Location: login.html");
    exit();
}
$student = $_SESSION['student'];

// --- 1. Handle Allergy Update POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_allergies'])) {
    $allergies = $conn->real_escape_string($_POST['allergies']);

    $stmt = $conn->prepare("UPDATE students SET allergies = ? WHERE id = ?");
    $stmt->bind_param("si", $allergies, $patient_id);
    
    if ($stmt->execute()) {
        $message = "Allergy information updated successfully! ✅";
        // Refresh session data if needed
        $_SESSION['student']['allergies'] = $allergies; 
    } else {
        $message = "Error updating allergies: " . $stmt->error;
    }
    $stmt->close();
}

// --- 2. Fetch Patient Data (including new allergies) ---
$patient_data = null;
$patientPrescriptions = [];
$patientHistory = [];

$stmt = $conn->prepare("SELECT full_name, idNumber, email, allergies FROM students WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$res = $stmt->get_result();
$patient_data = $res->fetch_assoc();
$stmt->close();

if ($patient_data) {
    // --- 3. Fetch Prescriptions ---
    $stmt = $conn->prepare("SELECT * FROM prescriptions WHERE patient_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $patientPrescriptions[] = $row;
    }
    $stmt->close();

    // --- 4. Fetch Medical History ---
    $stmt = $conn->prepare("SELECT * FROM medical_history WHERE patient_id = ? ORDER BY visit_date DESC");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $patientHistory[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription & Health View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Reset & Layout */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #f5f7fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Header */
        .header { background-color: #003865; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
        .header img { width: 60px; margin-right: 15px; }
        .header-title { font-size: 1.4rem; font-weight: bold; }
        .profile-section { display: flex; align-items: center; gap: 10px; cursor: pointer; }

        /* Main Layout */
        .container { display: flex; flex: 1; }

        /* Sidebar (Assuming this is a separate menu or included here) */
        .sidebar { width: 250px; background-color: #fff; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); padding: 1rem 0; flex-shrink: 0; }
        .nav-item { padding: 1rem 2rem; display: flex; align-items: center; gap: 10px; cursor: pointer; color: #003865; font-weight: bold; transition: background 0.3s; }
        .nav-item:hover, .nav-item.active { background-color: #e8f4fc; border-left: 4px solid #0072CE; }
        .nav-item i { color: #0072CE; width: 20px; text-align: center; }

        /* Main Content Area */
        .main-content { 
            flex: 1; 
            padding: 2rem;
            display: flex; /* To center the card */
            justify-content: center; /* To center the card */
        }

        /* Card Styling (The main dashboard area) */
        .card { 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); 
            padding: 2.5rem; 
            /* 💥 MODIFIED: Increased maximum width for a bigger dashboard view */
            max-width: 1200px; 
            width: 100%; /* Ensure it uses max-width */
        }
        
        /* Tabs and Content */
        .tabs { display: flex; margin-top: 1rem; border: 1px solid #ccc; border-radius: 8px; overflow: hidden; }
        .tab-buttons { flex: 0 0 200px; background-color: #f8f9fa; padding: 1rem 0; }
        .tab-buttons button { background: none; border: none; padding: 1rem; width: 100%; text-align: left; cursor: pointer; border-left: 4px solid transparent; transition: background-color 0.3s; color: #333; font-weight: bold; }
        .tab-buttons button i { margin-right: 8px; color: #0072CE; }
        .tab-buttons button.active, .tab-buttons button:hover { background-color: white; border-left: 4px solid #0072CE; }
        .tab-content { flex: 1; padding: 1.5rem; background: white; }
        .tab-content > div { display: none; }
        .tab-content > div.active { display: block; }
        .tab-content h3 { color: #0072CE; margin-top: 1.5rem; margin-bottom: 0.5rem; border-bottom: 1px dashed #eee; padding-bottom: 5px; }

        /* Forms and Tables */
        form textarea { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 10px; }
        .btn-primary { background-color: #0072CE; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-primary:hover { background-color: #005fa3; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #eee; padding: 10px; text-align: left; font-size: 0.9em; }
        th { background: #003865; color: white; }

        .allergy-warning { color: red; font-weight: bold; }
        .message { padding: 1rem; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #c3e6cb;}

        /* Footer */
        footer { background-color: #012773; color: white; padding: 30px 0; margin-top: auto; }
        .footer-content { display: flex; justify-content: space-around; flex-wrap: wrap; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .footer-section { flex: 1; min-width: 250px; margin: 10px; }
        .footer-section h4 { margin-bottom: 10px; text-decoration: underline; }
        .footer-logo { width: 100px; height: 50px; margin-bottom: 10px; border-radius: 5px; background-color: #0072CE; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold; }
        .footer-section .icon { color: white; margin-right: 8px; vertical-align: middle; width: 16px; }
        .social-icons a { color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; margin-top: 10px; }
        .footer-section ul { list-style: none; padding-left: 0; }
        .footer-section ul li { margin-bottom: 4px; line-height: 1.4; }
    </style>
</head>
<body>

    <header class="header">
        <div class="logo-container" style="display:flex;align-items:center;">
            <img src="images/cput-logo.png" alt="CPUT Logo"> 
            <div class="header-title">CPUT Clinic - Patient Health View</div>
        </div>
        <div class="profile-section" onclick="window.location.href='profile.php'">
            <i class="fas fa-user-circle fa-2x"></i>
            <span>Welcome, <?php echo htmlspecialchars($student['full_name']); ?></span>
        </div>
    </header>

    <div class="container">

        <aside class="sidebar">
            <div class="nav-item" onclick="window.location.href='dashboard.php'">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="nav-item" onclick="window.location.href='update-profile.html'">
                <i class="fas fa-user-edit"></i>
                <span>Update Profile</span>
            </div>
            <div class="nav-item" onclick="window.location.href='book-appointment.html'">
                <i class="fas fa-calendar-check"></i>
                <span>Book Appointment</span>
            </div>
            
            <div class="nav-item active" onclick="window.location.href='prescriptionPatientView.php'">
                <i class="fas fa-prescription-bottle-alt"></i>
                <span>Prescription</span>
            </div>
            
            <div class="nav-item" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </div>
        </aside>

        <main class="main-content">
            <div class="card">
                <h2><i class="fas fa-notes-medical"></i> Your Health Records</h2>
                <p><strong>Student ID:</strong> <?= htmlspecialchars($patient_data['idNumber'] ?? 'N/A') ?></p>

                <?php if($message) echo "<div class='message'>{$message}</div>"; ?>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="active" onclick="showTab('prescriptions')"><i class="fas fa-prescription-bottle-alt"></i> Prescriptions</button>
                        <button onclick="showTab('history')"><i class="fas fa-notes-medical"></i> Medical History</button>
                        <button onclick="showTab('allergies')"><i class="fas fa-exclamation-triangle"></i> Update Allergies</button>
                    </div>

                    <div class="tab-content">
                        
                        <div id="prescriptions" class="active">
                            <h3>Your Prescriptions</h3>
                            <?php if(!empty($patientPrescriptions)): ?>
                                <table>
                                    <tr><th>Date</th><th>Diagnosis</th><th>Medication</th><th>Dosage</th><th>Instructions</th></tr>
                                    <?php foreach($patientPrescriptions as $presc): ?>
                                        <tr>
                                            <td><?= date('Y-m-d', strtotime($presc['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($presc['diagnosis']) ?></td>
                                            <td><?= htmlspecialchars($presc['medication']) ?></td>
                                            <td><?= htmlspecialchars($presc['dosage']) ?></td>
                                            <td><?= nl2br(htmlspecialchars($presc['treatment'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php else: ?>
                                <p>No active or past prescriptions found.</p>
                            <?php endif; ?>
                        </div>

                        <div id="history">
                            <h3>Your Medical History</h3>
                            <?php if(!empty($patientHistory)): ?>
                                <table>
                                    <tr><th>Date</th><th>Condition/Visit Type</th><th>Details</th></tr>
                                    <?php foreach($patientHistory as $mh): ?>
                                        <tr>
                                            <td><?= date('Y-m-d', strtotime($mh['visit_date'] ?? $mh['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($mh['condition']) ?></td>
                                            <td><?= nl2br(htmlspecialchars($mh['details'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php else: ?>
                                <p>No medical history records found.</p>
                            <?php endif; ?>
                        </div>

                        <div id="allergies">
                            <h3>Update Allergy Information</h3>
                            <p class="allergy-warning">
                                **Current Allergies:** <?= htmlspecialchars($patient_data['allergies'] ?? 'None Reported') ?>
                            </p>

                            <form method="POST">
                                <label for="allergies_text">List all known allergies (medications, food, environmental):</label>
                                <textarea id="allergies_text" name="allergies" rows="5" required 
                                    placeholder="e.g., Penicillin (causes rash), Peanuts, Dust Mites">
                                    <?= htmlspecialchars($patient_data['allergies'] ?? '') ?>
                                </textarea>
                                <button type="submit" name="update_allergies" class="btn-primary">
                                    <i class="fas fa-save"></i> Save Allergies
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </main>

    </div>

    <footer>
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
            // Update button active state
            let buttons = document.querySelectorAll('.tab-buttons button');
            buttons.forEach(button => button.classList.remove('active'));
            document.querySelector(`.tab-buttons button[onclick="showTab('${tabName}')"]`).classList.add('active');

            // Update content active state
            let tabs = document.querySelectorAll('.tab-content > div');
            tabs.forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
        }

        // Ensure the correct tab is active on load
        document.addEventListener('DOMContentLoaded', function() {
            showTab('prescriptions');
        });
    </script>
</body>
</html>