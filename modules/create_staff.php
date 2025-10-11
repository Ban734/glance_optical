<?php
session_start();
include '../engines/db.php';

// Collect form inputs
$full_name = $_POST['full_name'];
$phone = $_POST['phone'];
$birthday = $_POST['birthday'];
$address = $_POST['address'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$sql = "INSERT INTO users (full_name, phone, birthday, address, email, username, password, role)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $full_name, $phone, $birthday, $address, $email, $username, $hashedPassword, $role);

if ($stmt->execute()) {

    // 🔹 Audit Log Entry
    if (isset($_SESSION['username'])) {
        $log_user = $_SESSION['username'];
        $log_role = $_SESSION['role'];
        $log_action = "Created new account: $full_name ($role)";

        $log = $conn->prepare("INSERT INTO audit_logs (username, role, action) VALUES (?, ?, ?)");
        $log->bind_param("sss", $log_user, $log_role, $log_action);
        $log->execute();
    }

    echo "✅ New staff account created successfully.<br><a href='../dashboards/admin_dashboard.php'>Go back</a>";
} else {
    echo "❌ Error: " . $conn->error;
}
?>
