<?php
session_start();
require_once 'config/config.php';

// Verify ownership of booking
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['status'])) {
    $booking_id = isset($_GET['booking_id']) ? htmlspecialchars($_GET['booking_id']) : '';
    $guest_id = isset($_GET['guest_id']) ? htmlspecialchars($_GET['guest_id']) : '';
    if (!empty($booking_id) && !empty($guest_id)) {
        $status = $_GET['status'];
        if ($status == "1") {
            $status = "yes";
        } else {
            $status = "no";
        }
        // Update the status in the database using PDO
        $sql = "UPDATE guests SET rsvp_status = :status WHERE booking_id = :booking_id AND id = :guest_id";
        $params = [
            ':status' => $status,
            ':booking_id' => $booking_id,
            ':guest_id' => $guest_id
        ];

        if ($db->execute($sql, $params)) {
            $successMessage = "Your RSVP has been successfully updated. Thank you!";
        } else {
            $errorMessage = "Oops! Something went wrong while recording your RSVP.";
        }
    } else {
        $errorMessage = "Invalid booking or guest information provided.";
    }
}
?>

<html>

<head>
    <meta charset="utf-8">
    <title><?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            font-family: 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .thankyou-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 40px;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            border-radius: 15px;
            border: 1px solid #ddd;
        }

        h2 {
            color: #4a90e2;
            font-size: 28px;
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }

        .thankyou-container p.success {
            color: #28a745;
            font-weight: bold;
        }

        .thankyou-container p.error {
            color: #dc3545;
            font-weight: bold;
        }

        .thankyou-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4a90e2;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
        }

        .thankyou-container a:hover {
            background: #357ab8;
        }
    </style>
</head>

<body>
    <div class="thankyou-container">
        <?php if (isset($successMessage)): ?>
            <h2>Thank You!</h2>
            <p class="success"><?= $successMessage ?></p>
            <a href="index.php">Go Back to Home</a>
        <?php elseif (isset($errorMessage)): ?>
            <h2>Something Went Wrong</h2>
            <p class="error"><?= $errorMessage ?></p>
            <a href="index.php">Try Again</a>
        <?php else: ?>
            <h2>RSVP Recorded</h2>
            <p>Your response has been successfully saved. We appreciate your time!</p>
            <a href="index.php">Go Back to Home</a>
        <?php endif; ?>
    </div>
</body>

</html>