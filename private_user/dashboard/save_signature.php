<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_id = $_POST['document_id'];
    $signature_data = $_POST['signature_data'];
    $signature_file = $_FILES['signature'];

    // Decode base64 signature data
    $imageData = explode(',', $signature_data)[1];
    $imageData = base64_decode($imageData);

    // Generate unique file name for the signature
    $signaturePath = 'uploads/signatures/' . uniqid() . '.png';

    // Save the signature to the file system
    if (file_put_contents($signaturePath, $imageData)) {
        // Handle the file upload (optional, if you also want to handle the PNG signature file upload)
        if ($signature_file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/signatures/';
            $fileTmpPath = $signature_file['tmp_name'];
            $fileName = basename($signature_file['name']);
            move_uploaded_file($fileTmpPath, $uploadDir . $fileName);
        }

        // Save signature details to the database
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "file_management";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO signed_documents (document_id, signature_path, signed_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $document_id, $signaturePath);

        if ($stmt->execute()) {
            echo "Signature saved successfully.";
        } else {
            echo "Error saving signature.";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Error saving signature image.";
    }
}
?>
