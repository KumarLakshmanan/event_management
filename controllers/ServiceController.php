<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Service.php';

class ServiceController {
    private $serviceModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->serviceModel = new Service();
    }
    
    /**
     * Handle service creation
     * 
     * @param array $data Form data
     * @return int|false Service ID or false on failure
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['name']) || !isset($data['price']) || $data['price'] < 0) {
            return false;
        }
        
        // Process the description
        $description = empty($data['description']) ? '' : $data['description'];
        
        // Create the service
        return $this->serviceModel->create(
            $data['name'],
            $description,
            $data['price']
        );
    }
    
    /**
     * Handle service update
     * 
     * @param int $id Service ID
     * @param array $data Form data
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Validate ID
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Get existing service
        $service = $this->serviceModel->getById($id);
        if (!$service) {
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
        
        // Update the service
        if (empty($updateData)) {
            return true; // No updates needed
        }
        
        return $this->serviceModel->update($id, $updateData);
    }
    
    /**
     * Handle service deletion
     * 
     * @param int $id Service ID
     * @return bool Success or failure
     */
    public function delete($id) {
        // Validate ID
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Check if service is being used in packages
        if ($this->serviceModel->isUsedInPackage($id)) {
            return false; // Cannot delete service in use
        }
        
        // Check if service is being used in bookings
        if ($this->serviceModel->isUsedInBooking($id)) {
            return false; // Cannot delete service in use
        }
        
        return $this->serviceModel->delete($id);
    }
    
    /**
     * Get all services
     * 
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Services and pagination info
     */
    public function getAllServices($page = 1, $perPage = 10) {
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get services
        $services = $this->serviceModel->getAll($perPage, $offset);
        
        // Count total services
        $total = $this->serviceModel->countAll();
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        return [
            'services' => $services,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $total
            ]
        ];
    }
    
    /**
     * Get a single service
     * 
     * @param int $id Service ID
     * @return array|false Service data or false if not found
     */
    public function getService($id) {
        // Validate ID
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->serviceModel->getById($id);
    }
    
    /**
     * Get packages that include a service
     * 
     * @param int $id Service ID
     * @return array Packages that include the service
     */
    public function getPackagesWithService($id) {
        // Validate ID
        if (!$id || !is_numeric($id)) {
            return [];
        }
        
        return $this->serviceModel->getPackages($id);
    }
    
    /**
     * Search services by name
     * 
     * @param string $keyword Search keyword
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Matching services and pagination info
     */
    public function searchServices($keyword, $page = 1, $perPage = 10) {
        // Calculate offset
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Search services
        $services = $this->serviceModel->search($keyword, $perPage, $offset);
        
        // For simplicity, we're not counting total matches here
        // In a real application, you would implement this
        $totalPages = 1;
        
        return [
            'services' => $services,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage
            ],
            'keyword' => $keyword
        ];
    }
}
?>
