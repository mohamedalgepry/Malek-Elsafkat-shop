<?php
$pageTitle = escape($product['name']);
$pageDescription = escape(substr($product['description'] ?? '', 0, 160));
require __DIR__ . '/../layouts/header.php';

$finalPrice = getFinalPrice($product['regular_price'], $product['discount_price']);
$hasDiscount = $product['discount_price'] && $product['discount_price'] < $product['regular_price'];
$discountPercent = $hasDiscount ? calculateDiscount($product['regular_price'], $product['discount_price']) : 0;
?>

<section class="section product-section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ol>
                <li><a href="<?= BASE_URL ?>/">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M2 6L8 2L14 6V14H10V10H6V14H2V6Z" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    الرئيسية
                </a></li>
                <li><a href="<?= BASE_URL ?>/shop">المتجر</a></li>
                <li><a href="<?= BASE_URL ?>/shop?category=<?= $product['category_id'] ?>"><?= escape($product['category_name']) ?></a></li>
                <li class="active"><?= escape($product['name']) ?></li>
            </ol>
        </nav>
        
        <div class="product-layout">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="main-image-container">
                    <div class="main-image">
                        <img src="<?= UPLOAD_URL ?>/products/<?= escape($product['main_image']) ?>" 
                             alt="<?= escape($product['name']) ?>" 
                             id="mainImage">
                        
                        <?php if ($hasDiscount): ?>
                            <span class="product-badge badge-discount">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 2L9.5 6H14L10.5 9L12 13L8 10L4 13L5.5 9L2 6H6.5L8 2Z" fill="currentColor"/>
                                </svg>
                                خصم <?= $discountPercent ?>%
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($product['is_featured']): ?>
                            <span class="product-badge badge-featured">مميز</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="thumbnail-gallery">
                    <div class="thumbnail active" data-image="<?= UPLOAD_URL ?>/products/<?= escape($product['main_image']) ?>">
                        <img src="<?= UPLOAD_URL ?>/products/<?= escape($product['main_image']) ?>" alt="صورة 1">
                        <div class="thumbnail-overlay"></div>
                    </div>
                    
                    <?php foreach ($product['images'] as $index => $image): ?>
                        <div class="thumbnail" data-image="<?= UPLOAD_URL ?>/products/<?= escape($image['image_path']) ?>">
                            <img src="<?= UPLOAD_URL ?>/products/<?= escape($image['image_path']) ?>" alt="صورة <?= $index + 2 ?>">
                            <div class="thumbnail-overlay"></div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (!empty($product['videos'])): ?>
                        <?php $video = $product['videos'][0]; ?>
                        <div class="thumbnail thumbnail-video" data-video="<?= $video['id'] ?>" data-type="<?= $video['video_type'] ?>">
                            <div class="video-thumbnail-icon">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                                    <circle cx="16" cy="16" r="14" fill="rgba(212, 175, 55, 0.2)" stroke="var(--primary)" stroke-width="2"/>
                                    <path d="M12 10L22 16L12 22V10Z" fill="var(--primary)"/>
                                </svg>
                            </div>
                            <span style="position: absolute; bottom: 5px; left: 50%; transform: translateX(-50%); font-size: 11px; font-weight: 600; color: white; background: rgba(0,0,0,0.7); padding: 2px 6px; border-radius: 3px;">فيديو</span>
                            <div class="thumbnail-overlay"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Details -->
            <div class="product-details">
                <div class="product-header">
                    <div class="product-meta">
                        <span class="product-category">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <rect x="2" y="2" width="5" height="5" stroke="currentColor" stroke-width="1.5"/>
                                <rect x="9" y="2" width="5" height="5" stroke="currentColor" stroke-width="1.5"/>
                                <rect x="2" y="9" width="5" height="5" stroke="currentColor" stroke-width="1.5"/>
                                <rect x="9" y="9" width="5" height="5" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                            <a href="<?= BASE_URL ?>/shop?category=<?= $product['category_id'] ?>"><?= escape($product['category_name']) ?></a>
                        </span>
                        <span class="product-sku">كود المنتج: <?= escape($product['sku'] ?? 'N/A') ?></span>
                    </div>
                    
                    <h1 class="product-title"><?= escape($product['name']) ?></h1>
                </div>
                
                <div class="product-price-box">
                    <?php if ($hasDiscount): ?>
                        <div class="price-discount-wrapper">
                            <div class="price-main">
                                <span class="price-current-large"><?= formatPrice($finalPrice) ?></span>
                                <span class="price-original-large"><?= formatPrice($product['regular_price']) ?></span>
                            </div>
                            <div class="savings-badge">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M9 1L11 7H17L12 11L14 17L9 13L4 17L6 11L1 7H7L9 1Z" fill="currentColor"/>
                                </svg>
                                وفر <?= formatPrice($product['regular_price'] - $finalPrice) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="price-current-large"><?= formatPrice($finalPrice) ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if ($product['description']): ?>
                    <div class="product-description">
                        <p><?= nl2br(escape($product['description'])) ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= BASE_URL ?>/cart/add" id="addToCartForm" class="product-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <!-- Color Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="9" cy="9" r="4" fill="currentColor"/>
                            </svg>
                            اختر اللون:
                        </label>
                        <div class="color-options" id="colorOptions">
                            <?php foreach ($product['colors'] as $color): ?>
                                <label class="color-option <?= $color['total_stock'] <= 0 ? 'disabled' : '' ?>">
                                    <input type="radio" 
                                           name="color_id" 
                                           value="<?= $color['id'] ?>" 
                                           data-color-name="<?= escape($color['color_name']) ?>"
                                           data-sizes='<?= json_encode($color['sizes']) ?>'
                                           <?= $color['total_stock'] <= 0 ? 'disabled' : '' ?>
                                           required>
                                    <span class="color-box" style="background-color: <?= escape($color['color_hex']) ?>">
                                        <span class="color-checkmark">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M3 8L6 11L13 4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </span>
                                    <span class="color-info">
                                        <span class="color-label"><?= escape($color['color_name']) ?></span>
                                        <?php if ($color['total_stock'] <= 0): ?>
                                            <span class="stock-status out-of-stock">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                    <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M4 4L10 10M10 4L4 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                نفذت الكمية
                                            </span>
                                        <?php elseif ($color['total_stock'] < 5): ?>
                                            <span class="stock-status low-stock">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                    <path d="M7 2L8 5L11 6L8 8L7 12L6 8L3 6L6 5L7 2Z" fill="currentColor"/>
                                                </svg>
                                                متبقي <?= $color['total_stock'] ?> فقط!
                                            </span>
                                        <?php else: ?>
                                            <span class="stock-status in-stock">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                    <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M4 7L6 9L10 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                متوفر (<?= $color['total_stock'] ?>)
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Size Selection -->
                    <div class="form-group" id="sizeGroup" style="display: none;">
                        <label class="form-label">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <rect x="2" y="2" width="14" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M6 6H12M6 10H12M6 14H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            اختر المقاس:
                        </label>
                        <div class="size-options" id="sizeOptions">
                            <!-- Sizes will be loaded dynamically -->
                        </div>
                    </div>
                    
                    <!-- Quantity -->
                    <div class="form-group">
                        <label class="form-label">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <rect x="2" y="2" width="14" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M6 9H12M9 6V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            الكمية:
                        </label>
                        <div class="quantity-selector">
                            <button type="button" class="qty-btn qty-minus" disabled>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 8H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <input type="number" name="quantity" value="1" min="1" max="1" class="qty-input" id="quantityInput" readonly>
                            <button type="button" class="qty-btn qty-plus" disabled>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 4V12M4 8H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <p class="quantity-note" id="quantityNote">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 4V8M8 11V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            اختر لوناً أولاً
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="product-actions">
                        <button type="button" class="btn btn-primary btn-large btn-add-cart" id="addToCartBtn" disabled>
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                                <path d="M2 2H4L6.4 14.4C6.6 15.4 7.4 16 8.4 16H17.6C18.6 16 19.4 15.4 19.6 14.4L21 6H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="9" cy="20" r="1" fill="currentColor"/>
                                <circle cx="17" cy="20" r="1" fill="currentColor"/>
                            </svg>
                            <span>أضف إلى السلة</span>
                        </button>
                        <button type="button" class="btn btn-success btn-large btn-buy-now" id="buyNowBtn" disabled>
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                                <path d="M20 10L12 2L4 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 2V15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M2 15H22V20H2V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>اشتري الآن</span>
                        </button>
                    </div>
                </form>
                
                <!-- Product Info Tabs -->
                <div class="product-tabs">
                    <div class="tab-item">
                        <button type="button" class="tab-header">
                            <span>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M2 10L8 6V14L2 10Z" fill="currentColor"/>
                                    <rect x="8" y="4" width="10" height="12" rx="1" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M18 8H20M18 12H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                الشحن والإرجاع
                            </span>
                            <svg class="tab-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div class="tab-content">
                            <ul class="info-list">
                                <li>
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M6 9L8 11L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    شحن مجاني للطلبات فوق <?= formatPrice(FREE_SHIPPING_THRESHOLD) ?>
                                </li>
                                <li>
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M6 9L8 11L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    التوصيل خلال 3-5 أيام عمل
                                </li>
                                <li>
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M6 9L8 11L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    إمكانية الإرجاع خلال 14 يوم
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="tab-item">
                        <button type="button" class="tab-header">
                            <span>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M10 2L12 8L18 10L12 12L10 18L8 12L2 10L8 8L10 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                </svg>
                                العناية بالمنتج
                            </span>
                            <svg class="tab-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div class="tab-content">
                            <ul class="info-list">
                                <li>
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M6 9L8 11L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    نظف بقطعة قماش ناعمة ورطبة
                                </li>
                                <li>
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M6 9L8 11L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    تجنب التعرض المباشر للماء
                                </li>
                                <li>
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M6 9L8 11L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    احفظ في مكان جاف وبارد
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="related-products">
                <div class="section-header">
                    <h2 class="section-title">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                            <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                            <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                            <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        منتجات ذات صلة
                    </h2>
                    <p class="section-subtitle">منتجات قد تعجبك من نفس الفئة</p>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <?php
                        $relatedFinalPrice = getFinalPrice($relatedProduct['regular_price'], $relatedProduct['discount_price']);
                        $relatedHasDiscount = $relatedProduct['discount_price'] && $relatedProduct['discount_price'] < $relatedProduct['regular_price'];
                        $relatedDiscountPercent = $relatedHasDiscount ? calculateDiscount($relatedProduct['regular_price'], $relatedProduct['discount_price']) : 0;
                        ?>
                        
                        <div class="product-card">
                            <a href="<?= BASE_URL ?>/product/<?= escape($relatedProduct['slug']) ?>" class="product-link">
                                <div class="product-image-wrapper">
                                    <img src="<?= UPLOAD_URL ?>/products/<?= escape($relatedProduct['main_image']) ?>" 
                                         alt="<?= escape($relatedProduct['name']) ?>" 
                                         class="product-image"
                                         loading="lazy">
                                    
                                    <?php if ($relatedHasDiscount): ?>
                                        <span class="product-badge badge-discount">-<?= $relatedDiscountPercent ?>%</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <h3 class="product-name"><?= escape($relatedProduct['name']) ?></h3>
                                    
                                    <div class="product-colors">
                                        <?php foreach (array_slice($relatedProduct['colors'], 0, 4) as $color): ?>
                                            <span class="color-dot" style="background-color: <?= escape($color['color_hex']) ?>"></span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="product-price">
                                        <?php if ($relatedHasDiscount): ?>
                                            <span class="price-original"><?= formatPrice($relatedProduct['regular_price']) ?></span>
                                            <span class="price-discount"><?= formatPrice($relatedFinalPrice) ?></span>
                                        <?php else: ?>
                                            <span class="price-current"><?= formatPrice($relatedFinalPrice) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Product page functionality
