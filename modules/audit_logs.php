<?php
session_start();
include("../dashboards/dock.php");
include("../engines/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

$result = $conn->query("SELECT * FROM audit_logs ORDER BY timestamp DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Audit Logs | Glance Optical</title>
  <link rel="stylesheet" href="../css/audit.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <!-- 🔹 Side Pane Toggle -->
    <button class="pane-toggle" type="button" aria-expanded="false" aria-controls="sidePane">☰ Menu</button>

    <!-- 🔹 Side Pane -->
    <div class="side-pane" id="sidePane" role="dialog" aria-hidden="true">
      <h3>Admin Panel</h3>
      <a href="../modules/manage_accounts.php">Manage Accounts</a>
      <a href="../dashboards/admin_dashboard.php">Create Account</a>
      <a href="../modules/audit_logs.php">Audit Logs</a>
    </div>

  <main class="content">
    <div class="card">
      <h2>Audit Logs</h2>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Action</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars(ucfirst($row['role'])) ?></td>
              <td><?= htmlspecialchars($row['action']) ?></td>
              <td><?= htmlspecialchars($row['timestamp']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- 🔹 Pane Toggle Script -->
  <script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.querySelector(".pane-toggle");
    const sidePane = document.getElementById("sidePane");

    if (!toggleBtn || !sidePane) return;

    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      const opening = !sidePane.classList.contains("open");
      sidePane.classList.toggle("open");
      sidePane.setAttribute("aria-hidden", String(!opening));
      toggleBtn.setAttribute("aria-expanded", String(opening));
    });

    sidePane.addEventListener("click", function (e) {
      e.stopPropagation();
    });

    document.addEventListener("click", function (e) {
      if (sidePane.classList.contains("open")) {
        if (!sidePane.contains(e.target) && e.target !== toggleBtn) {
          sidePane.classList.remove("open");
          sidePane.setAttribute("aria-hidden", "true");
          toggleBtn.setAttribute("aria-expanded", "false");
        }
      }
    });

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
