<?php
// Start the session and check if the admin is logged in
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.html');
    exit();
}

// Include the database connection
require_once("include/connection.php");

// Fetch admin user details
$admin_id = mysqli_real_escape_string($conn, $_SESSION['admin_user']);
$result = mysqli_query($conn, "SELECT admin_user FROM admin_login WHERE id = '$admin_id'") or die(mysqli_error($conn));
$admin = mysqli_fetch_assoc($result);
$admin_username = htmlspecialchars($admin['admin_user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Management Dashboard</title>
    <!-- Meta Tags for Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css">
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Design Bootstrap -->
    <link href="css/mdb.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link href="css/style.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css"/>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- Material Design Bootstrap JS -->
    <script src="js/mdb.min.js"></script>
    <!-- Custom Scripts -->
    <style>
        /* Custom Styles */
        select[multiple], select[size] {
            height: auto;
            width: 20px;
        }
        .pull-right {
            float: right;
            margin: 2px !important;
        }
        .map-container{
            overflow:hidden;
            padding-bottom:56.25%;
            position:relative;
            height:0;
        }
        .map-container iframe{
            left:0;
            top:0;
            height:100%;
            width:100%;
            position:absolute;
        }
        #loader{
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('img/lg.flip-book-loader.gif') 50% 50% no-repeat rgb(249,249,249);
            opacity: 1;
        }
    </style>
    <!-- Loader Script -->
    <script type="text/javascript">
        $(window).on('load', function(){
            // Remove loader after the page has fully loaded
            $('#loader').fadeOut('slow');  
        });
    </script>
</head>
<body class="grey lighten-3">

    <!-- Loader -->
    <div id="loader"></div>

    <!-- Main Navigation -->
    <header>
        <!-- Navbar -->
        <nav class="navbar fixed-top navbar-expand-lg navbar-light white scrolling-navbar">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="navbar-brand waves-effect" href="#">
                    <strong class="blue-text">File Manager</strong>
                </a>
                <!-- Collapse Button -->
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <!-- Navbar Links -->
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side -->
                    <ul class="navbar-nav mr-auto">
                        <!-- Add any left-side links here if needed -->
                    </ul>
                    <!-- Right Side -->
                    <ul class="navbar-nav nav-flex-icons">
                        <li class="nav-item" style="margin-top: 10px;">
                            Welcome, <?php echo ucwords($admin_username); ?>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link waves-effect" target="_blank">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link waves-effect" target="_blank">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link border border-light rounded waves-effect">
                                <i class="far fa-user-circle"></i> Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Navbar -->

        <!-- Sidebar -->
        <div class="sidebar-fixed position-fixed">
            <a class="logo-wrapper waves-effect">
                <img src="img/images.jpg" width="150px" height="200px;" class="img-fluid" alt="Logo">
            </a>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item active waves-effect">
                    <i class="fas fa-chart-pie mr-3"></i> Dashboard
                </a>
                <a href="#" class="list-group-item list-group-item-action waves-effect" data-toggle="modal" data-target="#modalRegisterForm">
                    <i class="fas fa-user mr-3"></i> Add Admin
                </a>
                <a href="view_admin.php" class="list-group-item list-group-item-action waves-effect">
                    <i class="fas fa-users"></i> View Admin
                </a>
                <a href="#" class="list-group-item list-group-item-action waves-effect" data-toggle="modal" data-target="#modalRegisterForm2">
                    <i class="fas fa-user mr-3"></i> Add User
                </a>
                <a href="view_user.php" class="list-group-item list-group-item-action waves-effect">
                    <i class="fas fa-users"></i> View User
                </a>
                <a href="add_document.php" class="list-group-item list-group-item-action waves-effect">
                    <i class="fas fa-file-medical"></i> Add Document
                </a>
                <a href="view_userfile.php" class="list-group-item list-group-item-action waves-effect">
                    <i class="fas fa-folder-open"></i> View User File
                </a>
                <a href="admin_log.php" class="list-group-item list-group-item-action waves-effect">
                    <i class="fas fa-chalkboard-teacher"></i> Admin Log
                </a>
                <a href="user_log.php" class="list-group-item list-group-item-action waves-effect">
                    <i class="fas fa-chalkboard-teacher"></i> User Log
                </a>
            </div>
        </div>
        <!-- Sidebar -->

        <!-- Add Admin Modal -->
        <div class="modal fade" id="modalRegisterForm" tabindex="-1" role="dialog" aria-labelledby="addAdminModalLabel"
            aria-hidden="true">
            <form action="create_Admin.php" method="POST">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h4 class="modal-title w-100 font-weight-bold"><i class="fas fa-user-plus"></i> Add Admin</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body mx-3">
                            <input type="hidden" name="status" value="Admin">
                            <div class="md-form mb-5">
                                <i class="fas fa-user prefix grey-text"></i>
                                <input type="text" name="name" class="form-control validate" required>
                                <label for="name">Your Name</label>
                            </div>
                            <div class="md-form mb-5">
                                <i class="fas fa-envelope prefix grey-text"></i>
                                <input type="email" name="admin_user" class="form-control validate" required>
                                <label for="admin_user">Your Email</label>
                            </div>
                            <div class="md-form mb-4">
                                <i class="fas fa-lock prefix grey-text"></i>
                                <input type="password" name="admin_password" class="form-control validate" required>
                                <label for="admin_password">Your Password</label>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" class="btn btn-info" name="reg">Sign Up</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- End Add Admin Modal -->

        <!-- Add User Modal -->
        <div class="modal fade" id="modalRegisterForm2" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel"
            aria-hidden="true">
            <form action="create_user.php" method="POST">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h4 class="modal-title w-100 font-weight-bold"><i class="fas fa-user-plus"></i> Add User</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body mx-3">
                            <input type="hidden" name="status" value="Employee">
                            <div class="md-form mb-5">
                                <i class="fas fa-user prefix grey-text"></i>
                                <input type="text" name="name" class="form-control validate" required>
                                <label for="name">Your Name</label>
                            </div>
                            <div class="md-form mb-5">
                                <i class="fas fa-envelope prefix grey-text"></i>
                                <input type="email" name="email_address" class="form-control validate" required>
                                <label for="email_address">Your Email</label>
                            </div>
                            <div class="md-form mb-4">
                                <i class="fas fa-lock prefix grey-text"></i>
                                <input type="password" name="user_password" class="form-control validate" required>
                                <label for="user_password">Your Password</label>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" class="btn btn-info" name="reguser">Sign Up</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- End Add User Modal -->
    </header>
    <!-- End Main Navigation -->

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" role="dialog" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="rejectForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject File</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <input type="hidden" id="rejectFileId" name="id" value="">
                        <div class="form-group">
                            <label for="rejectReason">Reason for Rejection</label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- End Rejection Reason Modal -->

    <!-- Main Layout -->
    <main class="pt-5 mx-lg-5">
    <div class="container-fluid mt-5">
        <!-- Page Heading -->
        <div class="card mb-4 wow fadeIn">
            <div class="card-body d-sm-flex justify-content-between">
                <h4 class="mb-2 mb-sm-0 pt-1">
                    <a href="dashboard.php">Home Page</a>
                    <span>/</span>
                    <span>Dashboard</span>
                </h4>
            </div>
        </div>
        <!-- End Page Heading -->

        <!-- Navigation Buttons -->
        <div class="mb-3">
            <a href="add_document.php">
                <button type="button" class="btn btn-info">
                    <i class="fas fa-chevron-circle-left"></i> Document
                </button>
            </a>
        </div>
        <hr>

        <!-- Files Table (files) -->
        <div class="col-md-12">
            <h5>Files</h5>
            <table id="dtable-files" class="table table-striped">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>File Size</th>
                        <th>File Uploader</th>
                        <th>Status</th>
                        <th>Date/Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        // Fetch files from the database
                        $query = mysqli_query($conn, "SELECT id, filename, filesize, uploader, status, datetime FROM files ORDER BY datetime DESC") or die(mysqli_error($conn));
                        while ($file = mysqli_fetch_assoc($query)) {
                            $file_id = $file['id'];
                            $filename = htmlspecialchars($file['filename']);
                            $filesize = round($file['filesize'] / 1024, 2) . ' KB';
                            $uploader = htmlspecialchars($file['uploader']);
                            $status = htmlspecialchars($file['status']);
                            $datetime = htmlspecialchars($file['datetime']);
                    ?>
                        <tr id="row-<?php echo $file_id; ?>">
                            <td><?php echo $filename; ?></td>
                            <td><?php echo $filesize; ?></td>
                            <td><?php echo $uploader; ?></td>
                            <td><?php echo $status; ?></td>
                            <td><?php echo $datetime; ?></td>
                            <td>
                                <button class="btn btn-success accept-btn" data-id="<?php echo $file_id; ?>">Accept</button>
                                <button class="btn btn-danger reject-btn" data-id="<?php echo $file_id; ?>">Reject</button>
                            </td>
                        </tr>
                    <?php 
                        } 
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Send Document Table -->
        <div class="col-md-12 mt-5">
            <h5>Sent Documents</h5>
            <table id="dtable-send-docu" class="table table-striped">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>File Size</th>
                        <th>File Uploader</th>
                        <th>Status</th>
                        <th>Date/Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        // Fetch send_docu from the database
                        $query_send = mysqli_query($conn, "SELECT id, filename, filesize, uploader, status, datetime FROM sign_docu ORDER BY datetime DESC") or die(mysqli_error($conn));
                        while ($file = mysqli_fetch_assoc($query_send)) {
                            $file_id = $file['id'];
                            $filename = htmlspecialchars($file['filename']);
                            $filesize = round($file['filesize'] / 1024, 2) . ' KB';
                            $uploader = htmlspecialchars($file['uploader']);
                            $status = htmlspecialchars($file['status']);
                            $datetime = htmlspecialchars($file['datetime']);
                    ?>
                        <tr id="row-send-<?php echo $file_id; ?>">
                            <td><?php echo $filename; ?></td>
                            <td><?php echo $filesize; ?></td>
                            <td><?php echo $uploader; ?></td>
                            <td><?php echo $status; ?></td>
                            <td><?php echo $datetime; ?></td>
                            <td>
                                <button class="btn btn-success accept-btn" data-id="<?php echo $file_id; ?>">Accept</button>
                                <button class="btn btn-danger reject-btn" data-id="<?php echo $file_id; ?>">Reject</button>
                            </td>
                        </tr>
                    <?php 
                        } 
                    ?>
                </tbody>
            </table>
        </div>  

        <!-- JavaScript for DataTables and AJAX Handling -->
        <script>
            $(document).ready(function () {
                // Initialize DataTables on both tables
                $('#dtable-files, #dtable-send-docu').DataTable({
                    "aLengthMenu": [[5, 10, 15, 25, 50, 100, -1], [5, 10, 15, 25, 50, 100, "All"]],
                    "iDisplayLength": 10
                });

                // Shared functions and AJAX handling (same as original script)
                function showToast(message, isSuccess = true) {
                    const toastHTML = `
                        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
                            <div class="toast-header">
                                <strong class="mr-auto">${isSuccess ? 'Success' : 'Error'}</strong>
                                <small class="text-muted">Just now</small>
                                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="toast-body">
                                ${message}
                            </div>
                        </div>
                    `;
                    $('#toast-container').append(toastHTML);
                    $('.toast').toast('show');
                }

                // Event delegation for Accept and Reject buttons
                $(document).on('click', '.accept-btn, .reject-btn', function () {
                    const id = $(this).data('id');
                    const action = $(this).hasClass('accept-btn') ? 'accept' : 'reject';
                    const row = $(this).closest('tr');

                    if (action === 'accept') {
                        // Handle Accept
                        if (confirm(`Are you sure you want to accept this file?`)) {
                            $.ajax({
                                url: 'update_status.php',
                                type: 'POST',
                                data: { id: id, action: action },
                                success: function (response) {
                                    if (response.success) {
                                        showToast(response.message, true);
                                        row.fadeOut(800, function () {
                                            row.remove();
                                        });
                                    } else {
                                        showToast(response.message, false);
                                    }
                                },
                                error: function (xhr, status, error) {
                                    console.error("AJAX Error: " + error);
                                    showToast('An error occurred. Please try again.', false);
                                }
                            });
                        }
                    } else {
                        // Handle Reject
                        $('#rejectFileId').val(id);
                        $('#rejectReason').val('');
                        $('#rejectReasonModal').modal('show');
                    }
                });

                $('#rejectForm').on('submit', function (e) {
                    e.preventDefault();
                    const id = $('#rejectFileId').val();
                    const reason = $('#rejectReason').val().trim();

                    if (reason === '') {
                        alert('Please provide a reason for rejection.');
                        return;
                    }

                    $.ajax({
                        url: 'update_status.php',
                        type: 'POST',
                        data: { id: id, action: 'reject', reason: reason },
                        success: function (response) {
                            if (response.success) {
                                showToast(response.message, true);
                                $(`#row-${id}`).fadeOut(800, function () {
                                    $(this).remove();
                                });
                                $('#rejectReasonModal').modal('hide');
                            } else {
                                showToast(response.message, false);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error: " + error);
                            showToast('An error occurred. Please try again.', false);
                        }
                    });
                });
            });
        </script>
    </div>
</main>

    <!-- End Main Layout -->

    <!-- Toast Container for Notifications -->
    <div aria-live="polite" aria-atomic="true" style="position: relative;">
        <div id="toast-container" style="position: fixed; top: 10px; right: 10px;">
            <!-- Toasts will be appended here dynamically -->
        </div>
    </div>
</body>
</html>
