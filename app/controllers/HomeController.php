<?php
/**
 * Home Controller
 */
class HomeController {
    private $productModel;
    private $categoryModel;
    private $sliderModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->sliderModel = new Slider();
    }
    
    /**
     * Display home page
     */
    public function index() {
        // Get featured products
        $featuredProducts = $this->productModel->getFeatured(6);
        
        // Get new arrivals
        $newArrivals = $this->productModel->getNewArrivals(8);
        
        // Get categories
        $categories = $this->categoryModel->getAll();
        
        // Add colors and stock to products
        foreach ($featuredProducts as &$product) {
            $product['colors'] = $this->productModel->getColors($product['id']);
            $product['total_stock'] = $this->productModel->getTotalStock($product['id']);
        }
        
        foreach ($newArrivals as &$product) {
            $product['colors'] = $this->productModel->getColors($product['id']);
            $product['total_stock'] = $this->productModel->getTotalStock($product['id']);
        }
        
        // Get active sliders from database
        $heroSlides = $this->sliderModel->getActive('display_order ASC');
        
        require __DIR__ . '/../views/pages/home.php';
    }
}