document.addEventListener('DOMContentLoaded', function() {
    const colorOptions = document.querySelectorAll('input[name="color_id"]');
    const quantityInput = document.getElementById('quantityInput');
    const quantityNote = document.getElementById('quantityNote');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const qtyMinus = document.querySelector('.qty-minus');
    const qtyPlus = document.querySelector('.qty-plus');
    
    const buyNowBtn = document.getElementById('buyNowBtn');
    const form = document.getElementById('addToCartForm');
    
    // Update cart badge function
    function updateCartBadge(count) {
        // Update desktop cart badge
        const cartBtn = document.querySelector('.cart-btn');
        let cartBadge = cartBtn.querySelector('.cart-badge');
        
        if (count > 0) {
            if (!cartBadge) {
                cartBadge = document.createElement('span');
                cartBadge.className = 'cart-badge';
                cartBtn.appendChild(cartBadge);
            }
            cartBadge.textContent = count;
            cartBadge.style.display = 'flex';
            
            // Add pulse animation
            cartBadge.classList.add('cart-badge-pulse');
            setTimeout(() => {
                cartBadge.classList.remove('cart-badge-pulse');
            }, 600);
        } else if (cartBadge) {
            cartBadge.remove();
        }
        
        // Update mobile menu cart count
        const mobileCartLink = document.querySelector('.mobile-nav-link[href*="/cart"] span');
        if (mobileCartLink) {
            if (count > 0) {
                mobileCartLink.textContent = count;
                mobileCartLink.style.display = 'inline-block';
            } else {
                mobileCartLink.style.display = 'none';
            }
        }
    }
    
    const sizeGroup = document.getElementById('sizeGroup');
    const sizeOptions = document.getElementById('sizeOptions');
    let currentSizes = [];
    
    // Handle color selection
    colorOptions.forEach(option => {
        option.addEventListener('change', function() {
            const colorName = this.dataset.colorName;
            currentSizes = JSON.parse(this.dataset.sizes || '[]');
            
            // Show size selection
            if (currentSizes.length > 0) {
                sizeGroup.style.display = 'block';
                loadSizes(currentSizes);
                
                // Disable buttons until size is selected
                addToCartBtn.disabled = true;
                buyNowBtn.disabled = true;
                quantityNote.textContent = 'اختر المقاس أولاً';
                quantityNote.className = 'quantity-note';
            } else {
                // No sizes, hide size selection
                sizeGroup.style.display = 'none';
                addToCartBtn.disabled = false;
                buyNowBtn.disabled = false;
            }
            
            // Reset quantity
            quantityInput.value = 1;
            quantityInput.max = 1;
            qtyMinus.disabled = true;
            qtyPlus.disabled = true;
        });
    });
    
    // Load sizes function
    function loadSizes(sizes) {
        sizeOptions.innerHTML = '';
        
        sizes.forEach(size => {
            const sizeLabel = document.createElement('label');
            sizeLabel.className = `size-option ${size.stock_quantity <= 0 ? 'disabled' : ''}`;
            
            sizeLabel.innerHTML = `
                <input type="radio" 
                       name="size_id" 
                       value="${size.id}" 
                       data-stock="${size.stock_quantity}"
                       data-size-name="${size.size_name}"
                       ${size.stock_quantity <= 0 ? 'disabled' : ''}
                       required>
                <span class="size-box">
                    <span class="size-label">${size.size_name}</span>
                    ${size.stock_quantity <= 0 ? 
                        '<span class="size-stock out">نفذ</span>' : 
                        size.stock_quantity < 5 ?
                            `<span class="size-stock low">${size.stock_quantity}</span>` :
                            `<span class="size-stock">${size.stock_quantity}</span>`
                    }
                </span>
            `;
            
            sizeOptions.appendChild(sizeLabel);
        });
        
        // Handle size selection
        const sizeInputs = sizeOptions.querySelectorAll('input[name="size_id"]');
        sizeInputs.forEach(input => {
            input.addEventListener('change', function() {
                const stock = parseInt(this.dataset.stock);
                
                quantityInput.max = stock;
                quantityInput.value = 1;
                
                qtyMinus.disabled = false;
                qtyPlus.disabled = false;
                addToCartBtn.disabled = false;
                buyNowBtn.disabled = false;
                
                if (stock < 5) {
                    quantityNote.textContent = `متبقي ${stock} فقط!`;
                    quantityNote.className = 'quantity-note warning';
                } else {
                    quantityNote.textContent = `متوفر ${stock} قطعة`;
                    quantityNote.className = 'quantity-note';
                }
            });
        });
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `product-notification ${type}`;
        notification.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="2"/>
                <path d="M10 6V10M10 14V14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Check if color and size are selected
    function isColorSelected() {
        const selectedColor = document.querySelector('input[name="color_id"]:checked');
        if (!selectedColor) {
            showNotification('⚠️ من فضلك اختر اللون أولاً', 'warning');
            const colorOptions = document.getElementById('colorOptions');
            colorOptions.scrollIntoView({ behavior: 'smooth', block: 'center' });
            colorOptions.classList.add('shake');
            setTimeout(() => colorOptions.classList.remove('shake'), 500);
            return false;
        }
        
        // Check if size selection is visible and required
        if (sizeGroup.style.display !== 'none') {
            const selectedSize = document.querySelector('input[name="size_id"]:checked');
            if (!selectedSize) {
                showNotification('⚠️ من فضلك اختر المقاس أولاً', 'warning');
                sizeOptions.scrollIntoView({ behavior: 'smooth', block: 'center' });
                sizeOptions.classList.add('shake');
                setTimeout(() => sizeOptions.classList.remove('shake'), 500);
                return false;
            }
        }
        
        return true;
    }
    
    // Add to cart button
    addToCartBtn.addEventListener('click', function() {
        if (!isColorSelected()) return;
        
        // Disable button to prevent double clicks
        addToCartBtn.disabled = true;
        
        const formData = new FormData(form);
        
        // Use XMLHttpRequest for better compatibility
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= BASE_URL ?>/cart/add', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            addToCartBtn.disabled = false;
            
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        showNotification('✅ تم إضافة المنتج إلى السلة بنجاح', 'success');
                        // Update cart count in header
                        updateCartBadge(data.cart_count);
                    } else {
                        showNotification('❌ ' + (data.message || 'حدث خطأ'), 'error');
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    showNotification('❌ حدث خطأ في معالجة البيانات', 'error');
                }
            } else {
                showNotification('❌ حدث خطأ في الاتصال', 'error');
            }
        };
        
        xhr.onerror = function() {
            addToCartBtn.disabled = false;
            console.error('Network error');
            showNotification('❌ حدث خطأ في الاتصال', 'error');
        };
        
        xhr.send(formData);
    });
    
    // Buy now button
    buyNowBtn.addEventListener('click', function() {
        if (!isColorSelected()) return;
        
        // Disable button to prevent double clicks
        buyNowBtn.disabled = true;
        
        const formData = new FormData(form);
        
        // Use XMLHttpRequest for better compatibility
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= BASE_URL ?>/cart/add', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        // Redirect to checkout
                        window.location.href = '<?= BASE_URL ?>/checkout';
                    } else {
                        buyNowBtn.disabled = false;
                        showNotification('❌ ' + (data.message || 'حدث خطأ'), 'error');
                    }
                } catch (e) {
                    buyNowBtn.disabled = false;
                    console.error('Parse error:', e);
                    showNotification('❌ حدث خطأ في معالجة البيانات', 'error');
                }
            } else {
                buyNowBtn.disabled = false;
                showNotification('❌ حدث خطأ في الاتصال', 'error');
            }
        };
        
        xhr.onerror = function() {
            buyNowBtn.disabled = false;
            console.error('Network error');
            showNotification('❌ حدث خطأ في الاتصال', 'error');
        };
        
        xhr.send(formData);
    });
    
    // Quantity controls
    qtyMinus.addEventListener('click', function() {
        const current = parseInt(quantityInput.value);
        if (current > 1) {
            quantityInput.value = current - 1;
        }
    });
    
    qtyPlus.addEventListener('click', function() {
        const current = parseInt(quantityInput.value);
        const max = parseInt(quantityInput.max);
        if (current < max) {
            quantityInput.value = current + 1;
        }
    });
    
    // Thumbnail gallery
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainImage');
    const mainImageContainer = document.querySelector('.main-image');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Check if it's a video thumbnail
            if (this.classList.contains('thumbnail-video')) {
                const videoId = this.dataset.video;
                const videoType = this.dataset.type;
                
                // Replace main image with video
                if (videoType === 'youtube') {
                    const youtubeId = '<?= !empty($product['videos']) ? getYoutubeId($product['videos'][0]['video_path']) : '' ?>';
                    mainImageContainer.innerHTML = `
                        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px;">
                            <iframe src="https://www.youtube.com/embed/${youtubeId}?autoplay=1" 
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 12px;" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                            </iframe>
                        </div>
                    `;
                } else {
                    const videoPath = '<?= !empty($product['videos']) ? UPLOAD_URL . '/videos/' . escape($product['videos'][0]['video_path']) : '' ?>';
                    mainImageContainer.innerHTML = `
                        <video controls autoplay style="width: 100%; border-radius: 12px; max-height: 600px;">
                            <source src="${videoPath}" type="video/mp4">
                            متصفحك لا يدعم عرض الفيديو
                        </video>
                    `;
                }
            } else {
                // Restore image if video was playing
                if (!mainImage) {
                    mainImageContainer.innerHTML = `
                        <img src="${this.dataset.image}" alt="<?= escape($product['name']) ?>" id="mainImage">
                        <?php if ($hasDiscount): ?>
                            <span class="product-badge badge-discount">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 2L9.5 6H14L10.5 9L12 13L8 10L4 13L5.5 9L2 6H6.5L8 2Z" fill="currentColor"/>
                                </svg>
                                خصم <?= $discountPercent ?>%
                            </span>
                        <?php endif; ?>
                        <?php if ($product['is_featured']): ?>
                            <span class="product-badge badge-featured">مميز</span>
                        <?php endif; ?>
                    `;
                } else {
                    mainImage.src = this.dataset.image;
                }
            }
        });
    });
    
    // Product tabs
    const tabHeaders = document.querySelectorAll('.tab-header');
    tabHeaders.forEach(header => {
        header.addEventListener('click', function() {
            this.parentElement.classList.toggle('active');
        });
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
