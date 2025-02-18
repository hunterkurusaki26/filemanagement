<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "file_management");
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Join query for `files` and `send_docu` tables
$query = "
    SELECT 
        f.id AS file_id,
        f.filename AS file_name,
        f.filesize AS file_size,
        f.uploader AS file_uploader,
        f.status AS file_status,
        f.datetime AS file_datetime,
        s.id AS send_id,
        s.filename AS send_filename,
        s.filesize AS send_filesize,
        s.uploader AS send_uploader,
        s.status AS send_status,
        s.datetime AS send_datetime
    FROM 
        files f
    INNER JOIN 
        send_docu s ON f.id = s.id
";

$result = $conn->query($query);

$data = [{
    "file_id": 1,
    "filename": "",
    "filesize": "",
    "uploader": "",
    "status": "",
    "datetime": ""
}];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'file_id' => $row['file_id'],
            'file_name' => htmlspecialchars($row['file_name']),
            'file_size' => round($row['file_size'] / 1024, 2) . ' KB',
            'file_uploader' => htmlspecialchars($row['file_uploader']),
            'file_status' => htmlspecialchars($row['file_status']),
            'file_datetime' => htmlspecialchars($row['file_datetime']),
            'send_filename' => htmlspecialchars($row['send_filename']),
            'send_filesize' => round($row['send_filesize'] / 1024, 2) . ' KB',
            'send_uploader' => htmlspecialchars($row['send_uploader']),
            'send_status' => htmlspecialchars($row['send_status']),
            'send_datetime' => htmlspecialchars($row['send_datetime']),
        ];
    }
}

$conn->close();

// Output data in JSON format
echo json_encode(['data' => $data]);
?>
