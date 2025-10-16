<?php
// reports_dashboard.php
session_start();
include("../dashboards/dock.php");
include("../engines/db.php");

if (!isset($_SESSION['role'])) {
    header("Location: ../index.html");
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Handle filter inputs (sanitized)
$filter_date_from = $_GET['from'] ?? '';
$filter_date_to   = $_GET['to'] ?? '';
$filter_search    = trim($_GET['q'] ?? '');
$filter_payment   = $_GET['payment'] ?? '';
$show_archived    = ($_GET['archived'] ?? '0') === '1';

// Build base query for totals and counts (only non-archived)
$totalsWhere = $show_archived ? "1=1" : "archived = 0";
$salesQuery = "SELECT SUM(total) as total_sales FROM sales_records WHERE $totalsWhere";
$salesResult = mysqli_query($conn, $salesQuery);
$salesRow = mysqli_fetch_assoc($salesResult);

$inventoryQuery = "SELECT COUNT(*) as total_items FROM inventory";
$inventoryResult = mysqli_query($conn, $inventoryQuery);
$inventoryRow = mysqli_fetch_assoc($inventoryResult);

// Build daily records query with simplified filters; only non-archived unless archived requested
$whereClauses = [];
if (!$show_archived) $whereClauses[] = "archived = 0";
if ($filter_date_from) $whereClauses[] = "date_of_sale >= '". $conn->real_escape_string($filter_date_from) ."'";
if ($filter_date_to)   $whereClauses[] = "date_of_sale <= '". $conn->real_escape_string($filter_date_to) ."'";
if ($filter_payment)   $whereClauses[] = "payment_method = '". $conn->real_escape_string($filter_payment) ."'";
if ($filter_search) {
    $q = $conn->real_escape_string($filter_search);
    $whereClauses[] = "(product_name LIKE '%$q%' OR customer_name LIKE '%$q%' OR recorded_by LIKE '%$q%')";
}

$whereSQL = count($whereClauses) ? implode(' AND ', $whereClauses) : '1=1';

$dailyQuery = "
    SELECT date_of_sale, id, product_name, quantity, unit_price, total, customer_name, payment_method, card_type, recorded_by, recorded_at, archived
    FROM sales_records
    WHERE $whereSQL
    ORDER BY date_of_sale DESC, recorded_at DESC
";

$dailyResult = mysqli_query($conn, $dailyQuery);

// Card-specific
$cardQuery = "SELECT * FROM sales_records WHERE payment_method = 'Card' " . (!$show_archived ? " AND archived = 0 " : "") . " ORDER BY date_of_sale DESC";
$cardResult = mysqli_query($conn, $cardQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports Dashboard | Glance Optical</title>
  <!-- keep bootstrap; your dock.php already injects CSS but including here is OK -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/reports.css">
  <link rel="stylesheet" href="../css/dock.css">
</head>
<body>

<div class="container mt-4">

    <div class="report-controls d-flex gap-2 mb-3 align-items-center">
      <form class="d-flex gap-2" method="get" id="filterForm" style="align-items:center;">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search product/customer/recorded by" value="<?= htmlspecialchars($filter_search); ?>">
        <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($filter_date_from); ?>">
        <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($filter_date_to); ?>">
        <select name="payment" class="form-select form-select-sm">
          <option value="">All Payments</option>
          <option value="Cash" <?= $filter_payment === 'Cash' ? 'selected':''; ?>>Cash</option>
          <option value="Card" <?= $filter_payment === 'Card' ? 'selected':''; ?>>Card</option>
          <option value="GCash" <?= $filter_payment === 'GCash' ? 'selected':''; ?>>GCash</option>
        </select>
        <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
      </form>

      <div class="ms-auto d-flex gap-2">
        <a href="../modules/add_report.php" class="btn btn-success btn-sm">+ Add Record</a>
        <a href="../modules/download_reports.php?<?= http_build_query($_GET); ?>" class="btn btn-primary btn-sm">📥 Download CSV</a>
      </div>
    </div>

    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#overview">Overview</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#daily">Daily Records</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cards">Card Payments</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#archived">Archived</a></li>
    </ul>

    <div class="tab-content mt-4">

        <!-- OVERVIEW -->
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

        <!-- DAILY -->
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
                                                <th>Actions</th>
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
                            <td>
                                <a class="btn btn-sm btn-warning" href="../modules/update_report.php?id=<?= $row['id'] ?>">Edit</a>
                                <?php if (!$row['archived']): ?>
                                  <form style="display:inline" action="../modules/archive_report.php" method="POST" onsubmit="return confirm('Archive this record?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button class="btn btn-sm btn-secondary">Archive</button>
                                  </form>
                                <?php else: ?>
                                  <span class="badge bg-secondary">Archived</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php endwhile;
                if ($currentDate !== null) echo "</tbody></table></div></div></div>";
                ?>
            </div>
        </div>

        <!-- CARDS -->
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
                        <th>Actions</th>
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
                                <td>
                                  <a class="btn btn-sm btn-warning" href="../modules/update_report.php?id=<?= $row['id'] ?>">Edit</a>
                                  <form style="display:inline" action="../modules/archive_report.php" method="POST" onsubmit="return confirm('Archive this record?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button class="btn btn-sm btn-secondary">Archive</button>
                                  </form>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr><td colspan="8" class="text-center">No card payments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ARCHIVED -->
        <div class="tab-pane fade" id="archived">
            <h4 class="mb-3">🗂️ Archived Records</h4>
            <?php
              // show archived rows with restore / permanent delete
              $archQ = "SELECT * FROM sales_records WHERE archived = 1 ORDER BY date_of_sale DESC, recorded_at DESC";
              $archR = mysqli_query($conn, $archQ);
            ?>
            <table class="table table-bordered">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Total</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Recorded By</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($archR) > 0):
                  while ($r = mysqli_fetch_assoc($archR)): ?>
                    <tr>
                      <td><?= $r['id'] ?></td>
                      <td><?= htmlspecialchars($r['product_name']) ?></td>
                      <td>₱<?= number_format($r['total'],2) ?></td>
                      <td><?= htmlspecialchars($r['customer_name']) ?></td>
                      <td><?= htmlspecialchars($r['date_of_sale']) ?></td>
                      <td><?= htmlspecialchars($r['recorded_by']) ?></td>
                      <td>
                        <form style="display:inline" action="restore_report.php" method="POST">
                          <input type="hidden" name="id" value="<?= $r['id'] ?>">
                          <button class="btn btn-sm btn-success">Restore</button>
                        </form>

                        <?php if ($role === 'admin'): ?>
                        <form style="display:inline" action="delete_report_permanent.php" method="POST" onsubmit="return confirm('Permanently delete?');">
                          <input type="hidden" name="id" value="<?= $r['id'] ?>">
                          <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile;
                else: ?>
                  <tr><td colspan="7" class="text-center">No archived records.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
