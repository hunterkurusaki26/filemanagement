<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection and other code...

// Database connection
$host = 'localhost';
$dbname = 'file_management';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Check for file ID and token
if (isset($_GET['id']) && isset($_GET['token'])) {
    $fileId = (int) $_GET['id'];
    $token = $_GET['token'];
    
    // Fetch file from the database and validate token
    $stmt = $pdo->prepare("SELECT name, mime_type, file_data FROM files WHERE id = ? AND token = ?");
    $stmt->bindParam(1, $fileId, PDO::PARAM_INT);
    $stmt->bindParam(2, $token, PDO::PARAM_STR);
    $stmt->execute();
    
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($file) {
        // Set headers to force download
        header("Content-Type: " . $file['mime_type']);
        header("Content-Disposition: attachment; filename=\"" . $file['name'] . "\"");
        header("Content-Length: " . strlen($file['file_data']));
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Output the file content
        echo $file['file_data'];
    } else {
        echo "File not found or invalid token.";
    }
} else {
    echo "Invalid request.";
}

?>
