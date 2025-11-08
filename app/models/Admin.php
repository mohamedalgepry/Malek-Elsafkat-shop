<?php
/**
 * Admin Model
 */
class Admin {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get admin by username
     */
    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    /**
     * Get admin by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Verify login credentials
     */
    public function verifyLogin($username, $password) {
        $admin = $this->getByUsername($username);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $this->updateLastLogin($admin['id']);
            return $admin;
        }
        
        return false;
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin($id) {
        $stmt = $this->db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Update admin profile
     */
    public function updateProfile($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE admins 
            SET username = ?, email = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $id
        ]);
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE admins SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $id]);
    }
    
    /**
     * Verify current password
     */
    public function verifyPassword($id, $password) {
        $admin = $this->getById($id);
        return $admin && password_verify($password, $admin['password']);
    }
    
    /**
     * Get setting by key
     */
    public function getSetting($key) {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }
    
    /**
     * Get all settings
     */
    public function getAllSettings() {
        $stmt = $this->db->prepare("SELECT * FROM settings");
        $stmt->execute();
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    /**
     * Update setting
     */
    public function updateSetting($key, $value) {
        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        return $stmt->execute([$key, $value, $value]);
    }
    
    /**
     * Update multiple settings
     */
    public function updateSettings($settings) {
        foreach ($settings as $key => $value) {
            $this->updateSetting($key, $value);
        }
        return true;
    }
    
    /**
     * Create database backup
     */
    public function createBackup() {
        // This is a simple implementation
        // In production, use mysqldump or similar tools
        $backupFile = ROOT_PATH . '/database/backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Get all tables
        $tables = [];
        $result = $this->db->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $output = "-- Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $output .= "-- Table: {$table}\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            
            // Get CREATE TABLE statement
            $createTable = $this->db->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $output .= $createTable['Create Table'] . ";\n\n";
            
            // Get table data
            $rows = $this->db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $values = array_map(function($value) {
                        return $value === null ? 'NULL' : $this->db->quote($value);
                    }, array_values($row));
                    
                    $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }
        
        file_put_contents($backupFile, $output);
        return $backupFile;
    }
}
