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
$sender = $_SESSION['email_address'];

// Retrieve and validate input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['fileId'], $input['recipients']) || !is_array($input['recipients'])) {
    echo json_encode(["success" => false, "message" => "Invalid input. File ID and recipients are required."]);
    exit;
}

$fileId = intval($input['fileId']);
$recipients = array_map('htmlspecialchars', $input['recipients']);

// Check if the file exists and belongs to the sender
$stmt = $conn->prepare("SELECT * FROM accepted_files WHERE id = ? AND uploader = ?");
$stmt->bind_param("is", $fileId, $sender);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "File not found or unauthorized access."]);
    $stmt->close();
    $conn->close();
    exit;
}

$file = $result->fetch_assoc();
$filename = $file['filename'];
$filesize = $file['filesize'];

// Insert records into file_transfer_queue for each recipient
$transferOrder = 1;
foreach ($recipients as $recipient) {
    // Insert into file_transfer_queue with filename and filesize
    $stmt = $conn->prepare("INSERT INTO file_transfer_queue (file_id, filename, filesize, sender, recipient, transfer_order) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $fileId, $filename, $filesize, $sender, $recipient, $transferOrder);
    $stmt->execute();
    $transferOrder++;
}

echo json_encode([
    "success" => true,
    "message" => "File sent successfully to the recipients.",
    "fileDetails" => [
        "filename" => $filename,
        "filesize" => number_format($filesize) . " bytes",
        "recipients" => implode(", ", $recipients)
    ]
]);

// Close the database connection
$stmt->close();
$conn->close();
?>
