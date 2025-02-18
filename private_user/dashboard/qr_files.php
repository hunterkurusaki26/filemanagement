<?php
include 'db_connect.php'; // Ensure this connects to your database
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/view_accepted.css">
    <style>
        #qr-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
            text-align: center;
        }

        #qr-popup canvas {
            margin-bottom: 10px;
        }

        #qr-popup button {
            margin: 5px;
            padding: 10px 15px;
            background-color: #007bff;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        #qr-popup button:hover {
            background-color: #0056b3;
        }

        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="header">
            <img src="path_to_logo.png" alt="Logo" style="width:80%; margin-bottom:10px;">
        </div>
        <a href="../home.php"><i class="fas fa-tachometer-alt"></i> <span>User Dashboard</span></a>
        <a href="add_document.php"><i class="fas fa-user-plus"></i> <span>Add Document</span></a>
        <a href="view_accepted.php"><i class="fas fa-user-check"></i> <span>View Verify</span></a>
        <a href="view_transferred_files.php"><i class="fas fa-user-friends"></i> <span>View Received Files</span></a>
        <a href="view_rejected_files.php"><i class="fas fa-user-times"></i> <span>View Decline</span></a>
        <a href="qr_files.php"  class="active"><i class="fas fa-braille"></i> <span>Generate QR</span></a>
    </aside>

    <div class="content">
        <div class="dashboard-header">
            <button id="sidebarToggle" class="toggle-btn"><i class="fas fa-bars"></i></button>
            Badge Management
        </div>

        <div class="table-container">
            <div class="table-actions">
                <input type="text" id="searchInput" class="search-input" placeholder="Search...">
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>File Name</th>
                        <th>File Link</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fileBody">
                    <!-- Rows will be dynamically loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <div id="overlay" onclick="closePopup()"></div>
    <div id="qr-popup">
        <canvas id="qrCanvas"></canvas>
        <br>
        <button onclick="downloadQRCode()">Download QR Code</button>
        <button onclick="closePopup()">Close</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.content');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('sidebar-collapsed');
        });

        function fetchData() {
            fetch('qr_lists.php') // Connect to qr_lists.php
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const fileBody = document.getElementById('fileBody');
                    fileBody.innerHTML = ''; // Clear existing rows

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(file => {
                            const row = `
                                <tr>
                                    <td>${file.name}</td>
                                    <td>${file.file_name}</td>
                                    <td><a href="${file.file_link}" target="_blank">View File</a></td>
                                    <td>${file.created_at}</td>
                                    <td>
                                        <button onclick="generateQRCode('${file.file_name}')">Generate QR</button>
                                    </td>
                                </tr>`;
                            fileBody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        fileBody.innerHTML = `
                            <tr>
                                <td colspan="5" style="text-align: center;">No badges found.</td>
                            </tr>`;
                    }
                })
                .catch(err => {
                    console.error('Error fetching data:', err);
                });
        }

        function generateQRCode(uniqueId) {
            const qrPopup = document.getElementById('qr-popup');
            const overlay = document.getElementById('overlay');
            const qrCanvas = document.getElementById('qrCanvas');

            QRCode.toCanvas(qrCanvas, uniqueId, {
                width: 200,
                height: 200,
                margin: 1,
                color: {
                    dark: "#000000",
                    light: "#FFFFFF"
                }
            });

            qrPopup.style.display = 'block';
            overlay.style.display = 'block';
        }

        function closePopup() {
            document.getElementById('qr-popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        function downloadQRCode() {
            const qrCanvas = document.getElementById('qrCanvas');
            const link = document.createElement('a');
            link.download = 'QR_Code.png';
            link.href = qrCanvas.toDataURL('image/png');
            link.click();
        }

        // Fetch data on page load
        fetchData();
    </script>
</body>

</html>
