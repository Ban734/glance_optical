<?php
session_start();
include("../dashboards/dock.php");

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Toast handling
$toast_msg = '';
$toast_success = false;
if (isset($_SESSION['toast_msg'])) {
    $toast_msg = $_SESSION['toast_msg'];
    $toast_success = isset($_SESSION['toast_success']) && $_SESSION['toast_success'];
    unset($_SESSION['toast_msg'], $_SESSION['toast_success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Glance Optical</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/dock.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
  <button class="pane-toggle" type="button" aria-expanded="false" aria-controls="sidePane">☰ Menu</button>

  <div class="side-pane" id="sidePane" role="dialog" aria-hidden="true">
    <h3>Admin Panel</h3>
    <a href="../modules/manage_accounts.php">Manage Accounts</a>
    <a href="../dashboards/admin_dashboard.php">Create Account</a>
    <a href="../modules/audit_logs.php">Audit Logs</a>
  </div>

  <div id="formNotification" class="notification" aria-live="polite" role="status"></div>

  <main class="center-wrapper">
    <div class="card create-card">
      <h2>Create New User Account</h2>

      <form id="createUserForm" action="../modules/create_staff.php" method="POST" novalidate>
        <div class="form-group">
          <label>Full Name <small id="fullNameCount">(0)</small></label>
          <input id="full_name" type="text" name="full_name" maxlength="50" placeholder="Enter full name" required>
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input id="phone" type="text" name="phone" maxlength="15" placeholder="09XXXXXXXXX" required>
        </div>

        <div class="form-group">
          <label>Birthday</label>
          <input id="birthday" type="date" name="birthday" required>
        </div>

        <div class="form-group">
          <label>Address <small id="addressCount">(0)</small></label>
          <input id="address" type="text" name="address" maxlength="100" placeholder="Enter address" required>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input id="email" type="email" name="email" placeholder="example@email.com" required>
        </div>

        <div class="form-group">
          <label>Username <small id="usernameCount">(0)</small></label>
          <input id="username" type="text" name="username" maxlength="30" placeholder="Create username" required>
        </div>

        <div class="form-group password-group">
          <label>Password</label>
          <div class="password-wrapper">
            <input id="password" type="password" name="password" placeholder="Create password" required>
            <button type="button" class="toggle-password" data-target="password">👁️</button>
          </div>
        </div>

        <div class="form-group password-group">
          <label>Confirm Password</label>
          <div class="password-wrapper">
            <input id="confirm_password" type="password" name="confirm_password" placeholder="Confirm password" required>
            <button type="button" class="toggle-password" data-target="confirm_password">👁️</button>
          </div>
        </div>

        <div class="form-group">
          <label>Role</label>
          <select id="role" name="role" required>
            <option value="" disabled selected>Select role</option>
            <option value="staff">Staff</option>
            <option value="doctor">Doctor</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <button id="submitBtn" type="submit" class="submit-btn">Create Account</button>
      </form>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Pane toggle
    (function() {
      const toggle = document.querySelector('.pane-toggle');
      const side = document.getElementById('sidePane');
      toggle.addEventListener('click', () => {
        const open = side.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    })();

    // Toast display (fixed centered modal)
(function() {
  const toastEl = document.getElementById('formNotification');
  const serverMsg = <?php echo json_encode($toast_msg); ?>;
  const serverSuccess = <?php echo json_encode($toast_success); ?>;

  function showToast(message, success = false) {
    if (!message) return;
    toastEl.textContent = message;
    toastEl.classList.remove('success', 'error', 'show-toast');
    toastEl.classList.add(success ? 'success' : 'error');
    setTimeout(() => toastEl.classList.add('show-toast'), 10);
    setTimeout(() => toastEl.classList.remove('show-toast'), 4000);
  }

  if (serverMsg) showToast(serverMsg, serverSuccess);
  window.showToast = showToast;
})();

// Input validation & character restriction
(function() {
  const form = document.getElementById('createUserForm');
  const username = document.getElementById('username');
  const fullName = document.getElementById('full_name');
  const address = document.getElementById('address');
  const phone = document.getElementById('phone');
  const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');
  const email = document.getElementById('email');

  const usernameCount = document.getElementById('usernameCount');
  const fullNameCount = document.getElementById('fullNameCount');
  const addressCount = document.getElementById('addressCount');

  // Only digits for phone
  phone.addEventListener('input', () => {
    phone.value = phone.value.replace(/\D/g, '');
  });

  // Counters with strict stop
  function setupCounter(input, counter) {
    const max = parseInt(input.getAttribute('maxlength'), 10);
    input.addEventListener('input', () => {
      if (input.value.length > max) input.value = input.value.slice(0, max);
      counter.textContent = `(${input.value.length}/${max})`;
      counter.style.color = input.value.length >= max ? '#b23b3b' : '#6E412A';
    });
  }
  setupCounter(fullName, fullNameCount);
  setupCounter(address, addressCount);
  setupCounter(username, usernameCount);

  // Password show/hide toggle
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.textContent = 'Show';
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      const hidden = target.type === 'password';
      target.type = hidden ? 'text' : 'password';
      btn.textContent = hidden ? 'Hide' : 'Show';
    });
  });

  // Validation patterns
  function validUsername(u) { return /^[A-Za-z0-9_]{8,30}$/.test(u); }
  function validPhone(p) { return /^[0-9]{10,15}$/.test(p); }
  function validEmail(e) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }
  function strongPassword(p) { return /^(?=.{8,})(?=.*[A-Za-z])(?=.*\d).*$/.test(p); }

  form.addEventListener('submit', evt => {
    evt.preventDefault();
    const u = username.value.trim();
    const ph = phone.value.trim();
    const em = email.value.trim();
    const pw = password.value;
    const cpw = confirmPassword.value;

    if (!validUsername(u)) return showToast('❌ Username must be 8–30 chars (letters, numbers, underscores).');
    if (!validPhone(ph)) return showToast('❌ Phone must be 10–15 digits.');
    if (!validEmail(em)) return showToast('❌ Enter a valid email.');
    if (!strongPassword(pw)) return showToast('❌ Password must be 8+ chars with letters and numbers.');
    if (pw !== cpw) return showToast('❌ Passwords do not match.');

    form.submit();
  });
})();
  </script>
</body>
</html>
