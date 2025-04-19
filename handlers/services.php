<?php
session_start();
require_once '../config/config.php';
require_once 'api.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if user has appropriate role
if ($_SESSION['user_role'] === 'client') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Check action parameter
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        handleCreate();
        break;
        
    case 'update':
        handleUpdate();
        break;
        
    case 'delete':
        handleDelete();
        break;
        
    default:
        // Invalid action
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
        exit;
}
/**
 * Handle service creation
 */
function handleCreate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $name = sanitizeInput($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = sanitizeInput($_POST['description'] ?? '');
    
    // Validate input
    if (empty($name) || $price <= 0 || empty($description)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }
    
    $db = Database::getInstance();

    // Insert new service into database
    $result = $db->execute(
        "INSERT INTO services (name, description, price) VALUES (?, ?, ?)",
        [$name, $description, $price]
    );
    
    if ($result) {
        $newService = [
            'id' => $db->lastInsertId(),
            'name' => $name,
            'description' => $description,
            'price' => $price
        ];
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'service' => $newService]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to create service']);
    }
    exit;
}

/**
 * Handle service update
 */
function handleUpdate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $id = intval($_POST['id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = sanitizeInput($_POST['description'] ?? '');
    
    // Validate input
    if ($id <= 0 || empty($name) || $price <= 0 || empty($description)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }
    
    $db = Database::getInstance();

    // Update service in database
    $result = $db->execute(
        "UPDATE services SET name = ?, description = ?, price = ? WHERE id = ?",
        [$name, $description, $price, $id]
    );
    
    if ($result) {
        $updatedService = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price
        ];
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'service' => $updatedService]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to update service or no changes made']);
    }
    exit;
}

/**
 * Handle service deletion
 */
function handleDelete() {
    // Get service ID
    $id = intval($_REQUEST['id'] ?? 0);
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid service ID']);
        exit;
    }
    
    $db = Database::getInstance();

    // Delete service from database
    $result = $db->execute(
        "DELETE FROM services WHERE id = ?",
        [$id]
    );
    
    if ($result) {
        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to delete service or service not found']);
    }
    exit;
}
