<?php
// Start the session to check the logged-in user
session_start();

// Get the filename from the query parameter
$filename = isset($_GET['filename']) ? $_GET['filename'] : '';

// Check if the file exists
if (!$filename || !file_exists("uploads/$filename")) {
    die("Invalid or missing document.");
}

// Get the logged-in user's email
$loggedInUser = $_SESSION['email_address'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Document</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.14.305/pdf.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .pdf-container {
            margin-top: 20px;
        }
        .pdf-container canvas {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            display: block;
        }
        .controls {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="file"] {
            margin-bottom: 20px;
        }
        button {
            padding: 10px 15px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Sign Document: <?= htmlspecialchars($filename); ?></h2>

    <!-- File upload for signature -->
    <div class="controls">
        <label for="signature">Attach Your E-Signature (PNG Only):</label>
        <input type="file" id="signature" accept="image/png">
    </div>

    <!-- PDF container -->
    <div id="pdfContainer" class="pdf-container"></div>

    <!-- Finish Signing button -->
    <button id="finishBtn">Finish Signing</button>

    <script>
        const pdfUrl = 'uploads/<?= htmlspecialchars($filename); ?>'; // Path to the PDF file
        const pdfContainer = document.getElementById('pdfContainer');
        const signatureInput = document.getElementById('signature');
        const finishBtn = document.getElementById('finishBtn');
        let signatureImg;

        // Load the PDF using PDF.js
        const loadingTask = pdfjsLib.getDocument(pdfUrl);

        loadingTask.promise.then(pdf => {
            // Loop through all the pages
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                pdf.getPage(pageNum).then(page => {
                    const canvas = document.createElement('canvas');
                    pdfContainer.appendChild(canvas);

                    const ctx = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1 });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    page.render(renderContext);

                    // Add click event for placing signature on each page
                    canvas.addEventListener('click', (e) => {
                        if (signatureImg) {
                            const rect = canvas.getBoundingClientRect();
                            const x = e.clientX - rect.left;
                            const y = e.clientY - rect.top;
                            ctx.drawImage(signatureImg, x, y, 150, 50); // Adjust size as needed
                        } else {
                            alert('Please upload a signature first.');
                        }
                    });
                });
            }
        }).catch(error => {
            console.error("Error loading PDF:", error);
        });

        // Handle signature upload
        signatureInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file && file.type === 'image/png') {
                const reader = new FileReader();
                reader.onload = function (e) {
                    signatureImg = new Image();
                    signatureImg.src = e.target.result;
                    signatureImg.onload = () => alert('Signature uploaded. Click on the canvas to place it.');
                };
                reader.readAsDataURL(file);
            } else {
                alert('Please upload a valid PNG file.');
            }
        });

        // Handle the Finish Signing button click
        finishBtn.addEventListener('click', () => {
            // Send signed document to the server
            const canvases = pdfContainer.querySelectorAll('canvas');
            canvases.forEach((canvas, index) => {
                const signedDocument = canvas.toDataURL('image/png');

                // Prepare form data for sending the signed document
                const formData = new FormData();
                formData.append('signedDocument', signedDocument);
                formData.append('filename', '<?= htmlspecialchars($filename); ?>');
                formData.append('sender', '<?= htmlspecialchars($loggedInUser); ?>');

                // Check if file is in file_transfer_queue or signtransfer and send accordingly
                fetch('finish_signing.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        alert('Document signed and sent!');
                        window.location.href = 'view_documents.php'; // Redirect after successful signing
                    } else {
                        alert('Error: ' + data.error);
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('There was an error processing the document.');
                });
            });
        });
    </script>
</body>
</html>
