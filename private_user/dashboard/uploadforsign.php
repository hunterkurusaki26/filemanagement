<?php
session_start();
include 'db_connect.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['email_address'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

// Check if the request method is POST and a file is uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Validate the CSRF token if implemented
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["success" => false, "message" => "Invalid CSRF token."]);
        exit;
    }

    // Retrieve the logged-in user's email address from the session
    $uploader = $_SESSION['email_address'];

    // Define the upload directory
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        // Create the upload directory if it doesn't exist
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(["success" => false, "message" => "Failed to create upload directory."]);
            exit;
        }
    }

    $file = $_FILES['file'];
    $filename = basename($file['name']);
    $fileSize = $file['size'];
    $status = 'Pending'; // Default status for newly uploaded files
    $targetFile = $uploadDir . $filename;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png']; // Allowed file types
    $maxFileSize = 5 * 1024 * 1024; // Maximum file size in bytes (5 MB)

    // Validate the file size
    if ($fileSize > $maxFileSize) {
        echo json_encode(["success" => false, "message" => "File size exceeds the maximum allowed size of 5 MB."]);
        exit;
    }

    // Validate the file type
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(["success" => false, "message" => "Invalid file type. Allowed types: " . implode(', ', $allowedTypes)]);
        exit;
    }

    // Sanitize the filename
    $filename = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", basename($file['name']));

    // Prevent overwriting existing files by appending a timestamp
    if (file_exists($uploadDir . $filename)) {
        $filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . time() . '.' . $fileType;
        $targetFile = $uploadDir . $filename;
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // Insert file information into the database
        $stmt = $conn->prepare("INSERT INTO sign_docu (filename, filesize, uploader, datetime, status) VALUES (?, ?, ?, NOW(), ?)");
        if (!$stmt) {
            echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
            exit;
        }
        $stmt->bind_param("siss", $filename, $fileSize, $uploader, $status);
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "File uploaded successfully. Status set to Pending.",
                "unique_id" => $conn->insert_id
            ]);
        } else {
            // Delete the file if database insertion fails
            unlink($targetFile);
            echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Failed to upload file."]);
    }
    exit;
}

// If the request method is not POST or no file is uploaded, return an error
echo json_encode(["success" => false, "message" => "Invalid request."]);
?>
