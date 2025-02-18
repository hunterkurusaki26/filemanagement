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

$sender = $_SESSION['email_address'];

// Retrieve the input data from the request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate the input data
if (!isset($input['fileId'], $input['recipient'])) {
    echo json_encode(["success" => false, "message" => "Invalid input. File ID and recipient are required."]);
    exit;
}

$fileId = intval($input['fileId']);
$recipient = htmlspecialchars($input['recipient']);

// Check if the file exists and belongs to the sender
$stmt = $conn->prepare("SELECT * FROM acceptedsign WHERE id = ? AND uploader = ?");
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

// Insert a record into file_transfers for the recipient
$stmt = $conn->prepare("INSERT INTO signtransfer (file_id, filename, filesize, sender, recipient, transferred_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("issss", $fileId, $filename, $filesize, $sender, $recipient);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "File sent successfully to $recipient.",
        "fileDetails" => [
            "filename" => $filename,
            "filesize" => number_format($filesize) . " bytes",
            "recipient" => $recipient
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to send file.", "error" => $conn->error]);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
