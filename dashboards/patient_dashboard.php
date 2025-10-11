<?php
session_start();
include '../engines/db.php';
include("../dashboards/dock.php");

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['doctor', 'admin'])) {
    header("Location: login.html");
    exit();
}

$sql = "SELECT * FROM patients ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Clinic Dashboard | Glance Optical</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/clinic.css">
  <link rel="stylesheet" href="../css/dock.css">
</head>

<body>

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
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while($row = $result->fetch_assoc()): ?>
                <tr class="patient-row"
                    data-id="<?= $row['id'] ?>"
                    data-name="<?= htmlspecialchars($row['name']) ?>"
                    data-complaint="<?= htmlspecialchars($row['complaint']) ?>"
                    data-diagnosis="<?= htmlspecialchars($row['diagnosis']) ?>"
                    data-lenses="<?= htmlspecialchars($row['prescribed_lenses']) ?>"
                    data-notes="<?= htmlspecialchars($row['notes']) ?>">
                  <td><?= $row['id'] ?></td>
                  <td><?= $row['name'] ?></td>
                  <td><?= $row['complaint'] ?></td>
                  <td><?= $row['diagnosis'] ?></td>
                  <td><?= $row['prescribed_lenses'] ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No patients found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- 🔹 Patient Detail Modal -->
  <div class="modal fade" id="patientModal" tabindex="-1" aria-labelledby="patientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="patientModalLabel">Patient Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p><strong>Name:</strong> <span id="modalName"></span></p>
          <p><strong>Complaint:</strong> <span id="modalComplaint"></span></p>
          <p><strong>Diagnosis:</strong> <span id="modalDiagnosis"></span></p>
          <p><strong>Prescribed Lenses:</strong> <span id="modalLenses"></span></p>
          <p><strong>Notes:</strong></p>
          <div class="notes-box" id="modalNotes"></div>
        </div>

        <div class="modal-footer d-flex justify-content-between">
          <div>
            <a id="editLink" href="#" class="btn btn-warning">✏ Edit</a>
            <a id="deleteLink" href="#" class="btn btn-danger"
               onclick="return confirm('Are you sure you want to delete this patient?');">🗑 Delete</a>
          </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Modal Script -->
  <script>
  document.querySelectorAll('.patient-row').forEach(row => {
    row.addEventListener('click', function() {
      const id = this.dataset.id;
      document.getElementById('modalName').innerText = this.dataset.name;
      document.getElementById('modalComplaint').innerText = this.dataset.complaint;
      document.getElementById('modalDiagnosis').innerText = this.dataset.diagnosis;
      document.getElementById('modalLenses').innerText = this.dataset.lenses;
      document.getElementById('modalNotes').innerText = this.dataset.notes;

      document.getElementById('editLink').href = `../modules/edit_patient.php?id=${id}`;
      document.getElementById('deleteLink').href = `../modules/delete_patient.php?id=${id}`;

      const modal = new bootstrap.Modal(document.getElementById('patientModal'));
      modal.show();
    });
  });
  </script>

</body>
</html>
