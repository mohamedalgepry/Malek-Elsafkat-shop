-- ========================================
-- إنشاء قاعدة البيانات
-- ========================================
CREATE DATABASE IF NOT EXISTS shoe_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shoe_store;

-- ========================================
-- حذف الجداول القديمة
-- ========================================

-- DROP TABLE IF EXISTS order_status_history;
-- DROP TABLE IF EXISTS order_items;
-- DROP TABLE IF EXISTS orders;
-- DROP TABLE IF EXISTS product_sizes;
-- DROP TABLE IF EXISTS product_videos;
-- DROP TABLE IF EXISTS product_images;
-- DROP TABLE IF EXISTS product_colors;
-- DROP TABLE IF EXISTS products;
-- DROP TABLE IF EXISTS categories;
-- DROP TABLE IF EXISTS shipping_governorates;
-- DROP TABLE IF EXISTS admins;
-- DROP TABLE IF EXISTS settings;
-- DROP TABLE IF EXISTS sliders;

-- ========================================
-- إنشاء الجداول
-- ========================================

-- جدول التصنيفات
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    parent_id INT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المنتجات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(50) NULL UNIQUE,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    cost_price DECIMAL(10,2) DEFAULT 0,
    regular_price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2) NULL,
    main_image VARCHAR(255) NOT NULL,
    is_featured BOOLEAN DEFAULT 0,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_sku (sku),
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول صور المنتجات
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول فيديوهات المنتجات
CREATE TABLE product_videos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    video_path VARCHAR(255) NOT NULL,
    video_type ENUM('upload', 'youtube') DEFAULT 'upload',
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ألوان المنتجات
CREATE TABLE product_colors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    color_name VARCHAR(50) NOT NULL,
    color_hex VARCHAR(7) NOT NULL,
    stock_quantity INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول مقاسات المنتجات
CREATE TABLE product_sizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    color_id INT NOT NULL,
    size_name VARCHAR(50) NOT NULL,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (color_id) REFERENCES product_colors(id) ON DELETE CASCADE,
    INDEX idx_color (color_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المحافظات والشحن
CREATE TABLE shipping_governorates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الطلبات
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100) NULL,
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_state VARCHAR(100) NULL,
    shipping_postal_code VARCHAR(20) NULL,
    governorate_id INT NULL,
    order_notes TEXT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_fee DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cod',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_invoice (invoice_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_phone (customer_phone),
    INDEX idx_governorate (governorate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول عناصر الطلبات
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_sku VARCHAR(50) NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(255) NOT NULL,
    color_name VARCHAR(50) NOT NULL,
    color_hex VARCHAR(7) NOT NULL,
    size_name VARCHAR(50) NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول سجل حالة الطلبات
CREATE TABLE order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    note TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المسؤولين
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الإعدادات
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول السلايدر
CREATE TABLE sliders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255) NULL,
    button_text VARCHAR(100) DEFAULT 'تسوق الآن',
    button_link VARCHAR(255) DEFAULT '/shop',
    image VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (display_order),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- البيانات الأساسية
-- ========================================

-- محافظات مصر
INSERT INTO shipping_governorates (name_ar, name_en, shipping_cost, is_active) VALUES
('القاهرة', 'Cairo', 30.00, 1),
('الجيزة', 'Giza', 30.00, 1),
('القليوبية', 'Qalyubia', 35.00, 1),
('الإسكندرية', 'Alexandria', 40.00, 1),
('البحيرة', 'Beheira', 45.00, 1),
('مطروح', 'Matrouh', 80.00, 1),
('الدقهلية', 'Dakahlia', 40.00, 1),
('الشرقية', 'Sharqia', 40.00, 1),
('الغربية', 'Gharbia', 40.00, 1),
('المنوفية', 'Monufia', 40.00, 1),
('كفر الشيخ', 'Kafr El Sheikh', 45.00, 1),
('دمياط', 'Damietta', 45.00, 1),
('بورسعيد', 'Port Said', 50.00, 1),
('الإسماعيلية', 'Ismailia', 50.00, 1),
('السويس', 'Suez', 50.00, 1),
('شمال سيناء', 'North Sinai', 100.00, 1),
('جنوب سيناء', 'South Sinai', 100.00, 1),
('الفيوم', 'Fayoum', 45.00, 1),
('بني سويف', 'Beni Suef', 50.00, 1),
('المنيا', 'Minya', 55.00, 1),
('أسيوط', 'Asyut', 60.00, 1),
('سوهاج', 'Sohag', 65.00, 1),
('قنا', 'Qena', 70.00, 1),
('الأقصر', 'Luxor', 75.00, 1),
('أسوان', 'Aswan', 80.00, 1),
('البحر الأحمر', 'Red Sea', 90.00, 1),
('الوادي الجديد', 'New Valley', 100.00, 1);

-- الإعدادات
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'متجر الأحذية'),
('site_email', 'info@shop.com'),
('site_phone', '+20 123 456 7890'),
('site_address', 'القاهرة، مصر'),
('currency', 'EGP'),
('tax_rate', '0'),
('shipping_fee', '0'),
('free_shipping_threshold', '500'),
('shipping_title', 'تفاصيل الشحن'),
('shipping_details', 'يتم التوصيل خلال 3-5 أيام عمل داخل القاهرة والجيزة.\nالتوصيل خلال 5-7 أيام عمل للمحافظات الأخرى.\nشحن مجاني للطلبات فوق 500 جنيه.'),
('shipping_notes', 'يرجى التأكد من صحة العنوان ورقم الهاتف لضمان وصول الطلب في الوقت المحدد.'),
('shipping_contact', '+20 123 456 7890');



