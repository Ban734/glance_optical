<?php
session_start();
include("../engines/db.php");

// 🔹 Log user logout before destroying session
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    $log_action = "User Logout";
    $log_stmt = $conn->prepare("INSERT INTO audit_logs (username, role, action) VALUES (?, ?, ?)");
    $log_stmt->bind_param("sss", $_SESSION['username'], $_SESSION['role'], $log_action);
    $log_stmt->execute();
}

session_destroy();
header("Location: ../index.php");
exit();
?>
