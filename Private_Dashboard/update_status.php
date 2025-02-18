<?php
// Start the session and check if the admin is logged in
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Include the database connection
require_once("include/connection.php");

// Set the response header to JSON
header('Content-Type: application/json');

// Function to send JSON responses
function send_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method.');
}

// Retrieve and sanitize POST data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

// Validate inputs
if ($id <= 0 || !in_array($action, ['accept', 'reject'])) {
    send_response(false, 'Invalid parameters.');
}

// Define source and destination tables
$tables = ['files', 'sign_docu'];
$destination_accept = ['accepted_files', 'acceptedsign'];
$destination_reject = ['reject_files', 'rejectsign'];

try {
    $conn->begin_transaction();

    foreach ($tables as $index => $source) {
        // Check if the file exists in the current table
        $stmt = $conn->prepare("SELECT filename, filesize, uploader FROM $source WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($filename, $filesize, $uploader);
        if (!$stmt->fetch()) {
            $stmt->close();
            continue; // File not found in this table, check the next table
        }
        $stmt->close();

        // Determine the target table based on the action (accept/reject)
        if ($action === 'accept') {
            $target = $destination_accept[$index];
            // Insert into the accepted table
            $stmt = $conn->prepare("INSERT INTO $target (filename, filesize, uploader, accepted_at) VALUES (?, ?, ?, NOW())");
        } else {
            // Validate rejection reason
            if (empty($reason)) {
                throw new Exception('Rejection reason is required.');
            }
            $target = $destination_reject[$index];
            // Insert into the rejected table
            $stmt = $conn->prepare("INSERT INTO $target (filename, filesize, uploader, status, reason, rejected_at) VALUES (?, ?, ?, 'Rejected', ?, NOW())");
            $stmt->bind_param("siss", $filename, $filesize, $uploader, $reason);
        }
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $stmt->bind_param("sis", $filename, $filesize, $uploader);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert into ' . $target . ': ' . $stmt->error);
        }
        $stmt->close();

        // Delete from the source table
        $stmt = $conn->prepare("DELETE FROM $source WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete from ' . $source . ': ' . $stmt->error);
        }
        $stmt->close();

        // Break the loop if a match is found
        break;
    }

    $conn->commit();
    send_response(true, 'File ' . ($action === 'accept' ? 'accepted' : 'rejected') . ' successfully.');
} catch (Exception $e) {
    $conn->rollback();
    send_response(false, $e->getMessage());
}
?>
