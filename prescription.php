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

// Ensure doctor id exists in session
$doctor_id = $_SESSION['student']['id'] ?? null;

// Handle new prescription submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_prescription'])) {
    $patient_id = $_POST['patient_id'] ?? null;
    if ($patient_id && $doctor_id) {
        $diagnosis = $_POST['diagnosis'];
        $treatment = $_POST['treatment'];
        $medication = $_POST['medication'];
        $dosage = $_POST['dosage'];
        $follow_up = $_POST['follow_up'];

        $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, diagnosis, treatment, medication, dosage, follow_up) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $patient_id, $doctor_id, $diagnosis, $treatment, $medication, $dosage, $follow_up);
        $stmt->execute();
        $stmt->close();
        $message = "Prescription added successfully!";
    } else {
        $message = "Cannot add prescription: patient or doctor not found.";
    }
}

// Handle patient search
$searchResults = [];
if (isset($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $stmt = $conn->prepare("SELECT * FROM students WHERE full_name LIKE ? OR idNumber LIKE ?");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $searchResults[] = $row;
    }
    $stmt->close();
}

// Load patient details if selected
$selectedPatient = null;
$patientPrescriptions = [];
if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $selectedPatient = $res->fetch_assoc();
    $stmt->close();

    if ($selectedPatient) {
        // Get past prescriptions
        $stmt = $conn->prepare("SELECT * FROM prescriptions WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $patientPrescriptions[] = $row;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prescription Page</title>
<style>
body { font-family: Arial, sans-serif; background: #eef2f7; padding: 20px; }
.container { max-width: 950px; margin: auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
h1 { color: #0072CE; text-align: center; margin-bottom: 20px; }
h2 { color: #333; margin-top: 20px; margin-bottom: 10px; }

form input[type="text"], textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc; margin-bottom: 10px; }
textarea { resize: vertical; min-height: 80px; }

form button, button[type="submit"] { background: #0072CE; color: #fff; padding: 12px; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s; }
form button:hover, button[type="submit"]:hover { background: #005fa3; }

ul { list-style: none; margin-top: 10px; padding-left: 0; }
ul li { padding: 10px; background: #f7f9fc; border: 1px solid #ddd; margin-bottom: 8px; border-radius: 8px; }
ul li a { text-decoration: none; color: #0072CE; font-weight: bold; }

.profile, .prescriptions, .add-prescription { background: #f7f9fc; padding: 20px; margin-top: 15px; border-radius: 10px; box-shadow: inset 0 0 5px rgba(0,0,0,0.05); }
.profile p { margin-bottom: 8px; }

table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
th { background: #0072CE; color: #fff; }

.message { padding: 12px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 15px; }
</style>
</head>
<body>
<div class="container">
    <h1>Prescription Management</h1>

    <!-- Patient Search -->
    <form method="GET">
        <input type="text" name="search" placeholder="Search patient by name or ID">
        <button type="submit">Search</button>
    </form>

    <?php if(!empty($searchResults)): ?>
        <h2>Search Results</h2>
        <ul>
        <?php foreach($searchResults as $patient): ?>
            <li><a href="?patient_id=<?= $patient['id'] ?>"><?= htmlspecialchars($patient['full_name']) ?> (<?= $patient['idNumber'] ?>)</a></li>
        <?php endforeach; ?>
        </ul>
    <?php elseif(isset($_GET['search'])): ?>
        <p>No patient found.</p>
    <?php endif; ?>

    <?php if($selectedPatient): ?>
        <div class="profile">
            <h2>Patient Profile: <?= htmlspecialchars($selectedPatient['full_name']) ?></h2>
            <p><strong>ID:</strong> <?= $selectedPatient['idNumber'] ?></p>
            <p><strong>Email:</strong> <?= $selectedPatient['email'] ?></p>
            <p><strong>Phone:</strong> <?= $selectedPatient['phone'] ?></p>
            <p><strong>Gender:</strong> <?= $selectedPatient['gender'] ?></p>
            <p><strong>Address:</strong> <?= $selectedPatient['address'] ?></p>
        </div>

        <div class="prescriptions">
            <h2>Past Prescriptions</h2>
            <?php if(!empty($patientPrescriptions)): ?>
                <table>
                    <tr><th>Date</th><th>Diagnosis</th><th>Treatment</th><th>Medication</th><th>Dosage</th><th>Follow-up</th></tr>
                    <?php foreach($patientPrescriptions as $presc): ?>
                        <tr>
                            <td><?= $presc['created_at'] ?></td>
                            <td><?= htmlspecialchars($presc['diagnosis']) ?></td>
                            <td><?= htmlspecialchars($presc['treatment']) ?></td>
                            <td><?= htmlspecialchars($presc['medication']) ?></td>
                            <td><?= htmlspecialchars($presc['dosage']) ?></td>
                            <td><?= htmlspecialchars($presc['follow_up']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No prescriptions yet.</p>
            <?php endif; ?>
        </div>

        <div class="add-prescription">
            <h2>Add New Prescription</h2>
            <?php if(isset($message)) echo "<div class='message'>{$message}</div>"; ?>
            <form method="POST">
                <input type="hidden" name="patient_id" value="<?= $selectedPatient['id'] ?>">
                <label>Diagnosis</label>
                <input type="text" name="diagnosis" required>
                <label>Treatment</label>
                <textarea name="treatment" required></textarea>
                <label>Medication</label>
                <input type="text" name="medication" required>
                <label>Dosage</label>
                <input type="text" name="dosage" required>
                <label>Follow-up</label>
                <input type="text" name="follow_up">
                <button type="submit" name="add_prescription">Add Prescription</button>
            </form>
        </div>
    <?php elseif(isset($_GET['patient_id'])): ?>
        <p>No patient found.</p>
    <?php endif; ?>
</div>
</body>
</html>
