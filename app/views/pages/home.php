<?php
$pageTitle = 'الرئيسية';
$pageDescription = 'اكتشف أحدث تشكيلة من الأحذية الفاخرة بأفضل الأسعار';
require __DIR__ . '/../layouts/header.php';
?>

<!-- Hero Slider -->
<section class="hero-slider">
    <div class="slider-container">
        <?php foreach ($heroSlides as $index => $slide): ?>
            <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                <div class="slide-bg">
                    <img src="<?= BASE_URL ?><?= escape($slide['image']) ?>" alt="<?= escape($slide['title']) ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>">
                    <div class="slide-overlay"></div>
                </div>
                <div class="slide-content">
                    <div class="container">
                        <div class="hero-text">
                            <h1 class="hero-title"><?= escape($slide['title']) ?></h1>
                            <?php if (!empty($slide['subtitle'])): ?>
                                <p class="hero-subtitle"><?= escape($slide['subtitle']) ?></p>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?><?= escape($slide['button_link']) ?>" class="btn btn-primary btn-large">
                                <?= escape($slide['button_text']) ?>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Slider Controls -->
    <div class="slider-controls">
        <button class="slider-btn slider-prev" aria-label="السابق">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <button class="slider-btn slider-next" aria-label="التالي">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
    
    <!-- Slider Dots -->
    <div class="slider-dots">
        <?php foreach ($heroSlides as $index => $slide): ?>
            <button class="slider-dot <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>" aria-label="الشريحة <?= $index + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
</section>

