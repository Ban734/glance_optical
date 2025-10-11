<?php
session_start();
include("../engines/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
  $id = $_POST['id'];

  // Get user name for logs
  $fetch = $conn->prepare("SELECT full_name FROM users WHERE id=?");
  $fetch->bind_param("i", $id);
  $fetch->execute();
  $result = $fetch->get_result();
  $user = $result->fetch_assoc();
  $deleted_name = $user ? $user['full_name'] : 'Unknown';

  $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {

    // 🔹 Audit Log Entry
    if (isset($_SESSION['username'])) {
        $log_user = $_SESSION['username'];
        $log_role = $_SESSION['role'];
        $log_action = "Deleted account: $deleted_name (ID: $id)";

        $log = $conn->prepare("INSERT INTO audit_logs (username, role, action) VALUES (?, ?, ?)");
        $log->bind_param("sss", $log_user, $log_role, $log_action);
        $log->execute();
    }

    header("Location: manage_accounts.php?msg=deleted");
    exit();
  } else {
    echo "Error deleting account: " . $conn->error;
  }
} else {
  echo "Invalid request.";
}
?>
