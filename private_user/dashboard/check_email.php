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

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);
$emails = isset($data['emails']) ? $data['emails'] : [$data['email']];

// Normalize emails by trimming whitespace
$emails = array_map('trim', $emails);

// Initialize response
$response = ['exists' => true, 'error' => null];

// Check for duplicate emails
if (count($emails) !== count(array_unique($emails))) {
    $response['exists'] = false;
    $response['error'] = 'Duplicate emails found in input.';
    echo json_encode($response);
    $conn->close();
    exit;
}

// Check each email against the database
foreach ($emails as $email) {
    $email = $conn->real_escape_string($email); // Escape email for SQL query
    $sql = "SELECT id FROM login_user WHERE email_address = '$email'";

    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        $response['exists'] = false;
        $response['error'] = 'One or more emails do not exist in the database.';
        break;  // Stop checking further if one email does not exist
    }
}

// Return final response
echo json_encode($response);

// Close the database connection
$conn->close();
?>
