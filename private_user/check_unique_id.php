<?php
include 'include/connection.php'; // Update the path as needed

header('Content-Type: application/json');

try {
    // Decode incoming JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    $unique_id = isset($data['unique_id']) ? trim($data['unique_id']) : null;

    $response = ['exists' => false]; // Default response

    if ($unique_id) {
        // Prepare SQL statement to check for the unique ID
        $stmt = $conn->prepare("SELECT COUNT(*) FROM badges WHERE unique_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Database error during prepare.");
        }

        // Bind the parameter and execute
        $stmt->bind_param("s", $unique_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        // Update the response if a match is found
        if ($count > 0) {
            $response['exists'] = true;
        }
    } else {
        $response['error'] = "Invalid or missing unique ID.";
    }

    echo json_encode($response);
} catch (Exception $e) {
    error_log("Error in check_unique_id.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
