<?php
$uploadDir = "uploads/"; // Ensure this is the correct relative path

if (isset($_GET['file'])) {
    // Sanitize the file name
    $fileName = basename($_GET['file']);
    $file = $uploadDir . $fileName;

    // Debugging: Output the resolved file path
    echo "Resolved file path: " . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . "<br>";

    // Check if the file exists and is readable
    if (file_exists($file) && is_readable($file)) {
        // Set headers for file download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        // Debugging: Provide more detailed error information
        http_response_code(404);
        echo "Error: File does not exist or cannot be read.<br>";
        echo "Path: " . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . "<br>";
        echo "file_exists: " . (file_exists($file) ? 'true' : 'false') . "<br>";
        echo "is_readable: " . (is_readable($file) ? 'true' : 'false') . "<br>";
        exit;
    }
} else {
    http_response_code(400);
    echo "Error: Invalid request. Missing file parameter.";
}
?>
