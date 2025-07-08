<?php
include 'db.php'; // Connect to database

$message = '';

// Handle form submission: upload file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];

        $fileName = $_FILES['document']['name'];
        $fileTmpPath = $_FILES['document']['tmp_name'];
        $fileSize = $_FILES['document']['size'];
        $uploadedBy = htmlspecialchars($_POST['uploaded_by']);

        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            $message = "Error: Only PDF, DOC, DOCX, PPT, PPTX files are allowed.";
        } elseif ($fileSize > 10 * 1024 * 1024) {
            $message = "Error: File size must be under 10MB.";
        } else {
            $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $fileName);
            $destination = 'uploads/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                $sql = "INSERT INTO documents (file_name, file_path, uploaded_by) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $fileName, $destination, $uploadedBy);
                $stmt->execute();

                $message = "Success: File uploaded by <strong>$uploadedBy</strong>.";
            } else {
                $message = "Error: Failed to move uploaded file.";
            }
        }
    } else {
        $message = "Error: No file uploaded or something went wrong.";
    }
}

// Fetch all uploaded documents to display
$sql = "SELECT * FROM documents ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upload & View Documents</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f1f4f9;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: white;
      padding: 30px 40px;
      border-radius: 8px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    h2 {
      color: #1b4b84;
      margin-bottom: 20px;
      text-align: center;
    }
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #222;
    }
    input[type="file"],
    input[type="text"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #bbb;
      border-radius: 4px;
      margin-bottom: 20px;
      font-size: 15px;
    }
    button {
      width: 100%;
      padding: 12px;
      background-color: #1b4b84;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background-color: #163c6b;
    }
    .message {
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
      text-align: center;
      font-weight: 600;
    }
    .success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }
    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #1b4b84;
      color: white;
    }
    tr:hover {
      background-color: #f1f1f1;
    }
    .download-btn {
      padding: 6px 12px;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 14px;
    }
    .download-btn:hover {
      background-color: #218838;
    }
    .no-data {
      text-align: center;
      margin-top: 40px;
      color: #888;
      font-style: italic;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Upload Academic Document</h2>

    <?php if ($message): ?>
      <div class="message <?php echo (strpos($message, 'Success') === 0) ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
      <label for="document">Select Document (PDF, DOC, DOCX, PPT, PPTX)</label>
      <input type="file" name="document" id="document" required>

      <label for="uploaded_by">Your Full Name</label>
      <input type="text" name="uploaded_by" id="uploaded_by" required placeholder="e.g. Mary John">

      <button type="submit">Upload Document</button>
    </form>

    <h2>Available Uploaded Documents</h2>

    <?php if ($result && $result->num_rows > 0): ?>
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
  </div>

</body>
</html>
