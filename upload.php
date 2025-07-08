<?php
include 'db.php'; // connect to the database

$message = '';  // to display success or error message

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
                // Save to database
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upload Academic Document</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f1f4f9;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .upload-container {
      background: #ffffff;
      padding: 30px 40px;
      border-radius: 6px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 450px;
    }

    .upload-container h2 {
      margin-top: 0;
      font-size: 22px;
      color: #333;
      text-align: center;
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 5px;
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

    .footer-note {
      text-align: center;
      font-size: 12px;
      color: #777;
      margin-top: 15px;
    }

    .message {
      padding: 10px;
      margin-bottom: 15px;
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
  </style>
</head>
<body>

  <div class="upload-container">
    <h2>Upload Academic Document</h2>

    <?php if ($message): ?>
      <div class="message <?php echo (strpos($message, 'Success') === 0) ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <form action="upload.php" method="POST" enctype="multipart/form-data">
      <label for="document">Select Document (PDF, DOC, DOCX, PPT, PPTX)</label>
      <input type="file" name="document" id="document" required>

      <label for="uploaded_by">Your Full Name</label>
      <input type="text" name="uploaded_by" id="uploaded_by" required placeholder="e.g. Mary John">

      <button type="submit">Upload Document</button>
    </form>

    <div class="footer-note">
      Note: Only academic files are allowed. Max 10MB.
    </div>
  </div>

</body>
</html>
