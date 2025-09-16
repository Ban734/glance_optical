<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../index.html");
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Central Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            text-align: center;
        }
        h1 {
            margin-top: 30px;
        }
        .cards {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 50px;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
            width: 200px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
            cursor: pointer;
        }
        a {
            text-decoration: none;
            color: black;
        }
        .logout {
            margin-top: 40px;
        }
    </style>
</head>
<body>

<h1>Welcome, <?php echo $username; ?>!</h1>
<p>Select a module to continue</p>

<div class="cards">

    <?php if ($role === 'admin' || $role === 'staff'): ?>
        <a href="../dashboards/staff_dashboard.php">
            <div class="card">
                <h2>Sales</h2>
                <p>Track daily sales records</p>
            </div>
        </a>

        <a href="../dashboards/inventory_dashboard.php">
            <div class="card">
                <h2>Inventory</h2>
                <p>Manage stock levels</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if ($role === 'admin' || $role === 'staff'): ?>
        <a href="../dashboards/reports_dashboard.php">
            <div class="card">
                <h2>Reports</h2>
                <p>View summaries & analytics</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if ($role === 'admin' || $role === 'doctor'): ?>
        <a href="../dashboards/patient_dashboard.php">
            <div class="card">
                <h2>Clinic</h2>
                <p>Monitor patient records</p>
            </div>
        </a>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
        <a href="../dashboards/admin_dashboard.php">
            <div class="card">
                <h2>Admin</h2>
                <p>Manage users & system</p>
            </div>
        </a>
    <?php endif; ?>

</div>

<div class="logout">
    <a href="../functions/logout.php">Logout</a>
</div>

</body>
</html>
