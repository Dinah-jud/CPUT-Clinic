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
$email = $_POST['email'];
$password = $_POST['password'];

// Query to check if the user exists
$sql = "SELECT * FROM students WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
  $row = $result->fetch_assoc();
  if (password_verify($password, $row['password'])) {
    session_start();
    $_SESSION['student'] = $row;

    // Redirect to the dashboard in same folder
    header("Location: dashboard.html");
    exit();
  } else {
    echo "Incorrect password.";
  }
} else {
  echo "User not found.";
}
$conn->close();
?>