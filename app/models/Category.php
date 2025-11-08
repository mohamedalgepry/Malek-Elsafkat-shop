<?php
/**
 * Category Model
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all categories
     */
    public function getAll($orderBy = 'display_order ASC, name ASC') {
        $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY {$orderBy}");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get category by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    /**
     * Get categories with product count
     */
    public function getAllWithProductCount() {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.display_order ASC, c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Create category
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, slug, description, image, parent_id, display_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['image'] ?? null,
            $data['parent_id'] ?? null,
            $data['display_order'] ?? 0
        ]);
    }
    
    /**
     * Update category
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE categories 
            SET name = ?, slug = ?, description = ?, image = ?, parent_id = ?, display_order = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['image'] ?? null,
            $data['parent_id'] ?? null,
            $data['display_order'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Delete category
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Check if category has products
     */
    public function hasProducts($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get parent categories (no parent_id)
     */
    public function getParentCategories() {
        $stmt = $this->db->prepare("
            SELECT * FROM categories 
            WHERE parent_id IS NULL 
            ORDER BY display_order ASC, name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get subcategories by parent ID
     */
    public function getSubcategories($parentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM categories 
            WHERE parent_id = ? 
            ORDER BY display_order ASC, name ASC
        ");
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }
}
