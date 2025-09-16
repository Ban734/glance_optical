<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Staff Dashboard</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/staff.css">
</head>

<body>
<div class="container mt-4">

    <div class="d-flex justify-content-between mb-3">
        <a href="../dashboards/master_dashboard.php" class="btn btn-secondary">
            ⬅ Back to Main Dashboard
        </a>
        <a href="../functions/logout.php" class="btn btn-danger">
            🚪 Logout
        </a>
    </div>    

    <div class="card p-4 shadow">
        <h4>Add New Sale</h4>

        <?php
        // Dito yung function para sa drop-down ng item lists.
        include_once '../engines/db_connection.php';
        $conn = connectDB();
        $res  = $conn->query("SELECT id, product_name, price FROM inventory WHERE quantity > 0");

        $productRows = [];
        $productMap  = [];
        while ($row = $res->fetch_assoc()) {
            $productRows[] = $row;
            $productMap[$row['product_name']] = $row;
        }
        $conn->close();
        ?>

        <form action="../modules/add_sale.php" method="POST" id="saleForm">
    <div class="row g-3 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Product</label>
            <select id="productSelect" name="product_name" class="form-select" required>
                <option value="" selected disabled>Select a product...</option>
                <?php foreach ($productRows as $row): ?>
                    <option value="<?php echo htmlspecialchars($row['product_name']); ?>">
                        <?php echo "ID#{$row['id']} — {$row['product_name']}"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" id="quantityInput"
                   class="form-control" min="1" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Discount (%)</label>
            <select id="discountSelect" name="discount" class="form-select">
                <option value="0">No Discount</option>
                <option value="5">5%</option>
                <option value="10">10%</option>
                <option value="15">15%</option>
                <option value="20">20%</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Total (₱)</label>
            <input type="text" id="displayTotal" class="form-control" readonly>
        </div>
    </div>

    
    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <label class="form-label">Payment Method</label>
            <select id="paymentMethod" name="payment_method" class="form-select" required>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="GCash">GCash</option>
            </select>
        </div>

        
        <div class="col-md-4" id="cardTypeGroup" style="display: none;">
            <label class="form-label">Card Type</label>
            <input type="text" name="card_type" id="cardType" class="form-control" placeholder="e.g. Visa, MasterCard">
        </div>
    </div>

    <input type="hidden" name="unit_price" id="hiddenUnitPrice">  
    <input type="hidden" name="total" id="hiddenTotal">

    <div class="mt-3">
        <label class="form-label">Customer Name</label>
        <input type="text" name="customer_name" class="form-control" placeholder="Walk-in">
    </div>

    <div class="mt-3">
        <label class="form-label">Date of Sale</label>
        <input type="date" name="date_of_sale" class="form-control"
               value="<?php echo date('Y-m-d'); ?>" required>
    </div>

    <button type="submit" class="btn btn-success mt-3">Save Sale</button>
</form>

        <?php
        // PHP logic ng sales ngayong araw
        include_once '../engines/db_connection.php';
        $conn  = connectDB();
        $today = date('Y-m-d');

        $stmt = $conn->prepare("SELECT * FROM sales_records WHERE date_of_sale = ?");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>

        <div class="mt-5">
    <h4>Today's Recorded Sales (<?php echo $today; ?>)</h4>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price (₱)</th>
                <th>Total (₱)</th>
                <th>Customer</th>
                <th>Payment Method</th>
                <th>Card Type</th>
                <th>Recorded By</th>
                <th>Time Recorded</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0):
            $count = 1;
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo (int)$row['quantity']; ?></td>
                    <td><?php echo number_format((float)$row['unit_price'], 2); ?></td>
                    <td><?php echo number_format((float)$row['total'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                    <td><?php echo $row['payment_method'] === 'card' ? htmlspecialchars($row['card_type']) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                    <td><?php echo date('h:i A', strtotime($row['recorded_at'])); ?></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="10" class="text-center">No records for today.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById("paymentMethod").addEventListener("change", function() {
    let cardGroup = document.getElementById("cardTypeGroup");
    if (this.value === "Card") {
        cardGroup.style.display = "block";
    } else {
        cardGroup.style.display = "none";
    }
});
</script>


<!--script para sa calculations-->
<script>

    const productData = <?php echo json_encode($productMap); ?>;

    const productSelect   = document.getElementById('productSelect');
    const quantityInput   = document.getElementById('quantityInput');
    const discountSelect  = document.getElementById('discountSelect');
    const displayTotal    = document.getElementById('displayTotal');

    const hiddenUnitPrice = document.getElementById('hiddenUnitPrice');
    const hiddenTotal     = document.getElementById('hiddenTotal');    

    let basePrice = 0; 

    function computeAndRender() {
        const qty = Math.max(1, parseInt(quantityInput.value || '1', 10));
        const discount = parseFloat(discountSelect.value || '0');

        let total = basePrice * qty;
        if (discount > 0) total -= total * (discount / 100);

        displayTotal.value = total.toFixed(2);

        hiddenUnitPrice.value = basePrice.toFixed(2);
        hiddenTotal.value     = total.toFixed(2);
    }

    productSelect.addEventListener('change', () => {
        const name = productSelect.value;
        if (productData[name]) {
            basePrice = parseFloat(productData[name].price) || 0;

            quantityInput.value = 1;
            computeAndRender();
        } else {
            basePrice = 0;
            quantityInput.value = '';
            displayTotal.value = '';
            hiddenUnitPrice.value = '';
            hiddenTotal.value = '';
        }
    });

    quantityInput.addEventListener('input', computeAndRender);
    discountSelect.addEventListener('change', computeAndRender);
</script>
</body>

</html>