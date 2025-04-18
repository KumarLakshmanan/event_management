<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    respondWithError('Unauthorized', 401);
    exit;
}

// Get the requested action
$action = sanitizeInput($_POST['action'] ?? $_GET['action'] ?? '');

switch ($action) {
    case 'create':
        handleCreatePackage();
        break;
    case 'create_custom':
        handleCreateCustomPackage();
        break;
    case 'update':
        handleUpdatePackage();
        break;
    case 'delete':
        handleDeletePackage();
        break;
    default:
        respondWithError('Invalid action specified');
        break;
}

/**
 * Handle package creation
 */
function handleCreatePackage() {
    // Check if user has permission (managers/admins only)
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
        respondWithError('Permission denied', 403);
        return;
    }
    
    // Validate input
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $services = $_POST['services'] ?? [];
    
    if (empty($name) || empty($description) || $price <= 0 || empty($services)) {
        respondWithError('All fields are required');
        return;
    }
    
    // Insert package into database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Insert package
            $packageData = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'customized' => 'false',
                'created_by' => $_SESSION['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle image upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = handleImageUpload($_FILES['image']);
                if ($imagePath) {
                    $packageData['image_url'] = $imagePath;
                }
            }
            
            // Insert package and get ID
            $packageId = insertRecord('packages', $packageData);
            
            if ($packageId) {
                // Insert package services
                foreach ($services as $serviceId) {
                    $db->execute(
                        "INSERT INTO package_services (package_id, service_id) VALUES (?, ?)",
                        [$packageId, $serviceId]
                    );
                }
                
                // Commit transaction
                $db->commit();
                
                respondWithSuccess('Package created successfully', ['id' => $packageId]);
            } else {
                throw new Exception('Failed to create package');
            }
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            respondWithError('Error creating package: ' . $e->getMessage());
        }
    } else {
        // Fallback to mock data
        $packages = getMockData('packages.json');
        
        // Generate package ID
        $id = count($packages) > 0 ? max(array_column($packages, 'id')) + 1 : 1;
        
        // Create new package
        $newPackage = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image_url' => '',
            'customized' => false,
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s'),
            'services' => $services
        ];
        
        // Handle image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = handleImageUpload($_FILES['image']);
            if ($imagePath) {
                $newPackage['image_url'] = $imagePath;
            }
        }
        
        // Add package to data
        $packages[] = $newPackage;
        
        // Save data
        saveMockData('packages.json', $packages);
        
        respondWithSuccess('Package created successfully', ['id' => $id]);
    }
}

/**
 * Handle custom package creation by clients
 */
function handleCreateCustomPackage() {
    // Check if user is a client
    if ($_SESSION['user_role'] !== 'client') {
        respondWithError('Permission denied', 403);
        return;
    }
    
    // Validate input
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $services = $_POST['services'] ?? [];
    $created_by = $_SESSION['user_id'];
    
    if (empty($name) || empty($description) || $price <= 0 || empty($services)) {
        respondWithError('All fields are required');
        return;
    }
    
    // Insert custom package into database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Insert package
            $packageData = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'customized' => 'true', // Mark as custom package
                'created_by' => $created_by,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Insert package and get ID
            $packageId = insertRecord('packages', $packageData);
            
            if ($packageId) {
                // Insert package services
                foreach ($services as $serviceId) {
                    $db->execute(
                        "INSERT INTO package_services (package_id, service_id) VALUES (?, ?)",
                        [$packageId, $serviceId]
                    );
                }
                
                // Commit transaction
                $db->commit();
                
                respondWithSuccess('Custom package created successfully', ['id' => $packageId]);
            } else {
                throw new Exception('Failed to create custom package');
            }
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            respondWithError('Error creating custom package: ' . $e->getMessage());
        }
    } else {
        // Fallback to mock data
        $packages = getMockData('packages.json');
        
        // Generate package ID
        $id = count($packages) > 0 ? max(array_column($packages, 'id')) + 1 : 1;
        
        // Create new custom package
        $newPackage = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image_url' => '',
            'customized' => true, // Mark as custom package
            'created_by' => $created_by,
            'created_at' => date('Y-m-d H:i:s'),
            'services' => $services
        ];
        
        // Add package to data
        $packages[] = $newPackage;
        
        // Save data
        saveMockData('packages.json', $packages);
        
        respondWithSuccess('Custom package created successfully', ['id' => $id]);
    }
}

