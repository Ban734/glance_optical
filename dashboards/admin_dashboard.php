<?php
session_start();
include("../dashboards/dock.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/dock.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
  <!-- Dock navigation already comes from dock.php -->

  <main class="content">
    <div class="card">
      <h2>Create New User Account</h2>

      <form action="../modules/create_staff.php" method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role" required>
          <option value="staff">Staff</option>
          <option value="doctor">Doctor</option>
          <option value="admin">Admin</option>
        </select>

        <button type="submit" class="submit-btn">Create Account</button>
      </form>
    </div>
  </main>
</body>
</html>