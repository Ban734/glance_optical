<?php
session_start();
include("../dashboards/dock.php");
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sales Dashboard | Glance Optical</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/dock.css">
    <link rel="stylesheet" href="css/staff.css">
</head>

<body>
<div class="container mt-4">
    <div class="card p-4 shadow">
        <h4>Add New Sale</h4>

        <?php
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
                    <input type="number" name="quantity" id="quantityInput" class="form-control" min="1" value="1" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Discount (%)</label>
                    <div class="d-flex gap-2">
                      <select id="discountSelect" name="discount_select" class="form-select" required>
                          <option value="0" selected>0 — No Discount</option>
                          <option value="5">5 — Family</option>
                          <option value="10">10 — Student</option>
                          <option value="15">15 — PWD</option>
                          <option value="20">20 — Senior Citizen</option>
                          <option value="custom">Custom</option>
                      </select>
                      <input id="discountCustom" name="discount_custom" class="form-control" type="number" min="0" max="100" placeholder="Custom %" style="display:none;">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Total (₱)</label>
                    <input type="text" id="displayTotal" class="form-control" readonly>
                </div>
            </div>

            <div class="row g-3 mt-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <select id="paymentMethod" name="payment_method" class="form-select" required>
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="GCash">GCash</option>
                    </select>
                </div>

                <div class="col-md-4" id="cardFields" hidden>
                    <label class="form-label">Card Number</label>
                    <input type="text" name="card_number" id="cardNumber" class="form-control" maxlength="16" placeholder="12–16 digits">
                </div>

                <div class="col-md-4" id="cardTypeGroup" hidden>
                    <label class="form-label">Card Type</label>
                    <select name="card_type" id="cardType" class="form-select">
                        <option value="Visa">Visa</option>
                        <option value="MasterCard">MasterCard</option>
                        <option value="BDO">BDO</option>
                    </select>
                </div>

                <div class="col-md-4" id="paymentStatusGroup" hidden>
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" id="paymentStatus" class="form-select">
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>

            <!-- hidden fields that are reliably sent to server -->
            <input type="hidden" name="unit_price" id="hiddenUnitPrice">
            <input type="hidden" name="original_price" id="hiddenOriginalPrice">
            <input type="hidden" name="total" id="hiddenTotal">
            <input type="hidden" name="discount" id="hiddenDiscount">

            <div class="mt-3">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" id="customerName" class="form-control" placeholder="Enter customer name">
            </div>

            <div class="mt-3">
                <label class="form-label">Date of Sale</label>
                <input type="date" name="date_of_sale" class="form-control"
                       value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <button type="submit" class="btn btn-success mt-3">Save Sale</button>
        </form>

        <?php
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
                        <th>Original Price (₱)</th>
                        <th>Discount (%)</th>
                        <th>Final Total (₱)</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th>Card Type</th>
                        <th>Card Number</th>
                        <th>Status</th>
                        <th>Recorded By</th>
                        <th>Time</th>
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
                            <td><?php echo number_format((float)$row['original_price'], 2); ?></td>
                            <td><?php echo (float)$row['discount_percentage']; ?>%</td>
                            <td><?php echo number_format((float)$row['total'], 2); ?></td>
                            <td><?php echo ($row['customer_name'] !== '' ? htmlspecialchars($row['customer_name']) : 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td><?php echo ($row['card_type'] ?: 'N/A'); ?></td>
                            <td><?php echo ($row['card_number'] ?: 'N/A'); ?></td>
                            <td><?php echo ($row['payment_status'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['recorded_by']); ?></td>
                            <td><?php echo date('h:i A', strtotime($row['recorded_at'])); ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="13" class="text-center">No records for today.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const productData = <?php echo json_encode($productMap); ?>;
const productSelect = document.getElementById('productSelect');
const quantityInput = document.getElementById('quantityInput');
const discountSelect = document.getElementById('discountSelect');
const discountCustom = document.getElementById('discountCustom');
const displayTotal = document.getElementById('displayTotal');
const hiddenUnitPrice = document.getElementById('hiddenUnitPrice');
const hiddenTotal = document.getElementById('hiddenTotal');
const hiddenOriginalPrice = document.getElementById('hiddenOriginalPrice');
const hiddenDiscount = document.getElementById('hiddenDiscount');

let basePrice = 0;

function getDiscountValue() {
    if (discountSelect.value === 'custom') {
        const v = parseFloat(discountCustom.value || '0');
        return Math.min(Math.max(isNaN(v) ? 0 : v, 0), 100);
    }
    return Math.min(Math.max(parseFloat(discountSelect.value || '0'), 0), 100);
}

function computeAndRender() {
    const qty = Math.max(1, parseInt(quantityInput.value || '1', 10));
    const discount = getDiscountValue();
    const original = +(basePrice * qty);
    let total = original;
    if (discount > 0) total = +(original - (original * (discount / 100)));
    // normalize to 2 decimals
    const originalFixed = original ? original.toFixed(2) : '0.00';
    const totalFixed = total ? total.toFixed(2) : '0.00';
    displayTotal.value = totalFixed;
    hiddenUnitPrice.value = basePrice.toFixed(2);
    hiddenOriginalPrice.value = originalFixed;
    hiddenTotal.value = totalFixed;
    hiddenDiscount.value = discount.toFixed(2);
}

discountSelect.addEventListener('change', () => {
    if (discountSelect.value === 'custom') {
        discountCustom.style.display = 'block';
        discountCustom.focus();
    } else {
        discountCustom.style.display = 'none';
        discountCustom.value = '';
    }
    computeAndRender();
});
discountCustom.addEventListener('input', computeAndRender);

productSelect.addEventListener('change', () => {
    const name = productSelect.value;
    if (productData[name]) {
        basePrice = parseFloat(productData[name].price) || 0;
        quantityInput.value = 1;
        computeAndRender();
    } else {
        basePrice = 0;
        quantityInput.value = 1;
        displayTotal.value = '';
    }
});
quantityInput.addEventListener('input', computeAndRender);

// Payment logic
const paymentMethod = document.getElementById("paymentMethod");
const cardFields = document.getElementById("cardFields");
const cardNumber = document.getElementById("cardNumber");
const cardTypeGroup = document.getElementById("cardTypeGroup");
const paymentStatusGroup = document.getElementById("paymentStatusGroup");

paymentMethod.addEventListener("change", function() {
    const isCard = this.value === "Card";
    cardFields.hidden = !isCard;
    cardTypeGroup.hidden = !isCard;
    paymentStatusGroup.hidden = !isCard;

    // If not card, auto set status to Paid (front-end)
    if (!isCard) {
        document.getElementById("paymentStatus").value = "Paid";
    } else {
        document.getElementById("paymentStatus").value = "Pending";
    }
});

// Card number: digits only; limit 16; no helper text
cardNumber.addEventListener("input", function() {
    this.value = this.value.replace(/\D/g, "");
    if (this.value.length > 16) this.value = this.value.slice(0, 16);
});

// Ensure hidden fields and discount sent before submit
document.getElementById("saleForm").addEventListener("submit", function(e) {
    computeAndRender(); // populate hidden fields
    // ensure discount value is set into hiddenDiscount
    hiddenDiscount.value = getDiscountValue().toFixed(2);

    const method = paymentMethod.value;
    if (method === "Card") {
        const len = cardNumber.value.length;
        if (len < 12 || len > 16) {
            alert("Card number must be between 12 to 16 digits.");
            e.preventDefault();
            return;
        }
        if (!cardNumber.value || !document.getElementById('cardType').value) {
            alert("Card number and card type are required for card payments.");
            e.preventDefault();
            return;
        }
    }

    // validate discount numeric range
    const disc = parseFloat(hiddenDiscount.value || '0');
    if (isNaN(disc) || disc < 0 || disc > 100) {
        alert("Discount must be between 0 and 100.");
        e.preventDefault();
        return;
    }
});
</script>
</body>
</html>
