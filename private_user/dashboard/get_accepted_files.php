<?php
// get_accepted_files.php

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the uploads directory
$uploadDir = "uploads/"; // Ensure this path is correct relative to this script

// Check if 'file' parameter is set in the GET request
if (isset($_GET['file'])) {
    // Sanitize the filename to prevent directory traversal
    $filename = basename($_GET['file']);
    $filePath = $uploadDir . $filename;

    // Check if the file exists
    if (file_exists($filePath)) {
        // Set headers to initiate file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));

        // Clear output buffering
        ob_clean();
        flush();

        // Read the file and send it to the output buffer
        readfile($filePath);
        exit;
    } else {
        // File does not exist
        http_response_code(404);
        echo "File does not exist.";
        exit;
    }
}

// If 'file' parameter is not set, return an error
http_response_code(400);
echo "Invalid request.";
?>
