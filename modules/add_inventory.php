<?php
include '../engines/db_connection.php';
$conn = connectDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product = trim($_POST['product_name']);
    $qty = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    
    $check = $conn->prepare("SELECT id FROM inventory WHERE LOWER(product_name) = LOWER(?)");
    $check->bind_param("s", $product);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        
        header("Location: ../dashboards/inventory_dashboard.php?error=duplicate");
        exit();
    } else {
        
        $stmt = $conn->prepare("INSERT INTO inventory (product_name, quantity, price, last_updated) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sid", $product, $qty, $price);

        if ($stmt->execute()) {
            header("Location: ../dashboards/inventory_dashboard.php?success=1");
            exit();
        } else {
            header("Location: ../dashboards/inventory_dashboard.php?error=insertfail");
            exit();
        }

        $stmt->close();
    }

    $check->close();
}
$conn->close();
?>
