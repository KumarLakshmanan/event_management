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
    } else {
        include("components/login.php");
    }
    exit();
}
