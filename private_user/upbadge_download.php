<?php
// Database connection
$servername = "localhost";
$username = "root"; // update with your username
$password = ""; // update with your password
$dbname = "file_management"; // replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the file link by unique ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT file_link FROM badges WHERE id = $id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fileLink = $row["file_link"];

        // Serve the file as a download
        if (file_exists($fileLink)) {
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=" . basename($fileLink));
            readfile($fileLink);
            exit;
        } else {
            echo "File not found.";
        }
    } else {
        echo "Invalid ID.";
    }
} else {
    echo "ID not specified.";
}

$conn->close();
?>
