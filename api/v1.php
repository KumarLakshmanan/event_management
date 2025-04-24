<?php

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('log_errors', true);
ini_set('error_log', './php-error.log');
require_once "./config.php";

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailnew = new PHPMailer(true);
$db = new Connection();
$conn = $db->getConnection();
$json["data"] = [];
$json["error"] = array("code" => "#200", "description" => "Success.");

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('Asia/Calcutta');

$emailRegex  =  '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
$phoneRegex  =  '/^[0-9]{10}$/';
$nameRegex   =  '/^[a-zA-Z ]{2,30}$/';

if (isset($_REQUEST["mode"])) {
    $mode = $_REQUEST["mode"];
    if ($mode == 'adminlogin') {
        if (isset($_REQUEST["email"]) && isset($_REQUEST["password"])) {
            try {
                $email = trim(htmlspecialchars($_REQUEST["email"]));
                $password = trim(htmlspecialchars($_REQUEST["password"]));
                $regid = trim(htmlspecialchars($_REQUEST["regid"] ?? ""));
                if (trim($email) == "" || trim($password) == "") {
                    $json["error"] = array("code" => "#400", "description" => "Please enter email and password.");
                    echo json_encode($json);
                    exit;
                }
                if (strlen($password) < 5) {
                    $json["error"] = array("code" => "#400", "description" => "Password must be at least 5 characters.");
                    echo json_encode($json);
                    exit;
                }
                //  AND role = 'admin'
                $sql = "SELECT * FROM users WHERE email = :email AND password = :password";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":password", $password);
                $stmt->execute();
                $result = $stmt->fetchAll();

                if (count($result) > 0) {
                    $id = $result[0]["id"];
                    $token = getSessionToken($conn, $result[0]['email'], $id);

                    $json["error"] = array("code" => "#200", "description" => "Success.");
                    $json["data"] =  $result[0];
                    $_SESSION['id'] = $id;
                    $_SESSION['email'] = $result[0]["email"];
                    $_SESSION['fullname'] = $result[0]["fullname"];
                    $_SESSION['role'] = $result[0]["role"];
                    $_SESSION['phone'] = $result[0]["phone"];
                    $_SESSION['discount_permission'] = $result[0]["discount_permission"];
                    $_SESSION['token'] = $token;
                    $json["error"] = array("code" => "#200", "description" => "Success.");
                } else {
                    $json["error"] = array("code" => "#400", "description" => "Invalid email or password.");
                }
            } catch (Exception $e) {
                $json["error"] = array("code" => "#500", "description" => $e->getMessage());
            }
        } else {
            $json["error"] = array("code" => "#400", "description" => "email and password are required.");
        }
    } else if ($mode == "register") {
        if (isset($_REQUEST["email"]) && isset($_REQUEST["password"])) {
            try {
                $email = trim(htmlspecialchars($_REQUEST["email"]));
                $password = trim(htmlspecialchars($_REQUEST["password"]));
                $fullname = trim(htmlspecialchars($_REQUEST["fullname"]));
                $phone = trim(htmlspecialchars($_REQUEST["phone"]));
                $address = trim(htmlspecialchars($_REQUEST["address"]));
                if (trim($email) == "" || trim($password) == "") {
                    $json["error"] = array("code" => "#400", "description" => "Please enter email and password.");
                    echo json_encode($json);
                    exit;
                }
                if (strlen($password) < 5) {
                    $json["error"] = array("code" => "#400", "description" => "Password must be at least 5 characters.");
                    echo json_encode($json);
                    exit;
                }
                //  AND role = 'admin'
                $sql = "SELECT * FROM users WHERE email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":email", $email);
                $stmt->execute();
                $result = $stmt->fetchAll();

                if (count($result) > 0) {
                    $json["error"] = array("code" => "#400", "description" => "Email already exists.");
                } else {
                    $sql = "INSERT INTO users (email, password, fullname, phone, address, role) VALUES (:email, :password, :fullname, :phone, :address, 'client')";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":password", $password);
                    $stmt->bindParam(":fullname", $fullname);
                    $stmt->bindParam(":phone", $phone);
                    $stmt->bindParam(":address", $address);
                    if ($stmt->execute()) {
                        $json["error"] = array("code" => "#200", "description" => "Success.");
                    } else {
                        $json["error"] = array("code" => "#500", "description" => "Failed to register. Please try again.");
                    }
                }
            } catch (Exception $e) {
                $json["error"] = array("code" => "#500", "description" => $e->getMessage());
            }
        } else {
            $json["error"] = array("code" => "#400", "description" => "email and password are required.");
        }
    } else if ($mode == 'rsvp_attend') {
        $guest_id = trim(htmlspecialchars($_REQUEST['guest_id']));
        $booking_id = trim(htmlspecialchars($_REQUEST['booking_id']));
        $sql = "UPDATE guests SET rsvp_status = 2 WHERE id = :guest_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":guest_id", $guest_id);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'rsvp_notattend') {
        $guest_id = trim(htmlspecialchars($_REQUEST['guest_id']));
        $booking_id = trim(htmlspecialchars($_REQUEST['booking_id']));
        $sql = "UPDATE guests SET rsvp_status = 1 WHERE id = :guest_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":guest_id", $guest_id);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'addservice') {
        $service_name = trim(htmlspecialchars($_REQUEST['service_name']));
        $description = trim(htmlspecialchars($_REQUEST['description']));
        $price = trim(htmlspecialchars($_REQUEST['price']));
        $sql = "INSERT INTO service ( `service_name`,`price`,`description` ) VALUES ( :service_name, :price, :description )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":service_name", $service_name);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":description", $description);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'editservice') {
        $service_id = trim(htmlspecialchars($_REQUEST['service_id']));
        $service_name = trim(htmlspecialchars($_REQUEST['service_name']));
        $description = trim(htmlspecialchars($_REQUEST['description']));
        $price = trim(htmlspecialchars($_REQUEST['price']));
        $sql = "UPDATE service SET 
                `service_name` = :service_name,`price` = :price,`description` = :description WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":service_name", $service_name);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $service_id);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "deleteservice") {
        $serviceid = $_REQUEST["serviceid"];
        $sql = "DELETE FROM service WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $serviceid);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'addpackage') {
        $package_name = trim(htmlspecialchars($_REQUEST['package_name']));
        $description = trim(htmlspecialchars($_REQUEST['description']));
        $price = trim(htmlspecialchars($_REQUEST['price']));
        $image_url = trim(htmlspecialchars($_REQUEST['image']));
        $service_types = json_decode($_REQUEST['service_types']);


        if (isset($_REQUEST['image'])) {
            $image = $_REQUEST['image'];
            $sql = "INSERT INTO package ( `package_name`,`price`,`description`,`image_url` ) VALUES ( :package_name, :price, :description, :image_url )";
        } else {
            $sql = "INSERT INTO package ( `package_name`,`price`,`description` ) VALUES ( :package_name, :price, :description )";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":package_name", $package_name);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":description", $description);
        if (isset($_REQUEST['image'])) {
            $stmt->bindParam(":image_url", $image_url);
        }
        $stmt->execute();
        $package_id = $conn->lastInsertId();

        foreach ($service_types as $key => $value) {
            $sql = "INSERT INTO package_services (package_id, service_id) VALUES (:package_id, :service_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":package_id", $package_id);
            $stmt->bindParam(":service_id", $value);
            $stmt->execute();
        }

        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'editpackage') {
        $package_id = trim(htmlspecialchars($_REQUEST['package_id']));
        $package_name = trim(htmlspecialchars($_REQUEST['package_name']));
        $description = trim(htmlspecialchars($_REQUEST['description']));
        $price = trim(htmlspecialchars($_REQUEST['price']));
        $image_url = trim(htmlspecialchars($_REQUEST['image']));
        $service_types = json_decode($_REQUEST['service_types']);

        $sql = "DELETE FROM package_services WHERE package_id = :package_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":package_id", $package_id);
        $stmt->execute();

        foreach ($service_types as $key => $value) {
            $sql = "INSERT INTO package_services (package_id, service_id) VALUES (:package_id, :service_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":package_id", $package_id);
            $stmt->bindParam(":service_id", $value);
            $stmt->execute();
        }
        if (isset($_REQUEST['image'])) {
            $sql = "UPDATE package SET 
                `package_name` = :package_name,`price` = :price,`description` = :description, `image_url` = :image_url WHERE id = :id";
        } else {
            $sql = "UPDATE package SET 
                `package_name` = :package_name,`price` = :price,`description` = :description WHERE id = :id";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":package_name", $package_name);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $package_id);
        if (isset($_REQUEST['image'])) {
            $stmt->bindParam(":image_url", $image_url);
        }
        $stmt->execute();
        $package_id = $conn->lastInsertId();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "deletepackage") {
        $packageid = $_REQUEST["packageid"];
        $sql = "DELETE FROM package WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $packageid);
        $stmt->execute();

        $sql = "DELETE FROM package_services WHERE package_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $packageid);
        $stmt->execute();

        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "deletebooking") {
        $bookingid = $_REQUEST["bookingid"];
        $sql = "DELETE FROM booking WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $bookingid);
        $stmt->execute();

        $sql = "DELETE FROM guests WHERE booking_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $bookingid);
        $stmt->execute();

        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'addcustompackage') {
        $package_name = trim(htmlspecialchars($_REQUEST['package_name']));
        $description = trim(htmlspecialchars($_REQUEST['description']));
        $price = trim(htmlspecialchars($_REQUEST['price']));
        $image_url = trim(htmlspecialchars($_REQUEST['image']));
        $service_types = json_decode($_REQUEST['service_types']);
        $type = 'custom';
        $user_id = $_SESSION['id'];
        if (isset($_REQUEST['image'])) {
            $image = $_REQUEST['image'];
            $sql = "INSERT INTO package ( `package_name`,`price`,`description`,`image_url`,`created_by` ) VALUES ( :package_name, :price, :description, :image_url, :created_by )";
        } else {
            $sql = "INSERT INTO package ( `package_name`,`price`,`description` ) VALUES ( :package_name, :price, :description )";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":package_name", $package_name);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":created_by", $user_id);
        if (isset($_REQUEST['image'])) {
            $stmt->bindParam(":image_url", $image_url);
        }
        $stmt->execute();
        $package_id = $conn->lastInsertId();

        foreach ($service_types as $key => $value) {
            $sql = "INSERT INTO package_services (package_id, service_id) VALUES (:package_id, :service_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":package_id", $package_id);
            $stmt->bindParam(":service_id", $value);
            $stmt->execute();
        }

        $event_date = trim(htmlspecialchars($_REQUEST['event_date']));
        $event_place = trim(htmlspecialchars($_REQUEST['event_place']));

        $sql = "INSERT INTO bookings ( `package_id`,`event_date`,`event_place`,`user_id`,`package_type` ) VALUES ( :package_id, :event_date, :event_place, :user_id,:package_type )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":package_id", $package_id);
        $stmt->bindParam(":event_date", $event_date);
        $stmt->bindParam(":event_place", $event_place);
        $stmt->bindParam(":package_type", $type);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'updatestatusbooking') {
        $user_id = $_SESSION['id'];
        $booking_id = $_REQUEST['booking_id'];
        $status = $_REQUEST['booking_status'];
        $discount_amount = $_REQUEST['discount_amount'];

        $sql = "UPDATE bookings SET status = :status, discount_amount = :discount_amount WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":discount_amount", $discount_amount);
        $stmt->bindParam(":id", $booking_id);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "deletecustompackage") {
        $packageid = $_REQUEST["packageid"];
        $sql = "DELETE FROM package WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $packageid);
        $stmt->execute();

        $sql = "DELETE FROM package_services WHERE package_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $packageid);
        $stmt->execute();

        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'addmanager') {
        $email = trim(htmlspecialchars($_REQUEST['email']));
        $password = trim(htmlspecialchars($_REQUEST['password']));
        $fullname = trim(htmlspecialchars($_REQUEST['fullname']));
        $phone = trim(htmlspecialchars($_REQUEST['phone']));
        $role = trim(htmlspecialchars($_REQUEST['role']));
        $discount_permission = trim(htmlspecialchars($_REQUEST['discount_permission']));
        $address = trim(htmlspecialchars($_REQUEST['address']));

        $sql = "INSERT INTO users ( `email`,`fullname`,`password`,`phone`,`role`,`discount_permission`,`address` ) VALUES ( :email, :fullname, :password, :phone, :role, :discount_permission, :address )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":discount_permission", $discount_permission);
        $stmt->bindParam(":address", $address);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'editmanager') {
        $id = trim(htmlspecialchars($_REQUEST['id']));
        $email = trim(htmlspecialchars($_REQUEST['email']));
        $password = trim(htmlspecialchars($_REQUEST['password']));
        $fullname = trim(htmlspecialchars($_REQUEST['fullname']));
        $phone = trim(htmlspecialchars($_REQUEST['phone']));
        $discount_permission = trim(htmlspecialchars($_REQUEST['discount_permission']));
        $address = trim(htmlspecialchars($_REQUEST['address']));
        $sql = "UPDATE users SET `email` = :email,`fullname` = :fullname,`password` = :password,`phone` = :phone,`discount_permission` = :discount_permission,`address` = :address WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":discount_permission", $discount_permission);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "deletemanager") {
        $managerid = $_REQUEST["managerid"];
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $managerid);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'addclient') {
        $email = trim(htmlspecialchars($_REQUEST['email']));
        $password = trim(htmlspecialchars($_REQUEST['password']));
        $fullname = trim(htmlspecialchars($_REQUEST['fullname']));
        $phone = trim(htmlspecialchars($_REQUEST['phone']));
        $role = trim(htmlspecialchars($_REQUEST['role']));
        $address = trim(htmlspecialchars($_REQUEST['address']));
        $sql = "INSERT INTO users ( `email`,`fullname`,`password`,`phone`,`role`,`address`) VALUES ( :email, :fullname, :password, :phone, :role, :address )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":address", $address);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'editclient') {
        $id = trim(htmlspecialchars($_REQUEST['id']));
        $email = trim(htmlspecialchars($_REQUEST['email']));
        $password = trim(htmlspecialchars($_REQUEST['password']));
        $fullname = trim(htmlspecialchars($_REQUEST['fullname']));
        $phone = trim(htmlspecialchars($_REQUEST['phone']));
        $address = trim(htmlspecialchars($_REQUEST['address']));
        $sql = "UPDATE users SET `email` = :email,`fullname` = :fullname,`password` = :password,`phone` = :phone,`address` = :address WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "deleteclient") {
        $clientid = $_REQUEST["clientid"];
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $clientid);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "addguest") {
        $booking_id = $_POST['booking_id'];
        $name = $_POST['guest_name'];
        $contact = $_POST['guest_contact'];
        $guest_email = $_POST['guest_email'];
        // $rsvp_status = $_POST['rsvp_status'];

        $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $booking = $bookings[0];
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$booking['user_id']]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $user = $users[0];

        $sql = "INSERT INTO guests (booking_id, guest_name, guest_contact, guest_email) VALUES (:booking_id, :guest_name, :guest_contact, :guest_email)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":booking_id", $booking_id);
        $stmt->bindParam(":guest_name", $name);
        $stmt->bindParam(":guest_contact", $contact);
        $stmt->bindParam(":guest_email", $guest_email);

        $stmt->execute();
        $guest_id = $conn->lastInsertId();

        $basueurl_rsvp_attend = $adminBaseUrl . "rsvp_attend.php?booking_id=" . $booking_id . "&guest_id=" . $guest_id;
        $basueurl_rsvp_not_attend = $adminBaseUrl . "rsvp_notattend.php?booking_id=" . $booking_id . "&guest_id=" . $guest_id;

        $mailnew->isSMTP();
        $mailnew->Host       = 'smtp.gmail.com';
        $mailnew->SMTPAuth   = true;
        $mailnew->Username   = $mailUsername;
        $mailnew->Password   =  $mailPassword;
        $mailnew->SMTPSecure = 'tls';
        $mailnew->Port       = 587;

        $mailnew->setFrom($mailUsername, $webName);
        $mailnew->addAddress($guest_email, $name);

        // Email Content
        $mailnew->isHTML(true);
        $mailnew->Subject = 'Event Invitation';
        $mailnew->Body    = '<html>
                                <head>
                                    <title>Event Invitation</title>
                                </head>
                                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                                    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                                        <div style="background-color: #4e73df; color: white; padding: 10px 20px; text-align: center;">
                                            <h1>You\'re Invited!</h1>
                                        </div>
                                        <div style="padding: 20px; border: 1px solid #ddd;">
                                            <p>Dear ' . $name . ',</p>
                                            <p>' . $user['fullname'] . ' has invited you to an event. Here are the details:</p>
                                            
                                            <table style="width: 100%; border-collapse: collapse;">
                                                <tr>
                                                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Event Date</th>
                                                    <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">' . date('F d, Y', strtotime($booking['event_date'])) . '</td>
                                                </tr>
                                                <tr>
                                                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Event Place</th>
                                                    <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">' . $booking['event_place'] . '</td>
                                                </tr>
                                            </table>
                                            
                                            <p>Please let us know if you can attend:</p>
                                            
                                            <p style="text-align: center; margin: 30px 0;">
                                                <a href="' . $basueurl_rsvp_attend . '" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Yes, I\'ll Attend</a>
                                                <a href="' . $basueurl_rsvp_not_attend . '" style="display: inline-block; padding: 10px 20px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">No, I Can\'t Attend</a>
                                            </p>
                                            
                                            <p>We hope to see you there!</p>
                                        </div>
                                        <div style="font-size: 12px; text-align: center; margin-top: 20px; color: #666;">
                                            <p>This is an automated email. Please do not reply to this message.</p>
                                            <p>&copy; ' . date('Y') . ' IWD. All rights reserved.</p>
                                        </div>
                                    </div>
                                </body>
                                </html>';
        $mailnew->AltBody = $webName;

        $mailnew->send();


        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "getguest") {
        $booking_id = $_POST['booking_id'] ?? $_GET['booking_id'] ?? null;
        $stmt = $conn->prepare("SELECT * FROM guests WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $json["data"] = $guests;
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == 'book_event') {

        $package_id = trim(htmlspecialchars($_REQUEST['package_id']));
        $event_date = trim(htmlspecialchars($_REQUEST['event_date']));
        $event_place = trim(htmlspecialchars($_REQUEST['event_place']));
        $user_id = $_SESSION['id'];
        $sql = "INSERT INTO bookings ( `package_id`,`event_date`,`event_place`,`user_id` ) VALUES ( :package_id, :event_date, :event_place, :user_id )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":package_id", $package_id);
        $stmt->bindParam(":event_date", $event_date);
        $stmt->bindParam(":event_place", $event_place);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else {
        $json['error'] = array("code" => "#403", "description" => "Invalid mode.");
    }
} else {
    $json["error"] = array("code" => "#403", "description" => "Mode is required.");
}

unset($json["regid"]);
$json['request'] = $_REQUEST;
echo json_encode($json);
