<?php
/**
 * Product Controller
 */
class ProductController {
    private $productModel;
    private $categoryModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * Display shop page with filters
     */
    public function shop() {
        $page = $_GET['page'] ?? 1;
        $perPage = PRODUCTS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // Get filters
        $filters = [
            'category' => $_GET['category'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'colors' => $_GET['colors'] ?? [],
            'sort' => $_GET['sort'] ?? 'newest'
        ];
        
        // Get products
        $products = $this->productModel->filter($filters, $perPage, $offset);
        $totalProducts = $this->productModel->count($filters);
        $totalPages = ceil($totalProducts / $perPage);
        
        // Add colors to each product
        foreach ($products as &$product) {
            $product['colors'] = $this->productModel->getColors($product['id']);
            $product['total_stock'] = $this->productModel->getTotalStock($product['id']);
        }
        
        // Get categories for filter
        $categories = $this->categoryModel->getAllWithProductCount();
        
        // Get all available colors
        $allColors = $this->getAllColors();
        
        require __DIR__ . '/../views/pages/shop.php';
    }
    
    /**
     * Display single product
     */
    public function show($slug) {
        $product = $this->productModel->getBySlug($slug);
        
        if (!$product) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }
        
        // Increment views
        $this->productModel->incrementViews($product['id']);
        
        // Get related products
        $relatedProducts = $this->productModel->getRelated($product['id'], $product['category_id'], 4);
        
        // Add colors to related products
        foreach ($relatedProducts as &$relatedProduct) {
            $relatedProduct['colors'] = $this->productModel->getColors($relatedProduct['id']);
            $relatedProduct['total_stock'] = $this->productModel->getTotalStock($relatedProduct['id']);
        }
        
        require __DIR__ . '/../views/pages/product.php';
    }
    
    /**
     * Search products
     */
    public function search() {
        $query = $_GET['q'] ?? '';
        $page = $_GET['page'] ?? 1;
        $perPage = PRODUCTS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        if (empty($query)) {
            redirect('/shop');
            return;
        }
        
        $products = $this->productModel->search($query, $perPage, $offset);
        $totalProducts = count($this->productModel->search($query));
        $totalPages = ceil($totalProducts / $perPage);
        
        // Add colors to each product
        foreach ($products as &$product) {
            $product['colors'] = $this->productModel->getColors($product['id']);
            $product['total_stock'] = $this->productModel->getTotalStock($product['id']);
        }
        
        require __DIR__ . '/../views/pages/search.php';
    }
    
    /**
     * Get all unique colors from products
     */
    private function getAllColors() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT DISTINCT color_name, color_hex 
            FROM product_colors 
            ORDER BY color_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
