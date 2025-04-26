<?php

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('log_errors', true);
ini_set('error_log', './php-error.log');
require_once "./config.php";
require_once __DIR__ . '/config.php';

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailnew = new PHPMailer(true);
$db = new Connection();
$conn = $db->getConnection();
$json = ["data" => [], "error" => ["code" => "#200", "description" => "Success."]];

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('Asia/Calcutta');

// Validation patterns
const EMAIL_REGEX = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
const PHONE_REGEX = '/^[0-9]{10}$/';
const NAME_REGEX = '/^[a-zA-Z ]{2,30}$/';

if (!isset($_REQUEST["mode"])) {
    $json["error"] = ["code" => "#403", "description" => "Mode is required."];
    echo json_encode($json);
    exit;
}

$mode = $_REQUEST["mode"];
try {
    switch ($mode) {
        case 'adminlogin':
            handleAdminLogin($conn, $json);
            break;

        case 'register':
            handleRegistration($conn, $json);
            break;

        case 'rsvp_attend':
            updateRSVPStatus($conn, 2);
            break;

        case 'rsvp_notattend':
            updateRSVPStatus($conn, 1);
            break;

        case 'addservice':
            handleServiceOperation($conn, 'insert');
            break;

        case 'editservice':
            handleServiceOperation($conn, 'update');
            break;

        case 'deleteservice':
            deleteRecord($conn, 'service', $_REQUEST["serviceid"]);
            break;

        case 'addpackage':
            handlePackageOperation($conn, 'insert');
            break;

        case 'editpackage':
            handlePackageOperation($conn, 'update');
            break;

        case 'deletepackage':
            deletePackage($conn, $_REQUEST["packageid"]);
            break;

        case 'deletebooking':
            deleteBooking($conn, $_REQUEST["bookingid"]);
            break;

        case 'addcustompackage':
            handleCustomPackage($conn);
            break;

        case 'updatestatusbooking':
            updateBookingStatus($conn);
            break;

        case 'deletecustompackage':
            deletePackage($conn, $_REQUEST["packageid"]);
            break;

        case 'addmanager':
            handleUserOperation($conn, 'manager', 'insert');
            break;

        case 'editmanager':
            handleUserOperation($conn, 'manager', 'update');
            break;

        case 'deletemanager':
            deleteRecord($conn, 'users', $_REQUEST["managerid"]);
            break;

        case 'addclient':
            handleUserOperation($conn, 'client', 'insert');
            break;

        case 'editclient':
            handleUserOperation($conn, 'client', 'update');
            break;

        case 'deleteclient':
            deleteRecord($conn, 'users', $_REQUEST["clientid"]);
            break;

        case 'addguest':
            handleGuestOperation($conn, $mailnew, 'insert');
            break;

        case 'getguest':
            fetchGuests($conn);
            break;

        case 'book_event':
            handleEventBooking($conn);
            break;

        default:
            $json["error"] = ["code" => "#403", "description" => "Invalid mode."];
            break;
    }
} catch (Exception $e) {
    $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
}

unset($json["regid"]);
$json['request'] = $_REQUEST;
echo json_encode($json);

// Helper functions
function handleAdminLogin($conn, &$json) {
    $required = ['email', 'password'];
    foreach ($required as $field) {
        if (empty($_REQUEST[$field])) {
            $json["error"] = ["code" => "#400", "description" => "Missing required field: $field"];
            return;
        }
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$_REQUEST['email'], $_REQUEST['password']]);
    $user = $stmt->fetch();

    if (!$user) {
        $json["error"] = ["code" => "#400", "description" => "Invalid credentials"];
        return;
    }

    $_SESSION = array_intersect_key($user, array_flip(['id', 'email', 'fullname', 'role', 'phone', 'discount_permission']));
    $_SESSION['token'] = getSessionToken($conn, $user['email'], $user['id']);

    $json["data"] = $user;
}

