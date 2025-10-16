<?php
session_start();
include '../engines/db.php';

function redirect_with_toast($msg, $success = false) {
  $_SESSION['toast_msg'] = $msg;
  $_SESSION['toast_success'] = $success;
  header("Location: ../dashboards/admin_dashboard.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $birthday = trim($_POST['birthday'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $role = $_POST['role'] ?? '';

  // Server-side validation rules
  if (strlen($full_name) < 4 || strlen($full_name) > 30)
    redirect_with_toast('❌ Full name must be 4–30 characters.');

  if (!preg_match("/^[A-Za-z0-9_]{8,30}$/", $username))
    redirect_with_toast('❌ Username must be 8–30 characters, letters numbers and underscores only.');

  if (!preg_match("/^[0-9]{10,15}$/", $phone))
    redirect_with_toast('❌ Phone must be 10–15 digits.');

  if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    redirect_with_toast('❌ Invalid email address.');

  if ($password !== $confirm_password)
    redirect_with_toast('❌ Passwords do not match.');

  // Password strength: at least 8 chars, contains letter and number
  if (!preg_match("/^(?=.{8,})(?=.*[A-Za-z])(?=.*\d).*$/", $password))
    redirect_with_toast('❌ Password must be at least 8 characters and include letters and numbers.');

  // Role basic check
  $allowed_roles = ['staff', 'doctor', 'admin'];
  if (!in_array($role, $allowed_roles))
    redirect_with_toast('❌ Invalid role selected.');

  // Check duplicates
  $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
  $check->bind_param("ss", $username, $email);
  $check->execute();
  $res = $check->get_result();
  if ($res && $res->num_rows > 0)
    redirect_with_toast('⚠️ Username or email already exists.');

  // Hash and insert
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO users (full_name, phone, birthday, address, email, username, password, role)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  if (!$stmt) {
    redirect_with_toast('❌ Database prepare error: ' . $conn->error);
  }
  $stmt->bind_param("ssssssss", $full_name, $phone, $birthday, $address, $email, $username, $hashedPassword, $role);

  if ($stmt->execute()) {
    // audit log if admin is creating
    if (isset($_SESSION['username'])) {
      $log_user = $_SESSION['username'];
      $log_role = $_SESSION['role'];
      $log_action = "Created new account: $full_name ($role)";
      $log = $conn->prepare("INSERT INTO audit_logs (username, role, action) VALUES (?, ?, ?)");
      if ($log) {
        $log->bind_param("sss", $log_user, $log_role, $log_action);
        $log->execute();
      }
    }
    redirect_with_toast('✅ Account created successfully!', true);
  } else {
    redirect_with_toast('❌ Database error: ' . $conn->error);
  }
}
?>
