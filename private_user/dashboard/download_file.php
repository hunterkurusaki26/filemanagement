<?php
if (isset($_GET['id'])) {
    $fileId = $_GET['id'];

    $conn = new mysqli("localhost", "root", "", "file_management");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT filename, file_data FROM files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $stmt->bind_result($fileName, $fileData);
    $stmt->fetch();

    if ($fileData) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $fileData;
    } else {
        echo 'File not found';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid file ID';
}
?>
