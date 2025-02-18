<?php
// Enable error reporting for debugging
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

// Retrieve and validate input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['signedDocument'], $input['filename'], $input['fileId'], $input['nextRecipient'])) {
    echo json_encode(["success" => false, "message" => "Invalid input. Signed document, filename, file ID, and next recipient are required."]);
    exit;
}

$signedDocument = $input['signedDocument'];  // Assuming the document is base64-encoded
$filename = $input['filename'];
$fileId = intval($input['fileId']);
$nextRecipient = $input['nextRecipient'];

// Decode base64 to get the file content
$decodedFile = base64_decode($signedDocument);
if (!$decodedFile) {
    echo json_encode(["success" => false, "message" => "Failed to decode signed document."]);
    exit;
}

// Check if the file exists in the queue and is the sender's file
$stmt = $conn->prepare("SELECT * FROM file_transfer_queue WHERE file_id = ? AND sender = ? AND recipient = ?");
$stmt->bind_param("iss", $fileId, $sender, $sender);  // Ensure that the file is in the queue for the sender
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "File not found or unauthorized access."]);
    $stmt->close();
    $conn->close();
    exit;
}

// Get the original file details
$file = $result->fetch_assoc();
$filesize = $file['filesize'];

// Save the signed document to a file or database (adjust path as necessary)
$filePath = "signed_files/" . $filename;
file_put_contents($filePath, $decodedFile);

// Update the queue with the new file and send to the next recipient
$updateSql = "UPDATE file_transfer_queue SET recipient = ?, status = 'sent', transferred_at = NOW() WHERE file_id = ?";
$updateStmt = $conn->prepare($updateSql);

if (!$updateStmt) {
    echo json_encode(["success" => false, "message" => "Error preparing update statement."]);
    exit;
}

$updateStmt->bind_param("si", $nextRecipient, $fileId);
$updateStmt->execute();

if (!$updateStmt->execute()) {
    echo json_encode(["success" => false, "message" => "Error executing update statement."]);
    exit;
}

// Optionally, you could log the signed file transfer or any additional details

echo json_encode([
    "success" => true,
    "message" => "Signed document successfully sent to the next recipient.",
    "fileDetails" => [
        "filename" => $filename,
        "filesize" => number_format($filesize) . " bytes",
        "nextRecipient" => $nextRecipient
    ]
]);

$stmt->close();
$updateStmt->close();
$conn->close();
?>
