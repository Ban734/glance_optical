<?php
// archive_report.php
session_start();
include("../engines/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $stmt = $conn->prepare("UPDATE sales_records SET archived = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: ../dashboards/reports_dashboard.php?msg=archived");
        exit();
    } else {
        die("Archive failed: " . $conn->error);
    }
}
die("Invalid request.");
