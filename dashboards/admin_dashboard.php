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
  <title>Admin Dashboard | Glance Optical</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/dock.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
  <!-- Dock navigation already comes from dock.php -->

  <!-- 🔹 Side Pane Toggle (no inline onclick) -->
  <button class="pane-toggle" type="button" aria-expanded="false" aria-controls="sidePane">☰ Menu</button>

  <!-- 🔹 Side Pane -->
<div class="side-pane" id="sidePane" role="dialog" aria-hidden="true">
  <h3>Admin Panel</h3>
  <a href="../modules/manage_accounts.php">Manage Accounts</a>
  <a href="../dashboards/admin_dashboard.php">Create Account</a>
  <a href="../modules/audit_logs.php">Audit Logs</a>
</div>


  <!-- 🔹 Main Content -->
  <main class="content">
    <div class="card">
      <h2>Create New User Account</h2>

      <form action="../modules/create_staff.php" method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Phone Number</label>
        <input type="text" name="phone" required>

        <label>Birthday</label>
        <input type="date" name="birthday" required>

        <label>Address</label>
        <input type="text" name="address" required>

        <label>Email</label>
        <input type="email" name="email" required>

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

  <!-- Bootstrap bundle (keep this) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Pane Toggle Script: single, robust handler -->
  <script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.querySelector(".pane-toggle");
    const sidePane = document.getElementById("sidePane");

    if (!toggleBtn || !sidePane) return;

    // Open/close on button click
    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      const opening = !sidePane.classList.contains("open");
      sidePane.classList.toggle("open");
      sidePane.setAttribute("aria-hidden", String(!opening));
      toggleBtn.setAttribute("aria-expanded", String(opening));
    });

    // Prevent clicks inside pane from closing it
    sidePane.addEventListener("click", function (e) {
      e.stopPropagation();
    });

    // Close when clicking outside
    document.addEventListener("click", function (e) {
      if (sidePane.classList.contains("open")) {
        // click that is not inside the pane and not the toggle button
        if (!sidePane.contains(e.target) && e.target !== toggleBtn) {
          sidePane.classList.remove("open");
          sidePane.setAttribute("aria-hidden", "true");
          toggleBtn.setAttribute("aria-expanded", "false");
        }
      }
    });

    // Close with Escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && sidePane.classList.contains("open")) {
        sidePane.classList.remove("open");
        sidePane.setAttribute("aria-hidden", "true");
        toggleBtn.setAttribute("aria-expanded", "false");
      }
    });
  });
  </script>
</body>
</html>