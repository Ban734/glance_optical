<?php
session_start();
include '../engines/db.php';

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['doctor','admin'])) {
    header("Location: ../login.html");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Invalid patient ID");
}

// If form submitted, update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $complaint = $_POST['complaint'];
    $diagnosis = $_POST['diagnosis'];
    $lenses = $_POST['prescribed_lenses'];
    $notes = $_POST['notes'];

    $sql = "UPDATE patients SET name=?, complaint=?, diagnosis=?, prescribed_lenses=?, notes=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $complaint, $diagnosis, $lenses, $notes, $id);
    $stmt->execute();

    header("Location: ../dashboards/patient_dashboard.php");
    exit();
}

// Fetch existing data
$sql = "SELECT * FROM patients WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Edit Patient</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo $patient['name']; ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Complaint</label>
            <textarea name="complaint" class="form-control"><?php echo $patient['complaint']; ?></textarea>
        </div>
        <div class="mb-3">
            <label>Diagnosis</label>
            <textarea name="diagnosis" class="form-control"><?php echo $patient['diagnosis']; ?></textarea>
        </div>
        <div class="mb-3">
            <label>Prescribed Lenses</label>
            <input type="text" name="prescribed_lenses" value="<?php echo $patient['prescribed_lenses']; ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control"><?php echo $patient['notes']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="../dashboards/patient_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>