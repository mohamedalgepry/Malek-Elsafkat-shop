<?php
$pageTitle = 'سلة التسوق';
require __DIR__ . '/../layouts/header.php';
?>

<section class="section cart-section">
    <div class="container">
        <h1 class="page-title">سلة التسوق</h1>
        
        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                    <path d="M45 10L35 30H15C10 30 5 35 5 40V90C5 95 10 100 15 100H105C110 100 115 95 115 90V40C115 35 110 30 105 30H85L75 10H45Z" stroke="currentColor" stroke-width="3"/>
                    <circle cx="60" cy="65" r="20" stroke="currentColor" stroke-width="3"/>
                    <path d="M40 65L55 80L80 55" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h2>سلة التسوق فارغة</h2>
                <p>لم تقم بإضافة أي منتجات بعد</p>
                <a href="<?= BASE_URL ?>/shop" class="btn btn-primary">تصفح المنتجات</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($cartItems as $key => $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="<?= UPLOAD_URL ?>/products/<?= escape($item['image']) ?>" 
                                     alt="<?= escape($item['name']) ?>">
                            </div>
                            
                            <div class="item-details">
                                <h3 class="item-name">
                                    <a href="<?= BASE_URL ?>/product/<?= escape($item['product']['slug']) ?>">
                                        <?= escape($item['name']) ?>
                                    </a>
                                </h3>
                                <div class="item-color">
                                    <span class="color-dot" style="background-color: <?= escape($item['color_hex']) ?>"></span>
                                    <span><?= escape($item['color_name']) ?></span>
                                </div>
                                <?php if (!empty($item['size_name'])): ?>
                                    <div class="item-size" style="margin-top: 5px; font-size: 14px; color: var(--text-secondary);">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                                            <rect x="1" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        المقاس: <strong style="color: var(--primary);"><?= escape($item['size_name']) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <div class="item-price-mobile"><?= formatPrice($item['price']) ?></div>
                            </div>
                            
                            <div class="item-price">
                                <?= formatPrice($item['price']) ?>
                            </div>
                            
                            <div class="item-quantity">
                                <form method="POST" action="<?= BASE_URL ?>/cart/update" class="quantity-form">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="cart_key" value="<?= escape($key) ?>">
                                    
                                    <div class="quantity-selector">
                                        <button type="button" class="qty-btn qty-minus" data-key="<?= escape($key) ?>">-</button>
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                               min="1" class="qty-input" data-key="<?= escape($key) ?>" readonly>
                                        <button type="button" class="qty-btn qty-plus" data-key="<?= escape($key) ?>">+</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="item-subtotal">
                                <?= formatPrice($item['subtotal']) ?>
                            </div>
                            
                            <div class="item-remove">
                                <form method="POST" action="<?= BASE_URL ?>/cart/remove" class="remove-form">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="cart_key" value="<?= escape($key) ?>">
                                    <button type="submit" class="btn-remove" title="حذف">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3 class="summary-title">ملخص الطلب</h3>
                    
                    <div class="summary-row">
                        <span>المجموع الفرعي:</span>
                        <span><?= formatPrice($subtotal) ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>الشحن:</span>
                        <span style="color: #666; font-size: 14px;">
                            يتم حسابه عند الطلب
                        </span>
                    </div>
                    
                    <div class="shipping-notice" style="background: #e3f2fd; padding: 12px; border-radius: 8px; margin: 15px 0; font-size: 14px; color: #1976d2;">
                        💡 <strong>ملاحظة:</strong> سيتم حساب تكلفة الشحن حسب المحافظة عند إتمام الطلب
                    </div>
                    
                    <div class="summary-total">
                        <span>المجموع الفرعي:</span>
                        <span class="total-amount"><?= formatPrice($subtotal) ?></span>
                    </div>
                    
                    <a href="<?= BASE_URL ?>/checkout" class="btn btn-primary btn-block btn-large">
                        إتمام الطلب
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/shop" class="btn btn-outline btn-block">متابعة التسوق</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const key = this.dataset.key;
            const input = document.querySelector(`input[data-key="${key}"]`);
            const current = parseInt(input.value);
            
            if (current > 1) {
                updateQuantity(key, current - 1);
            }
        });
    });
    
    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const key = this.dataset.key;
            const input = document.querySelector(`input[data-key="${key}"]`);
            const current = parseInt(input.value);
            
            updateQuantity(key, current + 1);
        });
    });
    
    function updateQuantity(key, quantity) {
        const formData = new FormData();
        formData.append('csrf_token', '<?= generateCsrfToken() ?>');
        formData.append('cart_key', key);
        formData.append('quantity', quantity);
        
        fetch('<?= BASE_URL ?>/cart/update', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
