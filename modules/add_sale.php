<?php
include_once '../engines/db_connection.php';
session_start();
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name   = $_POST['product_name'] ?? '';
    $quantity       = (int) ($_POST['quantity'] ?? 0);
    $unit_price     = (float) ($_POST['unit_price'] ?? 0.0);
    // prefer direct discount hidden field (numeric)
    $discount       = isset($_POST['discount']) ? (float) $_POST['discount'] : (float) ($_POST['discount_select'] ?? 0);
    $customer_name  = trim($_POST['customer_name'] ?? '');
    $date_of_sale   = $_POST['date_of_sale'] ?? date('Y-m-d');
    $recorded_by    = $_SESSION['username'] ?? 'unknown';
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $card_type      = $_POST['card_type'] ?? 'N/A';
    $card_number    = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
    $payment_status = $_POST['payment_status'] ?? null;

    if ($customer_name === '') {
        $customer_name = 'N/A';
    }

    // Compute pricing (server-side authoritative)
    $original_price = round($unit_price * $quantity, 2);
    $discount_value = round(($original_price * ($discount / 100)), 2);
    $final_total = round($original_price - $discount_value, 2);

    // Payment logic
    if (in_array($payment_method, ['Cash', 'GCash'])) {
        $payment_status = 'Paid';
        $card_type = 'N/A';
        $card_number = 'N/A';
    } else { // Card
        $payment_status = $payment_status ?: 'Pending';
        $len = strlen($card_number);
        if ($len < 12 || $len > 16) {
            echo "<script>alert('Card number must be between 12–16 digits.'); window.history.back();</script>";
            exit;
        }
    }

    // Prevent future date
    if (strtotime($date_of_sale) > strtotime(date('Y-m-d'))) {
        echo "<script>alert('Cannot record sales for future dates.'); window.history.back();</script>";
        exit;
    }

    $conn = connectDB();

    // stock check
    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE product_name = ?");
    $stmt->bind_param("s", $product_name);
    $stmt->execute();
    $stmt->bind_result($stock);

    if ($stmt->fetch() && $stock >= $quantity) {
        $stmt->close();

        // update stock
        $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE product_name = ?");
        $stmt->bind_param("is", $quantity, $product_name);
        $stmt->execute();
        $stmt->close();

        // Insert sale record
        $stmt = $conn->prepare("
            INSERT INTO sales_records
            (product_name, quantity, unit_price, original_price, discount_percentage, total, customer_name, date_of_sale, recorded_by, recorded_at, payment_method, card_type, card_number, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)
        ");
        if (!$stmt) {
            echo "<script>alert('DB prepare error: " . addslashes($conn->error) . "'); window.history.back();</script>";
            exit;
        }

        // types:
        // s = product_name
        // i = quantity
        // d = unit_price
        // d = original_price
        // d = discount (as numeric)
        // d = total (final_total)
        // s = customer_name
        // s = date_of_sale
        // s = recorded_by
        // s = payment_method
        // s = card_type
        // s = card_number
        // s = payment_status
        $stmt->bind_param(
            "siddddsssssss",
            $product_name,
            $quantity,
            $unit_price,
            $original_price,
            $discount,
            $final_total,
            $customer_name,
            $date_of_sale,
            $recorded_by,
            $payment_method,
            $card_type,
            $card_number,
            $payment_status
        );

        $exec = $stmt->execute();
        $stmt->close();

        if ($exec) {
            echo "<script>alert('Sale recorded successfully.'); window.location.href='../dashboards/staff_dashboard.php';</script>";
            exit;
        } else {
            echo "<script>alert('Database error: " . addslashes($conn->error) . "'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Not enough stock for that product.'); window.history.back();</script>";
    }

    $conn->close();
}
?>
