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
    
    // Get services data
    $services = getMockData('services.json');
    
    // Generate service ID
    $id = count($services) > 0 ? max(array_column($services, 'id')) + 1 : 1;
    
    // Create new service
    $newService = [
        'id' => $id,
        'name' => $name,
        'description' => $description,
        'price' => $price
    ];
    
    // Add service to data
    $services[] = $newService;
    
    // Save data
    saveMockData('services.json', $services);
    
    // Send API request to external API
    apiPost('services', $newService);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'service' => $newService]);
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
    
    // Get services data
    $services = getMockData('services.json');
    
    // Find service to update
    $serviceIndex = -1;
    foreach ($services as $index => $service) {
        if ($service['id'] === $id) {
            $serviceIndex = $index;
            break;
        }
    }
    
    if ($serviceIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Service not found']);
        exit;
    }
    
    // Update service
    $services[$serviceIndex]['name'] = $name;
    $services[$serviceIndex]['description'] = $description;
    $services[$serviceIndex]['price'] = $price;
    
    // Save data
    saveMockData('services.json', $services);
    
    // Send API request to external API
    apiPut('services/' . $id, $services[$serviceIndex]);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'service' => $services[$serviceIndex]]);
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
    
    // Get services data
    $services = getMockData('services.json');
    
    // Find service to delete
    $serviceIndex = -1;
    foreach ($services as $index => $service) {
        if ($service['id'] === $id) {
            $serviceIndex = $index;
            break;
        }
    }
    
    if ($serviceIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Service not found']);
        exit;
    }
    
    // Remove service from data
    array_splice($services, $serviceIndex, 1);
    
    // Save data
    saveMockData('services.json', $services);
    
    // Send API request to external API
    apiDelete('services/' . $id);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}
?>