/**
 * Handle package update
 */
function handleUpdatePackage() {
    // Check if user has permission (managers/admins only)
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
        respondWithError('Permission denied', 403);
        return;
    }
    
    // Validate input
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $services = $_POST['services'] ?? [];
    
    if (!$id || empty($name) || empty($description) || $price <= 0 || empty($services)) {
        respondWithError('All fields are required');
        return;
    }
    
    // Update package in database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Update package
            $packageData = [
                'name' => $name,
                'description' => $description,
                'price' => $price
            ];
            
            // Handle image upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = handleImageUpload($_FILES['image']);
                if ($imagePath) {
                    $packageData['image_url'] = $imagePath;
                }
            }
            
            // Update package
            $result = updateRecord('packages', $id, $packageData);
            
            if ($result) {
                // Delete existing package services
                $db->execute("DELETE FROM package_services WHERE package_id = ?", [$id]);
                
                // Insert updated package services
                foreach ($services as $serviceId) {
                    $db->execute(
                        "INSERT INTO package_services (package_id, service_id) VALUES (?, ?)",
                        [$id, $serviceId]
                    );
                }
                
                // Commit transaction
                $db->commit();
                
                respondWithSuccess('Package updated successfully');
            } else {
                throw new Exception('Failed to update package');
            }
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            respondWithError('Error updating package: ' . $e->getMessage());
        }
    } else {
        // Fallback to mock data
        $packages = getMockData('packages.json');
        $updated = false;
        
        foreach ($packages as $index => $package) {
            if ($package['id'] == $id) {
                $packages[$index]['name'] = $name;
                $packages[$index]['description'] = $description;
                $packages[$index]['price'] = $price;
                $packages[$index]['services'] = $services;
                
                // Handle image upload if provided
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imagePath = handleImageUpload($_FILES['image']);
                    if ($imagePath) {
                        $packages[$index]['image_url'] = $imagePath;
                    }
                }
                
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            saveMockData('packages.json', $packages);
            respondWithSuccess('Package updated successfully');
        } else {
            respondWithError('Package not found');
        }
    }
}

/**
 * Handle package deletion
 */
function handleDeletePackage() {
    // Check if user has permission (managers/admins only)
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
        respondWithError('Permission denied', 403);
        return;
    }
    
    // Validate input
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
    
    if (!$id) {
        respondWithError('Invalid package ID');
        return;
    }
    
    // Delete package from database or mock data
    if (USE_DATABASE) {
        $db = Database::getInstance();
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Delete package services first (cascading should handle this, but just to be sure)
            $db->execute("DELETE FROM package_services WHERE package_id = ?", [$id]);
            
            // Delete package
            $result = $db->execute("DELETE FROM packages WHERE id = ?", [$id]);
            
            if ($result) {
                // Commit transaction
                $db->commit();
                
                // Redirect back to packages page
                header("Location: ../pages/packages.php");
                exit;
            } else {
                throw new Exception('Failed to delete package');
            }
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            
            // Redirect with error
            header("Location: ../pages/packages.php?error=" . urlencode('Error deleting package: ' . $e->getMessage()));
            exit;
        }
    } else {
        // Fallback to mock data
        $packages = getMockData('packages.json');
        $deleted = false;
        
        foreach ($packages as $index => $package) {
            if ($package['id'] == $id) {
                array_splice($packages, $index, 1);
                $deleted = true;
                break;
            }
        }
        
        if ($deleted) {
            saveMockData('packages.json', $packages);
            
            // Redirect back to packages page
            header("Location: ../pages/packages.php");
            exit;
        } else {
            // Redirect with error
            header("Location: ../pages/packages.php?error=" . urlencode('Package not found'));
            exit;
        }
    }
}

/**
 * Handle image upload
 */
function handleImageUpload($file) {
    $uploadDir = '../assets/uploads/';
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileName = uniqid() . '_' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $uploadPath;
    }
    
    return false;
}

/**
 * Respond with success JSON
 */
function respondWithSuccess($message, $data = []) {
    $response = [
        'success' => true,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Respond with error JSON
 */
function respondWithError($message, $code = 400) {
    http_response_code($code);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
?>