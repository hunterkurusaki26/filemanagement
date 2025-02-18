<?php 
require_once("../include/connection.php");
session_start();

if(isset($_POST["logIn"])) {
    date_default_timezone_set("Asia/Manila");
    $date = date("M-d-Y h:i A", strtotime("+0 HOURS"));

    // Validate inputs
    if(empty($_POST["email_address"]) || empty($_POST["user_password"])) {
        echo "<script>alert('Email Address and Password cannot be empty!');document.location='../login.html';</script>";
        exit;
    }

    // Fetch input and sanitize
    $username = $_POST["email_address"];
    $password = $_POST["user_password"];

    // Use prepared statement for security
    $stmt = $conn->prepare("SELECT * FROM login_user WHERE email_address = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if($row) {
        if(password_verify($password, $row["user_password"])) {
            // Regenerate session ID
            session_regenerate_id(true);

            // Set session variables
            $_SESSION["user_no"] = $row["id"];
            $_SESSION["email_address"] = $row["email_address"];
            $_SESSION["login_time"] = $date;

            // Capture IP and host
            $ip = $_SERVER["REMOTE_ADDR"];
            $host = gethostbyaddr($ip);

            // Insert login history
            $remarks = "Has LoggedIn the system at";
            $history_stmt = $conn->prepare("INSERT INTO history_log (id, email_address, action, ip, host, login_time) VALUES (?, ?, ?, ?, ?, ?)");
            $history_stmt->bind_param("isssss", $row["id"], $row["email_address"], $remarks, $ip, $host, $date);
            $history_stmt->execute();

            // Redirect to private user home
            header("Location: ../private_user/home.php");
            exit;
        } else {
            echo "<script>alert('Invalid Password! Please try again.');document.location='../login.html';</script>";
        }
    } else {
        echo "<script>alert('Invalid Email Address! Please try again.');document.location='../login.html';</script>";
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>File Management System</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css">
  <!-- Bootstrap core CSS -->
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <!-- Material Design Bootstrap -->
  <link href="../css/mdb.min.css" rel="stylesheet">
  <!-- Custom styles -->
  <link href="../css/style.css" rel="stylesheet">

  <style type="text/css">
    #loader {
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 9999;
        background: url('../img/loading.gif') 50% 50% no-repeat rgb(249,249,249);
        opacity: 1;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark default-color">
    <a class="navbar-brand" href="index.html"><img src="../img/Files_Download.png" width="33" height="33"> File Management System</a>
  </nav>

  <div id="loader"></div>

  <!-- Sign In Form -->
  <div class="container col-md-5 mt-5">
    <div class="card">
      <div class="card-body">
        <form action="login.php" method="POST">
          <p class="h4 text-center py-4">Sign In</p>

          <div class="md-form">
            <i class="fa fa-envelope prefix grey-text"></i>
            <input type="email" id="materialFormCardEmailEx" name="email_address" class="form-control">
            <label for="materialFormCardEmailEx">Your email</label>
          </div>

          <div class="md-form">
            <i class="fa fa-lock prefix grey-text"></i>
            <input type="password" id="materialFormCardPasswordEx" name="user_password" class="form-control">
            <label for="materialFormCardPasswordEx">Your password</label>
          </div>

          <div class="text-center py-4">
            <button class="btn btn-default btn-lg btn-block" type="submit" name="logIn">Sign In</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../js/jquery-3.4.0.min.js"></script>
  <script src="../js/popper.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/mdb.min.js"></script>

  <script>
    $(window).on('load', function () {
      $('#loader').fadeOut('slow');
    });
  </script>
</body>
</html>
