<?php
// Start session
session_start();

// Load configuration
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/validation.php';

// Load models
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Order.php';
require_once __DIR__ . '/../app/models/Admin.php';
require_once __DIR__ . '/../app/models/Slider.php';
require_once __DIR__ . '/../app/models/Shipping.php';
require_once __DIR__ . '/../app/models/Notification.php';

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string and base path
$uri = parse_url($requestUri, PHP_URL_PATH);

// Remove base paths in correct order
$uri = str_replace('/shoppp/public', '', $uri);
$uri = str_replace('/shoppp', '', $uri);
$uri = str_replace('/public', '', $uri);

// Remove trailing slash
$uri = rtrim($uri, '/');

// Default to home if empty
$uri = $uri ?: '/';

// Define routes
$routes = [
    // Customer routes
    'GET' => [
        '/' => 'HomeController@index',
        '/shop' => 'ProductController@shop',
        '/product/{slug}' => 'ProductController@show',
        '/cart' => 'CartController@index',
        '/checkout' => 'CheckoutController@index',
        '/track-order' => 'OrderController@track',
        '/search' => 'ProductController@search',
        
        // Admin routes
        '/admin' => 'AdminController@login',
        '/admin/dashboard' => 'AdminController@dashboard',
        '/admin/products' => 'AdminController@products',
        '/admin/products/add' => 'AdminController@addProduct',
        '/admin/products/edit/{id}' => 'AdminController@editProduct',
        '/admin/categories' => 'AdminController@categories',
        '/admin/orders' => 'AdminController@orders',
        '/admin/orders/view/{id}' => 'AdminController@viewOrder',
        '/admin/sliders' => 'SliderController@index',
        '/admin/sliders/create' => 'SliderController@create',
        '/admin/sliders/edit/{id}' => 'SliderController@edit',
        '/admin/shipping' => 'AdminController@shipping',
        '/admin/shipping/add' => 'AdminController@addShipping',
        '/admin/shipping/edit/{id}' => 'AdminController@editShipping',
        '/admin/settings' => 'AdminController@settings',
        '/admin/logout' => 'AdminController@logout',
        '/admin/notifications' => 'AdminController@getNotifications',
    ],
    'POST' => [
        '/cart/add' => 'CartController@add',
        '/cart/update' => 'CartController@update',
        '/cart/remove' => 'CartController@remove',
        '/checkout/process' => 'CheckoutController@process',
        '/track-order/search' => 'OrderController@search',
        
        // Admin routes
        '/admin/login' => 'AdminController@loginPost',
        '/admin/products/store' => 'AdminController@storeProduct',
        '/admin/products/update/{id}' => 'AdminController@updateProduct',
        '/admin/products/update-sizes/{id}' => 'AdminController@updateSizes',
        '/admin/products/delete/{id}' => 'AdminController@deleteProduct',
        '/admin/categories/store' => 'AdminController@storeCategory',
        '/admin/categories/update/{id}' => 'AdminController@updateCategory',
        '/admin/categories/delete/{id}' => 'AdminController@deleteCategory',
        '/admin/orders/update-status/{id}' => 'AdminController@updateOrderStatus',
        '/admin/orders/delete/{id}' => 'AdminController@deleteOrder',
        '/admin/sliders/store' => 'SliderController@store',
        '/admin/sliders/update/{id}' => 'SliderController@update',
        '/admin/sliders/delete/{id}' => 'SliderController@delete',
        '/admin/sliders/toggle-active/{id}' => 'SliderController@toggleActive',
        '/admin/shipping/store' => 'AdminController@storeShipping',
        '/admin/shipping/update/{id}' => 'AdminController@updateShipping',
        '/admin/shipping/delete/{id}' => 'AdminController@deleteShipping',
        '/admin/shipping/toggle/{id}' => 'AdminController@toggleShipping',
        '/admin/settings/update' => 'AdminController@updateSettings',
        '/admin/notifications/read/{id}' => 'AdminController@markNotificationRead',
        '/admin/notifications/read-all' => 'AdminController@markAllNotificationsRead',
    ]
];

// Match route
$handler = null;
$params = [];

if (isset($routes[$requestMethod])) {
    foreach ($routes[$requestMethod] as $route => $action) {
        // Convert route pattern to regex (English slugs only)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            $handler = $action;
            array_shift($matches); // Remove full match
            $params = $matches;
            break;
        }
    }
}

// Execute handler
if ($handler) {
    list($controller, $method) = explode('@', $handler);
    $controllerFile = __DIR__ . '/../app/controllers/' . $controller . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controllerInstance = new $controller();
        
        if (method_exists($controllerInstance, $method)) {
            call_user_func_array([$controllerInstance, $method], $params);
        } else {
            http_response_code(404);
            require __DIR__ . '/../app/views/errors/404.php';
        }
    } else {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
    }
} else {
    http_response_code(404);
    require __DIR__ . '/../app/views/errors/404.php';
}
