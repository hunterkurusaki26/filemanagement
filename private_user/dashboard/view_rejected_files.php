<?php
// view_rejected_files.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>File Management Dashboard - View Rejected Files</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- External CSS (Optional) -->
  <link rel="stylesheet" href="css/view_rejected_files.css">
  
</head>

<body>
  <aside class="sidebar">
    <div class="header">
      <img src="path_to_your_logo.png" alt="Logo" style="width:80%; margin-bottom:10px;">
    </div>
    <a href="../home.php"><i class="fas fa-tachometer-alt"></i> <span>User Dashboard</span></a>
    <a href="add_document.php"><i class="fas fa-user-plus"></i> <span>Add Document</span></a>
    <a href="view_accepted.php"><i class="fas fa-user-check"></i> <span>View Verified Files</span></a>
    <a href="view_transferred_files.php"><i class="fas fa-user-friends"></i> <span>View Received Files</span></a>
    <a href="view_rejected_files.php" class="active"><i class="fas fa-user-times"></i> <span>View Declined Files</span></a>
    <a href="qr_files.php"><i class="fas fa-braille"></i> <span>Generate QR</span></a>
  </aside>

  <div class="content">
    <div class="dashboard-header">
      <button id="sidebarToggle" class="toggle-btn"><i class="fas fa-bars"></i></button>
      View Rejected Files
    </div>

    <div class="table-container">
      <div class="table-actions">
        <input type="text" id="searchInput" class="search-input" placeholder="Search...">
      </div>
      <table>
        <thead>
          <tr>
            <th>Filename</th>
            <th>File Size</th>
            <th>Uploader</th>
            <th>Rejected At</th>
            <th>Reason</th> <!-- Changed from Action to Reason -->
          </tr>
        </thead>
        <tbody id="fileBody">
          <!-- Rows will be populated here -->
        </tbody>
      </table>
    </div>
  </div>
 
  <script>
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');

    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      content.classList.toggle('sidebar-collapsed');
    });

    // Fetch rejected files dynamically
    function renderTable() {
      fetch('list_rejected_files.php') // Endpoint to fetch rejected files
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log("Fetched data:", data); // Debugging line
          const fileBody = document.getElementById('fileBody');
          fileBody.innerHTML = ''; // Clear existing rows

          // Check if data is an array
          if (Array.isArray(data)) {
            if (data.length === 0) {
              fileBody.innerHTML = `
                <tr>
                  <td colspan="5" style="text-align: center;">No rejected files found.</td>
                </tr>
              `;
              return;
            }

            data.forEach(file => {
              const row = `
                <tr>
                  <td>${file.filename}</td>
                  <td>${file.fileSize}</td>
                  <td>${file.uploader}</td>
                  <td>${file.rejectedAt}</td>
                  <td>${file.reason}</td> <!-- Displaying Reason instead of Action -->
                </tr>
              `;
              fileBody.insertAdjacentHTML('beforeend', row);
            });
          } else if (data.success === false) {
            // Display error message from PHP
            const row = `
              <tr>
                <td colspan="5" style="text-align: center; color: red;">
                  ${data.message}: ${data.error || 'Unknown error.'}
                </td>
              </tr>
            `;
            fileBody.insertAdjacentHTML('beforeend', row);
          } else {
            // Handle unexpected data format
            const row = `
              <tr>
                <td colspan="5" style="text-align: center; color: red;">
                  Unexpected data format received.
                </td>
              </tr>
            `;
            fileBody.insertAdjacentHTML('beforeend', row);
          }
        })
        .catch(err => {
          console.error("Error fetching files:", err);
          const fileBody = document.getElementById('fileBody');
          fileBody.innerHTML = `
            <tr>
              <td colspan="5" style="text-align: center; color: red;">
                Error fetching files: ${err.message}
              </td>
            </tr>
          `;
        });
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function () {
      const query = this.value.toLowerCase();
      const rows = document.querySelectorAll('#fileBody tr');
      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(query));
        row.style.display = match ? '' : 'none';
      });
    });

    // Initial table render
    renderTable();
  </script>
</body>

</html>
