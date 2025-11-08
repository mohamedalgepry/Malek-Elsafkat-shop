<?php
/**
 * Helper Functions
 */

/**
 * Sanitize output to prevent XSS
 */
function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Generate slug from string (converts Arabic to English)
 */
function generateSlug($string) {
    // Arabic to English transliteration map
    $arabicToEnglish = [
        'ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'aa',
        'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j',
        'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'th',
        'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sh',
        'ص' => 's', 'ض' => 'd', 'ط' => 't', 'ظ' => 'z',
        'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q',
        'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
        'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ى' => 'a',
        'ة' => 'h', 'ء' => 'a', 'ئ' => 'y', 'ؤ' => 'w',
        'ـ' => '', 'َ' => '', 'ً' => '', 'ُ' => '',
        'ٌ' => '', 'ِ' => '', 'ٍ' => '', 'ّ' => '',
        'ْ' => '', '،' => '', '؛' => ''
    ];
    
    // Trim whitespace
    $string = trim($string);
    
    // Convert Arabic to English
    $string = str_replace(array_keys($arabicToEnglish), array_values($arabicToEnglish), $string);
    
    // Convert to lowercase
    $string = strtolower($string);
    
    // Replace spaces with hyphens
    $string = preg_replace('/\s+/', '-', $string);
    
    // Remove special characters (keep only a-z, 0-9, hyphen, underscore)
    $string = preg_replace('/[^a-z0-9\-\_]/', '', $string);
    
    // Remove multiple consecutive hyphens
    $string = preg_replace('/-+/', '-', $string);
    
    // Remove leading/trailing hyphens
    $string = trim($string, '-');
    
    // If empty after conversion, generate random slug
    if (empty($string)) {
        $string = 'product-' . bin2hex(random_bytes(4));
    }
    
    return $string;
}

/**
 * Generate unique invoice number
 */
function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Format price
 */
function formatPrice($price) {
    return number_format($price, 2) . ' ' . CURRENCY;
}

/**
 * Calculate discount percentage
 */
function calculateDiscount($regular, $discount) {
    if ($regular <= 0) return 0;
    return round((($regular - $discount) / $regular) * 100);
}

/**
 * Get final price (with discount if available)
 */
function getFinalPrice($regular, $discount = null) {
    return $discount && $discount < $regular ? $discount : $regular;
}

/**
 * Upload image file
 */
function uploadImage($file, $directory = PRODUCT_UPLOAD_PATH) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'خطأ في رفع الملف'];
    }
    
    // Validate file size
    if ($file['size'] > MAX_IMAGE_SIZE) {
        return ['success' => false, 'message' => 'حجم الملف كبير جداً (الحد الأقصى 5 ميجابايت)'];
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح به'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $filepath = $directory . '/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'فشل في حفظ الملف'];
    }
    
    return ['success' => true, 'filename' => $filename];
}

/**
 * Upload video file
 */
function uploadVideo($file, $directory = VIDEO_UPLOAD_PATH) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'خطأ في رفع الفيديو'];
    }
    
    // Validate file size (max 50MB)
    $maxVideoSize = 50 * 1024 * 1024; // 50MB
    if ($file['size'] > $maxVideoSize) {
        return ['success' => false, 'message' => 'حجم الفيديو كبير جداً (الحد الأقصى 50 ميجابايت)'];
    }
    
    // Validate file type
    $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/quicktime'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedVideoTypes)) {
        return ['success' => false, 'message' => 'نوع الفيديو غير مسموح به (MP4, WebM, AVI, MOV فقط)'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $filepath = $directory . '/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'فشل في حفظ الفيديو'];
    }
    
    return ['success' => true, 'filename' => $filename];
}

/**
 * Delete file
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('/admin');
        exit;
    }
}

/**
 * Get cart items from session
 */
function getCart() {
    return $_SESSION['cart'] ?? [];
}

/**
 * Get cart count
 */
function getCartCount() {
    $cart = getCart();
    $count = 0;
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/**
 * Calculate cart total
 */
function getCartTotal() {
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

/**
 * Add item to cart
 */
function addToCart($productId, $productName, $price, $image, $colorName, $colorHex, $quantity = 1, $sizeName = null) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $cartKey = $productId . '_' . $colorName . ($sizeName ? '_' . $sizeName : '');
    
    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cartKey] = [
            'product_id' => $productId,
            'name' => $productName,
            'price' => $price,
            'image' => $image,
            'color_name' => $colorName,
            'color_hex' => $colorHex,
            'size_name' => $sizeName,
            'quantity' => $quantity
        ];
    }
}

