<?php
$pageTitle = 'تعديل محافظة: ' . $governorate['name_ar'];
require __DIR__ . '/../layouts/header.php';

$formData = $_SESSION['form_data'] ?? $governorate;
unset($_SESSION['form_data']);
?>

<div class="page-header">
    <h1 class="page-title">تعديل محافظة: <?= escape($governorate['name_ar']) ?></h1>
    <a href="<?= BASE_URL ?>/admin/shipping" class="btn btn-outline">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M15 10H5M5 10L10 5M5 10L10 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        رجوع
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/shipping/update/<?= $governorate['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            
            <div class="form-grid">
                <!-- Arabic Name -->
                <div class="form-group">
                    <label for="name_ar" class="form-label required">اسم المحافظة بالعربية</label>
                    <input type="text" 
                           id="name_ar" 
                           name="name_ar" 
                           class="form-control" 
                           value="<?= escape($formData['name_ar']) ?>"
                           required>
                </div>
                
                <!-- English Name -->
                <div class="form-group">
                    <label for="name_en" class="form-label">اسم المحافظة بالإنجليزية</label>
                    <input type="text" 
                           id="name_en" 
                           name="name_en" 
                           class="form-control" 
                           value="<?= escape($formData['name_en']) ?>">
                </div>
                
                <!-- Shipping Cost -->
                <div class="form-group">
                    <label for="shipping_cost" class="form-label required">تكلفة الشحن (ج.م)</label>
                    <input type="number" 
                           id="shipping_cost" 
                           name="shipping_cost" 
                           class="form-control" 
                           value="<?= escape($formData['shipping_cost']) ?>"
                           step="0.01"
                           min="0"
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
                                   <?= $formData['is_active'] ? 'checked' : '' ?>>
                            <span>نشط (سيظهر للعملاء)</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Info Box -->
            <div style="margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <small style="color: #666;">تاريخ الإضافة</small>
                        <div style="font-weight: bold; margin-top: 5px;"><?= formatDate($governorate['created_at']) ?></div>
                    </div>
                    <div>
                        <small style="color: #666;">آخر تحديث</small>
                        <div style="font-weight: bold; margin-top: 5px;"><?= formatDate($governorate['updated_at']) ?></div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M16.667 5L7.5 14.167L3.333 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    حفظ التعديلات
                </button>
                <a href="<?= BASE_URL ?>/admin/shipping" class="btn btn-outline">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
