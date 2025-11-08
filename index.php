<?php
/**
 * Front Controller for InfinityFree
 * Serves static files from public/ and routes dynamic requests
 */

// Get the request path
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Check if this is a static asset request
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|webp|svg|ico|woff|woff2|ttf|eot|mp4|webm|pdf)$/i', $path, $matches)) {
    // Try to serve from public directory
    $file = __DIR__ . '/public' . $path;
    
    if (file_exists($file) && is_file($file)) {
        // Determine content type
        $ext = strtolower($matches[1]);
        $types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm'
        ];
        
        if (isset($types[$ext])) {
            header('Content-Type: ' . $types[$ext]);
        }
        
        // Cache static files
        header('Cache-Control: public, max-age=2592000');
        readfile($file);
        exit;
    }
    
    // File not found
    http_response_code(404);
    exit;
}

// All other requests go to public/index.php
require __DIR__ . '/public/index.php';
