<?php
// add_report.php
session_start();
include("../engines/db.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$role = $_SESSION['role'] ?? 'staff';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // basic validation & sanitize
    $product_name = trim($_POST['product_name']);
    $quantity     = (int) $_POST['quantity'];
    $unit_price   = (float) $_POST['unit_price'];
    $total        = (float) $_POST['total'];
    $customer     = trim($_POST['customer_name']);
    $payment      = trim($_POST['payment_method']);
    $card_type    = trim($_POST['card_type'] ?? '');
    $date_of_sale = $_POST['date_of_sale'] ?? date('Y-m-d');
    $recorded_by  = $_SESSION['username'] ?? 'Unknown';

    $stmt = $conn->prepare("INSERT INTO sales_records (product_name, quantity, unit_price, total, customer_name, payment_method, card_type, date_of_sale, recorded_by, recorded_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("siddsssss", $product_name, $quantity, $unit_price, $total, $customer, $payment, $card_type, $date_of_sale, $recorded_by);

    if ($stmt->execute()) {
        header("Location: ../dashboards/reports_dashboard.php?msg=added");
        exit();
    } else {
        die("Insert failed: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/reports.css">
</head>
<body>
<div class="container mt-4">
  <div class="card p-4">
    <h4>Add Manual Sale Record</h4>
    <form method="POST">
      <div class="mb-2">
        <label>Product</label>
        <input name="product_name" class="form-control" required>
      </div>
      <div class="mb-2 row">
        <div class="col">
          <label>Quantity</label>
          <input type="number" name="quantity" class="form-control" value="1" min="1" required>
        </div>
        <div class="col">
          <label>Unit Price</label>
          <input type="number" name="unit_price" class="form-control" step="0.01" required>
        </div>
      </div>
      <div class="mb-2">
        <label>Total</label>
        <input type="number" name="total" class="form-control" step="0.01" required>
      </div>
      <div class="mb-2">
        <label>Customer</label>
        <input name="customer_name" class="form-control">
      </div>
      <div class="mb-2 row">
        <div class="col">
          <label>Payment Method</label>
          <select name="payment_method" class="form-select">
            <option>Cash</option>
            <option>Card</option>
            <option>GCash</option>
          </select>
        </div>
        <div class="col">
          <label>Card Type</label>
          <input name="card_type" class="form-control">
        </div>
      </div>

      <div class="mb-2">
        <label>Date of Sale</label>
        <input type="date" name="date_of_sale" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>

      <button class="btn btn-primary">Save</button>
      <a href="../dashboards/reports_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
</body>
</html>
