<?php
// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Content-Type to JSON
header('Content-Type: application/json');

// Include database connection
include 'db_connect.php'; 

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email_address'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Please log in."]);
    exit;
}

// Get the logged-in user's email
$uploader = $_SESSION['email_address'];

// Initialize an array to hold file data
$fileData = [];

// SQL query to fetch records for the logged-in user
$stmt = $conn->prepare("SELECT * FROM reject_files WHERE uploader = ? ORDER BY rejected_at DESC");
$stmt->bind_param("s", $uploader);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query returned results
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $fileData[] = [
            "id"         => $row['id'],
            "filename"   => htmlspecialchars($row['filename']),
            "fileSize"   => number_format($row['filesize']) . " bytes",
            "uploader"   => htmlspecialchars($row['uploader']),
            "rejectedAt" => $row['rejected_at'],
            "reason"     => nl2br(htmlspecialchars($row['reason']))
        ];
    }
    echo json_encode($fileData);
} else {
    echo json_encode(["success" => false, "message" => "Failed to fetch files.", "error" => $conn->error]);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
