<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../index.html");
    exit();
}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home | Glance Optical</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/dock.css">

</head>
<body>

<?php include("../dashboards/dock.php"); ?>

<div class="container text-center mt-5">
  <h1 class="fw-bold">Welcome, <?php echo $username; ?> </h1>
  <p class="text-muted">You are logged in as <strong><?php echo ucfirst($role); ?></strong></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>