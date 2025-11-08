<?php

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($type, $title, $message, $link = null, $orderId = null) {
        $sql = "INSERT INTO notifications (type, title, message, link, order_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$type, $title, $message, $link, $orderId]);
    }
    
    public function getAll($limit = 50) {
        $sql = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUnread() {
        $sql = "SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUnreadCount() {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE is_read = 0";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    public function markAsRead($id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function markAllAsRead() {
        $sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";
        return $this->db->exec($sql);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM notifications WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function deleteOld($days = 30) {
        $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$days]);
    }
}
