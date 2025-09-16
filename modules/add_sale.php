<?php
include_once '../engines/db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name   = $_POST['product_name'];
    $quantity       = (int) $_POST['quantity'];
    $unit_price     = (float) $_POST['unit_price'];
    $customer_name  = $_POST['customer_name'] ?? 'Walk-in';
    $date_of_sale   = $_POST['date_of_sale'];
    $recorded_by    = $_SESSION['username'];
    $total          = $quantity * $unit_price;

    
    $payment_method = $_POST['payment_method'];
    $card_type      = $_POST['card_type'] ?? null;

    $conn = connectDB();

    
    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE product_name = ?");
    $stmt->bind_param("s", $product_name);
    $stmt->execute();
    $stmt->bind_result($stock);

    if ($stmt->fetch() && $stock >= $quantity) {
        $stmt->close();

        
        $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE product_name = ?");
        $stmt->bind_param("is", $quantity, $product_name);
        $stmt->execute();
        $stmt->close();

        
        $stmt = $conn->prepare("
            INSERT INTO sales_records 
            (product_name, quantity, unit_price, total, customer_name, date_of_sale, recorded_by, recorded_at, payment_method, card_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ");
        $stmt->bind_param("siddsssss", 
            $product_name, $quantity, $unit_price, $total, 
            $customer_name, $date_of_sale, $recorded_by, 
            $payment_method, $card_type
        );
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Sale recorded and inventory updated.'); window.location.href='../dashboards/staff_dashboard.php';</script>";
    } else {
        echo "<script>alert('Not enough stock for that product.'); window.history.back();</script>";
    }

    $conn->close();
}
?>