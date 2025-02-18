<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "file_management";  // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search query from the URL
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Prepare the response array
$response = ['suggestions' => []];

if ($query) {
    $query = $conn->real_escape_string($query);  // Escape input to prevent SQL injection
    $sql = "SELECT email_address FROM login_user WHERE email_address LIKE '$query%' LIMIT 5";  // Limit to 5 results

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['suggestions'][] = $row['email_address'];
        }
    }
}

// Return the suggestions as a JSON response
echo json_encode($response);

$conn->close();
?>
