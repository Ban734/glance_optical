<?php
include '../engines/db_connection.php';
$conn = connectDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)$_POST['id'];

    $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ../dashboards/inventory_dashboard.php?deleted=1");
        exit();
    } else {
        header("Location: ../dashboards/inventory_dashboard.php?error=deletefail");
        exit();
    }

    $stmt->close();
}
$conn->close();
?>