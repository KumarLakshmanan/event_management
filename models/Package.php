<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/Database.php';

class Package {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new package
     * 
     * @param string $name Package name
     * @param string $description Package description
     * @param float $price Package price
     * @param string $imagePath Path to package image
     * @return int|false Package ID or false on failure
     */
    public function create($name, $description, $price, $imagePath = null) {
        // Validate inputs
        if (empty($name) || !is_numeric($price) || $price < 0) {
            return false;
        }
        
        // Prepare data for insert
        $data = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image_path' => $imagePath
        ];
        
        // Insert package
        return $this->db->insert('packages', $data);
    }
    
    /**
     * Get package by ID
     * 
     * @param int $id Package ID
     * @return array|false Package data or false if not found
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM packages WHERE id = :id",
            [':id' => $id]
        );
    }
    
    /**
     * Update package
     * 
     * @param int $id Package ID
     * @param array $data Package data to update
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Ensure id is valid
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Remove protected fields
        $allowedFields = ['name', 'description', 'price', 'image_path'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        // Add updated_at timestamp
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('packages', $updateData, 'id = :id', [':id' => $id]);
    }
    
    /**
     * Delete package
     * 
     * @param int $id Package ID
     * @return bool Success or failure
     */
    public function delete($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->db->delete('packages', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Get all packages
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param string $orderBy Order by field
     * @param string $orderDir Order direction
     * @return array Packages
     */
    public function getAll($limit = 100, $offset = 0, $orderBy = 'id', $orderDir = 'ASC') {
        // Validate order by field
        $allowedOrderByFields = ['id', 'name', 'price', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $allowedOrderByFields)) {
            $orderBy = 'id';
        }
        
        // Validate order direction
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
        return $this->db->fetchAll(
            "SELECT * FROM packages 
             ORDER BY $orderBy $orderDir 
             LIMIT :limit OFFSET :offset",
            [
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
    }
    
    /**
     * Count total packages
     * 
     * @return int Number of packages
     */
    public function countAll() {
        return $this->db->count('packages');
    }
    
    /**
     * Add a service to a package
     * 
     * @param int $packageId Package ID
     * @param int $serviceId Service ID
     * @return bool Success or failure
     */
    public function addService($packageId, $serviceId) {
        // Validate inputs
        if (!$packageId || !is_numeric($packageId) || !$serviceId || !is_numeric($serviceId)) {
            return false;
        }
        
        // Check if package and service exist
        $package = $this->getById($packageId);
        
        // Check if service exists by using database class directly
        $service = $this->db->fetchOne(
            "SELECT id FROM services WHERE id = :id",
            [':id' => $serviceId]
        );
        
        if (!$package || !$service) {
            return false;
        }
        
        // Check if service is already added to package
        $packageService = $this->db->fetchOne(
            "SELECT id FROM package_services WHERE package_id = :package_id AND service_id = :service_id",
            [
                ':package_id' => $packageId,
                ':service_id' => $serviceId
            ]
        );
        
        if ($packageService) {
            return true; // Service already added, consider this a success
        }
        
        // Add service to package
        $data = [
            'package_id' => $packageId,
            'service_id' => $serviceId
        ];
        
        return $this->db->insert('package_services', $data) ? true : false;
    }
    
    /**
     * Remove a service from a package
     * 
     * @param int $packageId Package ID
     * @param int $serviceId Service ID
     * @return bool Success or failure
     */
    public function removeService($packageId, $serviceId) {
        // Validate inputs
        if (!$packageId || !is_numeric($packageId) || !$serviceId || !is_numeric($serviceId)) {
            return false;
        }
        
        return $this->db->delete(
            'package_services', 
            'package_id = :package_id AND service_id = :service_id',
            [
                ':package_id' => $packageId,
                ':service_id' => $serviceId
            ]
        );
    }
    
    /**
     * Get services in a package
     * 
     * @param int $packageId Package ID
     * @return array Services in the package
     */
    public function getServices($packageId) {
        // Validate input
        if (!$packageId || !is_numeric($packageId)) {
            return [];
        }
        
        return $this->db->fetchAll(
            "SELECT s.* 
             FROM services s
             JOIN package_services ps ON s.id = ps.service_id
             WHERE ps.package_id = :package_id",
            [':package_id' => $packageId]
        );
    }
    
    /**
     * Update package services
     * 
     * @param int $packageId Package ID
     * @param array $serviceIds Service IDs to assign to package
     * @return bool Success or failure
     */
    public function updateServices($packageId, $serviceIds) {
        // Validate inputs
        if (!$packageId || !is_numeric($packageId)) {
            return false;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Delete existing services for this package
            $this->db->delete('package_services', 'package_id = :package_id', [':package_id' => $packageId]);
            
            // Add new services
            foreach ($serviceIds as $serviceId) {
                if (is_numeric($serviceId)) {
                    $this->addService($packageId, $serviceId);
                }
            }
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            error_log("Error updating package services: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search packages by name
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Matching packages
     */
    public function search($keyword, $limit = 100, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT * FROM packages 
             WHERE name LIKE :keyword OR description LIKE :keyword
             ORDER BY id ASC
             LIMIT :limit OFFSET :offset",
            [
                ':keyword' => '%' . $keyword . '%',
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
    }
}
?>
