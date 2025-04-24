<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in, if so redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        header, footer {
            background-color: #007BFF;
            color: #fff;
            text-align: center;
            padding: 15px 0;
        }
        .banner {
            background: linear-gradient(to right, #007BFF, #0056b3);
            color: #fff;
            text-align: center;
            padding: 60px 20px;
            margin-bottom: 30px;
        }
        .banner h1 {
            margin: 0;
            font-size: 42px;
        }
        .banner p {
            font-size: 18px;
            margin-top: 10px;
        }
        main {
            text-align: center;
            padding: 20px;
        }
        .button {
            display: inline-block;
            margin: 15px;
            padding: 12px 25px;
            font-size: 18px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .card-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            padding: 20px;
            text-align: center;
        }
        .card h2 {
            margin: 0 0 10px;
            font-size: 24px;
            color: #007BFF;
        }
        .card p {
            font-size: 16px;
            color: #555;
        }
        footer p {
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="banner">
        <h1>Welcome to Event Management</h1>
        <p>Organize and manage your events effortlessly</p>
    </div>
    <main>
        <p>Please choose an option below:</p>
        <div class="card-container">
            <div class="card">
                <h2>Register</h2>
                <p>Create an account to start managing your events.</p>
                <a href="pages/register.php" class="button">Register</a>
            </div>
            <div class="card">
                <h2>Login</h2>
                <p>Access your account and manage your events.</p>
                <a href="pages/login.php" class="button">Login</a>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Event Management. All rights reserved.</p>
    </footer>
</body>
</html>
