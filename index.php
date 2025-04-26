<?php
session_start();
include("controllers/config.php");
$db = new Connection();
$pdoConn = $db->getConnection();
if (isset($_SESSION['id']) ) {
    $currentTime = time();
    $authId = $_SESSION['token'];
    $username = $_SESSION['email'];
    $userAuth  = validateSessionToken($pdoConn, $authId, $username);
    if ($userAuth) {
        include("partials/landing.php");
        exit();
    } else {
        include("partials/login.php");
        exit();
    }
} else {
    if (isset($_GET['pageid']) && $_GET['pageid'] == 'register') {
        include("partials/register.php");
    } else if (isset($_GET['pageid']) && $_GET['pageid'] == 'rsvp_attend') {
        include("partials/mail/mail_read.php");
    } else if (isset($_GET['pageid']) && $_GET['pageid'] == 'rsvp_notattend') {
        include("partials/mail/mail_notread.php");
    } else {
        include("partials/login.php");
    }
    exit();
}
