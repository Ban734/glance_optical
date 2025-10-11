<?php
session_start();
include '../engines/db.php';

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['doctor', 'admin'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $complaint = $_POST['complaint'];
    $diagnosis = $_POST['diagnosis'];
    $lenses = $_POST['prescribed_lenses'];
    $notes = $_POST['notes'];

    $sql = "INSERT INTO patients (name, complaint, diagnosis, prescribed_lenses, notes) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $complaint, $diagnosis, $lenses, $notes);
    $stmt->execute();

    header("Location: ../dashboards/patient_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Patient - Glance Optical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/clinic.css">
    <link rel="stylesheet" href="../css/dock.css" >
</head>
<body class="NavBar">

<div class="container mt-5">
    <h2 class="mb-4">Add New Patient</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Complaint</label>
            <textarea name="complaint" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Diagnosis</label>
            <textarea name="diagnosis" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Prescribed Lenses</label>
            <input type="text" name="prescribed_lenses" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Patient</button>
        <a href="../dashboards/patient_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
