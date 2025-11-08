<?php
$pageTitle = 'إضافة سلايد جديد';
require __DIR__ . '/../layouts/header.php';
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="admin-header">
    <h1>إضافة سلايد جديد</h1>
    <a href="<?= BASE_URL ?>/admin/sliders" class="btn btn-secondary">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M15 10H5M5 10L10 15M5 10L10 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        رجوع
    </a>
</div>

<div class="card">
    <form method="POST" action="<?= BASE_URL ?>/admin/sliders/store" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        
        <div class="form-grid">
            <div class="form-group">
                <label for="title" class="form-label">العنوان الرئيسي <span class="required">*</span></label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-control" 
                       value="<?= escape($formData['title'] ?? '') ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="subtitle" class="form-label">العنوان الفرعي</label>
                <input type="text" 
                       id="subtitle" 
                       name="subtitle" 
                       class="form-control" 
                       value="<?= escape($formData['subtitle'] ?? '') ?>">
            </div>
        </div>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="button_text" class="form-label">نص الزر</label>
                <input type="text" 
                       id="button_text" 
                       name="button_text" 
                       class="form-control" 
                       value="<?= escape($formData['button_text'] ?? 'تسوق الآن') ?>">
            </div>
            
            <div class="form-group">
                <label for="button_link" class="form-label">رابط الزر</label>
                <input type="text" 
                       id="button_link" 
                       name="button_link" 
                       class="form-control" 
                       value="<?= escape($formData['button_link'] ?? '/shop') ?>"
                       placeholder="/shop">
            </div>
        </div>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="display_order" class="form-label">ترتيب العرض</label>
                <input type="number" 
                       id="display_order" 
                       name="display_order" 
                       class="form-control" 
                       value="<?= escape($formData['display_order'] ?? 0) ?>"
                       min="0">
                <small class="form-hint">الأرقام الأصغر تظهر أولاً</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">الحالة</label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               <?= isset($formData['is_active']) || !isset($formData['title']) ? 'checked' : '' ?>>
                        <span>نشط (يظهر في الصفحة الرئيسية)</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="image" class="form-label">صورة السلايد <span class="required">*</span></label>
            <div class="file-upload">
                <input type="file" 
                       id="image" 
                       name="image" 
                       class="file-input" 
                       accept="image/*"
                       required
                       onchange="previewImage(this)">
                <label for="image" class="file-label">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>اختر صورة</span>
                </label>
                <div id="imagePreview" class="image-preview" style="display: none;">
                    <img src="" alt="معاينة">
                    <button type="button" class="remove-preview" onclick="removePreview()">×</button>
                </div>
            </div>
            <small class="form-hint">يقبل أي حجم صورة. سيتم تحسينها تلقائياً. الحد الأقصى: 50MB</small>
            <small class="form-hint" style="color: var(--success); margin-top: 5px; display: block;">
                ✓ سيتم تصغير الصورة تلقائياً إلى 1920×1080 للحصول على أفضل أداء
            </small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-large">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M16 6L8 14L4 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                حفظ السلايد
            </button>
            <a href="<?= BASE_URL ?>/admin/sliders" class="btn btn-secondary btn-large">إلغاء</a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const img = preview.querySelector('img');
    const label = document.querySelector('.file-label');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
            label.style.display = 'none';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

function removePreview() {
    const input = document.getElementById('image');
    const preview = document.getElementById('imagePreview');
    const label = document.querySelector('.file-label');
    
    input.value = '';
    preview.style.display = 'none';
    label.style.display = 'flex';
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
