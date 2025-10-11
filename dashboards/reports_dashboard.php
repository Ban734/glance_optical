<?php
session_start();
include("../dashboards/dock.php");

if (!isset($_SESSION['role'])) {
    header("Location: ../index.html");
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

include("../engines/db.php");


$salesQuery = "SELECT SUM(total) as total_sales FROM sales_records";
$salesResult = mysqli_query($conn, $salesQuery);
$salesRow = mysqli_fetch_assoc($salesResult);

$inventoryQuery = "SELECT COUNT(*) as total_items FROM inventory";
$inventoryResult = mysqli_query($conn, $inventoryQuery);
$inventoryRow = mysqli_fetch_assoc($inventoryResult);


$dailyQuery = "
    SELECT date_of_sale, id, product_name, quantity, unit_price, total, customer_name, payment_method, card_type, recorded_by, recorded_at
    FROM sales_records
    ORDER BY date_of_sale DESC, recorded_at DESC
";
$dailyResult = mysqli_query($conn, $dailyQuery);


$cardQuery = "SELECT * FROM sales_records WHERE payment_method = 'Card' ORDER BY date_of_sale DESC";
$cardResult = mysqli_query($conn, $cardQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports Dashboard | Glance Optical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/reports.css">
    <link rel="stylesheet" href="../css/dock.css">
</head>
<body>

<div class="container mt-4">

    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#overview">Overview</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#daily">Daily Records</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cards">Card Payments</a></li>
    </ul>

    <div class="tab-content mt-4">

        <div class="tab-pane fade show active" id="overview">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow p-4 text-center">
                        <h3>Total Sales</h3>
                        <p class="fs-4 text-success">₱<?php echo number_format($salesRow['total_sales'] ?? 0, 2); ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow p-4 text-center">
                        <h3>Total Inventory Items</h3>
                        <p class="fs-4 text-primary"><?php echo $inventoryRow['total_items'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="daily">
            <h4 class="mb-3">📅 Sales by Day</h4>
            <div class="accordion" id="dailyAccordion">
                <?php
                $currentDate = null;
                $index = 0;
                while ($row = mysqli_fetch_assoc($dailyResult)):
                    if ($currentDate !== $row['date_of_sale']):
                        if ($currentDate !== null) echo "</tbody></table></div></div></div>";
                        $currentDate = $row['date_of_sale'];
                        $index++;
                        ?>
                        <div class="accordion-item mb-2">
                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>">
                                    <?php echo "📅 " . date("F d, Y", strtotime($currentDate)); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" data-bs-parent="#dailyAccordion">
                                <div class="accordion-body">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th>Customer</th>
                                                <th>Payment</th>
                                                <th>Card Type</th>
                                                <th>Recorded By</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        <?php
                    endif;
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo (int)$row['quantity']; ?></td>
                            <td>₱<?php echo number_format($row['unit_price'], 2); ?></td>
                            <td>₱<?php echo number_format($row['total'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td><?php echo $row['payment_method'] === 'Card' ? htmlspecialchars($row['card_type']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                            <td><?php echo date('h:i A', strtotime($row['recorded_at'])); ?></td>
                        </tr>
                <?php endwhile;
                if ($currentDate !== null) echo "</tbody></table></div></div></div>";
                ?>
            </div>
        </div>

        <div class="tab-pane fade" id="cards">
            <h4 class="mb-3">💳 Card Payment Records</h4>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Total</th>
                        <th>Customer</th>
                        <th>Card Type</th>
                        <th>Date</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($cardResult) > 0):
                        while ($row = mysqli_fetch_assoc($cardResult)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td>₱<?php echo number_format($row['total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['card_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_of_sale']); ?></td>
                                <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr><td colspan="7" class="text-center">No card payments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>