<?php
/**
 * Checkout Controller
 */
class CheckoutController {
    private $productModel;
    private $orderModel;
    private $shippingModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->shippingModel = new Shipping();
    }
    
    /**
     * Display checkout page
     */
    public function index() {
        $cart = getCart();
        
        if (empty($cart)) {
            $_SESSION['error'] = 'السلة فارغة';
            redirect('/cart');
            return;
        }
        
        $cartItems = [];
        $subtotal = 0;
        
        // Get full product details
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
        
        // Get governorates for shipping
        $governorates = $this->shippingModel->getAllGovernorates(true);
        
        // Default shipping (will be calculated based on governorate selection)
        $shippingFee = 0;
        $total = $subtotal + $shippingFee;
        
        require __DIR__ . '/../views/pages/checkout.php';
    }
    
    /**
     * Process checkout
     */
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/checkout');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/checkout');
            return;
        }
        
        // Check cart
        $cart = getCart();
        if (empty($cart)) {
            $_SESSION['error'] = 'السلة فارغة';
            redirect('/cart');
            return;
        }
        
        // Rate limiting
        if (!checkRateLimit('order', ORDER_MAX_PER_HOUR, 3600)) {
            $_SESSION['error'] = 'لقد تجاوزت الحد الأقصى للطلبات. يرجى المحاولة لاحقاً';
            redirect('/checkout');
            return;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator
            ->required('customer_name', 'الاسم مطلوب')
            ->min('customer_name', 3, 'الاسم يجب أن يكون 3 أحرف على الأقل')
            ->required('customer_phone', 'رقم الهاتف مطلوب')
            ->phone('customer_phone', 'رقم الهاتف غير صالح')
            ->required('shipping_address', 'العنوان مطلوب')
            ->required('shipping_city', 'المدينة مطلوبة')
            ->required('governorate_id', 'المحافظة مطلوبة');
        
        if (!empty($_POST['customer_email'])) {
            $validator->email('customer_email', 'البريد الإلكتروني غير صالح');
        }
        
        if ($validator->fails()) {
            $_SESSION['error'] = $validator->firstError();
            $_SESSION['form_data'] = $_POST;
            redirect('/checkout');
            return;
        }
        
        // Calculate totals
        $subtotal = 0;
        $orderItems = [];
        
        foreach ($cart as $item) {
            $product = $this->productModel->getById($item['product_id']);
            
            if (!$product) {
                $_SESSION['error'] = 'أحد المنتجات غير متوفر';
                redirect('/cart');
                return;
            }
            
            // Verify stock
            $colors = $this->productModel->getColors($product['id']);
            $availableStock = 0;
            $colorId = null;
            
            foreach ($colors as $color) {
                if ($color['color_name'] === $item['color_name']) {
                    $colorId = $color['id'];
                    
                    // If size is specified, check size stock
                    if (!empty($item['size_name'])) {
                        $size = $this->productModel->getSizeByColorAndName($color['id'], $item['size_name']);
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
            
            if ($availableStock < $item['quantity']) {
                $_SESSION['error'] = "الكمية المطلوبة من {$product['name']} ({$item['color_name']}" . 
                                     (!empty($item['size_name']) ? " - {$item['size_name']}" : "") . 
                                     ") غير متوفرة. المتوفر: {$availableStock}";
                redirect('/cart');
                return;
            }
            
            $itemSubtotal = $item['price'] * $item['quantity'];
            $subtotal += $itemSubtotal;
            
            $orderItems[] = [
                'product_id' => $product['id'],
                'product_sku' => $product['sku'] ?? null,
                'product_name' => $product['name'],
                'product_image' => $product['main_image'],
                'color_name' => $item['color_name'],
                'color_hex' => $item['color_hex'],
                'size_name' => $item['size_name'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $itemSubtotal
            ];
        }
        
        // Get shipping cost from governorate
        $governorateId = (int)$_POST['governorate_id'];
        $shippingFee = $this->shippingModel->getShippingCost($governorateId);
        
        if ($shippingFee === 0) {
            $_SESSION['error'] = 'المحافظة المختارة غير صالحة';
            redirect('/checkout');
            return;
        }
        
        $total = $subtotal + $shippingFee;
        
        // Generate invoice number
        $invoiceNumber = generateInvoiceNumber();
        
        // Create order
        $orderId = $this->orderModel->create([
            'invoice_number' => $invoiceNumber,
            'customer_name' => $_POST['customer_name'],
            'customer_phone' => $_POST['customer_phone'],
            'customer_email' => $_POST['customer_email'] ?? null,
            'shipping_address' => $_POST['shipping_address'],
            'shipping_city' => $_POST['shipping_city'],
            'shipping_state' => $_POST['shipping_state'] ?? null,
            'shipping_postal_code' => $_POST['shipping_postal_code'] ?? null,
            'governorate_id' => $governorateId,
            'order_notes' => $_POST['order_notes'] ?? null,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total_amount' => $total,
            'status' => 'pending',
            'payment_method' => 'cod'
        ]);
        
        // Add order items
        foreach ($orderItems as $item) {
            $this->orderModel->addItem($orderId, $item);
        }
        
        // Create notification for new order
        $notificationModel = new Notification();
        $notificationModel->create(
            'new_order',
            'طلب جديد #' . $invoiceNumber,
            'تم استلام طلب جديد من ' . $_POST['customer_name'] . ' بقيمة ' . formatPrice($total),
            '/admin/orders/view/' . $orderId,
            $orderId
        );
        
        // Clear cart
        clearCart();
        
        // Clear form data
        unset($_SESSION['form_data']);
        
        // Set success message
        $_SESSION['success'] = 'تم إنشاء طلبك بنجاح!';
        $_SESSION['invoice_number'] = $invoiceNumber;
        
        // Redirect to tracking page
        redirect('/track-order?invoice=' . $invoiceNumber);
    }
}
