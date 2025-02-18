<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'include/connection.php';

header('Content-Type: application/json');

// Get the unique ID from the query string
$uniqueId = isset($_GET['unique_id']) ? htmlspecialchars($_GET['unique_id'], ENT_QUOTES, 'UTF-8') : null;

if (!$uniqueId) {
    echo json_encode(["success" => false, "message" => "Invalid unique ID."]);
    exit;
}

try {
    // Query to fetch the file link from the database
    $query = "SELECT file_link FROM badges WHERE unique_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Failed to prepare the SQL statement: " . $conn->error);
    }

    $stmt->bind_param("s", $uniqueId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "file_link" => htmlspecialchars($row['file_link'], ENT_QUOTES, 'UTF-8'),
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No file found for the provided unique ID."]);
    }

    $stmt->close();
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error in fetch_file.php: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An error occurred. Please try again later."]);
}

$conn->close();
?>
