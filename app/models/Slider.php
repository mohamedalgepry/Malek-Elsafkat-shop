<?php
/**
 * Slider Model
 */
class Slider {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all sliders
     */
    public function getAll($orderBy = 'display_order ASC') {
        $stmt = $this->db->prepare("SELECT * FROM sliders ORDER BY $orderBy");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get active sliders
     */
    public function getActive($orderBy = 'display_order ASC') {
        $stmt = $this->db->prepare("SELECT * FROM sliders WHERE is_active = 1 ORDER BY $orderBy");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get slider by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sliders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new slider
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO sliders (title, subtitle, button_text, button_link, image, display_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['title'],
            $data['subtitle'] ?? null,
            $data['button_text'] ?? 'تسوق الآن',
            $data['button_link'] ?? '/shop',
            $data['image'],
            $data['display_order'] ?? 0,
            $data['is_active'] ?? 1
        ]);
    }
    
    /**
     * Update slider
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE sliders 
            SET title = ?, subtitle = ?, button_text = ?, button_link = ?, 
                image = ?, display_order = ?, is_active = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['title'],
            $data['subtitle'] ?? null,
            $data['button_text'] ?? 'تسوق الآن',
            $data['button_link'] ?? '/shop',
            $data['image'],
            $data['display_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id
        ]);
    }
    
    /**
     * Delete slider
     */
    public function delete($id) {
        // Get slider to delete image
        $slider = $this->getById($id);
        
        if ($slider) {
            // Delete from database
            $stmt = $this->db->prepare("DELETE FROM sliders WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            // Delete image file if exists
            if ($result && $slider['image']) {
                $imagePath = __DIR__ . '/../../public' . $slider['image'];
                if (file_exists($imagePath) && !str_contains($slider['image'], 'hero')) {
                    unlink($imagePath);
                }
            }
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * Toggle active status
     */
    public function toggleActive($id) {
        $stmt = $this->db->prepare("UPDATE sliders SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Update display order
     */
    public function updateOrder($id, $order) {
        $stmt = $this->db->prepare("UPDATE sliders SET display_order = ? WHERE id = ?");
        return $stmt->execute([$order, $id]);
    }
    
    /**
     * Get total count
     */
    public function getCount() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM sliders");
        return $stmt->fetchColumn();
    }
}
