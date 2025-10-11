<?php
session_start();
include("../dashboards/dock.php");

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
    <title>Inventory Dashboard | Glance Optical</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/dock.css">
    <link rel="stylesheet" href="css/staff.css">

</head>

<body>
<div class="container mt-5">

<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ New item added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

<?php elseif (isset($_GET['deleted'])): ?>
    <?php if ($_GET['deleted'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            🗑️ Item deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

<?php elseif (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] == 'duplicate'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            ⚠️ Item already exists in the inventory!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($_GET['error'] == 'insertfail'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ Failed to add item. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($_GET['error'] == 'deletefail'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ Failed to delete item. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <?php if ($_GET['updated'] == '1'): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            ✏️ Item updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>


  <div class="card p-4 shadow">
    <h4>Inventory Management</h4>

    <form action="../modules/add_inventory.php" method="POST" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Product Name</label>
            <input type="text" name="product_name" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Unit Price (₱)</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Add Item</button>
        </div>
    </form>
  </div>

  <div class="mt-4 card p-3">
      <h5>Current Inventory</h5>
      <table class="table table-bordered">
          <thead class="table-dark">
              <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Price (₱)</th>
                  <th>Last Updated</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
          <?php
          include_once '../engines/db_connection.php';
          $conn = connectDB();
          $result = $conn->query("SELECT * FROM inventory");

          if ($result->num_rows > 0):
              $count = 1;
              while ($row = $result->fetch_assoc()): ?>
                  <tr>
                      <td><?php echo $count++; ?></td>
                      <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                      <td><?php echo (int)$row['quantity']; ?></td>
                      <td><?php echo number_format((float)$row['price'], 2); ?></td>
                      <td><?php echo date('Y-m-d h:i A', strtotime($row['last_updated'])); ?></td>
                      <td>
  <!-- Delete Button -->
  <form action="../modules/delete_inventory.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
  </form>

  <!-- Edit Button (triggers modal) -->
  <button 
      type="button" 
      class="btn btn-warning btn-sm" 
      data-bs-toggle="modal" 
      data-bs-target="#editModal<?php echo $row['id']; ?>">
      Edit
  </button>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="../modules/update_inventory.php" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Edit Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <div class="mb-3">
                  <label class="form-label">Product Name</label>
                  <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($row['product_name']); ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Quantity</label>
                  <input type="number" name="quantity" class="form-control" min="1" value="<?php echo (int)$row['quantity']; ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Unit Price (₱)</label>
                  <input type="number" step="0.01" name="price" class="form-control" value="<?php echo (float)$row['price']; ?>" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</td>
                    </tr>
              <?php endwhile;
          else: ?>
              <tr><td colspan="5" class="text-center">No items found.</td></tr>
          <?php endif;
          $conn->close();
          ?>
          </tbody>
      </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
