<?php
session_start();
$conn = new mysqli("localhost", "root", "", "db");
if($conn->connect_error){ die(json_encode(['success'=>false])); }

$patient_id = $_POST['patient_id'];
$doctor_id = 1; 
$diagnosis = $_POST['diagnosis'];
$treatment = $_POST['treatment'];
$medication = $_POST['medication'];
$dosage = $_POST['dosage'];
$follow_up = $_POST['follow_up'];

$stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, diagnosis, treatment, medication, dosage, follow_up) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("iisssss", $patient_id, $doctor_id, $diagnosis, $treatment, $medication, $dosage, $follow_up);
$success = $stmt->execute();
$stmt->close();
echo json_encode(['success'=>$success]);
?>
