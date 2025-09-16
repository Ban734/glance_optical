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
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <h1>Welcome, <?php echo $_SESSION['username']; ?> (Admin)</h1>
  <a href="../functions/logout.php">Logout</a>

  <h2>Create New User Account</h2>
  <form action="../modules/create_staff.php" method="POST">
    <label>Username</label><br>
    <input type="text" name="username" required><br>
    
    <label>Password</label><br>
    <input type="password" name="password" required><br>
    
    <label>Role</label><br>
    <select name="role" required>
      <option value="staff">staff</option>
      <option value="doctor">doctor</option>
      <option value="admin">admin</option>
    </select><br><br>
    
    <button type="submit">Create Account</button>
  </form>
</body>

</html>