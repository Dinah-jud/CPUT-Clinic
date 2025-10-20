<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get form data
$email = $_POST['email'];
$password = $_POST['password'];
$selected_role = $_POST['user_type'] ?? '';

$stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (password_verify($password, $row['password'])) {
        // Check stored role
        $stored_role = $row['user_type'] ?? 'patient';

        // Option A: require user to pick same role as their stored role
        if ($selected_role !== '' && $selected_role !== $stored_role) {
            echo "<script>alert('Selected role does not match your account role.'); window.location='login.html';</script>";
            exit();
        }

        // Log user in
        // $_SESSION['user'] = [
        //   'id' => $row['id'],
        //   'full_name' => $row['full_name'],
        //   'email' => $row['email'],
        //   'phone' => $row['phone'],
        //   'idNumber' => $row['idNumber'],
        //   'gender' => $row['gender'],
        //   'address' => $row['address'],
        //   'user_type' => $row['user_type']
        // ];

        $_SESSION['student'] = [
            'id'          => $row['id'],
            'full_name'   => $row['full_name'],
            'email'       => $row['email'],
            'phone'       => $row['phone'],
            'idNumber'    => $row['idNumber'],
            'gender'      => $row['gender'],
            'address'     => $row['address'],
            'user_type'   => $row['user_type']
        ];
        

        // Redirect by role
        if ($stored_role === 'patient') {
            header("Location: patientDashboard.php");
        } elseif ($stored_role === 'doctor') {
            header("Location: doctorDashboard.html");
        } elseif ($stored_role === 'admin') {
            header("Location: adminDashboard.html");
        } else {
            // fallback
            header("Location: patientDashboard.html");
        }
        exit();
    } else {
        echo "<script>alert('Incorrect password.'); window.location='login.html';</script>";
    }
} else {
    echo "<script>alert('User not found.'); window.location='login.html';</script>";
}

$stmt->close();
$conn->close();
?>
