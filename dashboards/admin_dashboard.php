<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Create Staff</title>
  <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
  <div class="container">
    <div class="card">
      <h1>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</h1>
      <a href="../functions/logout.php" class="logout-btn">Logout</a>

      <h2>Create New User Account</h2>
      <form action="../modules/create_staff.php" method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role" required>
          <option value="staff">staff</option>
          <option value="doctor">doctor</option>
          <option value="admin">admin</option>
        </select>

        <button type="submit" class="submit-btn">Create Account</button>
      </form>
    </div>
  </div>
</body>
</html>