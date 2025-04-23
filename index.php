<?php
session_start();
include("api/config.php");
$db = new Connection();
$pdoConn = $db->getConnection();
// $page = isset($_GET['pageid']) ? $_GET['pageid'] : 'home';
// && $_SESSION['role'] == 'admin'
if (isset($_SESSION['id']) ) {
    $currentTime = time();
    $authId = $_SESSION['token'];
    $username = $_SESSION['email'];
    $userAuth  = validateSessionToken($pdoConn, $authId, $username);
    if ($userAuth) {
        include("components/dashboard.php");
        exit();
    } else {
        include("components/login.php");
        exit();
    }
} else {
    // include("components/login.php");
    if (isset($_GET['pageid']) && $_GET['pageid'] == 'register') {
        include("components/register.php");
    } else if (isset($_GET['pageid']) && $_GET['pageid'] == 'rsvp_attend') {
        include("components/rsvp_attend.php");
    } else if (isset($_GET['pageid']) && $_GET['pageid'] == 'rsvp_notattend') {
        include("components/rsvp_notattend.php");
    } else {
        include("components/login.php");
    }
    exit();
}
