<?php
session_start();
include '../engines/db.php';

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    header("Location: ../login.html");
    exit();
}

$sql = "SELECT * FROM patients ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard - Glance Optical</title>
    <link rel="stylesheet" href="../css/clinic.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar custom-navbar px-4">
        <span class="navbar-brand mb-0 h1">Doctor Dashboard</span>
        <span class="nav-user">
            Welcome, Dr. <?php echo $_SESSION['username']; ?> | 
            <a href="../functions/logout.php" class="logout-link">Logout</a>
        </span>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Patient Monitoring System</h2>

        <div class="mb-3">
            <a href="../modules/add_patient.php" class="btn btn-custom">➕ Add New Patient</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Patient Records</h5>
                <table class="table table-hover align-middle">
                    <thead class="table-header">
                        <tr>
                            <th>Patient ID</th>
                            <th>Name</th>
                            <th>Complaint</th>
                            <th>Diagnosis</th>
                            <th>Prescribed Lenses</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['complaint']; ?></td>
                                    <td><?php echo $row['diagnosis']; ?></td>
                                    <td><?php echo $row['prescribed_lenses']; ?></td>
                                    <td><?php echo $row['notes']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No patients found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>