<?php
$pageTitle = 'نتائج البحث';
require __DIR__ . '/../layouts/header.php';
$searchQuery = $_GET['q'] ?? '';
?>

<section class="section search-section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ol>
                <li><a href="<?= BASE_URL ?>/">الرئيسية</a></li>
                <li class="active">نتائج البحث</li>
            </ol>
        </nav>
        
        <h1 class="page-title">نتائج البحث عن: "<?= escape($searchQuery) ?>"</h1>
        <p class="search-count">تم العثور على <?= $totalProducts ?> منتج</p>
        
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                    <circle cx="60" cy="60" r="40" stroke="currentColor" stroke-width="3"/>
                    <path d="M95 95L75 75" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    <path d="M45 60H75M60 45V75" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <h2>لا توجد نتائج</h2>
                <p>جرب البحث بكلمات مختلفة</p>
                <a href="<?= BASE_URL ?>/shop" class="btn btn-primary">تصفح جميع المنتجات</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <?php
                    $finalPrice = getFinalPrice($product['regular_price'], $product['discount_price']);
                    $hasDiscount = $product['discount_price'] && $product['discount_price'] < $product['regular_price'];
                    $discountPercent = $hasDiscount ? calculateDiscount($product['regular_price'], $product['discount_price']) : 0;
                    $isOutOfStock = $product['total_stock'] <= 0;
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
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-name"><?= escape($product['name']) ?></h3>
                                
                                <div class="product-colors">
                                    <?php foreach (array_slice($product['colors'], 0, 4) as $color): ?>
                                        <span class="color-dot" style="background-color: <?= escape($color['color_hex']) ?>"></span>
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
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= BASE_URL ?>/search?q=<?= urlencode($searchQuery) ?>&page=<?= $page - 1 ?>" class="pagination-btn">السابق</a>
                    <?php endif; ?>
                    
                    <div class="pagination-numbers">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="pagination-number active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/search?q=<?= urlencode($searchQuery) ?>&page=<?= $i ?>" class="pagination-number"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= BASE_URL ?>/search?q=<?= urlencode($searchQuery) ?>&page=<?= $page + 1 ?>" class="pagination-btn">التالي</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
