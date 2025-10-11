<?php
session_start();
include("../engines/db.php");

if (!isset($_GET['id'])) {
  die("Error: Missing user ID.");
}

$id = $_GET['id'];

// 🔹 Fetch user info
$stmt = $conn->prepare("SELECT full_name, username, email, phone, birthday, address, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("User not found.");
}

$user = $result->fetch_assoc();

// 🔹 Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $full_name = $_POST['full_name'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $birthday = $_POST['birthday'];
  $address = $_POST['address'];
  $role = $_POST['role'];

  $update = $conn->prepare("UPDATE users SET full_name=?, username=?, email=?, phone=?, birthday=?, address=?, role=? WHERE id=?");
  $update->bind_param("sssssssi", $full_name, $username, $email, $phone, $birthday, $address, $role, $id);

  if ($update->execute()) {

    // 🔹 Audit Log Entry
    if (isset($_SESSION['username'])) {
        $log_user = $_SESSION['username'];
        $log_role = $_SESSION['role'];
        $log_action = "Updated account: $full_name (ID: $id)";

        $log = $conn->prepare("INSERT INTO audit_logs (username, role, action) VALUES (?, ?, ?)");
        $log->bind_param("sss", $log_user, $log_role, $log_action);
        $log->execute();
    }

    header("Location: manage_accounts.php?msg=updated");
    exit();
  } else {
    echo "Update failed: " . $conn->error;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Account | Glance Optical</title>
  <link rel="stylesheet" href="../css/manage_accounts.css">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/dock.css">
</head>
<body>

  <div class="content">
    <h2>Update Account</h2>

    <form method="POST">
      <label>Full Name</label>
      <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']); ?>" required>

      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>

      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

      <label>Phone</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>

      <label>Birthday</label>
      <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday']); ?>" required>

      <label>Address</label>
      <input type="text" name="address" value="<?= htmlspecialchars($user['address']); ?>" required>

      <label>Role</label>
      <select name="role" required>
        <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
        <option value="doctor" <?= $user['role'] === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
      </select>

      <button type="submit" class="submit-btn">Save Changes</button>
      <a href="manage_accounts.php" class="cancel-btn">Cancel</a>
    </form>
  </div>

</body>
</html>
