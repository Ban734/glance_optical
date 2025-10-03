<?php
if (!isset($_SESSION)) {
    session_start();
}
$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? 'Guest';
?>

<div class="dock-logo">
  <a href="../dashboards/home.php">
    <img src="../images/glanceoptilogo.png" alt="Glance Optical Logo">
  </a>
</div>

<nav class="navbar navbar-expand-lg custom-dock">
  <div class="container-fluid">
    

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">

        <?php if ($role === 'admin' || $role === 'staff'): ?>
          <li class="nav-item"><a class="nav-link" href="../dashboards/staff_dashboard.php">SALES</a></li>
          <li class="nav-item"><a class="nav-link" href="../dashboards/inventory_dashboard.php">INVENTORY</a></li>
          <li class="nav-item"><a class="nav-link" href="../dashboards/reports_dashboard.php">REPORTS</a></li>
        <?php endif; ?>

        <?php if ($role === 'admin' || $role === 'doctor'): ?>
          <li class="nav-item"><a class="nav-link" href="../dashboards/patient_dashboard.php">CLINIC</a></li>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="../dashboards/admin_dashboard.php">ADMIN</a></li>
        <?php endif; ?>
      </ul>

      <span class="navbar-text me-3">
        <?php echo $username; ?> | <a href="../functions/logout.php" class="logout-link">Logout</a>
    </span>
    </div>
  </div>
</nav>
