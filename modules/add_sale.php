<?php
include_once '../engines/db_connection.php';
session_start();
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name   = $_POST['product_name'] ?? '';
    $quantity       = (int) ($_POST['quantity'] ?? 0);
    $unit_price     = (float) ($_POST['unit_price'] ?? 0.0);
    $discount       = isset($_POST['discount']) ? (float) $_POST['discount'] : (float) ($_POST['discount_select'] ?? 0);
    $customer_name  = trim($_POST['customer_name'] ?? '');
    $date_of_sale   = $_POST['date_of_sale'] ?? date('Y-m-d');
    $recorded_by    = $_SESSION['username'] ?? 'unknown';
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $card_type      = $_POST['card_type'] ?? 'N/A';
    $card_number    = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
    $payment_status = $_POST['payment_status'] ?? null;

    if ($customer_name === '') $customer_name = 'N/A';

    $original_price = round($unit_price * $quantity, 2);
    $discount_value = round(($original_price * ($discount / 100)), 2);
    $final_total = round($original_price - $discount_value, 2);

    if (in_array($payment_method, ['Cash', 'GCash'])) {
        $payment_status = 'Paid';
        $card_type = 'N/A';
        $card_number = 'N/A';
    } else {
        $payment_status = $payment_status ?: 'Pending';
        $len = strlen($card_number);
        if ($len < 12 || $len > 16) {
            echo sweetAlert('error', 'Invalid Card', 'Card number must be between 12–16 digits.', true);
            exit;
        }
    }

    if (strtotime($date_of_sale) > strtotime(date('Y-m-d'))) {
        echo sweetAlert('error', 'Invalid Date', 'Cannot record sales for future dates.', true);
        exit;
    }

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
            (product_name, quantity, unit_price, original_price, discount_percentage, total, customer_name, date_of_sale, recorded_by, recorded_at, payment_method, card_type, card_number, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)
        ");

        if (!$stmt) {
            echo sweetAlert('error', 'Database Error', addslashes($conn->error), true);
            exit;
        }

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
            echo sweetAlert('success', 'Sale Recorded!', 'The sale has been recorded successfully.', false, '../dashboards/staff_dashboard.php');
            exit;
        } else {
            echo sweetAlert('error', 'Database Error', addslashes($conn->error), true);
            exit;
        }
    } else {
        echo sweetAlert('warning', 'Out of Stock', 'Not enough stock for that product.', true);
    }

    $conn->close();
}

function sweetAlert($icon, $title, $text, $back = false, $redirect = '')
{
    $redirectScript = $redirect
        ? "setTimeout(()=>{window.location.href='$redirect';},1500);"
        : ($back ? "setTimeout(()=>{window.history.back();},1500);" : '');
    return "
    <html><head>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head><body>
    <script>
    Swal.fire({
        icon: '$icon',
        title: '$title',
        text: '$text',
        confirmButtonColor: '#3085d6',
        background: '#fefefe',
        color: '#333',
        timer: 1500,
        showConfirmButton: false
    }).then(()=>{ $redirectScript });
    </script>
    </body></html>";
}
?>
