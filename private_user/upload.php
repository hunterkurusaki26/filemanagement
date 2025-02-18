<?php
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

// Handle file upload
// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    if ($fileSize > 0) {
        // Generate a secure random token for this file
        $token = bin2hex(random_bytes(16));

        // Read file content into a variable
        $fileData = file_get_contents($fileTmpName);
        
        // Insert file and token into the database
        $stmt = $pdo->prepare("INSERT INTO files (name, mime_type, file_data, token) VALUES (?, ?, ?, ?)");
        $stmt->bindParam(1, $fileName);
        $stmt->bindParam(2, $fileType);
        $stmt->bindParam(3, $fileData, PDO::PARAM_LOB);
        $stmt->bindParam(4, $token);
        $stmt->execute();
        
        // Get the inserted file ID
        $fileId = $pdo->lastInsertId();
        
        // Generate a link with the file ID and token
        $downloadLink = "http://192.168.1.15/download.php?id=" . $fileId . "&token=" . $token;

        // Return the download link
        echo json_encode(["link" => $downloadLink]);
    } else {
        echo json_encode(["error" => "File upload failed."]);
    }
} else {
    echo json_encode(["error" => "No file uploaded."]);
}

?>
