<?php
// https://dev.codingfrontend.in/
$apiUrl  = "http://localhost/iwd/api/v1.php";
$baseUrl  = "http://localhost/iwd/";
$adminBaseUrl  = "http://localhost/iwd/";
$webAddress = "http://localhost/iwd/";
$assetVersion = "1.0.0";
global $webAddress;
$websiteAddress = "http://localhost/iwd/";
$baseDirectory = "D:\\xampp\\htdocs\\iwd\\";
$serverKey = 'AAAAC_FcUL8:APA91bECMrLMS7qT_OuCJPNhMbDlnmVu89FbVer1qrxDgrNaa392i1YBOekvWdnYN9NI6B86G59zt_gJxLarVbQ_nucu3fdFcxFpT8eIpQDRKKfdbF1So8DeVladgchZ3xwwpmjGfAmj';

$getErrorCode =  array("code" => "#101", "description" => "Get request not allowed.");
$postErrorCode =  array("code" => "#102", "description" => "Post request not allowed.");
$invalidRequestErrorCode =  array("code" => "#103", "description" => "Invalid request.");
$invalidTokenErrorCode =  array("code" => "#104", "description" => "Invalid token.");
$invalidUsernameErrorCode =  array("code" => "#105", "description" => "Invalid username.");
$unauthorizedErrorCode =  array("code" => "#105", "description" => "Unauthorized access.");
$invalidIdErrorCode =  array("code" => "#106", "description" => "Invalid id.");
$invalidEmailErrorCode =  array("code" => "#107", "description" => "Invalid email.");
$invalidPasswordErrorCode =  array("code" => "#108", "description" => "Invalid password.");
$invalidPhoneErrorCode =  array("code" => "#109", "description" => "Invalid phone.");
$invalidNameErrorCode =  array("code" => "#110", "description" => "Invalid name.");
$invalidCategoryErrorCode =  array("code" => "#111", "description" => "Invalid category.");
$invalidUserOrPass =  array("code" => "#112", "description" => "Invalid username or password.");
$somethingWentWrong =  array("code" => "#113", "description" => "Something went wrong.");
$permissionDenied =  array("code" => "#114", "description" => "Permission denied.");
$fileNotFound =  array("code" => "#115", "description" => "File not found.");
$pleaseFillAll =  array("code" => "#116", "description" => "Please fill all the fields.");
$successErrorCode =  array("code" => "#200", "description" => "Success.");

$webName = "IWD";
$webLogo = $webAddress . "/img/logo-white.png";
$webDescription = "IWD";