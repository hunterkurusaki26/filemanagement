<?php
// Include the database connection
include 'db_connect.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email_address'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Please log in."]);
    exit;
}

// Prepare the query to fetch files for the logged-in user
$stmt = $conn->prepare("SELECT * FROM files WHERE uploader = ?");
$stmt->bind_param("s", $_SESSION['email_address']); // Filter by logged-in user
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold file data
$fileData = [];

while ($row = $result->fetch_assoc()) {
    $fileData[] = [
        "id" => $row['id'],
        "filename" => htmlspecialchars($row['filename'], ENT_QUOTES, 'UTF-8'),
        "fileSize" => number_format($row['filesize'], 0, '.', ',') . " bytes",
        "uploader" => htmlspecialchars($row['uploader'], ENT_QUOTES, 'UTF-8'),
        "dateTime" => $row['datetime'],
        "status" => htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8')
    ];
}

// Send the data as JSON response
echo json_encode($fileData);
?>
