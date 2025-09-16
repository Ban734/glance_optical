<?php
include '../engines/db_connection.php';
$conn = connectDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)$_POST['id'];
    $product = trim($_POST['product_name']);
    $qty = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    $stmt = $conn->prepare("UPDATE inventory SET product_name = ?, quantity = ?, price = ?, last_updated = NOW() WHERE id = ?");
    $stmt->bind_param("sidi", $product, $qty, $price, $id);

    if ($stmt->execute()) {
        header("Location: ../dashboards/inventory_dashboard.php?updated=1");
        exit();
    } else {
        header("Location: ../dashboards/inventory_dashboard.php?error=updatefail");
        exit();
    }

    $stmt->close();
}
$conn->close();
?>