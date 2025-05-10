<?php
include_once("../api/constant.php");
session_start();
$json["data"] = [];
session_destroy();
$json["error"] = array("code" => "#200", "description" => "Success.");
header("Location: " . $webAddress);
