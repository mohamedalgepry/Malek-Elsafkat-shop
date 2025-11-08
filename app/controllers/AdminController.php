<?php
/**
 * Admin Controller
 */
class AdminController {
    private $adminModel;
    private $productModel;
    private $categoryModel;
    private $orderModel;
    private $shippingModel;
    private $notificationModel;
    
    public function __construct() {
        $this->adminModel = new Admin();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->orderModel = new Order();
        $this->shippingModel = new Shipping();
        $this->notificationModel = new Notification();
    }
    
    /**
     * Display login page
     */
    public function login() {
        if (isAdminLoggedIn()) {
            redirect('/admin/dashboard');
            return;
        }
        
        require __DIR__ . '/../views/admin/login.php';
    }
    
    /**
     * Process login
     */
    public function loginPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin');
            return;
        }
        
        // Rate limiting
        if (!checkRateLimit('admin_login', LOGIN_MAX_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
            $_SESSION['error'] = 'تم تجاوز عدد محاولات تسجيل الدخول. يرجى المحاولة بعد 15 دقيقة';
            redirect('/admin');
            return;
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'يرجى إدخال اسم المستخدم وكلمة المرور';
            redirect('/admin');
            return;
        }
        
        $admin = $this->adminModel->verifyLogin($username, $password);
        
        if ($admin) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            
            redirect('/admin/dashboard');
        } else {
            $_SESSION['error'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
            redirect('/admin');
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        redirect('/admin');
    }
    
    /**
     * Display dashboard
     */
    public function dashboard() {
        requireAdmin();
        
        // Get statistics
        $todayStats = $this->orderModel->getStats('today');
        $monthStats = $this->orderModel->getStats('month');
        
        // Get recent orders
        $recentOrders = $this->orderModel->getRecent(10);
        
        // Get low stock products
        $lowStockProducts = $this->productModel->getLowStock(5);
        
        // Get total products
        $totalProducts = $this->productModel->count();
        
        require __DIR__ . '/../views/admin/dashboard.php';
    }
    
    /**
     * Display products list
     */
    public function products() {
        requireAdmin();
        
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $search = $_GET['search'] ?? '';
        $categoryFilter = $_GET['category'] ?? null;
        
        $filters = [];
        if ($categoryFilter) {
            $filters['category'] = $categoryFilter;
        }
        
        if ($search) {
            $products = $this->productModel->search($search, $perPage, $offset);
            $totalProducts = count($this->productModel->search($search));
        } else {
            $products = $this->productModel->filter($filters, $perPage, $offset);
            $totalProducts = $this->productModel->count($filters);
        }
        
        // Add total stock to each product
        foreach ($products as &$product) {
            $product['total_stock'] = $this->productModel->getTotalStock($product['id']);
        }
        
        $totalPages = ceil($totalProducts / $perPage);
        $categories = $this->categoryModel->getAll();
        
        require __DIR__ . '/../views/admin/products/index.php';
    }
    
    /**
     * Display add product form
     */
    public function addProduct() {
        requireAdmin();
        
        $categories = $this->categoryModel->getAll();
        
        require __DIR__ . '/../views/admin/products/add.php';
    }
    
    /**
     * Store new product
     */
    public function storeProduct() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/products');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/admin/products/add');
            return;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator
            ->required('name', 'اسم المنتج مطلوب')
            ->required('category_id', 'التصنيف مطلوب')
            ->required('regular_price', 'السعر مطلوب')
            ->numeric('regular_price', 'السعر يجب أن يكون رقماً')
            ->minValue('regular_price', 0, 'السعر يجب أن يكون أكبر من صفر');
        
        if ($validator->fails()) {
            $_SESSION['error'] = $validator->firstError();
            $_SESSION['form_data'] = $_POST;
            redirect('/admin/products/add');
            return;
        }
        
        // Upload main image
        if (empty($_FILES['main_image']['name'])) {
            $_SESSION['error'] = 'الصورة الرئيسية مطلوبة';
            $_SESSION['form_data'] = $_POST;
            redirect('/admin/products/add');
            return;
        }
        
        $mainImageUpload = uploadImage($_FILES['main_image']);
        if (!$mainImageUpload['success']) {
            $_SESSION['error'] = $mainImageUpload['message'];
            $_SESSION['form_data'] = $_POST;
            redirect('/admin/products/add');
            return;
        }
        
        // Generate slug
        $slug = generateSlug($_POST['name']);
        
        // Create product
        $productId = $this->productModel->create([
            'category_id' => $_POST['category_id'],
            'name' => $_POST['name'],
            'slug' => $slug,
            'description' => $_POST['description'] ?? null,
            'cost_price' => $_POST['cost_price'] ?? 0,
            'regular_price' => $_POST['regular_price'],
            'discount_price' => !empty($_POST['discount_price']) ? $_POST['discount_price'] : null,
            'main_image' => $mainImageUpload['filename'],
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ]);
        
        // Upload gallery images
        if (!empty($_FILES['gallery_images']['name'][0])) {
            foreach ($_FILES['gallery_images']['name'] as $key => $name) {
                if (!empty($name)) {
                    $file = [
                        'name' => $_FILES['gallery_images']['name'][$key],
                        'type' => $_FILES['gallery_images']['type'][$key],
                        'tmp_name' => $_FILES['gallery_images']['tmp_name'][$key],
                        'error' => $_FILES['gallery_images']['error'][$key],
                        'size' => $_FILES['gallery_images']['size'][$key]
                    ];
                    
                    $upload = uploadImage($file);
                    if ($upload['success']) {
                        $this->productModel->addImage($productId, $upload['filename'], $key);
                    }
                }
            }
        }
        
        // Upload video
        $videoType = $_POST['video_type'] ?? 'upload';
        
        if ($videoType === 'upload' && !empty($_FILES['product_video']['name'])) {
            // Upload video file
            $videoUpload = uploadVideo($_FILES['product_video']);
            if ($videoUpload['success']) {
                $this->productModel->addVideo($productId, $videoUpload['filename'], 'upload');
            }
        } elseif ($videoType === 'youtube' && !empty($_POST['youtube_url'])) {
            // Save YouTube URL
            $this->productModel->addVideo($productId, $_POST['youtube_url'], 'youtube');
        }
        
        // Add colors and sizes
        if (!empty($_POST['colors'])) {
            foreach ($_POST['colors'] as $color) {
                if (!empty($color['name']) && !empty($color['hex'])) {
                    // Add color (stock will be 0 as it's now in sizes)
                    $result = $this->productModel->addColor(
                        $productId,
                        $color['name'],
                        $color['hex'],
                        0
                    );
                    
                    // Get the inserted color ID
                    if ($result) {
                        $db = Database::getInstance()->getConnection();
                        $colorId = $db->lastInsertId();
                        
                        // Add sizes for this color
                        if (!empty($color['sizes'])) {
                            foreach ($color['sizes'] as $size) {
                                if (!empty($size['name']) && isset($size['stock'])) {
                                    $this->productModel->addSize(
                                        $colorId,
                                        $size['name'],
                                        $size['stock']
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $_SESSION['success'] = 'تم إضافة المنتج بنجاح';
        unset($_SESSION['form_data']);
        redirect('/admin/products');
    }
    
    /**
     * Display edit product form
     */
    public function editProduct($id) {
        requireAdmin();
        
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $_SESSION['error'] = 'المنتج غير موجود';
            redirect('/admin/products');
            return;
        }
        
        $categories = $this->categoryModel->getAll();
        
        require __DIR__ . '/../views/admin/products/edit.php';
    }
    
    /**
     * Update product
     */
    public function updateProduct($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/products');
            return;
        }
        
        // Debug: Log POST data
        error_log("Update Product - POST data: " . print_r($_POST, true));
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح - CSRF Token';
            error_log("CSRF Token verification failed");
            redirect('/admin/products/edit/' . $id);
            return;
        }
        
        $product = $this->productModel->getById($id);
        if (!$product) {
            $_SESSION['error'] = 'المنتج غير موجود';
            redirect('/admin/products');
            return;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator
            ->required('name', 'اسم المنتج مطلوب')
            ->required('category_id', 'التصنيف مطلوب')
            ->required('regular_price', 'السعر مطلوب')
            ->numeric('regular_price', 'السعر يجب أن يكون رقماً');
        
        if ($validator->fails()) {
            $_SESSION['error'] = $validator->firstError();
            error_log("Validation failed: " . $validator->firstError());
            redirect('/admin/products/edit/' . $id);
            return;
        }
        
        error_log("Validation passed, updating product...");
        
        // Handle main image upload
        $mainImage = $product['main_image'];
        if (!empty($_FILES['main_image']['name'])) {
            $mainImageUpload = uploadImage($_FILES['main_image']);
            if ($mainImageUpload['success']) {
                // Delete old image
                deleteFile(PRODUCT_UPLOAD_PATH . '/' . $product['main_image']);
                $mainImage = $mainImageUpload['filename'];
            }
        }
        
        // Generate slug
        $slug = generateSlug($_POST['name']);
        
        // Update product
        $this->productModel->update($id, [
            'category_id' => $_POST['category_id'],
            'name' => $_POST['name'],
            'slug' => $slug,
            'description' => $_POST['description'] ?? null,
            'cost_price' => $_POST['cost_price'] ?? 0,
            'regular_price' => $_POST['regular_price'],
            'discount_price' => !empty($_POST['discount_price']) ? $_POST['discount_price'] : null,
            'main_image' => $mainImage,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ]);
        
        // Upload video
        $videoType = $_POST['video_type'] ?? 'upload';
        
        if ($videoType === 'upload' && !empty($_FILES['product_video']['name'])) {
            // Delete old videos
            $oldVideos = $this->productModel->getVideos($id);
            foreach ($oldVideos as $oldVideo) {
                if ($oldVideo['video_type'] === 'upload') {
                    deleteFile(PRODUCT_UPLOAD_PATH . '/' . $oldVideo['video_path']);
                }
                $this->productModel->deleteVideo($oldVideo['id']);
            }
            
            // Upload new video
            $videoUpload = uploadVideo($_FILES['product_video']);
            if ($videoUpload['success']) {
                $this->productModel->addVideo($id, $videoUpload['filename'], 'upload');
            }
        } elseif ($videoType === 'youtube' && !empty($_POST['youtube_url'])) {
            // Delete old videos
            $oldVideos = $this->productModel->getVideos($id);
            foreach ($oldVideos as $oldVideo) {
                if ($oldVideo['video_type'] === 'upload') {
                    deleteFile(PRODUCT_UPLOAD_PATH . '/' . $oldVideo['video_path']);
                }
                $this->productModel->deleteVideo($oldVideo['id']);
            }
            
            // Save YouTube URL
            $this->productModel->addVideo($id, $_POST['youtube_url'], 'youtube');
        }
        
        // Add new colors with sizes
        if (!empty($_POST['new_colors'])) {
            foreach ($_POST['new_colors'] as $color) {
                if (!empty($color['name']) && !empty($color['hex'])) {
                    // Add color
                    $result = $this->productModel->addColor(
                        $id,
                        $color['name'],
                        $color['hex'],
                        0
                    );
                    
                    if ($result) {
                        $db = Database::getInstance()->getConnection();
                        $colorId = $db->lastInsertId();
                        
                        // Add sizes for this color
                        if (!empty($color['sizes'])) {
                            foreach ($color['sizes'] as $size) {
                                if (!empty($size['name']) && isset($size['stock'])) {
                                    $this->productModel->addSize(
                                        $colorId,
                                        $size['name'],
                                        $size['stock']
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        
        error_log("Product updated successfully, redirecting...");
        $_SESSION['success'] = 'تم تحديث المنتج بنجاح';
        redirect('/admin/products');
    }
    
    /**
     * Update sizes for a color
     */
    public function updateSizes($colorId) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/products');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/admin/products');
            return;
        }
        
        $productId = $_POST['product_id'] ?? null;
        if (!$productId) {
            $_SESSION['error'] = 'معرف المنتج مطلوب';
            redirect('/admin/products');
            return;
        }
        
        // Get color to verify it exists
        $color = $this->productModel->getColorById($colorId);
        if (!$color) {
            $_SESSION['error'] = 'اللون غير موجود';
            redirect('/admin/products/edit/' . $productId);
            return;
        }
        
        // Process sizes
        if (!empty($_POST['sizes'])) {
            foreach ($_POST['sizes'] as $size) {
                if (!empty($size['name'])) {
                    if (!empty($size['id'])) {
                        // Update existing size
                        $this->productModel->updateSize(
                            $size['id'],
                            $size['name'],
                            $size['stock'] ?? 0
                        );
                    } else {
                        // Add new size
                        $this->productModel->addSize(
                            $colorId,
                            $size['name'],
                            $size['stock'] ?? 0
                        );
                    }
                }
            }
        }
        
        $_SESSION['success'] = 'تم تحديث المقاسات بنجاح';
        redirect('/admin/products/edit/' . $productId);
    }
    
    /**
     * Delete product
     */
    public function deleteProduct($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/products');
            return;
        }
        
        $product = $this->productModel->getById($id);
        if (!$product) {
            $_SESSION['error'] = 'المنتج غير موجود';
            redirect('/admin/products');
            return;
        }
        
        // Delete images
        deleteFile(PRODUCT_UPLOAD_PATH . '/' . $product['main_image']);
        
        foreach ($product['images'] as $image) {
            deleteFile(PRODUCT_UPLOAD_PATH . '/' . $image['image_path']);
        }
        
        // Delete product
        $this->productModel->delete($id);
        
        $_SESSION['success'] = 'تم حذف المنتج بنجاح';
        redirect('/admin/products');
    }
    
    /**
     * Display categories
     */
    public function categories() {
        requireAdmin();
        
        $categories = $this->categoryModel->getAllWithProductCount();
        
        require __DIR__ . '/../views/admin/categories/index.php';
    }
    
    /**
     * Store category
     */
    public function storeCategory() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/categories');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/admin/categories');
            return;
        }
        
        $validator = new Validator($_POST);
        $validator->required('name', 'اسم التصنيف مطلوب');
        
        if ($validator->fails()) {
            $_SESSION['error'] = $validator->firstError();
            redirect('/admin/categories');
            return;
        }
        
        $slug = generateSlug($_POST['name']);
        
        // Handle image upload
        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadImage($_FILES['image']);
            if ($upload['success']) {
                $image = $upload['filename'];
            }
        }
        
        $this->categoryModel->create([
            'name' => $_POST['name'],
            'slug' => $slug,
            'description' => $_POST['description'] ?? null,
            'image' => $image,
            'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
            'display_order' => $_POST['display_order'] ?? 0
        ]);
        
        $_SESSION['success'] = 'تم إضافة التصنيف بنجاح';
        redirect('/admin/categories');
    }
    
    /**
     * Update category
     */
    public function updateCategory($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/categories');
            return;
        }
        
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            $_SESSION['error'] = 'التصنيف غير موجود';
            redirect('/admin/categories');
            return;
        }
        
        $validator = new Validator($_POST);
        $validator->required('name', 'اسم التصنيف مطلوب');
        
        if ($validator->fails()) {
            $_SESSION['error'] = $validator->firstError();
            redirect('/admin/categories');
            return;
        }
        
        $slug = generateSlug($_POST['name']);
        
        // Handle image upload
        $image = $category['image'];
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadImage($_FILES['image']);
            if ($upload['success']) {
                if ($category['image']) {
                    deleteFile(PRODUCT_UPLOAD_PATH . '/' . $category['image']);
                }
                $image = $upload['filename'];
            }
        }
        
        $this->categoryModel->update($id, [
            'name' => $_POST['name'],
            'slug' => $slug,
            'description' => $_POST['description'] ?? null,
            'image' => $image,
            'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
            'display_order' => $_POST['display_order'] ?? 0
        ]);
        
        $_SESSION['success'] = 'تم تحديث التصنيف بنجاح';
        redirect('/admin/categories');
    }
    
    /**
     * Delete category
     */
    public function deleteCategory($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/categories');
            return;
        }
        
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            $_SESSION['error'] = 'التصنيف غير موجود';
            redirect('/admin/categories');
            return;
        }
        
        if ($this->categoryModel->hasProducts($id)) {
            $_SESSION['error'] = 'لا يمكن حذف تصنيف يحتوي على منتجات';
            redirect('/admin/categories');
            return;
        }
        
        $this->categoryModel->delete($id);
        
        $_SESSION['success'] = 'تم حذف التصنيف بنجاح';
        redirect('/admin/categories');
    }
    
    /**
     * Display orders
     */
    public function orders() {
        requireAdmin();
        
        $page = $_GET['page'] ?? 1;
        $perPage = ORDERS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        $filters = [
            'status' => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $orders = $this->orderModel->filter($filters, $perPage, $offset);
        $totalOrders = $this->orderModel->count($filters);
        $totalPages = ceil($totalOrders / $perPage);
        
        require __DIR__ . '/../views/admin/orders/index.php';
    }
    
    /**
     * View order details
     */
    public function viewOrder($id) {
        requireAdmin();
        
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            $_SESSION['error'] = 'الطلب غير موجود';
            redirect('/admin/orders');
            return;
        }
        
        require __DIR__ . '/../views/admin/orders/view.php';
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/orders');
            return;
        }
        
        $order = $this->orderModel->getById($id);
        if (!$order) {
            $_SESSION['error'] = 'الطلب غير موجود';
            redirect('/admin/orders');
            return;
        }
        
        $newStatus = $_POST['status'] ?? '';
        $note = $_POST['note'] ?? null;
        
        $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['error'] = 'حالة غير صالحة';
            redirect('/admin/orders/view/' . $id);
            return;
        }
        
        $this->orderModel->updateStatus($id, $newStatus, $note, $_SESSION['admin_id']);
        
        $_SESSION['success'] = 'تم تحديث حالة الطلب بنجاح';
        redirect('/admin/orders/view/' . $id);
    }
    
    /**
     * Delete order
     */
    public function deleteOrder($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/orders');
            return;
        }
        
        $this->orderModel->delete($id);
        
        $_SESSION['success'] = 'تم حذف الطلب بنجاح';
        redirect('/admin/orders');
    }
    
    /**
     * Display settings
     */
    public function settings() {
        requireAdmin();
        
        $settings = $this->adminModel->getAllSettings();
        
        require __DIR__ . '/../views/admin/settings.php';
    }
    
    /**
     * Update settings
     */
    public function updateSettings() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/admin/settings');
            return;
        }
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'website':
                $this->adminModel->updateSettings([
                    'site_name' => $_POST['site_name'],
                    'site_email' => $_POST['site_email'],
                    'site_phone' => $_POST['site_phone'],
                    'site_address' => $_POST['site_address'],
                    'currency' => $_POST['currency'],
                    'tax_rate' => $_POST['tax_rate'],
                    'shipping_fee' => $_POST['shipping_fee'],
                    'free_shipping_threshold' => $_POST['free_shipping_threshold']
                ]);
                $_SESSION['success'] = 'تم تحديث إعدادات الموقع';
                break;
                
            case 'shipping':
                $this->adminModel->updateSettings([
                    'shipping_title' => $_POST['shipping_title'] ?? 'تفاصيل الشحن',
                    'shipping_details' => $_POST['shipping_details'] ?? '',
                    'shipping_notes' => $_POST['shipping_notes'] ?? '',
                    'shipping_contact' => $_POST['shipping_contact'] ?? ''
                ]);
                $_SESSION['success'] = 'تم تحديث تفاصيل الشحن';
                break;
                
            case 'password':
                if (empty($_POST['current_password']) || empty($_POST['new_password'])) {
                    $_SESSION['error'] = 'يرجى ملء جميع الحقول';
                    break;
                }
                
                if (!$this->adminModel->verifyPassword($_SESSION['admin_id'], $_POST['current_password'])) {
                    $_SESSION['error'] = 'كلمة المرور الحالية غير صحيحة';
                    break;
                }
                
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    $_SESSION['error'] = 'كلمات المرور غير متطابقة';
                    break;
                }
                
                $this->adminModel->updatePassword($_SESSION['admin_id'], $_POST['new_password']);
                $_SESSION['success'] = 'تم تغيير كلمة المرور بنجاح';
                break;
                
            case 'cleanup':
                $days = (int)($_POST['days'] ?? 30);
                $this->orderModel->deleteOldOrders($days);
                $_SESSION['success'] = 'تم حذف الطلبات القديمة';
                break;
                
            case 'backup':
                $backupFile = $this->adminModel->createBackup();
                $_SESSION['success'] = 'تم إنشاء نسخة احتياطية';
                // Trigger download
                header('Content-Type: application/sql');
                header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
                readfile($backupFile);
                exit;
        }
        
        redirect('/admin/settings');
    }
    
    /**
     * ===== Shipping Management =====
     */
    
    /**
     * Display shipping governorates list
     */
    public function shipping() {
        requireAdmin();
        
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $governorates = $this->shippingModel->search($search);
        } else {
            $governorates = $this->shippingModel->getAllGovernorates(false);
        }
        
        $stats = $this->shippingModel->getStats();
        
        require __DIR__ . '/../views/admin/shipping/index.php';
    }
    
    /**
     * Display add governorate form
     */
    public function addShipping() {
        requireAdmin();
        require __DIR__ . '/../views/admin/shipping/add.php';
    }
    
    /**
     * Store new governorate
     */
    public function storeShipping() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/shipping');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/admin/shipping/add');
            return;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator
            ->required('name_ar', 'اسم المحافظة بالعربية مطلوب')
            ->required('shipping_cost', 'تكلفة الشحن مطلوبة')
            ->numeric('shipping_cost', 'تكلفة الشحن يجب أن تكون رقماً');
        
        if ($validator->fails()) {
            $_SESSION['error'] = $validator->firstError();
            $_SESSION['form_data'] = $_POST;
            redirect('/admin/shipping/add');
            return;
        }
        
        $data = [
            'name_ar' => $_POST['name_ar'],
            'name_en' => $_POST['name_en'] ?? '',
            'shipping_cost' => (float)$_POST['shipping_cost'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        $this->shippingModel->create($data);
        
        $_SESSION['success'] = 'تم إضافة المحافظة بنجاح';
        redirect('/admin/shipping');
    }
    
    /**
     * Display edit governorate form
     */
    public function editShipping($id) {
        requireAdmin();
        
        $governorate = $this->shippingModel->getGovernorateById($id);
        
        if (!$governorate) {
            $_SESSION['error'] = 'المحافظة غير موجودة';
            redirect('/admin/shipping');
            return;
        }
        
        require __DIR__ . '/../views/admin/shipping/edit.php';
    }
    
    /**
     * Update governorate
     */
    public function updateShipping($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/shipping');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/admin/shipping/edit/' . $id);
            return;
        }
        
        // Validate input
        $validator = new Validator($_POST);
        $validator
            ->required('name_ar', 'اسم المحافظة بالعربية مطلوب')
            ->required('shipping_cost', 'تكلفة الشحن مطلوبة')
            ->numeric('shipping_cost', 'تكلفة الشحن يجب أن تكون رقماً');
        
        if ($validator->fails()) {
            $_SESSION['error'] = $validator->firstError();
            $_SESSION['form_data'] = $_POST;
            redirect('/admin/shipping/edit/' . $id);
            return;
        }
        
        $data = [
            'name_ar' => $_POST['name_ar'],
            'name_en' => $_POST['name_en'] ?? '',
            'shipping_cost' => (float)$_POST['shipping_cost'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        $this->shippingModel->update($id, $data);
        
        $_SESSION['success'] = 'تم تحديث المحافظة بنجاح';
        redirect('/admin/shipping');
    }
    
    /**
     * Delete governorate
     */
    public function deleteShipping($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/shipping');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/admin/shipping');
            return;
        }
        
        $this->shippingModel->delete($id);
        
        $_SESSION['success'] = 'تم حذف المحافظة بنجاح';
        redirect('/admin/shipping');
    }
    
    /**
     * Toggle governorate active status
     */
    public function toggleShipping($id) {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/shipping');
            return;
        }
        
        // Verify CSRF token
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'طلب غير صالح';
            redirect('/admin/shipping');
            return;
        }
        
        $this->shippingModel->toggleActive($id);
        
        $_SESSION['success'] = 'تم تحديث حالة المحافظة';
        redirect('/admin/shipping');
    }
    
    // Notifications
    public function getNotifications() {
        // Return JSON 401 if not logged in (avoid HTML redirect for AJAX)
        if (!isAdminLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $notifications = $this->notificationModel->getUnread();
        $count = $this->notificationModel->getUnreadCount();
        
        echo json_encode([
            'success' => true,
            'count' => $count,
            'notifications' => $notifications
        ]);
        exit;
    }
    
    public function markNotificationRead($id) {
        if (!isAdminLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        
        $this->notificationModel->markAsRead($id);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
    
    public function markAllNotificationsRead() {
        if (!isAdminLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        
        $this->notificationModel->markAllAsRead();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
