<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>IWD: Admin</title>
    <script src="<?php echo $adminBaseUrl ?>js/jquery.min.js"></script>
    <script src="<?php echo $adminBaseUrl ?>js/sweetalert.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Ubuntu, sans-serif;
        }

        body {
            background-color: #f4f6f8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: #5469d4;
            padding: 16px 0;
            text-align: center;
            color: white;
        }

        nav {
            background-color: #3e54a3;
            display: flex;
            justify-content: center;
            padding: 12px 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 20px;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        h1,
        h2 {
            text-align: center;
            margin-bottom: 24px;
            color: #1a1f36;
        }

        .button {
            padding: 12px 24px;
            font-size: 16px;
            background-color: #5469d4;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin: 10px;
        }

        .button:hover {
            background-color: #3e54a3;
        }

        .welcome-screen,
        .login-screen {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .welcome-screen.active,
        .login-screen.active {
            display: flex;
        }

        .formbg {
            max-width: 420px;
            width: 100%;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .field {
            margin-bottom: 20px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1f36;
        }

        .field input[type="email"],
        .field input[type="password"] {
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 100%;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
            transition: border 0.3s ease;
        }

        .field input:focus {
            border-color: #5469d4;
            outline: none;
        }

        input[type="submit"] {
            background-color: #5469d4;
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #3e54a3;
        }

        .back-button {
            margin-top: 20px;
            text-align: center;
        }

        footer {
            background-color: #2e3a59;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .welcome-content {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            max-width: 700px;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>

<body>

    <header>
        <h1>IWD Admin Portal</h1>
    </header>

    <nav>
        <a href="#" onclick="showWelcome()">Home</a>
        <a href="#" onclick="showLogin()">Login</a>
        <a href="index.php?pageid=register">Register</a>
    </nav>

    <main>
        <!-- Welcome Screen -->
        <div class="welcome-screen active" id="welcomeScreen">
            <div class="welcome-content">
                <h2>Welcome to the IWD Admin Portal</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.</p>
                <p>Suspendisse potenti. Morbi mattis ullamcorper velit. Phasellus gravida semper nisi. Nullam vel sem.</p>
                <div>
                    <button class="button" onclick="showLogin()">Login</button>
                    <a href="index.php?pageid=register"><button class="button">Register</button></a>
                </div>
            </div>
        </div>

        <!-- Login Form -->
        <div class="login-screen" id="loginScreen">
            <div class="formbg">
                <h2>Admin Login</h2>
                <form id="admin-login-form" action="<?= $apiUrl ?>">
                    <div class="field">
                        <label for="email">Email</label>
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
                <div class="back-button">
                    <button class="button" onclick="goBack()">← Back</button>
                </div>
            </div>
        </div>
    </main>

    <footer>
        &copy; <?= date("Y") ?> IWD Admin. All rights reserved.
    </footer>

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

        // Handle login AJAX
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
