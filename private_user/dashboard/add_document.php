<?php
session_start();

// Implement User Authentication Check (Optional)
// Uncomment the following lines if you have a login system in place
/*
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
*/

// Generate CSRF token if not set (Optional)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>File Management Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="css/add_document.css">

</head>

<body>
  <aside class="sidebar">
    <div class="header">
      <img src="logo.png" alt="Logo" style="width:80%; margin-bottom:10px;">
    </div>
    <a href="../home.php" ><i class="fas fa-tachometer-alt"></i> <span>User Dashboard</span></a>
    <a href="add_document.php" id="addFileLink" class="active"><i class="fas fa-user-plus"></i> <span>Add Document</span></a>
    <a href="view_accepted.php"><i class="fas fa-user-check"></i> <span>View Verified Files</span></a>
    <a href="view_transferred_files.php"><i class="fas fa-user-friends"></i> <span>View Received Files</span></a>
    <a href="view_rejected_files.php"><i class="fas fa-user-times"></i> <span>View Declined Files</span></a>
    <a href="qr_files.php"><i class="fas fa-braille"></i> <span>Generate QR</span></a>
  </aside>

  <div class="content">
    <div class="dashboard-header">
      <button id="sidebarToggle" class="toggle-btn"><i class="fas fa-bars"></i></button>
      Dashboard
    </div>

    <div class="table-container">
      <div class="table-actions">
        <button class="add-file-btn" id="openFileModal">Add File</button>
        <input type="text" id="searchInput" class="search-input" placeholder="Search...">
      </div>
      <table>
        <thead>
          <tr>
            <th>Filename</th>
            <th>FileSize</th>
            <th>Uploader</th>
            <th>Date/Time Upload</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="fileBody"></tbody>
      </table>
    </div>
  </div>

  <!-- File Upload Modal -->
  <div class="modal" id="fileModal">
    <div class="modal-content">
      <div class="modal-header">
        <h4>Add New Document</h4>
        <button id="closeModal">&times;</button>
      </div>
      <form id="fileForm" enctype="multipart/form-data">
        <!-- CSRF Token (Optional) -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="file" name="file" required>
        <button type="submit" class="add-file-btn">Upload</button>
      </form>
    </div>
  </div>

  <script>
    // Sidebar Toggle Functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');

    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      content.classList.toggle('sidebar-collapsed');
    });

    // Modal functionality
    const modal = document.getElementById('fileModal');
    const openFileModal = document.getElementById('openFileModal');
    const closeModal = document.getElementById('closeModal');
    const fileBody = document.getElementById('fileBody');

    openFileModal.addEventListener('click', () => {
      modal.style.display = 'flex';
    });

    closeModal.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    // Fetch files dynamically
    function renderTable() {
      fetch('list_files.php')
        .then(response => response.json())
        .then(data => {
          fileBody.innerHTML = '';
          data.forEach(file => {
            const row = `
              <tr>
                <td>${file.filename}</td>
                <td>${file.fileSize}</td>
                <td>${file.uploader}</td>
                <td>${file.dateTime}</td>
                <td>${file.status}</td>
                <td>
                  <a href="get_files.php?file=${encodeURIComponent(file.filename)}" class="download-btn" target="_blank">Download</a>
                  <button class="delete-btn" data-filename="${file.filename}">Delete</button>
                </td>
              </tr>
            `;
            fileBody.insertAdjacentHTML('beforeend', row);
          });
        })
        .catch(err => {
          console.error("Error fetching files:", err);
        });
    }

    // Handle file upload
    document.getElementById('fileForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('uploads.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
          alert(data.message);
          if (data.success) {
            renderTable();
            modal.style.display = 'none';
          }
        })
        .catch(err => {
          console.error("Error uploading file:", err);
          alert("An error occurred while uploading the file.");
        });
    });

    // Handle file delete
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('delete-btn')) {
        const filename = e.target.getAttribute('data-filename');

        // Confirm before deletion
        if (confirm(`Are you sure you want to delete the file "${filename}"?`)) {
          fetch('delete_file.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename }) // Send the filename in the request
          })
            .then(response => response.json())
            .then(data => {
              alert(data.message); // Show success or error message
              if (data.success) {
                renderTable(); // Refresh the file list after deletion
              }
            })
            .catch(err => {
              alert("An error occurred while deleting the file.");
              console.error(err);
            });
        }
      }
    });

    // Search functionality (optional)
    document.getElementById('searchInput').addEventListener('input', function () {
      const query = this.value.toLowerCase();
      const rows = document.querySelectorAll('#fileBody tr');

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;
        cells.forEach(cell => {
          if (cell.textContent.toLowerCase().includes(query)) {
            match = true;
          }
        });
        row.style.display = match ? '' : 'none';
      });
    });

    // Initial table render
    renderTable();

    // Close modals when clicking outside of them
    window.addEventListener('click', function (e) {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });
  </script>
</body>

</html>
