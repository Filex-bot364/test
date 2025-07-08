<?php
include 'db.php'; // Connect to MySQL

// Fetch all uploaded documents
$sql = "SELECT * FROM documents ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Uploaded Documents</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9f9f9;
      padding: 30px;
    }

    h2 {
      text-align: center;
      color: #1b4b84;
    }

    table {
      width: 100%;
      max-width: 900px;
      margin: 20px auto;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #1b4b84;
      color: #fff;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .download-btn {
      padding: 6px 10px;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 13px;
    }

    .download-btn:hover {
      background-color: #218838;
    }

    .no-data {
      text-align: center;
      margin-top: 50px;
      color: #888;
    }
  </style>
</head>
<body>

  <h2>📚 Uploaded Academic Documents</h2>

  <?php if ($result->num_rows > 0): ?>
    <table>
      <tr>
        <th>#</th>
        <th>File Name</th>
        <th>Uploaded By</th>
        <th>Date</th>
        <th>Download</th>
      </tr>

      <?php $counter = 1; while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo $counter++; ?></td>
          <td><?php echo htmlspecialchars($row['file_name']); ?></td>
          <td><?php echo htmlspecialchars($row['uploaded_by']); ?></td>
          <td><?php echo date('d M Y H:i', strtotime($row['uploaded_at'])); ?></td>
          <td><a class="download-btn" href="<?php echo $row['file_path']; ?>" download>Download</a></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p class="no-data">No documents uploaded yet.</p>
  <?php endif; ?>

</body>
</html>
