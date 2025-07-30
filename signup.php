<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);

// Check
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get form data
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // encrypt password
$phone = $_POST['phone'];
$student_no = $_POST['student_no'];

// Insert into DB
$sql = "INSERT INTO students (full_name, email, password, phone, student_no)
        VALUES ('$full_name', '$email', '$password', '$phone', '$student_no')";

if ($conn->query($sql) === TRUE) {
  echo "Signup successful!";
} else {
  echo "Error: " . $conn->error;
}

$conn->close();
?>