<?php
/**
 * Shipping Model
 * إدارة المحافظات وأسعار الشحن
 */
class Shipping {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all governorates
     */
    public function getAllGovernorates($activeOnly = true) {
        $sql = "SELECT * FROM shipping_governorates";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY name_ar ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get governorate by ID
     */
    public function getGovernorateById($id) {
        $stmt = $this->db->prepare("
            SELECT * FROM shipping_governorates 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get shipping cost by governorate ID
     */
    public function getShippingCost($governorateId) {
        $stmt = $this->db->prepare("
            SELECT shipping_cost 
            FROM shipping_governorates 
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$governorateId]);
        $result = $stmt->fetch();
        return $result ? (float)$result['shipping_cost'] : 0;
    }
    
    /**
     * Create new governorate
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO shipping_governorates 
            (name_ar, name_en, shipping_cost, is_active)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['name_ar'],
            $data['name_en'] ?? '',
            $data['shipping_cost'],
            $data['is_active'] ?? 1
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update governorate
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE shipping_governorates 
            SET name_ar = ?, 
                name_en = ?, 
                shipping_cost = ?, 
                is_active = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name_ar'],
            $data['name_en'] ?? '',
            $data['shipping_cost'],
            $data['is_active'] ?? 1,
            $id
        ]);
    }
    
    /**
     * Delete governorate
     */
    public function delete($id) {
        $stmt = $this->db->prepare("
            DELETE FROM shipping_governorates 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
    
    /**
     * Toggle active status
     */
    public function toggleActive($id) {
        $stmt = $this->db->prepare("
            UPDATE shipping_governorates 
            SET is_active = NOT is_active 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get statistics
     */
    public function getStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_governorates,
                SUM(is_active = 1) as active_governorates,
                MIN(shipping_cost) as min_cost,
                MAX(shipping_cost) as max_cost,
                AVG(shipping_cost) as avg_cost
            FROM shipping_governorates
        ");
        return $stmt->fetch();
    }
    
    /**
     * Search governorates
     */
    public function search($query) {
        $searchTerm = "%{$query}%";
        $stmt = $this->db->prepare("
            SELECT * FROM shipping_governorates 
            WHERE name_ar LIKE ? OR name_en LIKE ?
            ORDER BY name_ar ASC
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Count governorates
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM shipping_governorates WHERE 1=1";
        $params = [];
        
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
}
