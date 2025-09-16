<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login | Glance Optical</title>
  <link href='https://fonts.googleapis.com/css?family=DM Sans' rel='stylesheet'>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="d-flex justify-content-center align-items-center vh-100">
  
  <img src="images/logo.png.png" alt="Glance Logo" class="logo-img"> 

  <div class="wrapper">
    <div class="loginContainer">
      <div class="login-box p-4 shadow rounded">
        <form action="functions/loginform.php" method="POST">

          <div class="mb-3">
            <input type="text" name="username" placeholder="Username" class="form-control" required>
          </div>

          <div class="input-group mb-3">
            <input type="password" name="password" placeholder="Password" id="password" class="form-control" required>
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
              <i class="bi bi-eye"></i>
            </button>
          </div>

          <button type="submit" class="btn btn-primary w-100">Log In</button>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center p-4">
        <h5 class="text-danger">Incorrect Login</h5>
        <p>Username or Password is incorrect. Please try again.</p>
        <button type="button" class="btn btn-danger w-50 mx-auto" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>

  <script>
    document.getElementById("togglePassword").addEventListener("click", function () {
      let passwordField = document.getElementById("password");
      let icon = this.querySelector("i");

      if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      } else {
        passwordField.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      }
    });
  </script>

  <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      let errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
      errorModal.show();
    });
  </script>
<?php endif; ?>

</body>
</html>