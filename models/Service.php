<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/Database.php';

class Service {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new service
     * 
     * @param string $name Service name
     * @param string $description Service description
     * @param float $price Service price
     * @return int|false Service ID or false on failure
     */
    public function create($name, $description, $price) {
        // Validate inputs
        if (empty($name) || !is_numeric($price) || $price < 0) {
            return false;
        }
        
        // Prepare data for insert
        $data = [
            'name' => $name,
            'description' => $description,
            'price' => $price
        ];
        
        // Insert service
        return $this->db->insert('services', $data);
    }
    
    /**
     * Get service by ID
     * 
     * @param int $id Service ID
     * @return array|false Service data or false if not found
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM services WHERE id = :id",
            [':id' => $id]
        );
    }
    
    /**
     * Update service
     * 
     * @param int $id Service ID
     * @param array $data Service data to update
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Ensure id is valid
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        // Remove protected fields
        $allowedFields = ['name', 'description', 'price'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        // Add updated_at timestamp
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('services', $updateData, 'id = :id', [':id' => $id]);
    }
    
    /**
     * Delete service
     * 
     * @param int $id Service ID
     * @return bool Success or failure
     */
    public function delete($id) {
        // Validate input
        if (!$id || !is_numeric($id)) {
            return false;
        }
        
        return $this->db->delete('services', 'id = :id', [':id' => $id]);
    }
    
    /**
     * Get all services
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param string $orderBy Order by field
     * @param string $orderDir Order direction
     * @return array Services
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
            "SELECT * FROM services 
             ORDER BY $orderBy $orderDir 
             LIMIT :limit OFFSET :offset",
            [
                ':limit' => $limit,
                ':offset' => $offset
            ]
        );
    }
    
    /**
     * Count total services
     * 
     * @return int Number of services
     */
    public function countAll() {
        return $this->db->count('services');
    }
    
    /**
     * Get packages that include this service
     * 
     * @param int $serviceId Service ID
     * @return array Packages
     */
    public function getPackages($serviceId) {
        // Validate input
        if (!$serviceId || !is_numeric($serviceId)) {
            return [];
        }
        
        return $this->db->fetchAll(
            "SELECT p.* 
             FROM packages p
             JOIN package_services ps ON p.id = ps.package_id
             WHERE ps.service_id = :service_id",
            [':service_id' => $serviceId]
        );
    }
    
    /**
     * Check if service is used in any package
     * 
     * @param int $serviceId Service ID
     * @return bool True if service is used, false otherwise
     */
    public function isUsedInPackage($serviceId) {
        // Validate input
        if (!$serviceId || !is_numeric($serviceId)) {
            return false;
        }
        
        $count = $this->db->count(
            'package_services',
            'service_id = :service_id',
            [':service_id' => $serviceId]
        );
        
        return $count > 0;
    }
    
    /**
     * Check if service is used in any booking
     * 
     * @param int $serviceId Service ID
     * @return bool True if service is used, false otherwise
     */
    public function isUsedInBooking($serviceId) {
        // Validate input
        if (!$serviceId || !is_numeric($serviceId)) {
            return false;
        }
        
        $count = $this->db->count(
            'booking_services',
            'service_id = :service_id',
            [':service_id' => $serviceId]
        );
        
        return $count > 0;
    }
    
    /**
     * Search services by name
     * 
     * @param string $keyword Search keyword
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Matching services
     */
    public function search($keyword, $limit = 100, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT * FROM services 
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