<!-- Categories -->
<section class="section categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">تسوق حسب الفئة</h2>
            <p class="section-subtitle">اختر من بين مجموعة واسعة من التصنيفات</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                <a href="<?= BASE_URL ?>/shop?category=<?= $category['id'] ?>" class="category-card">
                    <div class="category-image">
                        <?php if ($category['image']): ?>
                            <img src="<?= UPLOAD_URL ?>/products/<?= escape($category['image']) ?>" alt="<?= escape($category['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="category-placeholder">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="currentColor">
                                    <path d="M12 36C12 36 16 32 24 32C32 32 36 36 36 36V40C36 41.1 35.1 42 34 42H14C12.9 42 12 41.1 12 40V36Z"/>
                                    <path d="M24 8C19.58 8 16 11.58 16 16C16 20.42 19.58 24 24 24C28.42 24 32 20.42 32 16C32 11.58 28.42 8 24 8Z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="category-name"><?= escape($category['name']) ?></h3>
                    <span class="category-link-text">تسوق الآن</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section featured-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">المنتجات المميزة</h2>
            <p class="section-subtitle">أفضل اختياراتنا لك</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <?php
                $finalPrice = getFinalPrice($product['regular_price'], $product['discount_price']);
                $hasDiscount = $product['discount_price'] && $product['discount_price'] < $product['regular_price'];
                $discountPercent = $hasDiscount ? calculateDiscount($product['regular_price'], $product['discount_price']) : 0;
                $isOutOfStock = $product['total_stock'] <= 0;
                $isLowStock = $product['total_stock'] > 0 && $product['total_stock'] < 5;
                ?>
                
                <div class="product-card">
                    <a href="<?= BASE_URL ?>/product/<?= escape($product['slug']) ?>" class="product-link">
                        <div class="product-image-wrapper">
                            <img src="<?= UPLOAD_URL ?>/products/<?= escape($product['main_image']) ?>" 
                                 alt="<?= escape($product['name']) ?>" 
                                 class="product-image"
                                 loading="lazy">
                            
                            <?php if ($hasDiscount): ?>
                                <span class="product-badge badge-discount">-<?= $discountPercent ?>%</span>
                            <?php endif; ?>
                            
                            <?php if ($isOutOfStock): ?>
                                <span class="product-badge badge-out-of-stock">نفذت الكمية</span>
                            <?php elseif ($isLowStock): ?>
                                <span class="product-badge badge-low-stock">الكمية محدودة</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?= escape($product['name']) ?></h3>
                            
                            <div class="product-colors">
                                <?php foreach (array_slice($product['colors'], 0, 4) as $color): ?>
                                    <span class="color-dot" style="background-color: <?= escape($color['color_hex']) ?>" title="<?= escape($color['color_name']) ?>"></span>
                                <?php endforeach; ?>
                                <?php if (count($product['colors']) > 4): ?>
                                    <span class="color-more">+<?= count($product['colors']) - 4 ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-price">
                                <?php if ($hasDiscount): ?>
                                    <span class="price-original"><?= formatPrice($product['regular_price']) ?></span>
                                    <span class="price-discount"><?= formatPrice($finalPrice) ?></span>
                                <?php else: ?>
                                    <span class="price-current"><?= formatPrice($finalPrice) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer">
            <a href="<?= BASE_URL ?>/shop" class="btn btn-outline">عرض جميع المنتجات</a>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="section new-arrivals-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">وصل حديثاً</h2>
            <p class="section-subtitle">أحدث الإضافات إلى مجموعتنا</p>
        </div>
        
        <div class="carousel-container">
            <div class="carousel" id="newArrivalsCarousel">
                <?php foreach ($newArrivals as $product): ?>
                    <?php
                    $finalPrice = getFinalPrice($product['regular_price'], $product['discount_price']);
                    $hasDiscount = $product['discount_price'] && $product['discount_price'] < $product['regular_price'];
                    $discountPercent = $hasDiscount ? calculateDiscount($product['regular_price'], $product['discount_price']) : 0;
                    $isOutOfStock = $product['total_stock'] <= 0;
                    ?>
                    
                    <div class="carousel-item">
                        <div class="product-card">
                            <a href="<?= BASE_URL ?>/product/<?= escape($product['slug']) ?>" class="product-link">
                                <div class="product-image-wrapper">
                                    <img src="<?= UPLOAD_URL ?>/products/<?= escape($product['main_image']) ?>" 
                                         alt="<?= escape($product['name']) ?>" 
                                         class="product-image"
                                         loading="lazy">
                                    
                                    <?php if ($hasDiscount): ?>
                                        <span class="product-badge badge-discount">-<?= $discountPercent ?>%</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($isOutOfStock): ?>
                                        <span class="product-badge badge-out-of-stock">نفذت الكمية</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <h3 class="product-name"><?= escape($product['name']) ?></h3>
                                    
                                    <div class="product-colors">
                                        <?php foreach (array_slice($product['colors'], 0, 4) as $color): ?>
                                            <span class="color-dot" style="background-color: <?= escape($color['color_hex']) ?>" title="<?= escape($color['color_name']) ?>"></span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="product-price">
                                        <?php if ($hasDiscount): ?>
                                            <span class="price-original"><?= formatPrice($product['regular_price']) ?></span>
                                            <span class="price-discount"><?= formatPrice($finalPrice) ?></span>
                                        <?php else: ?>
                                            <span class="price-current"><?= formatPrice($finalPrice) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button class="carousel-btn carousel-prev" aria-label="السابق">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button class="carousel-btn carousel-next" aria-label="التالي">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>
</section>

<!-- Features -->
<section class="section features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                        <path d="M6 18L24 6L42 18V38C42 39.1 41.1 40 40 40H8C6.9 40 6 39.1 6 38V18Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M18 40V24H30V40" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <h3 class="feature-title">شحن مجاني</h3>
                <p class="feature-text">للطلبات فوق <?= formatPrice(FREE_SHIPPING_THRESHOLD) ?></p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                        <path d="M24 44C35.0457 44 44 35.0457 44 24C44 12.9543 35.0457 4 24 4C12.9543 4 4 12.9543 4 24C4 35.0457 12.9543 44 24 44Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M16 24L22 30L34 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="feature-title">ضمان الجودة</h3>
                <p class="feature-text">منتجات أصلية 100%</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                        <path d="M38 8H10C7.79 8 6 9.79 6 12V36C6 38.21 7.79 40 10 40H38C40.21 40 42 38.21 42 36V12C42 9.79 40.21 8 38 8Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M32 4V12M16 4V12M6 20H42" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3 class="feature-title">دعم 24/7</h3>
                <p class="feature-text">خدمة عملاء متميزة</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                        <path d="M24 4L6 12V22C6 32 12 40.8 24 44C36 40.8 42 32 42 22V12L24 4Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M18 24L22 28L30 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="feature-title">دفع آمن</h3>
                <p class="feature-text">الدفع عند الاستلام</p>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
