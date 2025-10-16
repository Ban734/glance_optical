<?php
session_start();
include '../engines/db.php';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $deleteSession = $conn->prepare("DELETE FROM active_sessions WHERE username = ?");
    $deleteSession->bind_param("s", $username);
    $deleteSession->execute();
}

session_unset();
session_destroy();

header("Location: ../index.php");
exit();
?>
