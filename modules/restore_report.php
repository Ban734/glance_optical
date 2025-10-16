<?php
// restore_report.php
session_start();
include("../engines/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $stmt = $conn->prepare("UPDATE sales_records SET archived = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: ../dashboards/reports_dashboard.php?msg=restored");
        exit();
    } else {
        die("Restore failed: " . $conn->error);
    }
}
die("Invalid request.");
