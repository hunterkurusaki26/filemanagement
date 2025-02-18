<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$password = ''; // Set your MySQL root password if applicable
$database = 'file_management';

// Connect to the database
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? 'Anonymous';
    $role = $_POST['role'] ?? 'Unknown';
    $company = $_POST['company'] ?? 'None';
    $uniqueID = $_POST['unique_id'] ?? uniqid('ID-');

    // Ensure the "QRgenerated" directory exists
    $uploadDir = __DIR__ . '/QRgenerated/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Check if a file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $destinationPath = $uploadDir . $safeFileName;

        if (move_uploaded_file($fileTmpPath, $destinationPath)) {
            $fileUrl = 'QRgenerated/' . $safeFileName;

            // Insert the record into the database
            $stmt = $conn->prepare("INSERT INTO badges (name, role, company, file_name, file_link, unique_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssss', $name, $role, $company, $safeFileName, $fileUrl, $uniqueID);

            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'File uploaded and record saved successfully.',
                    'link' => $fileUrl,
                    'unique_id' => $uniqueID
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save record in the database.'
                ]);
            }

            $stmt->close();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to move the uploaded file.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No file uploaded or an error occurred.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}

$conn->close();
?>