/**
 * Remove item from cart
 */
function removeFromCart($cartKey) {
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
    }
}

/**
 * Update cart item quantity
 */
function updateCartQuantity($cartKey, $quantity) {
    if (isset($_SESSION['cart'][$cartKey])) {
        if ($quantity <= 0) {
            removeFromCart($cartKey);
        } else {
            // Verify stock availability
            $item = $_SESSION['cart'][$cartKey];
            $productModel = new Product();
            $product = $productModel->getById($item['product_id']);
            
            if ($product) {
                $colors = $productModel->getColors($product['id']);
                $availableStock = 0;
                
                foreach ($colors as $color) {
                    if ($color['color_name'] === $item['color_name']) {
                        // If size is specified, check size stock
                        if (!empty($item['size_name'])) {
                            $size = $productModel->getSizeByColorAndName($color['id'], $item['size_name']);
                            if ($size) {
                                $availableStock = $size['stock_quantity'];
                            }
                        } else {
                            // If no size, check color total stock
                            $availableStock = $color['total_stock'] ?? 0;
                        }
                        break;
                    }
                }
                
                // Only update if stock is available
                if ($quantity <= $availableStock) {
                    $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
                } else {
                    $_SESSION['error'] = "الكمية المطلوبة غير متوفرة. المتوفر: {$availableStock}";
                }
            }
        }
    }
}

/**
 * Clear cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Format date in Arabic
 */
function formatDate($date, $format = 'Y-m-d H:i') {
    return date($format, strtotime($date));
}

/**
 * Get time ago in Arabic
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'الآن';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' دقيقة';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ساعة';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' يوم';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Paginate array
 */
function paginate($items, $page = 1, $perPage = PRODUCTS_PER_PAGE) {
    $total = count($items);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $total,
        'per_page' => $perPage
    ];
}

/**
 * Check rate limit
 */
function checkRateLimit($key, $maxAttempts, $timeWindow) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $now = time();
    $rateKey = $key . '_' . $_SERVER['REMOTE_ADDR'];
    
    // Clean old entries
    if (isset($_SESSION['rate_limit'][$rateKey])) {
        $_SESSION['rate_limit'][$rateKey] = array_filter(
            $_SESSION['rate_limit'][$rateKey],
            function($timestamp) use ($now, $timeWindow) {
                return ($now - $timestamp) < $timeWindow;
            }
        );
    } else {
        $_SESSION['rate_limit'][$rateKey] = [];
    }
    
    // Check limit
    if (count($_SESSION['rate_limit'][$rateKey]) >= $maxAttempts) {
        return false;
    }
    
    // Add attempt
    $_SESSION['rate_limit'][$rateKey][] = $now;
    return true;
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'badge-warning',
        'confirmed' => 'badge-info',
        'shipped' => 'badge-purple',
        'delivered' => 'badge-success',
        'cancelled' => 'badge-danger'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

/**
 * Get status label in Arabic
 */
function getStatusLabel($status) {
    $labels = [
        'pending' => 'قيد الانتظار',
        'confirmed' => 'مؤكد',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التوصيل',
        'cancelled' => 'ملغي'
    ];
    return $labels[$status] ?? $status;
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Egyptian format)
 */
function isValidPhone($phone) {
    // Remove spaces and special characters
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    // Check if it's a valid Egyptian number
    return preg_match('/^(\+20|0)?1[0-2,5]{1}[0-9]{8}$/', $phone);
}

/**
 * Generate breadcrumb
 */
function breadcrumb($items) {
    $html = '<nav class="breadcrumb" aria-label="breadcrumb"><ol>';
    foreach ($items as $label => $url) {
        if ($url) {
            $html .= '<li><a href="' . escape($url) . '">' . escape($label) . '</a></li>';
        } else {
            $html .= '<li class="active">' . escape($label) . '</li>';
        }
    }
    $html .= '</ol></nav>';
    return $html;
}

/**
 * Extract YouTube video ID from URL
 */
function getYoutubeId($url) {
    if (empty($url)) {
        return '';
    }
    
    // If it's already just an ID
    if (strlen($url) == 11 && !strpos($url, '/') && !strpos($url, '.')) {
        return $url;
    }
    
    // Parse different YouTube URL formats
    $patterns = [
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',  // https://www.youtube.com/watch?v=VIDEO_ID
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',     // https://www.youtube.com/embed/VIDEO_ID
        '/youtu\.be\/([a-zA-Z0-9_-]+)/',               // https://youtu.be/VIDEO_ID
        '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',         // https://www.youtube.com/v/VIDEO_ID
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return '';
}
