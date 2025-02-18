<?php
// Start the session to check the logged-in user
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "file_management";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['email_address'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Get the logged-in user's email
$loggedInUser = $_SESSION['email_address'];

// Fetch documents that need signing and their queue status for the logged-in user
$sql = "SELECT signtransfer.id, signtransfer.filename, file_transfer_queue.status
        FROM signtransfer
        LEFT JOIN file_transfer_queue ON signtransfer.id = file_transfer_queue.file_id
        WHERE signtransfer.recipient = ? AND file_transfer_queue.transfer_order = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUser); // Bind the logged-in user's email to the query
$stmt->execute();
$result = $stmt->get_result();

$documents = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $documents[] = [
            'id' => $row['id'],
            'filename' => $row['filename'],
            'queue_status' => $row['status']
        ];
    }
} else {
    $documents = [];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Documents</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/view_rejected_files.css"> <!-- Link to the external CSS -->
</head>

<body>
    <aside class="sidebar">
        <div class="header">
            <img src="" alt="Logo">
        </div>
        <a href="../home.php"><i class="fas fa-tachometer-alt"></i> <span>User Dashboard</span></a>
        <a href="addforsign.php"><i class="fas fa-user-plus"></i> <span>Add Document</span></a>
        <a href="viewacceptedsign.php"><i class="fas fa-user-check"></i> <span>View Verified Files</span></a>
        <a href="sign_documents.php" class="active"><i class="fas fa-pen"></i> <span>Sign Documents</span></a>
    </aside>

    <div class="content">
        <div class="dashboard-header">Documents for Signing</div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($documents)) : ?>
                        <?php foreach ($documents as $document) : ?>
                            <tr>
                                <td><?= htmlspecialchars($document['filename']); ?></td>
                                <td>
                                    <a href="signing_page.php?filename=<?= urlencode($document['filename']); ?>" class="action-link">Sign</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="2" style="text-align:center;">No documents available for signing.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
