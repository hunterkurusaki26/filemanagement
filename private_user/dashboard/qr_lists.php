<?php
include 'db_connect.php'; // Ensure this connects to the `file_management` database

header('Content-Type: application/json');

// Initialize an array to store badge data
$badges = [];

try {
    // Query to fetch all data from the badges table
    $query = "SELECT * FROM badges ORDER BY created_at DESC";
    $result = $conn->query($query);

    // Fetch rows and populate the badges array
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $badges[] = [
                "name" => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
                "file_name" => htmlspecialchars($row['file_name'], ENT_QUOTES, 'UTF-8'),
                "file_link" => htmlspecialchars($row['file_link'], ENT_QUOTES, 'UTF-8'),
                "created_at" => $row['created_at']
            ];
        }
    }

    // Return the badges array as JSON
    echo json_encode($badges);
} catch (Exception $e) {
    echo json_encode(["error" => "Error fetching data: " . $e->getMessage()]);
}
?>
