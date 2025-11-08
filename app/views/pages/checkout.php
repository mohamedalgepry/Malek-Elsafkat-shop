<?php
$pageTitle = 'إتمام الطلب';
require __DIR__ . '/../layouts/header.php';
$formData = $_SESSION['form_data'] ?? [];
?>

<section class="section checkout-section">
    <div class="container">
        <h1 class="page-title">إتمام الطلب</h1>
        
        <div class="checkout-layout">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <form method="POST" action="<?= BASE_URL ?>/checkout/process" id="checkoutForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    
                    <!-- Customer Information -->
                    <div class="form-section">
                        <h2 class="form-section-title">معلومات العميل</h2>
                        
                        <div class="form-group">
                            <label for="customer_name" class="form-label">الاسم الكامل <span class="required">*</span></label>
                            <input type="text" 
                                   id="customer_name" 
                                   name="customer_name" 
                                   class="form-control" 
                                   value="<?= escape($formData['customer_name'] ?? '') ?>"
                                   required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_phone" class="form-label">رقم الهاتف <span class="required">*</span></label>
                                <input type="tel" 
                                       id="customer_phone" 
                                       name="customer_phone" 
                                       class="form-control" 
                                       placeholder="01xxxxxxxxx"
                                       value="<?= escape($formData['customer_phone'] ?? '') ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_email" class="form-label">البريد الإلكتروني (اختياري)</label>
                                <input type="email" 
                                       id="customer_email" 
                                       name="customer_email" 
                                       class="form-control"
                                       value="<?= escape($formData['customer_email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Information -->
                    <div class="form-section">
                        <h2 class="form-section-title">عنوان الشحن</h2>
                        
                        <div class="form-group">
                            <label for="shipping_address" class="form-label">العنوان <span class="required">*</span></label>
                            <textarea id="shipping_address" 
                                      name="shipping_address" 
                                      class="form-control" 
                                      rows="3" 
                                      required><?= escape($formData['shipping_address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="governorate_id" class="form-label">المحافظة <span class="required">*</span></label>
                                <select id="governorate_id" 
                                        name="governorate_id" 
                                        class="form-control"
                                        required>
                                    <option value="">اختر المحافظة...</option>
                                    <?php foreach ($governorates as $gov): ?>
                                        <option value="<?= $gov['id'] ?>" 
                                                data-cost="<?= $gov['shipping_cost'] ?>"
                                                <?= (isset($formData['governorate_id']) && $formData['governorate_id'] == $gov['id']) ? 'selected' : '' ?>>
                                            <?= escape($gov['name_ar']) ?> - <?= formatPrice($gov['shipping_cost']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-help">سيتم حساب تكلفة الشحن حسب المحافظة المختارة</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_city" class="form-label">المدينة/المنطقة <span class="required">*</span></label>
                                <input type="text" 
                                       id="shipping_city" 
                                       name="shipping_city" 
                                       class="form-control"
                                       placeholder="مثال: مدينة نصر"
                                       value="<?= escape($formData['shipping_city'] ?? '') ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_postal_code" class="form-label">الرمز البريدي</label>
                            <input type="text" 
                                   id="shipping_postal_code" 
                                   name="shipping_postal_code" 
                                   class="form-control"
                                   value="<?= escape($formData['shipping_postal_code'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <!-- Order Notes -->
                    <div class="form-section">
                        <h2 class="form-section-title">ملاحظات الطلب (اختياري)</h2>
                        
                        <div class="form-group">
                            <textarea id="order_notes" 
                                      name="order_notes" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="أي ملاحظات خاصة بالطلب..."><?= escape($formData['order_notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="form-section">
                        <h2 class="form-section-title">طريقة الدفع</h2>
                        
                        <div class="payment-method">
                            <div class="payment-option selected">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M2 10H22M7 15H7.01M11 15H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <div class="payment-info">
                                    <h3>الدفع عند الاستلام</h3>
                                    <p>ادفع نقداً عند استلام طلبك</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large btn-block">
                        تأكيد الطلب
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M16 6L8 14L4 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="checkout-summary">
                <h2 class="summary-title">ملخص الطلب</h2>
                
                <div class="summary-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <img src="<?= UPLOAD_URL ?>/products/<?= escape($item['image']) ?>" 
                                     alt="<?= escape($item['name']) ?>">
                                <span class="item-qty"><?= $item['quantity'] ?></span>
                            </div>
                            <div class="item-info">
                                <h4><?= escape($item['name']) ?></h4>
                                <p class="item-color">
                                    <span class="color-dot" style="background-color: <?= escape($item['color_hex']) ?>"></span>
                                    <?= escape($item['color_name']) ?>
                                </p>
                            </div>
                            <div class="item-price">
                                <?= formatPrice($item['subtotal']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>المجموع الفرعي:</span>
                        <span id="subtotal-amount"><?= formatPrice($subtotal) ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>الشحن:</span>
                        <span id="shipping-amount" style="font-weight: 600; color: var(--primary-color);">
                            اختر المحافظة
                        </span>
                    </div>
                    
                    <div class="summary-total">
                        <span>الإجمالي:</span>
                        <span class="total-amount" id="total-amount"><?= formatPrice($total) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Calculate shipping dynamically
document.addEventListener('DOMContentLoaded', function() {
    const governorateSelect = document.getElementById('governorate_id');
    const shippingAmount = document.getElementById('shipping-amount');
    const totalAmount = document.getElementById('total-amount');
    const subtotal = <?= $subtotal ?>;
    
    if (governorateSelect) {
        governorateSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const shippingCost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
            
            if (shippingCost > 0) {
                // Update shipping display
                shippingAmount.textContent = shippingCost.toFixed(2) + ' ج.م';
                shippingAmount.style.color = 'var(--primary-color)';
                
                // Update total
                const total = subtotal + shippingCost;
                totalAmount.textContent = total.toFixed(2) + ' ج.م';
            } else {
                shippingAmount.textContent = 'اختر المحافظة';
                shippingAmount.style.color = '#999';
                totalAmount.textContent = subtotal.toFixed(2) + ' ج.م';
            }
        });
        
        // Trigger change if already selected
        if (governorateSelect.value) {
            governorateSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>

<?php 
unset($_SESSION['form_data']);
require __DIR__ . '/../layouts/footer.php'; 
?>
