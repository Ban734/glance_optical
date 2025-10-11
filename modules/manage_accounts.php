<?php
session_start();
include("../dashboards/dock.php");
include("../engines/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit();
}

// 🔹 Fetch all account info (now includes new fields)
$query = "SELECT id, full_name, username, email, phone, birthday, address, role FROM users ORDER BY role DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Accounts | Glance Optical</title>
  <link rel="stylesheet" href="../css/dock.css">
  <link rel="stylesheet" href="../css/manage_accounts.css">
</head>
<body>

  <div class="content">
    <h2>Manage Accounts</h2>
    <?php if (isset($_GET['msg'])): ?>
  <div class="msg <?= htmlspecialchars($_GET['msg']); ?>">
    <?= $_GET['msg'] === 'updated' ? 'Account Updated Successfully!' : ''; ?>
    <?= $_GET['msg'] === 'deleted' ? 'Account Deleted Successfully!' : ''; ?>
  </div>
<?php endif; ?>


    <!-- 🔹 Side Pane Toggle -->
    <button class="pane-toggle" type="button" aria-expanded="false" aria-controls="sidePane">☰ Menu</button>

    <!-- 🔹 Side Pane -->
    <div class="side-pane" id="sidePane" role="dialog" aria-hidden="true">
      <h3>Admin Panel</h3>
      <a href="../modules/manage_accounts.php">Manage Accounts</a>
      <a href="../dashboards/admin_dashboard.php">Create Account</a>
      <a href="../modules/audit_logs.php">Audit Logs</a>
    </div>

    <!-- 🔹 Accounts Grid -->
    <div class="accounts-container">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="account-card"
             data-id="<?= $row['id']; ?>"
             data-name="<?= htmlspecialchars($row['full_name']); ?>"
             data-username="<?= htmlspecialchars($row['username']); ?>"
             data-email="<?= htmlspecialchars($row['email']); ?>"
             data-phone="<?= htmlspecialchars($row['phone']); ?>"
             data-birthday="<?= htmlspecialchars($row['birthday']); ?>"
             data-address="<?= htmlspecialchars($row['address']); ?>"
             data-role="<?= htmlspecialchars($row['role']); ?>">

          <h3><?= htmlspecialchars($row['username']); ?></h3>
          <p><strong>Role:</strong> <?= htmlspecialchars($row['role']); ?></p>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- 🔹 Modal -->
  <div class="custom-modal-bg" id="accountModal">
    <div class="custom-modal">
      <span class="close-btn">&times;</span>
      <p><strong>Full Name:</strong> <span id="modalName"></span></p>
      <p><strong>Username:</strong> <span id="modalUsername"></span></p>
      <p><strong>Email:</strong> <span id="modalEmail"></span></p>
      <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
      <p><strong>Birthday:</strong> <span id="modalBirthday"></span></p>
      <p><strong>Address:</strong> <span id="modalAddress"></span></p>
      <p><strong>Role:</strong> <span id="modalRole"></span></p>

      <div class="custom-modal-actions">
        <form action="update_account.php" method="GET">
          <input type="hidden" name="id" id="updateId">
          <button type="submit" class="update-btn">Update</button>
        </form>

        <form action="delete_account.php" method="POST" onsubmit="return confirm('Delete this account?');">
          <input type="hidden" name="id" id="deleteId">
          <button type="submit" class="delete-btn">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <!-- 🔹 Modal Script -->
  <script>
  document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll(".account-card");
    const modalBg = document.getElementById("accountModal");
    const closeBtn = document.querySelector(".close-btn");

    cards.forEach(card => {
      card.addEventListener("click", () => {
        document.getElementById("modalName").textContent = card.dataset.name;
        document.getElementById("modalUsername").textContent = card.dataset.username;
        document.getElementById("modalEmail").textContent = card.dataset.email;
        document.getElementById("modalPhone").textContent = card.dataset.phone;
        document.getElementById("modalBirthday").textContent = card.dataset.birthday;
        document.getElementById("modalAddress").textContent = card.dataset.address;
        document.getElementById("modalRole").textContent = card.dataset.role;

        document.getElementById("updateId").value = card.dataset.id;
        document.getElementById("deleteId").value = card.dataset.id;

        modalBg.classList.add("visible");
      });
    });

    closeBtn.addEventListener("click", () => {
      modalBg.classList.remove("visible");
    });

    modalBg.addEventListener("click", (e) => {
      if (e.target === modalBg) modalBg.classList.remove("visible");
    });
  });
  </script>

  <!-- 🔹 Pane Toggle Script -->
  <script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.querySelector(".pane-toggle");
    const sidePane = document.getElementById("sidePane");

    if (!toggleBtn || !sidePane) return;

    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      const opening = !sidePane.classList.contains("open");
      sidePane.classList.toggle("open");
      sidePane.setAttribute("aria-hidden", String(!opening));
      toggleBtn.setAttribute("aria-expanded", String(opening));
    });

    sidePane.addEventListener("click", function (e) {
      e.stopPropagation();
    });

    document.addEventListener("click", function (e) {
      if (sidePane.classList.contains("open")) {
        if (!sidePane.contains(e.target) && e.target !== toggleBtn) {
          sidePane.classList.remove("open");
          sidePane.setAttribute("aria-hidden", "true");
          toggleBtn.setAttribute("aria-expanded", "false");
        }
      }
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && sidePane.classList.contains("open")) {
        sidePane.classList.remove("open");
        sidePane.setAttribute("aria-hidden", "true");
        toggleBtn.setAttribute("aria-expanded", "false");
      }
    });
  });
  </script>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const msg = document.querySelector(".msg");
    if (msg) {
      setTimeout(() => {
        msg.style.animation = "fadeOut 1s forwards";
      }, 1000); // waits 3 seconds before fading out
    }
  });
</script>

</body>
</html>
