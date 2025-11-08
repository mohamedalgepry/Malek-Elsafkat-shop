<?php
$pageTitle = 'المتجر';
$pageDescription = 'تصفح جميع منتجاتنا من الأحذية الفاخرة';
require __DIR__ . '/../layouts/header.php';
?>

<section class="section shop-section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ol>
                <li><a href="<?= BASE_URL ?>/">الرئيسية</a></li>
                <li class="active">المتجر</li>
            </ol>
        </nav>
        
        <div class="shop-layout">
            <!-- Sidebar Filters -->
            <aside class="shop-sidebar">
                <div class="sidebar-header">
                    <h3>تصفية النتائج</h3>
                    <button class="btn-clear-filters" id="clearFilters">مسح الكل</button>
                </div>
                
                <form method="GET" action="<?= BASE_URL ?>/shop" id="filterForm">
                    <!-- Categories Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title">التصنيفات</h4>
                        <div class="filter-options">
                            <?php foreach ($categories as $category): ?>
                                <label class="filter-checkbox">
                                    <input type="radio" name="category" value="<?= $category['id'] ?>" 
                                           <?= (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'checked' : '' ?>>
                                    <span><?= escape($category['name']) ?> (<?= $category['product_count'] ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title">نطاق السعر</h4>
                        <div class="price-range">
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="السعر الأدنى (جنيه)" 
                                       value="<?= escape($_GET['min_price'] ?? '') ?>" 
                                       class="price-input" min="0">
                                <input type="number" name="max_price" placeholder="السعر الأقصى (جنيه)" 
                                       value="<?= escape($_GET['max_price'] ?? '') ?>" 
                                       class="price-input" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Colors Filter -->
                    <?php if (!empty($allColors)): ?>
                        <div class="filter-group">
                            <h4 class="filter-title">الألوان</h4>
                            <div class="filter-colors">
                                <?php foreach ($allColors as $color): ?>
                                    <label class="color-checkbox" title="<?= escape($color['color_name']) ?>">
                                        <input type="checkbox" name="colors[]" value="<?= escape($color['color_hex']) ?>"
                                               <?= (isset($_GET['colors']) && in_array($color['color_hex'], $_GET['colors'])) ? 'checked' : '' ?>>
                                        <span class="color-swatch" style="background-color: <?= escape($color['color_hex']) ?>"></span>
                                        <span class="color-name"><?= escape($color['color_name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary btn-block">تطبيق الفلاتر</button>
                </form>
            </aside>
            
            <!-- Products Area -->
            <div class="shop-content">
                <!-- Toolbar -->
                <div class="shop-toolbar">
                    <div class="toolbar-left">
                        <p class="results-count">عرض <?= count($products) ?> من <?= $totalProducts ?> منتج</p>
                    </div>
                    
                    <div class="toolbar-right">
                        <div class="sort-dropdown">
                            <select name="sort" id="sortSelect" onchange="applySort(this.value)">
                                <option value="newest" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'newest') ? 'selected' : '' ?>>الأحدث</option>
                                <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'price_asc') ? 'selected' : '' ?>>السعر: من الأقل للأعلى</option>
                                <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'price_desc') ? 'selected' : '' ?>>السعر: من الأعلى للأقل</option>
                                <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'name_asc') ? 'selected' : '' ?>>الاسم: أ - ي</option>
                            </select>
                        </div>
                        
                        <button class="mobile-filter-toggle" id="mobileFilterToggle">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M2 4H18M5 10H15M8 16H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            فلاتر
                        </button>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                            <circle cx="60" cy="60" r="50" stroke="currentColor" stroke-width="2" opacity="0.3"/>
                            <path d="M40 60H80M60 40V80" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <h3>لا توجد منتجات</h3>
                        <p>جرب تغيير الفلاتر أو البحث عن شيء آخر</p>
                        <a href="<?= BASE_URL ?>/shop" class="btn btn-primary">مسح الفلاتر</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
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
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $currentUrl = $_SERVER['REQUEST_URI'];
                            $urlParts = parse_url($currentUrl);
                            parse_str($urlParts['query'] ?? '', $queryParams);
                            ?>
                            
                            <?php if ($page > 1): ?>
                                <?php
                                $queryParams['page'] = $page - 1;
                                $prevUrl = $urlParts['path'] . '?' . http_build_query($queryParams);
                                ?>
                                <a href="<?= escape($prevUrl) ?>" class="pagination-btn">السابق</a>
                            <?php endif; ?>
                            
                            <div class="pagination-numbers">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="pagination-number active"><?= $i ?></span>
                                    <?php else: ?>
                                        <?php
                                        $queryParams['page'] = $i;
                                        $pageUrl = $urlParts['path'] . '?' . http_build_query($queryParams);
                                        ?>
                                        <a href="<?= escape($pageUrl) ?>" class="pagination-number"><?= $i ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            
                            <?php if ($page < $totalPages): ?>
                                <?php
                                $queryParams['page'] = $page + 1;
                                $nextUrl = $urlParts['path'] . '?' . http_build_query($queryParams);
                                ?>
                                <a href="<?= escape($nextUrl) ?>" class="pagination-btn">التالي</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function applySort(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

document.getElementById('clearFilters')?.addEventListener('click', function() {
    window.location.href = '<?= BASE_URL ?>/shop';
});

document.getElementById('mobileFilterToggle')?.addEventListener('click', function() {
    document.querySelector('.shop-sidebar').classList.toggle('active');
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
