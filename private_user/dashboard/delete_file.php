<?php
include 'db_connect.php'; // Include database connection

$uploadDir = "uploads/"; // Directory where files are stored
$inputData = json_decode(file_get_contents('php://input'), true);

if (isset($inputData['filename'])) {
    $filename = basename($inputData['filename']); // Get the filename
    $filePath = $uploadDir . $filename; // Construct the file path

    // Delete file record from the database
    $stmt = $conn->prepare("DELETE FROM files WHERE filename = ?");
    $stmt->bind_param("s", $filename);

    if ($stmt->execute()) {
        // If database record deleted successfully, remove the physical file
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file from the uploads directory
        }
        echo json_encode(["success" => true, "message" => "File deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete file from database."]);
    }
    $stmt->close();
    exit;
}

// If the request is invalid
http_response_code(400);
echo json_encode(["success" => false, "message" => "Invalid request."]);
?>
