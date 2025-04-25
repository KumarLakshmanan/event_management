<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Package.php';
require_once __DIR__ . '/../models/Service.php';

class PackageController {
    private $packageModel;
    private $serviceModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->packageModel = new Package();
        $this->serviceModel = new Service();
    }
    
    /**
     * Handle package creation
     * 
     * @param array $data Form data
     * @return int|false Package ID or false on failure
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['name']) || !isset($data['price']) || $data['price'] < 0) {
            return false;
        }
        
        // Process the description
        $description = empty($data['description']) ? '' : $data['description'];
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadImage($_FILES['image']);
        }
        
        // Create the package
        $packageId = $this->packageModel->create(
            $data['name'],
            $description,
            $data['price'],
            $imagePath
        );
        
        // Add services if provided
        if ($packageId && isset($data['services']) && is_array($data['services'])) {
            $this->packageModel->updateServices($packageId, $data['services']);
        }
        
        return $packageId;
    }
    
    /**
     * Handle package update
     * 
     * @param int $id Package ID
     * @param array $data Form data
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Validate ID
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Get existing package
        $package = $this->packageModel->getById($id);
        if (!$package) {
            return false;
        }
        
        // Prepare update data
        $updateData = [];
        
        // Update name if provided
        if (isset($data['name']) && !empty($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        
        // Update description if provided
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        
        // Update price if provided
        if (isset($data['price']) && is_numeric($data['price']) && $data['price'] >= 0) {
            $updateData['price'] = $data['price'];
        }
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadImage($_FILES['image']);
            if ($imagePath) {
                $updateData['image_path'] = $imagePath;
            }
        }
        
        // Update the package
        $updateResult = false;
        if (!empty($updateData)) {
            $updateResult = $this->packageModel->update($id, $updateData);
        } else {
            $updateResult = true; // No updates needed
        }
        
        // Update services if provided
        if (isset($data['services']) && is_array($data['services'])) {
            $this->packageModel->updateServices($id, $data['services']);
        }
        
        return $updateResult;
    }
    
    /**
     * Handle package deletion
     * 
     * @param int $id Package ID
     * @return bool Success or failure
     */
    public function delete($id) {
        // Validate ID
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->packageModel->delete($id);
    }
    
    /**
     * Get all packages
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Packages and pagination info
     */
    public function getAllPackages($page = 1, $perPage = 10) {
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get packages
        $packages = $this->packageModel->getAll($perPage, $offset);
        
        // Count total packages
        $total = $this->packageModel->countAll();
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        return [
            'packages' => $packages,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ];
    }
    
    /**
     * Get a single package with its services
     * 
     * @param int $id Package ID
     * @return array|false Package data with services or false if not found
     */
    public function getPackageWithServices($id) {
        // Validate ID
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Get package
        $package = $this->packageModel->getById($id);
        if (!$package) {
            return false;
        }
        
        // Get services
        $services = $this->packageModel->getServices($id);
        
        // Add services to package data
        $package['services'] = $services;
        
        return $package;
    }
    
    /**
     * Get all services for package selection
     * 
     * @return array All services
     */
    public function getAllServices() {
        return $this->serviceModel->getAll();
    }
    
    /**
     * Check if service is included in package
     * 
     * @param int $packageId Package ID
     * @param int $serviceId Service ID
     * @return bool True if service is in package, false otherwise
     */
    public function hasService($packageId, $serviceId) {
        $services = $this->packageModel->getServices($packageId);
        
        foreach ($services as $service) {
            if ($service['id'] == $serviceId) {
                return true;
            }
        }
        
        return false;
    }
}
?>