function handleRegistration($conn, &$json) {
    $required = ['email', 'password', 'fullname'];
    foreach ($required as $field) {
        if (empty($_REQUEST[$field])) {
            $json["error"] = ["code" => "#400", "description" => "Missing required field: $field"];
            return;
        }
    }

    if (!preg_match(EMAIL_REGEX, $_REQUEST['email'])) {
        $json["error"] = ["code" => "#400", "description" => "Invalid email format"];
        return;
    }

    $stmt = $conn->prepare("INSERT INTO users (email, password, fullname, phone, address, role) 
                           VALUES (?, ?, ?, ?, ?, 'client')");
    $success = $stmt->execute([
        $_REQUEST['email'],
        $_REQUEST['password'],
        $_REQUEST['fullname'],
        $_REQUEST['phone'] ?? null,
        $_REQUEST['address'] ?? null
    ]);

    if (!$success) {
        $json["error"] = ["code" => "#500", "description" => "Registration failed"];
    }
}

function updateRSVPStatus($conn, $status) {
    $stmt = $conn->prepare("UPDATE guests SET rsvp_status = ? WHERE id = ?");
    $stmt->execute([$status, $_REQUEST['guest_id']]);
}

function handleServiceOperation($conn, $operation) {
    $fields = ['service_name', 'description', 'price'];
    $data = array_map(fn($f) => $_REQUEST[$f] ?? null, $fields);

    if ($operation === 'insert') {
        $stmt = $conn->prepare("INSERT INTO service (service_name, description, price) VALUES (?, ?, ?)");
    } else {
        $data[] = $_REQUEST['service_id'];
        $stmt = $conn->prepare("UPDATE service SET service_name = ?, description = ?, price = ? WHERE id = ?");
    }
    
    $stmt->execute($data);
}

function deleteRecord($conn, $table, $id) {
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
}

// Additional helper functions for packages, bookings, users, etc. would follow similar patterns

// COMPLETE HELPER FUNCTIONS
function handlePackageOperation($conn, $operation, $jsonData = null) {
    if ($jsonData === null) {
        $jsonData = $_REQUEST;
    }
    $required = $operation === 'insert' 
        ? ['package_name', 'price', 'description', 'service_types']
        : ['package_id', 'package_name', 'price', 'description', 'service_types'];

    foreach ($required as $field) {
        if (empty($jsonData[$field])) {
            $json["error"] = ["code" => "#400", "description" => "Missing $field"];
            return;
        }
    }

    $packageData = [
        'name' => trim($jsonData['package_name']),
        'price' => (float)$jsonData['price'],
        'desc' => trim($jsonData['description']),
        'services' => json_decode($jsonData['service_types'], true),
        'image' => $jsonData['image'] ?? null
    ];

    if (!is_array($packageData['services'])) {
        $json["error"] = ["code" => "#400", "description" => "Invalid service format"];
        return;
    }

    try {
        $conn->beginTransaction();

        // Package CRUD
        if ($operation === 'insert') {
            $stmt = $conn->prepare("INSERT INTO package (package_name, price, description, image_url) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $packageData['name'],
                $packageData['price'],
                $packageData['desc'],
                $packageData['image']
            ]);
            $packageId = $conn->lastInsertId();
        } else {
            $packageId = (int)$jsonData['package_id'];
            $stmt = $conn->prepare("UPDATE package SET 
                package_name = ?,
                price = ?,
                description = ?,
                image_url = ?
                WHERE id = ?");
            $stmt->execute([
                $packageData['name'],
                $packageData['price'],
                $packageData['desc'],
                $packageData['image'],
                $packageId
            ]);

            // Clear existing services
            $conn->prepare("DELETE FROM package_services WHERE package_id = ?")
                 ->execute([$packageId]);
        }

        // Insert services
        $stmt = $conn->prepare("INSERT INTO package_services (package_id, service_id) VALUES (?, ?)");
        foreach ($packageData['services'] as $serviceId) {
            $stmt->execute([$packageId, (int)$serviceId]);
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
}

function deletePackage($conn, $packageId) {
    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM package_services WHERE package_id = ?")->execute([$packageId]);
        $conn->prepare("DELETE FROM package WHERE id = ?")->execute([$packageId]);
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
}

function handleCustomPackage($conn) {
    // Validate input
    $required = ['package_name', 'price', 'description', 'service_types', 'event_date', 'event_place'];
    $json = [
        "error" => ["code" => "#200", "description" => "Success"],
        "data" => []
    ];
    foreach ($required as $field) {
        if (empty($_REQUEST[$field])) {
            $json["error"] = ["code" => "#400", "description" => "Missing $field"];
            return;
        }
    }

    // Create package first
    $_REQUEST['mode'] = 'addpackage';
    handlePackageOperation($conn, 'insert', $_REQUEST);

    
    if ($json["error"]["code"] !== "#200") return;

    // Get created package ID
    $packageId = $conn->lastInsertId();

    // Create booking
    try {
        $stmt = $conn->prepare("INSERT INTO bookings 
            (package_id, event_date, event_place, user_id, package_type) 
            VALUES (?, ?, ?, ?, 'custom')");
        $stmt->execute([
            $packageId,
            $_REQUEST['event_date'],
            $_REQUEST['event_place'],
            $_SESSION['id']
        ]);
    } catch (Exception $e) {
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
}

function handleUserOperation($conn, $roleType, $operation) {
    $required = ['email', 'password', 'fullname'];
    if ($operation === 'update') $required[] = 'id';

    foreach ($required as $field) {
        if (empty($_REQUEST[$field])) {
            $json["error"] = ["code" => "#400", "description" => "Missing $field"];
            return;
        }
    }

    $userData = [
        'email' => trim($_REQUEST['email']),
        'pass' => trim($_REQUEST['password']),
        'name' => trim($_REQUEST['fullname']),
        'phone' => $_REQUEST['phone'] ?? null,
        'address' => $_REQUEST['address'] ?? null,
        'discount' => ($roleType === 'manager') ? ($_REQUEST['discount_permission'] ?? 0) : 0
    ];

    try {
        if ($operation === 'insert') {
            // Check existing email
            $exists = $conn->prepare("SELECT id FROM users WHERE email = ?")
                         ->execute([$userData['email']])
                         ->fetchColumn();
            if ($exists) {
                $json["error"] = ["code" => "#400", "description" => "Email exists"];
                return;
            }

            $stmt = $conn->prepare("INSERT INTO users 
                (email, password, fullname, phone, address, role, discount_permission)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userData['email'],
                $userData['pass'],
                $userData['name'],
                $userData['phone'],
                $userData['address'],
                $roleType,
                $userData['discount']
            ]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET
                email = ?,
                password = ?,
                fullname = ?,
                phone = ?,
                address = ?,
                discount_permission = ?
                WHERE id = ?");
            $stmt->execute([
                $userData['email'],
                $userData['pass'],
                $userData['name'],
                $userData['phone'],
                $userData['address'],
                $userData['discount'],
                (int)$_REQUEST['id']
            ]);
        }
    } catch (Exception $e) {
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
}

function handleGuestOperation($conn, $mailer) {
    $required = ['booking_id', 'guest_name', 'guest_contact', 'guest_email'];
    foreach ($required as $field) {
        if (empty($_REQUEST[$field])) {
            $json["error"] = ["code" => "#400", "description" => "Missing $field"];
            return;
        }
    }

    try {
        // Insert guest
        $stmt = $conn->prepare("INSERT INTO guests (booking_id, guest_name, guest_contact, guest_email) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            (int)$_REQUEST['booking_id'],
            trim($_REQUEST['guest_name']),
            trim($_REQUEST['guest_contact']),
            trim($_REQUEST['guest_email'])
        ]);
        $guestId = $conn->lastInsertId();

        // Get booking details
        $stmt = $conn->prepare("SELECT b.*, u.email AS user_email 
                              FROM bookings b
                              JOIN users u ON b.user_id = u.id
                              WHERE b.id = ?");
        $stmt->execute([(int)$_REQUEST['booking_id']]);
        $booking = $stmt->fetch();

        $mailer->isSMTP();
        $mailer->Host       = 'smtp.gmail.com';
        $mailer->SMTPAuth   = true;
        $mailer->Username   = GMAIL_USERNAME;
        $mailer->Password   = GMAIL_PASSWORD;
        $mailer->SMTPSecure = 'tls';
        $mailer->Port       = 587;

        $mailer->setFrom(GMAIL_USERNAME, "EVENT MANAGEMENT");
        $mailer->addReplyTo(GMAIL_USERNAME, "EVENT MANAGEMENT");
        // Add recipient
        $mailer->addAddress($_REQUEST['guest_email'], $_REQUEST['guest_name']);
        $acceptLink = $GLOBALS['adminBaseUrl'] . "index.php?guest_id=$guestId&pageid=rsvp_attend&booking_id={$_REQUEST['booking_id']}";
        $declineLink = $GLOBALS['adminBaseUrl'] . "index.php?guest_id=$guestId&pageid=rsvp_notattend&booking_id={$_REQUEST['booking_id']}";

        $mailer->isHTML(true);
        $mailer->Subject = "Event Invitation from {$booking['user_email']}";
        $mailer->Body = generateInvitationEmail(
            $_REQUEST['guest_name'],
            $booking,
            $guestId,
            $acceptLink,
            $declineLink
        );
        $mailer->AltBody = "Dear {$_REQUEST['guest_name']},\nYou're invited to an event on {$booking['event_date']} at {$booking['event_place']}\n\nAccept: $acceptLink\nDecline: $declineLink";
        
        $mailer->send();

    } catch (Exception $e) {
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
}

// Additional helper functions
function generateInvitationEmail($name, $booking, $guestId, $acceptLink, $declineLink) {
    return "
        <h3>Dear " . htmlspecialchars($name) . ",</h3>
        <p>You're invited to an event on " . htmlspecialchars($booking['event_date']) . " at " . htmlspecialchars($booking['event_place']) . "</p>
        <p>RSVP Links:</p>
        <ul>
            <li><a href='" . htmlspecialchars($acceptLink) . "'>Accept</a></li>
            <li><a href='" . htmlspecialchars($declineLink) . "'>Decline</a></li>
        </ul>
    ";
}

function deleteBooking($conn, $bookingId) {
    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM guests WHERE booking_id = ?")->execute([$bookingId]);
        $conn->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bookingId]);
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
}

function updateBookingStatus($conn) {
    $required = ['booking_id', 'booking_status', 'discount_amount'];
    foreach ($required as $field) {
        if (empty($_REQUEST[$field])) {
            $json["error"] = ["code" => "#400", "description" => "Missing $field"];
            return;
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE bookings SET 
            status = ?,
            discount_amount = ?,
            updated_at = NOW()
            WHERE id = ?");
        $stmt->execute([
            $_REQUEST['booking_status'],
            (float)$_REQUEST['discount_amount'],
            (int)$_REQUEST['booking_id']
        ]);
    } catch (Exception $e) {
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
}

function fetchGuests($conn) {
    if (empty($_REQUEST['booking_id'])) {
        $json["error"] = ["code" => "#400", "description" => "Missing booking_id"];
        return;
    }

    try {
        $bookingId = isset($_REQUEST['booking_id']) ? (int)$_REQUEST['booking_id'] : 0;
    
        $stmt = $conn->prepare("SELECT * FROM guests WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
    
        $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $json["error"] = ["code" => "#200", "description" => "Success","data"=>$guests];
        $json["data"] = $guests;
        echo json_encode($json);
        exit;
        // print_r($json);die;
    } catch (PDOException $e) {
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
    
}

function handleEventBooking($conn) {
    $required = ['package_id', 'event_date', 'event_place'];
    foreach ($required as $field) {
        if (empty($_REQUEST[$field])) {
            $json["error"] = ["code" => "#400", "description" => "Missing $field"];
            return;
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO bookings 
            (package_id, event_date, event_place, user_id)
            VALUES (?, ?, ?, ?)");
        $stmt->execute([
            (int)$_REQUEST['package_id'],
            $_REQUEST['event_date'],
            $_REQUEST['event_place'],
            $_SESSION['id']
        ]);
    } catch (Exception $e) {
        $json["error"] = ["code" => "#500", "description" => $e->getMessage()];
    }
}
