<?php
include '../engines/db.php';

$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashedPassword', '$role')";
if ($conn->query($sql) === TRUE) {
    echo "New staff account created successfully.<br><a href='../dashboards/admin_dashboard.php'>Go back</a>";
} else {
    echo "Error: " . $conn->error;
}
?>