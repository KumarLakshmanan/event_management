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
 * Handle package creation
 */
function handleCreate() {
    // Check if user has manager or admin role
    if (!hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Permission denied']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
    
    // Get input data
    $name = sanitizeInput($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = sanitizeInput($_POST['description'] ?? '');
    $services = isset($_POST['services']) && is_array($_POST['services']) ? $_POST['services'] : [];
    
    // Validate input
    if (empty($name) || $price <= 0 || empty($description) || empty($services)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }
    
    // Get packages data
    $packages = getMockData('packages.json');
    
    // Generate package ID
    $id = count($packages) > 0 ? max(array_column($packages, 'id')) + 1 : 1;
    
    // Process image upload
    $imageUrl = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // In a real application, you would process the upload and save the file
        // For demo, we'll use a placeholder image
        $imageUrl = 'https://via.placeholder.com/500x300';
    }
    
    // Create new package
    $newPackage = [
        'id' => $id,
        'name' => $name,
        'image_url' => $imageUrl,
        'description' => $description,
        'price' => $price,
        'customized' => false,
        'created_by' => $_SESSION['user_id'],
        'created_at' => date('Y-m-d H:i:s'),
        'services' => array_map('intval', $services)
    ];
    
    // Add package to data
    $packages[] = $newPackage;
    
    // Save data
    saveMockData('packages.json', $packages);
    
    // Send API request to external API
    apiPost('packages', $newPackage);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'package' => $newPackage]);
    exit;
}

/**
 * Handle package update
 */
function handleUpdate() {
    // Check if user has manager or admin role
    if (!hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Permission denied']);
        exit;
    }
    
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
    $services = isset($_POST['services']) && is_array($_POST['services']) ? $_POST['services'] : [];
    
    // Validate input
    if ($id <= 0 || empty($name) || $price <= 0 || empty($description) || empty($services)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }
    
    // Get packages data
    $packages = getMockData('packages.json');
    
    // Find package to update
    $packageIndex = -1;
    foreach ($packages as $index => $package) {
        if ($package['id'] === $id) {
            $packageIndex = $index;
            break;
        }
    }
    
    if ($packageIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Package not found']);
        exit;
    }
    
    // Process image upload
    $imageUrl = $packages[$packageIndex]['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // In a real application, you would process the upload and save the file
        // For demo, we'll use a placeholder image
        $imageUrl = 'https://via.placeholder.com/500x300';
    }
    
    // Update package
    $packages[$packageIndex]['name'] = $name;
    $packages[$packageIndex]['image_url'] = $imageUrl;
    $packages[$packageIndex]['description'] = $description;
    $packages[$packageIndex]['price'] = $price;
    $packages[$packageIndex]['services'] = array_map('intval', $services);
    
    // Save data
    saveMockData('packages.json', $packages);
    
    // Send API request to external API
    apiPut('packages/' . $id, $packages[$packageIndex]);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'package' => $packages[$packageIndex]]);
    exit;
}

/**
 * Handle package deletion
 */
function handleDelete() {
    // Check if user has manager or admin role
    if (!hasRole('manager')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Permission denied']);
        exit;
    }
    
    // Get package ID
    $id = intval($_REQUEST['id'] ?? 0);
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid package ID']);
        exit;
    }
    
    // Get packages data
    $packages = getMockData('packages.json');
    
    // Find package to delete
    $packageIndex = -1;
    foreach ($packages as $index => $package) {
        if ($package['id'] === $id) {
            $packageIndex = $index;
            break;
        }
    }
    
    if ($packageIndex === -1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Package not found']);
        exit;
    }
    
    // Remove package from data
    array_splice($packages, $packageIndex, 1);
    
    // Save data
    saveMockData('packages.json', $packages);
    
    // Send API request to external API
    apiDelete('packages/' . $id);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}
?>
