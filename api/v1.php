<?php

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('log_errors', true);
ini_set('error_log', './php-error.log');
include("./config.php");
include("./mail.php");

$db = new Connection();
$conn = $db->getConnection();
$json["data"] = [];
$json["error"] = array("code" => "#200", "description" => "Success.");

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('Asia/Calcutta');

$emailRegex  =  '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
$phoneRegex  =  '/^[0-9]{10}$/';
$nameRegex   =  '/^[a-zA-Z ]{2,30}$/';

function sendGCM($title, $message, $id)
{
    $id = array_filter($id);
    $id = array_unique($id);
    $count = count($id);

    $i = 0;
    $gcmresult = array();
    while ($i < $count) {
        $id1 = array_slice($id, $i, 1000);
        $i = $i + 1000;
        $cmd = "nohup php sendGCM.php '" . json_encode($id1) . "' '$title' '$message' > /dev/null 2>&1 &";
        exec($cmd);
    }
    return $gcmresult;
}

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
                $sql = "SELECT * FROM admins WHERE email = :email AND password = :password";
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
    } else if($mode=="register"){
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
                $sql = "SELECT * FROM admins WHERE email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":email", $email);
                $stmt->execute();
                $result = $stmt->fetchAll();
                
                if (count($result) > 0) {
                    $json["error"] = array("code" => "#400", "description" => "Email already exists.");
                } else {
                    $sql = "INSERT INTO admins (email, password, fullname, phone, address, role) VALUES (:email, :password, :fullname, :phone, :address, 'client')";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":password", $password);
                    $stmt->bindParam(":fullname", $fullname);
                    $stmt->bindParam(":phone", $phone);
                    $stmt->bindParam(":address", $address);
                    if ($stmt->execute()) {
                        // Send email to user
                        // sendEmail($email, "Registration Successful", "Welcome to our service. Your account has been created successfully.");
                        // // Send SMS to user
                        // sendSMS($phone, "Welcome to our service. Your account has been created successfully.");
                        // // Send push notification to user
                        // sendGCM("Registration Successful", "Welcome to our service. Your account has been created successfully.", array($email));
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
    } else if ($mode == "primarysettings") {
        foreach ($_REQUEST as $key => $value) {
            if ($key == "mode") {
                continue;
            } else {
                $sql = "SELECT  * FROM `settings` WHERE name = :name";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":name", $key);
                $stmt->execute();
                // if the settings value is in database then update else insert
                if ($stmt->rowCount() > 0) {
                    $sql = "UPDATE settings SET value = :value WHERE name = :name";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":name", $key);
                    $stmt->bindParam(":value", $value);
                    $stmt->execute();
                } else {
                    $sql = "INSERT INTO settings (name, value) VALUES (:name, :value)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":name", $key);
                    $stmt->bindParam(":value", $value);
                    $stmt->execute();
                }
            }
        }
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

        foreach($service_types as $key => $value) {
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

        foreach($service_types as $key => $value) {
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
    }else if ($mode == "deletebooking") {
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

        foreach($service_types as $key => $value) {
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
    } else if($mode == 'updatestatusbooking') {
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

        $sql = "INSERT INTO admins ( `email`,`fullname`,`password`,`phone`,`role`,`discount_permission`,`address` ) VALUES ( :email, :fullname, :password, :phone, :role, :discount_permission, :address )";
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
        $sql = "UPDATE admins SET `email` = :email,`fullname` = :fullname,`password` = :password,`phone` = :phone,`discount_permission` = :discount_permission,`address` = :address WHERE id = :id";
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
        $sql = "DELETE FROM admins WHERE id = :id";
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
        $sql = "INSERT INTO admins ( `email`,`fullname`,`password`,`phone`,`role`,`address`) VALUES ( :email, :fullname, :password, :phone, :role, :address )";
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
        $sql = "UPDATE admins SET `email` = :email,`fullname` = :fullname,`password` = :password,`phone` = :phone,`address` = :address WHERE id = :id";
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
        $sql = "DELETE FROM admins WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $clientid);
        $stmt->execute();
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "addguest") {
        $booking_id = $_POST['booking_id'];
        $name = $_POST['guest_name'];
        $contact = $_POST['guest_contact'];
        $guest_email = $_POST['guest_email'];
        $rsvp_status = $_POST['rsvp_status'];

        $sql = "INSERT INTO guests (booking_id, guest_name, guest_contact, guest_email, rsvp_status) VALUES (:booking_id, :guest_name, :guest_contact, :guest_email, :rsvp_status)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":booking_id", $booking_id);
        $stmt->bindParam(":guest_name", $name);
        $stmt->bindParam(":guest_contact", $contact);
        $stmt->bindParam(":guest_email", $guest_email);
        $stmt->bindParam(":rsvp_status", $rsvp_status);
        $stmt->execute();

        // $stmt = $conn->prepare("INSERT INTO guests (booking_id, guest_name, guest_contact, guest_email) VALUES (?, ?, ?, ?)");
        // $stmt->execute([$booking_id, $name, $contact, $status]);
        $json["error"] = array("code" => "#200", "description" => "Success.");
    }else if ($mode == "getguest") {
        $booking_id = $_POST['booking_id'] ?? $_GET['booking_id'] ?? null;
        $stmt = $conn->prepare("SELECT * FROM guests WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $json["data"] = $guests;
        $json["error"] = array("code" => "#200", "description" => "Success.");
    }else if ($mode == 'book_event') {
        
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
    } else if ($mode == "contactform") {
        if (isset($_REQUEST["name"]) && isset($_REQUEST["email"]) && isset($_REQUEST["phone"]) && isset($_REQUEST["subject"]) && isset($_REQUEST["message"])) {
            try {
                $name = $_REQUEST["name"];
                $email = $_REQUEST["email"];
                $phone = $_REQUEST["phone"];
                $subject = $_REQUEST["subject"];
                $message = $_REQUEST["message"];
                if ($name == "" || $email == "" || $message == "" || $phone == "" || $subject == "") {
                    $json["error"] = array("code" => "#400", "description" => "All fields are required.");
                } else if (!preg_match($emailRegex, $email)) {
                    $json["error"] = array("code" => "#400", "description" => "Invalid email.");
                } else if (!preg_match($phoneRegex, $phone)) {
                    $json["error"] = array("code" => "#400", "description" => "Invalid phone.");
                } else {
                    $name = htmlspecialchars($name);
                    $email = htmlspecialchars($email);
                    $message = htmlspecialchars($message);
                    $phone = htmlspecialchars($phone);
                    $subject = htmlspecialchars($subject);
                    $created_at = date("Y-m-d H:i:s");
                    $sql = "INSERT INTO contact_forms (name, email, phone, subject, message, created_at) VALUES (:name, :email, :phone, :subject, :message, :created_at)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":name", $name);
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":phone", $phone);
                    $stmt->bindParam(":subject", $subject);
                    $stmt->bindParam(":message", $message);
                    $stmt->bindParam(":created_at", $created_at);
                    $stmt->execute();
                    sendContactMessage($name, $email, $phone, $subject, $message);
                    $json["error"] = array("code" => "#200", "description" => "Contact form submitted successfully.");
                }
            } catch (Exception $e) {
                $json["error"] = array("code" => "#500", "description" => $e->getMessage());
            }
        } else {
            $json["error"] = array("code" => "#400", "description" => "Username and password are required.");
        }
    } else if ($mode == "bookform") {
        if (isset($_REQUEST["name"]) && isset($_REQUEST["email"]) && isset($_REQUEST["phone"]) && isset($_REQUEST["youarea"]) && isset($_REQUEST["bustype"]) && isset($_REQUEST["noofbus"]) && isset($_REQUEST["fromdate"]) && isset($_REQUEST["todate"]) && isset($_REQUEST["message"])) {
            try {
                $name = $_REQUEST["name"];
                $email = $_REQUEST["email"];
                $phone = $_REQUEST["phone"];
                $youarea = $_REQUEST["youarea"];
                $bustype = $_REQUEST["bustype"];
                $noofbus = $_REQUEST["noofbus"];
                $fromdate = $_REQUEST["fromdate"];
                $todate = $_REQUEST["todate"];
                $message = $_REQUEST["message"];
                $fromdate = date("Y-m-d", strtotime($fromdate));
                $todate = date("Y-m-d", strtotime($todate));

                if ($name == "" || $email == "" || $message == "" || $phone == "" || $youarea == "" || $bustype == "" || $noofbus == "" || $fromdate == "" || $todate == "") {
                    $json["error"] = array("code" => "#400", "description" => "All fields are required.");
                } else if (!preg_match($emailRegex, $email)) {
                    $json["error"] = array("code" => "#400", "description" => "Invalid email.");
                } else if (!preg_match($phoneRegex, $phone)) {
                    $json["error"] = array("code" => "#400", "description" => "Invalid phone.");
                } else if ($fromdate > $todate) {
                    $json["error"] = array("code" => "#400", "description" => "From date should be less than to date.");
                } else {
                    $name = htmlspecialchars($name);
                    $email = htmlspecialchars($email);
                    $message = htmlspecialchars($message);
                    $phone = htmlspecialchars($phone);
                    $created_at = date("Y-m-d H:i:s");
                    $sql = "INSERT INTO booking_forms (name, email, phone, youarea, bustype, noofbus, fromdate, todate, message, created_at) VALUES (:name, :email, :phone, :youarea, :bustype, :noofbus, :fromdate, :todate, :message, :created_at)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":name", $name);
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":phone", $phone);
                    $stmt->bindParam(":youarea", $youarea);
                    $stmt->bindParam(":bustype", $bustype);
                    $stmt->bindParam(":noofbus", $noofbus);
                    $stmt->bindParam(":fromdate", $fromdate);
                    $stmt->bindParam(":todate", $todate);
                    $stmt->bindParam(":message", $message);
                    $stmt->bindParam(":created_at", $created_at);
                    $stmt->execute();
                    sendEnquiryForm($name, $email, $phone, $youarea, $bustype, $noofbus, $fromdate, $todate, $message);
                    $json["error"] = array("code" => "#200", "description" => "Booking request submitted successfully.");
                }
            } catch (Exception $e) {
                $json["error"] = array("code" => "#500", "description" => $e->getMessage());
            }
        } else {
            $json["error"] = array("code" => "#400", "description" => "All fields are required.");
        }
    } else if ($mode == "closeMessage") {
        $id = $_REQUEST["id"] ?? "";
        if ($id == "") {
            $json["error"] = array("code" => "#400", "description" => "Invalid request.");
        } else {
            $sql = "UPDATE contact_forms SET status = 'read' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $json["error"] = array("code" => "#200", "description" => "Success.");
        }
    } else if ($mode == "openMessage") {
        $id = $_REQUEST["id"] ?? "";
        if ($id == "") {
            $json["error"] = array("code" => "#400", "description" => "Invalid request.");
        } else {
            $sql = "UPDATE contact_forms SET status = 'unread' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $json["error"] = array("code" => "#200", "description" => "Success.");
        }
    } else if ($mode == "deleteMessage") {
        $id = $_REQUEST["id"] ?? "";
        if ($id == "") {
            $json["error"] = array("code" => "#400", "description" => "Invalid request.");
        } else {
            $sql = "DELETE FROM contact_forms WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $json["error"] = array("code" => "#200", "description" => "Success.");
        }
    } else if ($mode == "closeEnquiry") {
        $id = $_REQUEST["id"] ?? "";
        if ($id == "") {
            $json["error"] = array("code" => "#400", "description" => "Invalid request.");
        } else {
            $sql = "UPDATE booking_forms SET status = 'read' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $json["error"] = array("code" => "#200", "description" => "Success.");
        }
    } else if ($mode == "openEnquiry") {
        $id = $_REQUEST["id"] ?? "";
        if ($id == "") {
            $json["error"] = array("code" => "#400", "description" => "Invalid request.");
        } else {
            $sql = "UPDATE booking_forms SET status = 'unread' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $json["error"] = array("code" => "#200", "description" => "Success.");
        }
    } else if ($mode == "deleteEnquiry") {
        $id = $_REQUEST["id"] ?? "";
        if ($id == "") {
            $json["error"] = array("code" => "#400", "description" => "Invalid request.");
        } else {
            $sql = "DELETE FROM booking_forms WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $json["error"] = array("code" => "#200", "description" => "Success.");
        }
    } else if ($mode == "saveautoreplies") {
        $message_pattern = json_decode($_REQUEST["message_pattern"]);
        $reply_message = json_decode($_REQUEST["reply_message"]);
        $sql = "DELETE FROM auto_replies";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $i = 0;
        foreach ($message_pattern as $key => $value) {
            $sql = "INSERT INTO auto_replies (message_pattern, reply_message) VALUES (:message_pattern, :reply_message)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":message_pattern", $value);
            $stmt->bindParam(":reply_message", $reply_message[$i]);
            $stmt->execute();
            $i++;
        }
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "savesuggestions") {
        $suggestion_message = json_decode($_REQUEST["suggestion_message"]);
        $suggestion_button = json_decode($_REQUEST["suggestion_button"]);
        $sql = "DELETE FROM chat_suggestions";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $i = 0;
        foreach ($suggestion_message as $key => $value) {
            $sql = "INSERT INTO chat_suggestions (suggestion_message, suggestion_button) VALUES (:suggestion_message, :suggestion_button)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":suggestion_message", $value);
            $stmt->bindParam(":suggestion_button", $suggestion_button[$i]);
            $stmt->execute();
            $i++;
        }
        $json["error"] = array("code" => "#200", "description" => "Success.");
    } else if ($mode == "generate_reply") {
        $message = $_REQUEST["message"];
        $chatId = $_SESSION["chatId"] ?? "";
        if ($chatId == "") {
            $chatId = time() . "_" . substr(uniqid(), 0, 10);
            $_SESSION["chatId"] = $chatId;
            $sql = "INSERT INTO chats (chat_id, last_message, created_at, updated_at) VALUES (:chat_id, :last_message, :created_at, :updated_at)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":chat_id", $chatId);
            $stmt->bindParam(":last_message", $message);
            $stmt->bindParam(":created_at", date("Y-m-d H:i:s"));
            $stmt->bindParam(":updated_at", date("Y-m-d H:i:s"));
            $stmt->execute();
        }
        $sql = "SELECT * FROM `auto_replies` WHERE :user_message LIKE message_pattern";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_message", $message);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            $sql = "SELECT suggestion_button FROM `chat_suggestions` WHERE :user_message LIKE suggestion_message";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":user_message", $result[0]["reply_message"]);
            $stmt->execute();
            $suggestion_buttons = $stmt->fetchAll();
            $btns = array();
            foreach ($suggestion_buttons as $key => $value) {
                $svalue = preg_split('/\r\n|\r|\n/', $value['suggestion_button']);
                $svalue = array_filter($svalue);
                for ($j = 0; $j < count($svalue); $j++) {
                    $btns[] = $svalue[$j];
                }
            }
            $json["data"] = [
                "reply" => $result[0]["reply_message"],
                "suggestions" => $btns
            ];
        } else {
            $sql = "SELECT * FROM `settings` WHERE name = 'no_saved_reply'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $sql = "SELECT suggestion_button FROM `chat_suggestions` WHERE :user_message LIKE suggestion_message";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":user_message", $result[0]["value"]);
            $stmt->execute();
            $suggestion_buttons = $stmt->fetchAll();
            $btns = array();
            for ($i = 0; $i < count($suggestion_buttons); $i++) {
                $value = preg_split('/\r\n|\r|\n/', $suggestion_buttons[$i]["suggestion_button"]);
                $value = array_filter($value);
                for ($j = 0; $j < count($value); $j++) {
                    $btns[] = $value[$j];
                }
            }
            $json["data"] = [
                "reply" => $result[0]["value"],
                "suggestions" => $btns
            ];
        }
        $sql = "INSERT INTO chat_history (chat_id, user_message, reply_message) VALUES (:chat_id, :user_message, :reply_message)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":chat_id", $chatId);
        $stmt->bindParam(":user_message", $message);
        $stmt->bindParam(":reply_message", $json["data"]["reply"]);
        $stmt->execute();

        $sql = "UPDATE chats SET last_message = :last_message, updated_at = :updated_at WHERE chat_id = :chat_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":last_message", $json["data"]["reply"]);
        $stmt->bindParam(":updated_at", date("Y-m-d H:i:s"));
        $stmt->bindParam(":chat_id", $chatId);
        $stmt->execute();
    } else if ($mode == "save_chat") {
        $phone = $_REQUEST["phone"] ?? "";
        $name = $_REQUEST["name"] ?? "";
        $email = $_REQUEST["email"] ?? "";
        $chatId = $_SESSION["chatId"] ?? "";

        if ($name != "") {
            $sql = "UPDATE chats SET name = :name WHERE chat_id = :chat_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":chat_id", $chatId);
            $stmt->execute();

            $sql = "INSERT INTO chat_history (chat_id, user_message, reply_message) VALUES (:chat_id, :user_message, :reply_message)";
            $stmt = $conn->prepare($sql);
            $message = "Hello " . $name . ", Welcome to IWD.";
            $stmt->bindParam(":chat_id", $chatId);
            $stmt->bindParam(":user_message", $name);
            $stmt->bindParam(":reply_message", $message);
            $stmt->execute();
        }
        if ($phone != "") {
            $sql = "UPDATE chats SET phone = :phone WHERE chat_id = :chat_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":phone", $phone);
            $stmt->bindParam(":chat_id", $chatId);
            $stmt->execute();

            $sql = "INSERT INTO chat_history (chat_id, user_message, reply_message) VALUES (:chat_id, :user_message, :reply_message)";
            $stmt = $conn->prepare($sql);
            $message = "Thanks for providing your phone number.";
            $stmt->bindParam(":chat_id", $chatId);
            $stmt->bindParam(":user_message", $phone);
            $stmt->bindParam(":reply_message", $message);
            $stmt->execute();

            $sql = "SELECT * FROM `chat_history` WHERE chat_id = :chat_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":chat_id", $chatId);
            $stmt->execute();
            $messages = $stmt->fetchAll();
            sendFirstMessage($chatId, $messages);
        }
        if ($email != "") {
            $sql = "UPDATE chats SET email = :email WHERE chat_id = :chat_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":chat_id", $chatId);
            $stmt->execute();
        }
    } else if ($mode == "get_chats") {
        $chatId = $_SESSION["chatId"] ?? "";
        $sql = "SELECT * FROM chats WHERE chat_id = :chat_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":chat_id", $chatId);
        $stmt->execute();
        $result = $stmt->fetch();
        $json["data"]['chat'] = $result;
        if ($result) {
            $sql = "SELECT * FROM chat_history WHERE chat_id = :chat_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":chat_id", $chatId);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $json["data"]["history"] = $result;
        } else {
            $json["data"]["history"] = [];
        }
    } else if ($mode == "delete_chatid") {
        $chatId = $_REQUEST["chat_id"] ?? "";
        $sql = "DELETE FROM chats WHERE chat_id = :chat_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":chat_id", $chatId);
        $stmt->execute();
    } else {
        $json['error'] = array("code" => "#403", "description" => "Invalid mode.");
    }
} else {
    $json["error"] = array("code" => "#403", "description" => "Mode is required.");
}

unset($json["regid"]);
$json['request'] = $_REQUEST;
echo json_encode($json);
