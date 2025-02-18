<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Management Dashboard - Transferred Files</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="css/add_document.css">
  <style>
    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border-radius: 8px;
      width: 50%;
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      padding-top: 5px;
    }

    .btn {
      padding: 10px 20px;
      margin: 5px;
      cursor: pointer;
    }

    .btn.red {
      background-color:rgb(173, 0, 0);
      color: white;
      border: none;
    }

    .btn.red:hover {
      background-color: #c0392b;
    }

    .btn:hover {
      opacity: 0.9;
    }
  </style>
</head>

<body>
  <aside class="sidebar">
    <div class="header">File Management</div>
    <a href="../home.php"><i class="fas fa-tachometer-alt"></i> <span>User Dashboard</span></a>
    <a href="add_document.php"><i class="fas fa-user-plus"></i> <span>Add Document</span></a>
    <a href="view_accepted.php"><i class="fas fa-user-check"></i> <span>View Verified Files</span></a>
    <a href="view_transferred_files.php" class="active"><i class="fas fa-user-friends"></i> <span>View Received Files</span></a>
    <a href="view_rejected_files.php"><i class="fas fa-user-times"></i> <span>View Declined Files</span></a>
    <a href="qr_files.php"><i class="fas fa-braille"></i> <span>Generate QR</span></a>
  </aside>

  <div class="content">
    <div class="dashboard-header">
      <button id="sidebarToggle" class="toggle-btn"><i class="fas fa-bars"></i></button>
      Received Files
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Filename</th>
            <th>File Size</th>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Transferred At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="fileBody">
          <!-- Rows will be populated here -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Transfer Modal -->
  <div id="transferModal" class="modal">
    <div class="modal-content">
      <h4>Transfer File</h4>
      <p>Enter the recipient's email:</p>
      <input type="email" id="recipientEmail" placeholder="Recipient's email" required />
    </div>
    <div class="modal-footer">
      <button id="transferConfirm" class="btn">Transfer</button>
      <button id="transferCancel" class="btn red">Cancel</button>
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

    // Fetch files dynamically
    function renderTable() {
      fetch('list_transferred_files.php')
        .then(response => response.json())
        .then(data => {
          const fileBody = document.getElementById('fileBody');
          fileBody.innerHTML = '';

          if (Array.isArray(data) && data.length > 0) {
            data.forEach(file => {
              const row = `
                <tr>
                  <td>${file.filename}</td>
                  <td>${file.filesize} bytes</td>
                  <td>${file.sender}</td>
                  <td>${file.recipient}</td>
                  <td>${file.transferred_at}</td>
                  <td>
                    <a href="get_accepted_files.php?file=${encodeURIComponent(file.filename)}" class="download-btn">Download</a>
                    <a href="#" class="transfer-btn" data-id="${file.id}" data-filename="${file.filename}">Transfer</a>
                    <button class="delete-btn" data-filename="${file.filename}">Delete</button>
                  </td>
                </tr>
              `;
              fileBody.insertAdjacentHTML('beforeend', row);
            });
          } else {
            fileBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No files found.</td></tr>';
          }
        })
        .catch(err => console.error("Error fetching files:", err));
    }

    // Initial table render
    renderTable();

    // Delete functionality
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('delete-btn')) {
        const filename = e.target.getAttribute('data-filename');
        if (confirm(`Are you sure you want to delete "${filename}"?`)) {
          fetch('delete_transferred_files.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename })
          })
            .then(response => response.json())
            .then(data => {
              alert(data.message);
              if (data.success) {
                renderTable();
              }
            })
            .catch(err => alert("Error deleting file."));
        }
      }
    });

    // Transfer functionality with modal
    const modal = document.getElementById('transferModal');
    const recipientInput = document.getElementById('recipientEmail');
    const transferConfirm = document.getElementById('transferConfirm');
    const transferCancel = document.getElementById('transferCancel');
    let currentFileId = null;

    document.addEventListener('click', event => {
      if (event.target.classList.contains('transfer-btn')) {
        event.preventDefault();
        currentFileId = event.target.getAttribute('data-id');
        modal.style.display = 'block'; // Show modal
      }
    });

    transferConfirm.addEventListener('click', () => {
      const recipient = recipientInput.value.trim();

      if (recipient) {
        fetch('file_transfer.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ fileId: currentFileId, recipient })
        })
          .then(response => response.json())
          .then(data => {
            alert(data.message);
            modal.style.display = 'none'; // Close modal
            recipientInput.value = '';   // Reset input
          })
          .catch(err => {
            console.error(err);
            alert('An error occurred while transferring the file.');
          });
      } else {
        alert('Please enter a valid email address.');
      }
    });

    transferCancel.addEventListener('click', () => {
      modal.style.display = 'none'; // Close modal
      recipientInput.value = '';   // Reset input
    });

    // Close modal when clicking outside
    window.onclick = event => {
      if (event.target === modal) {
        modal.style.display = 'none';
        recipientInput.value = '';
      }
    };
  </script>
</body>

</html>
