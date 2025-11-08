<?php
/**
 * Admin Slider Controller
 */
class SliderController {
    private $sliderModel;
    
    public function __construct() {
        // Check if admin is logged in
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        $this->sliderModel = new Slider();
    }
    
    /**
     * Display sliders list
     */
    public function index() {
        $sliders = $this->sliderModel->getAll('display_order ASC');
        require __DIR__ . '/../views/admin/sliders/index.php';
    }
    
    /**
     * Show create slider form
     */
    public function create() {
        require __DIR__ . '/../views/admin/sliders/create.php';
    }
    
    /**
     * Store new slider
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/sliders');
            exit;
        }
        
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'طلب غير صالح';
            header('Location: ' . BASE_URL . '/admin/sliders/create');
            exit;
        }
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['title'])) {
            $errors[] = 'العنوان مطلوب';
        }
        
        if (empty($_FILES['image']['name'])) {
            $errors[] = 'الصورة مطلوبة';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/admin/sliders/create');
            exit;
        }
        
        // Handle image upload
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->uploadImage($_FILES['image']);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['error'];
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . BASE_URL . '/admin/sliders/create');
                exit;
            }
        }
        
        // Create slider
        $data = [
            'title' => $_POST['title'],
            'subtitle' => $_POST['subtitle'] ?? null,
            'button_text' => $_POST['button_text'] ?? 'تسوق الآن',
            'button_link' => $_POST['button_link'] ?? '/shop',
            'image' => $imagePath,
            'display_order' => $_POST['display_order'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if ($this->sliderModel->create($data)) {
            $_SESSION['success'] = 'تم إضافة السلايد بنجاح';
            header('Location: ' . BASE_URL . '/admin/sliders');
        } else {
            $_SESSION['error'] = 'حدث خطأ أثناء إضافة السلايد';
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/admin/sliders/create');
        }
        exit;
    }
    
    /**
     * Show edit slider form
     */
    public function edit($id) {
        $slider = $this->sliderModel->getById($id);
        
        if (!$slider) {
            $_SESSION['error'] = 'السلايد غير موجود';
            header('Location: ' . BASE_URL . '/admin/sliders');
            exit;
        }
        
        require __DIR__ . '/../views/admin/sliders/edit.php';
    }
    
    /**
     * Update slider
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/sliders');
            exit;
        }
        
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'طلب غير صالح';
            header('Location: ' . BASE_URL . '/admin/sliders/edit/' . $id);
            exit;
        }
        
        $slider = $this->sliderModel->getById($id);
        if (!$slider) {
            $_SESSION['error'] = 'السلايد غير موجود';
            header('Location: ' . BASE_URL . '/admin/sliders');
            exit;
        }
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['title'])) {
            $errors[] = 'العنوان مطلوب';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/admin/sliders/edit/' . $id);
            exit;
        }
        
        // Handle image upload
        $imagePath = $slider['image'];
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->uploadImage($_FILES['image']);
            if ($uploadResult['success']) {
                // Delete old image
                if ($slider['image'] && !str_contains($slider['image'], 'hero')) {
                    $oldImagePath = __DIR__ . '/../../public' . $slider['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $imagePath = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['error'];
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . BASE_URL . '/admin/sliders/edit/' . $id);
                exit;
            }
        }
        
        // Update slider
        $data = [
            'title' => $_POST['title'],
            'subtitle' => $_POST['subtitle'] ?? null,
            'button_text' => $_POST['button_text'] ?? 'تسوق الآن',
            'button_link' => $_POST['button_link'] ?? '/shop',
            'image' => $imagePath,
            'display_order' => $_POST['display_order'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if ($this->sliderModel->update($id, $data)) {
            $_SESSION['success'] = 'تم تحديث السلايد بنجاح';
            header('Location: ' . BASE_URL . '/admin/sliders');
        } else {
            $_SESSION['error'] = 'حدث خطأ أثناء تحديث السلايد';
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/admin/sliders/edit/' . $id);
        }
        exit;
    }
    
    /**
     * Delete slider
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/sliders');
            exit;
        }
        
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'طلب غير صالح';
            header('Location: ' . BASE_URL . '/admin/sliders');
            exit;
        }
        
        if ($this->sliderModel->delete($id)) {
            $_SESSION['success'] = 'تم حذف السلايد بنجاح';
        } else {
            $_SESSION['error'] = 'حدث خطأ أثناء حذف السلايد';
        }
        
        header('Location: ' . BASE_URL . '/admin/sliders');
        exit;
    }
    
    /**
     * Toggle slider active status
     */
    public function toggleActive($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/sliders');
            exit;
        }
        
        if ($this->sliderModel->toggleActive($id)) {
            $_SESSION['success'] = 'تم تحديث حالة السلايد';
        } else {
            $_SESSION['error'] = 'حدث خطأ أثناء تحديث حالة السلايد';
        }
        
        header('Location: ' . BASE_URL . '/admin/sliders');
        exit;
    }
    
    /**
     * Upload slider image with auto-resize
     */
    private function uploadImage($file) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize = 50 * 1024 * 1024; // 50MB - نقبل أي حجم تقريباً
        
        // Check file type
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'نوع الملف غير مدعوم. يرجى رفع صورة JPG, PNG, WEBP أو GIF'];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'حجم الملف كبير جداً. الحد الأقصى 50MB'];
        }
        
        // Create upload directories if not exist
        $uploadDir = __DIR__ . '/../../public/uploads/sliders/';
        $thumbDir = __DIR__ . '/../../public/uploads/sliders/thumbs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'slider_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        $thumbPath = $thumbDir . $filename;
        
        // Move uploaded file temporarily
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => false, 'error' => 'فشل رفع الملف'];
        }
        
        // Resize main image for slider (max 1920x1080)
        $this->resizeImage($uploadPath, $uploadPath, 1920, 1080);
        
        // Create thumbnail for admin list (120x80)
        $this->resizeImage($uploadPath, $thumbPath, 120, 80);
        
        return ['success' => true, 'path' => '/uploads/sliders/' . $filename];
    }
    
    /**
     * Resize image maintaining aspect ratio
     */
    private function resizeImage($source, $destination, $maxWidth, $maxHeight) {
        // Get image info
        $imageInfo = getimagesize($source);
        if (!$imageInfo) {
            return false;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        
        // Don't upscale
        if ($ratio > 1) {
            $ratio = 1;
        }
        
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Create image resource based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($source);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($source);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($source);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save resized image
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $destination, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $destination, 9);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($newImage, $destination, 90);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $destination);
                break;
        }
        
        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return true;
    }
}
