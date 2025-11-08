<?php
/**
 * Order Model
 */
class Order {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all orders
     */
    public function getAll($limit = null, $offset = 0, $orderBy = 'created_at DESC') {
        $sql = "SELECT * FROM orders ORDER BY {$orderBy}";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get order by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        
        if ($order) {
            $order['items'] = $this->getItems($id);
            $order['status_history'] = $this->getStatusHistory($id);
        }
        
        return $order;
    }
    
    /**
     * Get order by invoice number
     */
    public function getByInvoice($invoiceNumber) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE invoice_number = ?");
        $stmt->execute([$invoiceNumber]);
        $order = $stmt->fetch();
        
        if ($order) {
            $order['items'] = $this->getItems($order['id']);
            $order['status_history'] = $this->getStatusHistory($order['id']);
        }
        
        return $order;
    }
    
    /**
     * Filter orders
     */
    public function filter($filters, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];
        
        // Status filter
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        // Date range filter
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (invoice_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count orders with filters
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) FROM orders WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (invoice_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Create order
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO orders (
                invoice_number, customer_name, customer_phone, customer_email,
                shipping_address, shipping_city, shipping_state, shipping_postal_code,
                order_notes, subtotal, shipping_fee, total_amount, status, payment_method
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['invoice_number'],
            $data['customer_name'],
            $data['customer_phone'],
            $data['customer_email'] ?? null,
            $data['shipping_address'],
            $data['shipping_city'],
            $data['shipping_state'] ?? null,
            $data['shipping_postal_code'] ?? null,
            $data['order_notes'] ?? null,
            $data['subtotal'],
            $data['shipping_fee'] ?? 0,
            $data['total_amount'],
            $data['status'] ?? 'pending',
            $data['payment_method'] ?? 'cod'
        ]);
        
        $orderId = $this->db->lastInsertId();
        
        // Add initial status to history
        $this->addStatusHistory($orderId, $data['status'] ?? 'pending', 'تم إنشاء الطلب');
        
        return $orderId;
    }
    
    /**
     * Update order
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET customer_name = ?, customer_phone = ?, customer_email = ?,
                shipping_address = ?, shipping_city = ?, shipping_state = ?, 
                shipping_postal_code = ?, order_notes = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['customer_name'],
            $data['customer_phone'],
            $data['customer_email'] ?? null,
            $data['shipping_address'],
            $data['shipping_city'],
            $data['shipping_state'] ?? null,
            $data['shipping_postal_code'] ?? null,
            $data['order_notes'] ?? null,
            $id
        ]);
    }
    
    /**
     * Update order status
     */
    public function updateStatus($id, $status, $note = null, $adminId = null) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);
        
        if ($result) {
            $this->addStatusHistory($id, $status, $note, $adminId);
            
            // If status is delivered, deduct stock
            if ($status === 'delivered') {
                $this->deductStock($id);
            }
        }
        
        return $result;
    }
    
    /**
     * Delete order
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Delete old orders
     */
    public function deleteOldOrders($days, $statuses = ['delivered', 'cancelled']) {
        $placeholders = str_repeat('?,', count($statuses) - 1) . '?';
        $stmt = $this->db->prepare("
            DELETE FROM orders 
            WHERE status IN ({$placeholders}) 
            AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        
        $params = array_merge($statuses, [$days]);
        return $stmt->execute($params);
    }
    
    /**
     * Get order items
     */
    public function getItems($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add order item
     */
    public function addItem($orderId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_sku, product_name, product_image,
                color_name, color_hex, size_name, quantity, unit_price, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $orderId,
            $data['product_id'],
            $data['product_sku'] ?? null,
            $data['product_name'],
            $data['product_image'],
            $data['color_name'],
            $data['color_hex'],
            $data['size_name'] ?? null,
            $data['quantity'],
            $data['unit_price'],
            $data['subtotal']
        ]);
    }
    
    /**
     * Get order status history
     */
    public function getStatusHistory($orderId) {
        $stmt = $this->db->prepare("
            SELECT osh.*, a.username as admin_username
            FROM order_status_history osh
            LEFT JOIN admins a ON osh.created_by = a.id
            WHERE osh.order_id = ?
            ORDER BY osh.created_at ASC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add status to history
     */
    public function addStatusHistory($orderId, $status, $note = null, $adminId = null) {
        $stmt = $this->db->prepare("
            INSERT INTO order_status_history (order_id, status, note, created_by)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$orderId, $status, $note, $adminId]);
    }
    
    /**
     * Deduct stock when order is delivered
     */
    private function deductStock($orderId) {
        $items = $this->getItems($orderId);
        $productModel = new Product();
        
        foreach ($items as $item) {
            // Find the color ID
            $colors = $productModel->getColors($item['product_id']);
            foreach ($colors as $color) {
                if ($color['color_name'] === $item['color_name']) {
                    // If size is specified, update size stock
                    if (!empty($item['size_name'])) {
                        $size = $productModel->getSizeByColorAndName($color['id'], $item['size_name']);
                        if ($size) {
                            $productModel->updateSizeStock($size['id'], $item['quantity']);
                        }
                    } else {
                        // Fallback: update color stock (for old products without sizes)
                        $productModel->updateColorStock($color['id'], $item['quantity']);
                    }
                    break;
                }
            }
        }
    }
    
    /**
     * Get statistics
     */
    public function getStats($period = 'today') {
        $dateCondition = "";
        
        switch ($period) {
            case 'today':
                $dateCondition = "DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $dateCondition = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $dateCondition = "MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
                break;
            case 'year':
                $dateCondition = "YEAR(created_at) = YEAR(NOW())";
                break;
            default:
                $dateCondition = "1=1";
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_order_value,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
            FROM orders
            WHERE {$dateCondition}
        ");
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Get recent orders
     */
    public function getRecent($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM orders 
            ORDER BY created_at DESC 
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
