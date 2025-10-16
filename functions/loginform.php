<?php
session_start();
include '../engines/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ Verify password first
        if (password_verify($password, $user['password'])) {

            // 🧹 Clean up old ghost session if any
            $cleanup = $conn->prepare("DELETE FROM active_sessions WHERE username = ?");
            $cleanup->bind_param("s", $username);
            $cleanup->execute();

            // 🔹 Double check after cleanup (optional safety)
            $sessionCheck = $conn->prepare("SELECT * FROM active_sessions WHERE username = ?");
            $sessionCheck->bind_param("s", $username);
            $sessionCheck->execute();
            $active = $sessionCheck->get_result();

            if ($active->num_rows > 0) {
                header("Location: ../index.php?error=2");
                exit();
            }

            // ✅ Create new session record
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            $insertSession = $conn->prepare("INSERT INTO active_sessions (username, login_time) VALUES (?, NOW())");
            $insertSession->bind_param("s", $_SESSION['username']);
            $insertSession->execute();

            // 🔸 Audit log
            $log_action = "User Login";
            $log_stmt = $conn->prepare("INSERT INTO audit_logs (username, role, action) VALUES (?, ?, ?)");
            $log_stmt->bind_param("sss", $_SESSION['username'], $_SESSION['role'], $log_action);
            $log_stmt->execute();

            header("Location: ../dashboards/home.php");
            exit();
        } else {
            header("Location: ../index.php?error=1");
            exit();
        }
    } else {
        header("Location: ../index.php?error=1");
        exit();
    }
}
?>
