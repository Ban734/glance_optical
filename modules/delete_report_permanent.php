<?php
// delete_report_permanent.php
session_start();
include("../engines/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Permission denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM sales_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: ../dashboards/reports_dashboard.php?msg=deleted");
        exit();
    } else {
        die("Delete failed: " . $conn->error);
    }
}
die("Invalid request.");
