<?php 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get form data
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // encrypt password
$phone = $_POST['phone'];
// $student_no = $_POST['student_no'];
$idNumber = $_POST['idNumber'];
$gender = $_POST['gender'];
$address = $_POST['address'];
$user_type = $_POST['user_type'];

// Validate user type
$valid_roles = ['patient', 'doctor', 'admin'];
if (!in_array($user_type, $valid_roles)) {
    die("Invalid user type selected."); // stops execution if role is invalid
}


// Prepare an insert statement
$stmt = $conn->prepare("INSERT INTO students (full_name, email, password, phone, idNumber, gender, address, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt === false) {
  die("Prepare failed: " . htmlspecialchars($conn->error));
}

// Bind parameters (s = string)
$stmt->bind_param("ssssssss", $full_name, $email, $password, $phone, $idNumber, $gender, $address, $user_type);

// Execute statement
if ($stmt->execute()) {
  echo "<script>
          alert('Signup successful!');
          window.location.href = 'login.html';
        </script>";
} else {
  echo "Error: " . htmlspecialchars($stmt->error);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>