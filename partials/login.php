<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Event Management</title>
  <script src="<?php echo $adminBaseUrl ?>js/jquery.min.js"></script>
  <script src="<?php echo $adminBaseUrl ?>js/sweetalert.js"></script>
  <link rel="stylesheet" type="text/css" href="style.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      display: flex;
      min-height: 100vh;
      background-color: #eef4f7;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background-color: #004d40;
      color: white;
      display: flex;
      flex-direction: column;
      padding: 20px 0;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header {
      text-align: center;
      padding: 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header h2 {
      font-size: 22px;
      font-weight: bold;
      color: #e0f2f1;
    }

    .sidebar a {
      padding: 14px 24px;
      color: #e0f2f1;
      text-decoration: none;
      display: block;
      font-weight: 500;
      transition: background 0.3s;
    }

    .sidebar a:hover {
      background-color: #00796b;
    }

    /* Main Content */
    .main-content {
      margin-left: 220px;
      flex: 1;
      display: flex;
      flex-direction: column;
      background-image: url('img/e_image.jpg'); /* Add your image URL here */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    header {
      background-color: rgba(0, 150, 136, 0.8); /* Transparent background to make it stand out over image */
      color: white;
      padding: 20px;
      font-size: 24px;
      font-weight: bold;
      text-align: center;
    }

    main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px;
      text-align: center;
    }

    .button {
      padding: 12px 28px;
      background-color: #009688;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin: 10px;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .button:hover {
      background-color: #00796b;
      transform: translateY(-2px);
    }

    .formbg {
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
      max-width: 420px;
      width: 100%;
    }

    .field {
      margin-bottom: 20px;
    }

    .field label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #37474f;
    }

    .field input {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: border 0.3s;
    }

    .field input:focus {
      border-color: #009688;
      outline: none;
    }

    input[type="submit"] {
      background-color: #009688;
      color: white;
      font-weight: 600;
      padding: 12px;
      border: none;
      width: 100%;
      border-radius: 6px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #00796b;
    }

    .welcome-screen,
    .login-screen {
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100%;
    }

    .welcome-screen.active,
    .login-screen.active {
      display: flex;
      animation: fadeIn 0.5s ease;
    }

    .welcome-content {
      background-color: white;
      padding: 50px;
      border-radius: 16px;
      box-shadow: 0 4px 30px rgba(0, 0, 0, 0.06);
      max-width: 600px;
      text-align: center;
    }

    .welcome-content h2 {
      font-size: 30px;
      color: #004d40;
      margin-bottom: 15px;
    }

    .welcome-content p {
      font-size: 18px;
      color: #455a64;
      margin-bottom: 30px;
    }

    footer {
      background-color: #263238;
      color: white;
      padding: 15px;
      text-align: center;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 180px;
      }

      .main-content {
        margin-left: 180px;
      }

      .welcome-content {
        padding: 30px;
      }
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>🎯 EVENT MANAGEMENT Admin</h2>
    </div>
    <a href="#" onclick="showWelcome()">🏠 Home</a>
    <a href="#" onclick="showLogin()">🔐 Login</a>
    <a href="index.php?pageid=register">📝 Register</a>
  </aside>

  <!-- Main Content -->
  <div class="main-content">

    <main>
      <!-- Welcome -->
      <div class="welcome-screen active" id="welcomeScreen">
        <div class="welcome-content">
          <h2>Welcome to the Event Management Admin Portal</h2>
          <p>Manage events, registrations, bookings, and services — all in one place.</p>
          <div>
            <button class="button" onclick="showLogin()">Login</button>
            <a href="index.php?pageid=register"><button class="button">Register</button></a>
          </div>
        </div>
      </div>

      <!-- Login -->
      <div class="login-screen" id="loginScreen">
        <div class="formbg">
          <h2 style="text-align: center; color: #004d40;">Admin Login</h2>
          <p style="text-align: center; color: #607d8b; font-size: 14px; margin-bottom: 20px;">
            Please enter your credentials to continue.
          </p>
          <form id="admin-login-form" action="<?= $apiUrl ?>">
            <div class="field">
              <label for="email">Email Address</label>
              <input type="email" name="email" placeholder="admin@example.com" required>
            </div>
            <div class="field">
              <label for="password">Password</label>
              <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="field">
              <input type="submit" value="Login">
            </div>
          </form>
          <div class="back-button" style="margin-top: 15px; text-align: center;">
            <button class="button" onclick="goBack()">← Back to Home</button>
          </div>
        </div>
      </div>
    </main>

    <footer>
      &copy; <?= date("Y") ?> EVENT MANAGEMENT Admin. All rights reserved.
    </footer>
  </div>

  <script>
    function showLogin() {
      $('#welcomeScreen').removeClass('active');
      $('#loginScreen').addClass('active');
    }

    function showWelcome() {
      $('#loginScreen').removeClass('active');
      $('#welcomeScreen').addClass('active');
    }

    function goBack() {
      showWelcome();
    }

    $(document).ready(function () {
      $("#admin-login-form").submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize() + '&mode=adminlogin';
        $.ajax({
          type: "POST",
          url: url,
          data: data,
          success: function (data) {
            if (data.error.code == '#200') {
              swal("Success", "Login Success", "success").then(() => {
                window.location.href = '<?= $adminBaseUrl ?>';
              });
            } else {
              swal("Error", data.error.description, "error");
            }
          }
        });
      });
    });
  </script>

</body>

</html>
