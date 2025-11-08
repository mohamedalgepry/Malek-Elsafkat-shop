<?php
/**
 * Application Configuration
 */

// Load environment variables (strip surrounding quotes)
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // remove surrounding single/double quotes if present (PHP 7 compatible)
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = substr($value, -1);
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        $_ENV[$key] = $value;
    }
}

// Application settings
define('APP_NAME', $_ENV['APP_NAME'] ?? 'ملك الصفقات');
define('APP_DESCRIPTION', $_ENV['APP_DESCRIPTION'] ?? 'ملك الصفقات - أفضل العروض والتخفيضات على الملابس والإكسسوارات. تسوق الآن واحصل على أفضل الأسعار!');
define('APP_KEYWORDS', $_ENV['APP_KEYWORDS'] ?? 'ملك الصفقات, تسوق اونلاين, ملابس, عروض, تخفيضات, أزياء, موضة, تيشيرتات, بناطيل, إكسسوارات');
define('APP_AUTHOR', $_ENV['APP_AUTHOR'] ?? 'Algebry');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/shoppp');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');

// Paths
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('PRODUCT_UPLOAD_PATH', UPLOAD_PATH . '/products');
define('VIDEO_UPLOAD_PATH', UPLOAD_PATH . '/videos');

// URLs
define('BASE_URL', rtrim(APP_URL, '/'));
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');
define('APP_LOGO', ASSETS_URL . '/images/logo/logo.png');

// Upload settings
define('MAX_IMAGE_SIZE', $_ENV['MAX_IMAGE_SIZE'] ?? 5242880); // 5MB
define('MAX_VIDEO_SIZE', $_ENV['MAX_VIDEO_SIZE'] ?? 52428800); // 50MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);

// Session settings
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 1800); // 30 minutes
define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');

// Business settings
define('CURRENCY', $_ENV['CURRENCY'] ?? 'EGP');
define('TAX_RATE', $_ENV['TAX_RATE'] ?? 0);
define('SHIPPING_FEE', $_ENV['SHIPPING_FEE'] ?? 0);
define('FREE_SHIPPING_THRESHOLD', $_ENV['FREE_SHIPPING_THRESHOLD'] ?? 500);

// Rate limiting
define('LOGIN_MAX_ATTEMPTS', $_ENV['LOGIN_MAX_ATTEMPTS'] ?? 5);
define('LOGIN_LOCKOUT_TIME', $_ENV['LOGIN_LOCKOUT_TIME'] ?? 900); // 15 minutes
define('ORDER_MAX_PER_HOUR', $_ENV['ORDER_MAX_PER_HOUR'] ?? 3);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);

// Create upload directories if they don't exist
$directories = [
    UPLOAD_PATH,
    PRODUCT_UPLOAD_PATH,
    VIDEO_UPLOAD_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
