<?php
/**
 * Cart Controller
 */
class CartController {
    private $productModel;
    
    public function __construct() {
        $this->productModel = new Product();
    }
    
    /**
     * Display cart page
     */
    public function index() {
        $cart = getCart();
        $cartItems = [];
        $subtotal = 0;
        
        // Get full product details for cart items
        foreach ($cart as $key => $item) {
            $product = $this->productModel->getById($item['product_id']);
            
            if ($product) {
                $cartItems[$key] = array_merge($item, [
                    'product' => $product,
                    'subtotal' => $item['price'] * $item['quantity']
                ]);
                $subtotal += $cartItems[$key]['subtotal'];
            }
        }
        
        // Shipping will be calculated at checkout based on governorate
        $shippingFee = 0;
        $total = $subtotal;
        
        require __DIR__ . '/../views/pages/cart.php';
    }
    
    /**
     * Add item to cart
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/shop');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/shop');
            return;
        }
        
        $productId = $_POST['product_id'] ?? null;
        $colorId = $_POST['color_id'] ?? null;
        $sizeId = $_POST['size_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if (!$productId || !$colorId || $quantity < 1) {
            $_SESSION['error'] = 'بيانات غير صالحة';
            redirect('/shop');
            return;
        }
        
        // Get product
        $product = $this->productModel->getById($productId);
        if (!$product) {
            $_SESSION['error'] = 'المنتج غير موجود';
            redirect('/shop');
            return;
        }
        
        // Get color
        $color = $this->productModel->getColorById($colorId);
        if (!$color) {
            $_SESSION['error'] = 'اللون غير متوفر';
            redirect('/product/' . $product['slug']);
            return;
        }
        
        // Check if size is required and get size info
        $sizeName = null;
        $stockToCheck = 0;
        
        if ($sizeId) {
            $size = $this->productModel->getSizeById($sizeId);
            if (!$size) {
                $_SESSION['error'] = 'المقاس غير متوفر';
                redirect('/product/' . $product['slug']);
                return;
            }
            $sizeName = $size['size_name'];
            $stockToCheck = $size['stock_quantity'];
        } else {
            // If no size specified, get total stock for the color
            $colors = $this->productModel->getColors($product['id']);
            foreach ($colors as $c) {
                if ($c['id'] == $colorId) {
                    $stockToCheck = $c['total_stock'] ?? 0;
                    break;
                }
            }
        }
        
        // Check stock
        if ($stockToCheck < $quantity) {
            $_SESSION['error'] = "الكمية المطلوبة غير متوفرة. المتوفر: {$stockToCheck}";
            redirect('/product/' . $product['slug']);
            return;
        }
        
        // Get final price
        $price = getFinalPrice($product['regular_price'], $product['discount_price']);
        
        // Add to cart
        addToCart(
            $product['id'],
            $product['name'],
            $price,
            $product['main_image'],
            $color['color_name'],
            $color['color_hex'],
            $quantity,
            $sizeName
        );
        
        $_SESSION['success'] = 'تمت إضافة المنتج إلى السلة';
        
        // Return JSON for AJAX requests
        if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'تمت إضافة المنتج إلى السلة',
                'cart_count' => getCartCount()
            ]);
            exit;
        }
        
        redirect('/cart');
    }
    
    /**
     * Update cart item quantity
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/cart');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/cart');
            return;
        }
        
        $cartKey = $_POST['cart_key'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 0);
        
        if (!$cartKey) {
            $_SESSION['error'] = 'بيانات غير صالحة';
            redirect('/cart');
            return;
        }
        
        updateCartQuantity($cartKey, $quantity);
        $_SESSION['success'] = 'تم تحديث السلة';
        
        // Return JSON for AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث السلة',
                'cart_count' => getCartCount()
            ]);
            exit;
        }
        
        redirect('/cart');
    }
    
    /**
     * Remove item from cart
     */
    public function remove() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/cart');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/cart');
            return;
        }
        
        $cartKey = $_POST['cart_key'] ?? null;
        
        if (!$cartKey) {
            $_SESSION['error'] = 'بيانات غير صالحة';
            redirect('/cart');
            return;
        }
        
        removeFromCart($cartKey);
        $_SESSION['success'] = 'تم حذف المنتج من السلة';
        
        // Return JSON for AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'تم حذف المنتج من السلة',
                'cart_count' => getCartCount()
            ]);
            exit;
        }
        
        redirect('/cart');
    }
}
