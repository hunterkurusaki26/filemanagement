<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>File Management Dashboard - View Verified Files</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="css/add_document.css"> <!-- Reuse the CSS file from Add Document -->

  <style>
    /* Modal styles */
    .modal {
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      display: none; /* Hidden by default */
    }
    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 5px;
      width: 90%;
      max-width: 500px;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .close {
      float: right;
      font-size: 20px;
      cursor: pointer;
    }
    .modal-btn {
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 10px 20px;
      margin: 10px 0;
      border-radius: 5px;
      cursor: pointer;
    }
    .modal-btn:hover {
      background-color: #0056b3;
    }
    #multipleEmailsContainer input {
      margin-bottom: 10px;
      width: 100%;
    }
  </style>
</head>

<body>
  <aside class="sidebar">
    <div class="header">
      <img src="" alt="Logo" style="width:80%; margin-bottom:10px;">
    </div>
    <a href="../home.php"><i class="fas fa-tachometer-alt"></i> <span>User Dashboard</span></a>
    <a href="addforsign.php" id="addFileLink"><i class="fas fa-user-plus"></i> <span>Add Document</span></a>
    <a href="viewacceptedsign.php" class="active"><i class="fas fa-user-check"></i> <span>View Verified Files</span></a>
  </aside>

  <div class="content">
    <div class="dashboard-header">
      <button id="sidebarToggle" class="toggle-btn"><i class="fas fa-bars"></i></button>
      Sign Storage
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
            <th>Accepted At</th>
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
      <span id="closeModal" class="close">&times;</span>
      <h2>Transfer File</h2>
      <p>Select transfer type:</p>
      <button id="oneRecipientBtn" class="modal-btn">Send to One Recipient</button>
      <button id="multipleRecipientsBtn" class="modal-btn">Send to Multiple Recipients</button>

      <div id="singleRecipientInput" style="display: none; margin-top: 20px;">
        <label for="singleEmail">Recipient Email:</label>
        <input type="email" id="singleEmail" placeholder="Enter recipient email" list="emailSuggestions">
        <datalist id="emailSuggestions"></datalist>
        <button id="singleSendBtn" class="modal-btn">Send</button>
      </div>

      <div id="multipleRecipientInput" style="display: none; margin-top: 20px;">
        <label for="multipleEmails">Recipient Emails:</label>
        <div id="multipleEmailsContainer">
          <input type="email" class="multipleEmail" placeholder="Enter recipient email" list="emailSuggestions">
          <datalist id="emailSuggestions"></datalist>
        </div>
        <button id="addEmailBtn" class="modal-btn">Add Another Email</button>
        <button id="multipleSendBtn" class="modal-btn">Send</button>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener("DOMContentLoaded", () => {
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebar = document.querySelector(".sidebar");
    const content = document.querySelector(".content");

    const modal = document.getElementById("transferModal");
    const closeModal = document.getElementById("closeModal");
    const singleRecipientInput = document.getElementById("singleRecipientInput");
    const multipleRecipientInput = document.getElementById("multipleRecipientInput");
    const singleSendBtn = document.getElementById("singleSendBtn");
    const multipleSendBtn = document.getElementById("multipleSendBtn");
    const addEmailBtn = document.getElementById("addEmailBtn");
    const multipleEmailsContainer = document.getElementById("multipleEmailsContainer");

    // Sidebar toggle
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      content.classList.toggle("sidebar-collapsed");
    });

    // Reset modal
    const resetModal = () => {
      singleRecipientInput.style.display = "none";
      multipleRecipientInput.style.display = "none";
      document.getElementById("singleEmail").value = "";
      multipleEmailsContainer.innerHTML = `
        <input type="email" class="multipleEmail" placeholder="Enter recipient email" list="emailSuggestions">
      `;
    };

    // Render table
    const renderTable = () => {
      fetch("list_acceptedsign.php")
        .then((res) => {
          if (!res.ok) throw new Error("Failed to fetch files.");
          return res.json();
        })
        .then((data) => {
          const fileBody = document.getElementById("fileBody");
          fileBody.innerHTML = "";
          if (Array.isArray(data) && data.length > 0) {
            data.forEach((file) => {
              fileBody.insertAdjacentHTML(
                "beforeend",
                `
                  <tr>
                    <td>${file.filename}</td>
                    <td>${file.fileSize}</td>
                    <td>${file.uploader}</td>
                    <td>${file.acceptedAt}</td>
                    <td>
                      <a href="get_acceptedsign_files.php?file=${encodeURIComponent(file.filename)}" class="download-btn">Download</a>
                      <a href="#" class="transfer-btn" data-id="${file.id}" data-filename="${file.filename}">Transfer</a>
                      <button class="delete-btn" data-filename="${file.filename}">Delete</button>
                    </td>
                  </tr>
                `
              );
            });
          } else {
            fileBody.innerHTML = `
              <tr>
                <td colspan="5" style="text-align: center;">No files found.</td>
              </tr>
            `;
          }
        })
        .catch((err) => {
          console.error(err);
          document.getElementById("fileBody").innerHTML = `
            <tr>
              <td colspan="5" style="text-align: center; color: red;">Failed to load files.</td>
            </tr>
          `;
        });
    };

    // Fetch email suggestions
    const fetchEmailSuggestions = (query, datalistId) => {
      if (query.length > 2) {
        fetch(`get_email_suggestions.php?query=${encodeURIComponent(query)}`)
          .then((res) => res.json())
          .then((data) => {
            const datalist = document.getElementById(datalistId);
            datalist.innerHTML = "";
            data.suggestions.forEach((email) => {
              const option = document.createElement("option");
              option.value = email;
              datalist.appendChild(option);
            });
          })
          .catch((err) => console.error("Error fetching suggestions:", err));
      }
    }; 
    

    // Modal event listeners
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("transfer-btn")) {
        e.preventDefault();
        modal.style.display = "flex";
        const fileId = e.target.dataset.id;

        document.getElementById("oneRecipientBtn").onclick = () => {
          singleRecipientInput.style.display = "block";
          multipleRecipientInput.style.display = "none";
        };

        document.getElementById("multipleRecipientsBtn").onclick = () => {
          singleRecipientInput.style.display = "none";
          multipleRecipientInput.style.display = "block";
        };

// Validate email existence
      const validateEmails = (emails, callback) => {
        fetch("check_email.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ emails }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.exists) {
              callback(true);
            } else {
              // Show appropriate error message based on the response
              if (data.error === "Duplicate emails found in input.") {
                alert("Duplicate emails found in the input. Please remove duplicates and try again.");
              } else if (data.error === "One or more emails do not exist in the database.") {
                alert("One or more emails do not exist in the system. Please check and try again.");
              } else {
                alert("An unknown error occurred. Please try again.");
              }
              callback(false);
            }
          })
          .catch((err) => {
            console.error("Error validating emails:", err);
            alert("An error occurred while validating emails. Please try again.");
            callback(false);
          });
      };


        // Update singleSendBtn event listener
        singleSendBtn.onclick = () => {
          const email = document.getElementById("singleEmail").value.trim();
          if (email) {
            validateEmails([email], (isValid) => {
              if (isValid) {
                fetch("signtransfer.php", {
                  method: "POST",
                  headers: { "Content-Type": "application/json" },
                  body: JSON.stringify({ fileId, recipient: email }),
                })
                  .then((res) => res.json())
                  .then((data) => {
                    alert(data.message);
                    modal.style.display = "none";
                  })
                  .catch((err) => console.error("Error:", err));
              }
            });
          } else {
            alert("Enter a recipient email.");
          }
        };

        // Update multipleSendBtn event listener
        multipleSendBtn.onclick = () => {
          const emails = Array.from(
            multipleEmailsContainer.querySelectorAll(".multipleEmail")
          )
            .map((input) => input.value.trim())
            .filter((email) => email);

          if (emails.length > 0) {
            validateEmails(emails, (isValid) => {
              if (isValid) {
                fetch("list_queue.php", {
                  method: "POST",
                  headers: { "Content-Type": "application/json" },
                  body: JSON.stringify({ fileId, recipients: emails }),
                })
                  .then((res) => res.json())
                  .then((data) => {
                    alert(data.message);
                    modal.style.display = "none";
                  })
                  .catch((err) => console.error("Error:", err));
              }
            });
          } else {
            alert("Enter at least one recipient email.");
          }
        };

      }

      if (e.target === closeModal || e.target === modal) {
        modal.style.display = "none";
        resetModal();
      }

      if (e.target.classList.contains("delete-btn")) {
        const filename = e.target.dataset.filename;
        if (confirm(`Delete "${filename}"?`)) {
          fetch("delete_transferred_files.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ filename }),
          })
            .then((res) => res.json())
            .then((data) => {
              alert(data.message);
              if (data.success) renderTable();
            })
            .catch(() => alert("Error deleting file."));
        }
      }
    });

    document.getElementById("singleEmail").addEventListener("input", (e) => {
      fetchEmailSuggestions(e.target.value, "emailSuggestions");
    });

    multipleEmailsContainer.addEventListener("input", (e) => {
      if (e.target.classList.contains("multipleEmail")) {
        fetchEmailSuggestions(e.target.value, "emailSuggestions");
      }
    });

    addEmailBtn.addEventListener("click", () => {
      const input = document.createElement("input");
      input.type = "email";
      input.classList.add("multipleEmail");
      input.placeholder = "Enter another email";
      input.setAttribute("list", "emailSuggestions");
      multipleEmailsContainer.appendChild(input);
    });

    renderTable();
  });
</script>

</body>

</html>
