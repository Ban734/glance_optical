<?php
// update_report.php
session_start();
include("../engines/db.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Missing id");
}
$id = (int) $_GET['id'];

// fetch
$stmt = $conn->prepare("SELECT * FROM sales_records WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Record not found");
$record = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $quantity     = (int) $_POST['quantity'];
    $unit_price   = (float) $_POST['unit_price'];
    $total        = (float) $_POST['total'];
    $customer     = trim($_POST['customer_name']);
    $payment      = trim($_POST['payment_method']);
    $card_type    = trim($_POST['card_type'] ?? '');
    $date_of_sale = $_POST['date_of_sale'];

    $u = $conn->prepare("UPDATE sales_records SET product_name=?, quantity=?, unit_price=?, total=?, customer_name=?, payment_method=?, card_type=?, date_of_sale=? WHERE id=?");
    $u->bind_param("siddssssi", $product_name, $quantity, $unit_price, $total, $customer, $payment, $card_type, $date_of_sale, $id);
    if ($u->execute()) {
        header("Location: ../dashboards/reports_dashboard.php?msg=updated");
        exit();
    } else {
        die("Update failed: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Record</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/reports.css">
</head>
<body>
<div class="container mt-4">
  <div class="card p-4">
    <h4>Edit Record #<?= $record['id'] ?></h4>
    <form method="POST">
      <div class="mb-2">
        <label>Product</label>
        <input name="product_name" class="form-control" required value="<?= htmlspecialchars($record['product_name']) ?>">
      </div>
      <div class="mb-2 row">
        <div class="col">
          <label>Quantity</label>
          <input type="number" name="quantity" class="form-control" value="<?= (int)$record['quantity'] ?>" min="1" required>
        </div>
        <div class="col">
          <label>Unit Price</label>
          <input type="number" name="unit_price" class="form-control" step="0.01" required value="<?= htmlspecialchars($record['unit_price']) ?>">
        </div>
      </div>
      <div class="mb-2">
        <label>Total</label>
        <input type="number" name="total" class="form-control" step="0.01" required value="<?= htmlspecialchars($record['total']) ?>">
      </div>
      <div class="mb-2">
        <label>Customer</label>
        <input name="customer_name" class="form-control" value="<?= htmlspecialchars($record['customer_name']) ?>">
      </div>
      <div class="mb-2 row">
        <div class="col">
          <label>Payment Method</label>
          <select name="payment_method" class="form-select">
            <option <?= $record['payment_method']==='Cash' ? 'selected':'' ?>>Cash</option>
            <option <?= $record['payment_method']==='Card' ? 'selected':'' ?>>Card</option>
            <option <?= $record['payment_method']==='GCash' ? 'selected':'' ?>>GCash</option>
          </select>
        </div>
        <div class="col">
          <label>Card Type</label>
          <input name="card_type" class="form-control" value="<?= htmlspecialchars($record['card_type']) ?>">
        </div>
      </div>

      <div class="mb-2">
        <label>Date of Sale</label>
        <input type="date" name="date_of_sale" class="form-control" value="<?= htmlspecialchars($record['date_of_sale']) ?>">
      </div>

      <button class="btn btn-primary">Save</button>
      <a href="../dashboards/reports_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
</body>
</html>
