<?php
$pageTitle = 'إضافة محافظة جديدة';
require __DIR__ . '/../layouts/header.php';

$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="page-header">
    <h1 class="page-title">إضافة محافظة جديدة</h1>
    <a href="<?= BASE_URL ?>/admin/shipping" class="btn btn-outline">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M15 10H5M5 10L10 5M5 10L10 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        رجوع
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/shipping/store">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            
            <div class="form-grid">
                <!-- Arabic Name -->
                <div class="form-group">
                    <label for="name_ar" class="form-label required">اسم المحافظة بالعربية</label>
                    <input type="text" 
                           id="name_ar" 
                           name="name_ar" 
                           class="form-control" 
                           value="<?= escape($formData['name_ar'] ?? '') ?>"
                           placeholder="مثال: القاهرة"
                           required>
                </div>
                
                <!-- English Name -->
                <div class="form-group">
                    <label for="name_en" class="form-label">اسم المحافظة بالإنجليزية</label>
                    <input type="text" 
                           id="name_en" 
                           name="name_en" 
                           class="form-control" 
                           value="<?= escape($formData['name_en'] ?? '') ?>"
                           placeholder="Example: Cairo">
                </div>
                
                <!-- Shipping Cost -->
                <div class="form-group">
                    <label for="shipping_cost" class="form-label required">تكلفة الشحن (ج.م)</label>
                    <input type="number" 
                           id="shipping_cost" 
                           name="shipping_cost" 
                           class="form-control" 
                           value="<?= escape($formData['shipping_cost'] ?? '30') ?>"
                           step="0.01"
                           min="0"
                           placeholder="30.00"
                           required>
                    <small class="form-help">أدخل تكلفة الشحن للمحافظة بالجنيه المصري</small>
                </div>
                
                <!-- Active Status -->
                <div class="form-group">
                    <label class="form-label">الحالة</label>
                    <div class="checkbox-wrapper">
                        <label class="checkbox-label">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   <?= isset($formData['is_active']) || !isset($formData['name_ar']) ? 'checked' : '' ?>>
                            <span>نشط (سيظهر للعملاء)</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M16.667 5L7.5 14.167L3.333 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    حفظ المحافظة
                </button>
                <a href="<?= BASE_URL ?>/admin/shipping" class="btn btn-outline">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<div style="margin-top: 20px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
    <h3 style="color: #1976d2; margin-bottom: 10px;">💡 نصائح:</h3>
    <ul style="color: #1565c0; line-height: 1.8;">
        <li>تأكد من كتابة اسم المحافظة بشكل صحيح</li>
        <li>حدد تكلفة الشحن المناسبة حسب البعد الجغرافي</li>
        <li>المحافظات القريبة عادة تكون تكلفة شحنها أقل</li>
        <li>يمكنك تعديل التكلفة في أي وقت لاحقاً</li>
    </ul>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
