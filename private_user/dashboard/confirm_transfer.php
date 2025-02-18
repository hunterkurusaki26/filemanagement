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
$recipient = $_SESSION['email_address'];

// Retrieve and validate input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['fileId'], $input['action'])) {
    echo json_encode(["success" => false, "message" => "Invalid input. File ID and action are required."]);
    exit;
}

$fileId = intval($input['fileId']);
$action = $input['action'];  // 'confirm' to proceed, 'cancel' to abort

// Check if the recipient is in the transfer queue
$stmt = $conn->prepare("SELECT * FROM file_transfer_queue WHERE file_id = ? AND recipient = ? AND transfer_status = 'pending'");
$stmt->bind_param("is", $fileId, $recipient);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "No pending transfer found for this file."]);
    $stmt->close();
    $conn->close();
    exit;
}

$transfer = $result->fetch_assoc();
$transferId = $transfer['id'];

// Update the transfer status based on action
if ($action == 'confirm') {
    // Mark this transfer as completed
    $stmt = $conn->prepare("UPDATE file_transfer_queue SET transfer_status = 'completed', completed_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $transferId);
    $stmt->execute();

    // Get the next recipient in the queue
    $stmt = $conn->prepare("SELECT * FROM file_transfer_queue WHERE file_id = ? AND transfer_order = ?");
    $stmt->bind_param("ii", $fileId, $transfer['transfer_order'] + 1);
    $stmt->execute();
    $nextRecipient = $stmt->get_result()->fetch_assoc();

    // If there is a next recipient, update their transfer status to 'pending'
    if ($nextRecipient) {
        $stmt = $conn->prepare("UPDATE file_transfer_queue SET transfer_status = 'pending' WHERE id = ?");
        $stmt->bind_param("i", $nextRecipient['id']);
        $stmt->execute();
    }

    echo json_encode(["success" => true, "message" => "File transfer completed. Waiting for next recipient."]);
} elseif ($action == 'cancel') {
    // Mark transfer as failed and abort the process
    $stmt = $conn->prepare("UPDATE file_transfer_queue SET transfer_status = 'failed' WHERE id = ?");
    $stmt->bind_param("i", $transferId);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "File transfer cancelled."]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid action."]);
}

$stmt->close();
$conn->close();
?>
