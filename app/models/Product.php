<?php
/**
 * Product Model
 */
class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all products with category info
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get product by ID with full details
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product) {
            $product['images'] = $this->getImages($id);
            $product['videos'] = $this->getVideos($id);
            $product['colors'] = $this->getColors($id);
            $product['total_stock'] = $this->getTotalStock($id);
        }
        
        return $product;
    }
    
    /**
     * Get product by slug
     */
    public function getBySlug($slug) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ?
        ");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();
        
        if ($product) {
            $product['images'] = $this->getImages($product['id']);
            $product['videos'] = $this->getVideos($product['id']);
            $product['colors'] = $this->getColors($product['id']);
            $product['total_stock'] = $this->getTotalStock($product['id']);
        }
        
        return $product;
    }
    
    /**
     * Get products by category
     */
    public function getByCategory($categoryId, $limit = null, $offset = 0) {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.category_id = ?
            ORDER BY p.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get featured products
     */
    public function getFeatured($limit = 6) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_featured = 1
            ORDER BY p.created_at DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get new arrivals
     */
    public function getNewArrivals($limit = 8) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Search products
     */
    public function search($query, $limit = null, $offset = 0) {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ? OR p.sku LIKE ?
            ORDER BY 
                CASE 
                    WHEN p.sku = ? THEN 1
                    WHEN p.sku LIKE ? THEN 2
                    WHEN p.name LIKE ? THEN 3
                    ELSE 4
                END,
                p.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $searchTerm = "%{$query}%";
        $exactMatch = $query;
        $startsWith = $query . "%";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $searchTerm, $searchTerm, $searchTerm, $searchTerm,  // WHERE conditions
            $exactMatch, $startsWith, $startsWith                 // ORDER BY conditions
        ]);
        return $stmt->fetchAll();
    }
    
    /**
     * Search product by SKU only
     */
    public function searchBySku($sku) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.sku = ?
        ");
        $stmt->execute([$sku]);
        return $stmt->fetch();
    }
    
    /**
     * Filter products
     */
    public function filter($filters, $limit = null, $offset = 0) {
        $sql = "
            SELECT DISTINCT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_colors pc ON p.id = pc.product_id
            WHERE 1=1
        ";
        $params = [];
        
        // Category filter
        if (!empty($filters['category'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category'];
        }
        
        // Price range filter
        if (!empty($filters['min_price'])) {
            $sql .= " AND COALESCE(p.discount_price, p.regular_price) >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND COALESCE(p.discount_price, p.regular_price) <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Color filter
        if (!empty($filters['colors']) && is_array($filters['colors'])) {
            $placeholders = str_repeat('?,', count($filters['colors']) - 1) . '?';
            $sql .= " AND pc.color_hex IN ({$placeholders})";
            $params = array_merge($params, $filters['colors']);
        }
        
        // Sorting
        $orderBy = "p.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderBy = "COALESCE(p.discount_price, p.regular_price) ASC";
                    break;
                case 'price_desc':
                    $orderBy = "COALESCE(p.discount_price, p.regular_price) DESC";
                    break;
                case 'name_asc':
                    $orderBy = "p.name ASC";
                    break;
                case 'name_desc':
                    $orderBy = "p.name DESC";
                    break;
            }
        }
        
        $sql .= " ORDER BY {$orderBy}";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count products with filters
     */
    public function count($filters = []) {
        $sql = "
            SELECT COUNT(DISTINCT p.id)
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_colors pc ON p.id = pc.product_id
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND COALESCE(p.discount_price, p.regular_price) >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND COALESCE(p.discount_price, p.regular_price) <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['colors']) && is_array($filters['colors'])) {
            $placeholders = str_repeat('?,', count($filters['colors']) - 1) . '?';
            $sql .= " AND pc.color_hex IN ({$placeholders})";
            $params = array_merge($params, $filters['colors']);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Generate unique SKU
     */
    private function generateSKU() {
        // Get the last product ID
        $stmt = $this->db->query("SELECT MAX(id) as max_id FROM products");
        $result = $stmt->fetch();
        $nextId = ($result['max_id'] ?? 0) + 1;
        
        // Generate SKU: PRD-XXXXXX (6 digits)
        $sku = 'PRD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        
        // Check if SKU exists (just in case)
        $checkStmt = $this->db->prepare("SELECT id FROM products WHERE sku = ?");
        $checkStmt->execute([$sku]);
        
        // If exists, add timestamp to make it unique
        if ($checkStmt->fetch()) {
            $sku = 'PRD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT) . '-' . time();
        }
        
        return $sku;
    }
    
    /**
     * Create product
     */
    public function create($data) {
        // Generate SKU automatically
        $sku = $this->generateSKU();
        
        $stmt = $this->db->prepare("
            INSERT INTO products (sku, category_id, name, slug, description, cost_price, regular_price, discount_price, main_image, is_featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $sku,
            $data['category_id'],
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['cost_price'] ?? 0,
            $data['regular_price'],
            $data['discount_price'] ?? null,
            $data['main_image'],
            $data['is_featured'] ?? 0
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update product
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET category_id = ?, name = ?, slug = ?, description = ?, 
                cost_price = ?, regular_price = ?, discount_price = ?, 
                main_image = ?, is_featured = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['cost_price'] ?? 0,
            $data['regular_price'],
            $data['discount_price'] ?? null,
            $data['main_image'],
            $data['is_featured'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Delete product
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Increment views
     */
    public function incrementViews($id) {
        $stmt = $this->db->prepare("UPDATE products SET views_count = views_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get product images
     */
    public function getImages($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM product_images 
            WHERE product_id = ? 
            ORDER BY display_order ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add product image
     */
    public function addImage($productId, $imagePath, $displayOrder = 0) {
        $stmt = $this->db->prepare("
            INSERT INTO product_images (product_id, image_path, display_order)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$productId, $imagePath, $displayOrder]);
    }
    
    /**
     * Delete product image
     */
    public function deleteImage($imageId) {
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE id = ?");
        return $stmt->execute([$imageId]);
    }
    
    /**
     * Delete all product images
     */
    public function deleteAllImages($productId) {
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE product_id = ?");
        return $stmt->execute([$productId]);
    }
    
    /**
     * Get product videos
     */
    public function getVideos($productId) {
        $stmt = $this->db->prepare("SELECT * FROM product_videos WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add product video
     */
    public function addVideo($productId, $videoPath, $videoType = 'upload') {
        $stmt = $this->db->prepare("
            INSERT INTO product_videos (product_id, video_path, video_type)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$productId, $videoPath, $videoType]);
    }
    
    /**
     * Delete product video
     */
    public function deleteVideo($videoId) {
        $stmt = $this->db->prepare("DELETE FROM product_videos WHERE id = ?");
        return $stmt->execute([$videoId]);
    }
    
    /**
     * Get product colors with sizes
     */
    public function getColors($productId) {
        $stmt = $this->db->prepare("SELECT * FROM product_colors WHERE product_id = ?");
        $stmt->execute([$productId]);
        $colors = $stmt->fetchAll();
        
        // Get sizes for each color
        foreach ($colors as &$color) {
            $color['sizes'] = $this->getSizesByColor($color['id']);
            
            // Calculate total stock
            if (!empty($color['sizes'])) {
                // If sizes exist, use sizes stock
                $color['total_stock'] = array_sum(array_column($color['sizes'], 'stock_quantity'));
            } else {
                // If no sizes, use color stock_quantity
                $color['total_stock'] = $color['stock_quantity'];
            }
        }
        
        return $colors;
    }
    
    /**
     * Get color by ID
     */
    public function getColorById($colorId) {
        $stmt = $this->db->prepare("SELECT * FROM product_colors WHERE id = ?");
        $stmt->execute([$colorId]);
        return $stmt->fetch();
    }
    
    /**
     * Add product color
     */
    public function addColor($productId, $colorName, $colorHex, $stockQuantity) {
        $stmt = $this->db->prepare("
            INSERT INTO product_colors (product_id, color_name, color_hex, stock_quantity)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$productId, $colorName, $colorHex, $stockQuantity]);
    }
    
    /**
     * Update product color
     */
    public function updateColor($colorId, $colorName, $colorHex, $stockQuantity) {
        $stmt = $this->db->prepare("
            UPDATE product_colors 
            SET color_name = ?, color_hex = ?, stock_quantity = ?
            WHERE id = ?
        ");
        return $stmt->execute([$colorName, $colorHex, $stockQuantity, $colorId]);
    }
    
    /**
     * Delete product color
     */
    public function deleteColor($colorId) {
        $stmt = $this->db->prepare("DELETE FROM product_colors WHERE id = ?");
        return $stmt->execute([$colorId]);
    }
    
    /**
     * Delete all product colors
     */
    public function deleteAllColors($productId) {
        $stmt = $this->db->prepare("DELETE FROM product_colors WHERE product_id = ?");
        return $stmt->execute([$productId]);
    }
    
    /**
     * Update color stock
     */
    public function updateColorStock($colorId, $quantity) {
        $stmt = $this->db->prepare("
            UPDATE product_colors 
            SET stock_quantity = stock_quantity - ?
            WHERE id = ?
        ");
        return $stmt->execute([$quantity, $colorId]);
    }
    
    /**
     * Get total stock for product
     */
    public function getTotalStock($productId) {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(ps.stock_quantity), 0) as total_stock 
            FROM product_sizes ps
            INNER JOIN product_colors pc ON ps.color_id = pc.id
            WHERE pc.product_id = ?
        ");
        $stmt->execute([$productId]);
        $result = $stmt->fetch();
        return (int)($result['total_stock'] ?? 0);
    }
    
    /**
     * Get related products
     */
    public function getRelated($productId, $categoryId, $limit = 4) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.category_id = ? AND p.id != ?
            ORDER BY RAND()
            LIMIT {$limit}
        ");
        $stmt->execute([$categoryId, $productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sizes by color ID
     */
    public function getSizesByColor($colorId) {
        $stmt = $this->db->prepare("SELECT * FROM product_sizes WHERE color_id = ? ORDER BY id ASC");
        $stmt->execute([$colorId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get size by ID
     */
    public function getSizeById($sizeId) {
        $stmt = $this->db->prepare("SELECT * FROM product_sizes WHERE id = ?");
        $stmt->execute([$sizeId]);
        return $stmt->fetch();
    }
    
    /**
     * Add size to color
     */
    public function addSize($colorId, $sizeName, $stockQuantity) {
        $stmt = $this->db->prepare("
            INSERT INTO product_sizes (color_id, size_name, stock_quantity)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$colorId, $sizeName, $stockQuantity]);
    }
    
    /**
     * Update size
     */
    public function updateSize($sizeId, $sizeName, $stockQuantity) {
        $stmt = $this->db->prepare("
            UPDATE product_sizes 
            SET size_name = ?, stock_quantity = ?
            WHERE id = ?
        ");
        return $stmt->execute([$sizeName, $stockQuantity, $sizeId]);
    }
    
    /**
     * Delete size
     */
    public function deleteSize($sizeId) {
        $stmt = $this->db->prepare("DELETE FROM product_sizes WHERE id = ?");
        return $stmt->execute([$sizeId]);
    }
    
    /**
     * Update size stock quantity
     */
    public function updateSizeStock($sizeId, $quantity) {
        $stmt = $this->db->prepare("
            UPDATE product_sizes 
            SET stock_quantity = stock_quantity - ?
            WHERE id = ? AND stock_quantity >= ?
        ");
        return $stmt->execute([$quantity, $sizeId, $quantity]);
    }
    
    /**
     * Get size by color and size name
     */
    public function getSizeByColorAndName($colorId, $sizeName) {
        $stmt = $this->db->prepare("
            SELECT * FROM product_sizes 
            WHERE color_id = ? AND size_name = ?
        ");
        $stmt->execute([$colorId, $sizeName]);
        return $stmt->fetch();
    }
    
    /**
     * Get low stock products
     */
    public function getLowStock($threshold = 5) {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   COALESCE(SUM(ps.stock_quantity), 0) as total_stock,
                   c.name as category_name
            FROM products p
            LEFT JOIN product_colors pc ON p.id = pc.product_id
            LEFT JOIN product_sizes ps ON pc.id = ps.color_id
            LEFT JOIN categories c ON p.category_id = c.id
            GROUP BY p.id
            HAVING total_stock <= ?
            ORDER BY total_stock ASC
        ");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
}
