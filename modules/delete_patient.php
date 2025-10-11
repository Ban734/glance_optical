<?php
session_start();
include '../engines/db.php';

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['doctor','admin'])) {
    header("Location: ../login.html");
    exit();
}

$id = $_GET['id'] ?? null;

if ($id) {
    $sql = "DELETE FROM patients WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../dashboards/patient_dashboard.php");
exit();
?>
