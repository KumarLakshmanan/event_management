<?php
ini_set('error_log', './php-error.log');
$pageId = "index";
if (isset($_GET["pageid"])) {
    $pageId = $_GET["pageid"];
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" type="text/css">
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/richtext.min.css" type="text/css">
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/dashboard.css" type="text/css" />
    <link rel="stylesheet" href="<?= $adminBaseUrl ?>css/dataTables.bootstrap4.min.css">
    <script src="<?= $adminBaseUrl ?>js/jquery.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/sweetalert.js"></script>
    <script src="<?= $adminBaseUrl ?>js/jquery.dataTables.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/dataTables.bootstrap4.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/bootstrap.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/jquery.richtext.min.js"></script>
    <script src="<?= $adminBaseUrl ?>js/custom.js"></script>
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full" data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        <?php include "./partials/sidebar.php"; ?>
        <div class="page-wrapper mt-5">
            <div class="container-fluid">
                <?php
                // Define page map
                $adminPages = [
                    "services" => "modules_view/services.php",
                    "editservice" => "modules_view/services/edit.php",
                    "addservice" => "modules_view/services/add.php",
                    "packages" => "modules_view/packages.php",
                    "editpackage" => "modules_view/packages/edit.php",
                    "addpackage" => "modules_view/packages/add.php",
                    "managers" => "modules_view/managers.php",
                    "editmanager" => "modules_view/member/edit.php",
                    "addmanager" => "modules_view/member/add.php",
                    "clients" => "modules_view/clients.php",
                    "editclient" => "modules_view/users/edit.php",
                    "addclient" => "modules_view/users/add.php",
                    "clientbooking" => "modules_view/clientbooking.php",
                    "editallbooking" => "modules_view/editallbooking.php"
                ];

                $clientPages = [
                    "addcustompackage" => "modules_view/createcustompackage.php",
                    "booking" => "modules_view/booking.php",
                    "editbooking" => "modules_view/editbooking.php",
                    "rsvp_attend" => "mail/mail_read.php",
                    "rsvp_notattend" => "mail/mail_notread.php"
                ];

                // Load correct view based on role and pageId
                if ($_SESSION['role'] != 'client') {
                    include $adminPages[$pageId] ?? "modules_view/services.php";
                } else {
                    include $clientPages[$pageId] ?? "modules_view/explore_bundles.php";
                }
                ?>
            </div>
            <footer class="footer text-center"> Created by <a href="#">EVENT MANAGEMENT</a></footer>
        </div>
    </div>
</body>

</html>
