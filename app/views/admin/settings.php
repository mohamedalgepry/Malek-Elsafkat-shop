<?php
$pageTitle = 'الإعدادات';
require __DIR__ . '/layouts/header.php';
?>

<h1 class="page-title">إعدادات الموقع</h1>

<div style="display: grid; gap: 25px;">
    <!-- Website Settings -->
    <div class="card">
        <div class="card-header"><h2>إعدادات الموقع</h2></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/settings/update">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="website">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">اسم الموقع</label>
                        <input type="text" name="site_name" class="form-control" value="<?= escape($settings['site_name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="site_email" class="form-control" value="<?= escape($settings['site_email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="site_phone" class="form-control" value="<?= escape($settings['site_phone'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="site_address" class="form-control" value="<?= escape($settings['site_address'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">العملة</label>
                        <select name="currency" class="form-control">
                            <option value="EGP" <?= ($settings['currency'] ?? '') === 'EGP' ? 'selected' : '' ?>>جنيه مصري (EGP)</option>
                            <option value="USD" <?= ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>دولار أمريكي (USD)</option>
                            <option value="SAR" <?= ($settings['currency'] ?? '') === 'SAR' ? 'selected' : '' ?>>ريال سعودي (SAR)</option>
                            <option value="AED" <?= ($settings['currency'] ?? '') === 'AED' ? 'selected' : '' ?>>درهم إماراتي (AED)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">نسبة الضريبة (%)</label>
                        <input type="number" name="tax_rate" class="form-control" value="<?= escape($settings['tax_rate'] ?? '0') ?>" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">رسوم الشحن</label>
                        <input type="number" name="shipping_fee" class="form-control" value="<?= escape($settings['shipping_fee'] ?? '0') ?>" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">حد الشحن المجاني</label>
                        <input type="number" name="free_shipping_threshold" class="form-control" value="<?= escape($settings['free_shipping_threshold'] ?? '500') ?>" step="0.01" min="0">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
            </form>
        </div>
    </div>
    
    <!-- Shipping Details -->
    <div class="card">
        <div class="card-header"><h2>تفاصيل الشحن</h2></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/settings/update">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="shipping">
                
                <div style="display: grid; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">عنوان تفاصيل الشحن</label>
                        <input type="text" name="shipping_title" class="form-control" value="<?= escape($settings['shipping_title'] ?? 'تفاصيل الشحن') ?>" placeholder="مثال: سياسة الشحن والتوصيل">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">تفاصيل الشحن الرئيسية</label>
                        <textarea name="shipping_details" class="form-control" rows="6" placeholder="اكتب تفاصيل الشحن هنا..."><?= escape($settings['shipping_details'] ?? '') ?></textarea>
                        <small style="color: var(--admin-text-muted); display: block; margin-top: 5px;">
                            يمكنك كتابة معلومات الشحن مثل: مدة التوصيل، المناطق المشمولة، رسوم الشحن، إلخ.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ملاحظات إضافية</label>
                        <textarea name="shipping_notes" class="form-control" rows="4" placeholder="ملاحظات إضافية حول الشحن..."><?= escape($settings['shipping_notes'] ?? '') ?></textarea>
                        <small style="color: var(--admin-text-muted); display: block; margin-top: 5px;">
                            ملاحظات أو شروط إضافية تظهر للعملاء
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">رقم خدمة العملاء للشحن</label>
                        <input type="text" name="shipping_contact" class="form-control" value="<?= escape($settings['shipping_contact'] ?? '') ?>" placeholder="رقم الهاتف أو البريد الإلكتروني">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">حفظ تفاصيل الشحن</button>
            </form>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="card">
        <div class="card-header"><h2>تغيير كلمة المرور</h2></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/settings/update">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="password">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">تغيير كلمة المرور</button>
            </form>
        </div>
    </div>
    
    <!-- Maintenance -->
    <div class="card">
        <div class="card-header"><h2>الصيانة</h2></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Cleanup Orders -->
                <div>
                    <h3 style="margin-bottom: 15px;">حذف الطلبات القديمة</h3>
                    <form method="POST" action="<?= BASE_URL ?>/admin/settings/update">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="cleanup">
                        
                        <div class="form-group">
                            <label class="form-label">حذف الطلبات الأقدم من:</label>
                            <select name="days" class="form-control">
                                <option value="30">30 يوم</option>
                                <option value="60">60 يوم</option>
                                <option value="90">90 يوم</option>
                                <option value="180">180 يوم</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-danger" data-confirm="هل أنت متأكد من حذف الطلبات القديمة؟">حذف الطلبات</button>
                    </form>
                </div>
                
                <!-- Database Backup -->
                <div>
                    <h3 style="margin-bottom: 15px;">نسخة احتياطية</h3>
                    <p style="color: var(--admin-text-muted); margin-bottom: 15px;">إنشاء نسخة احتياطية من قاعدة البيانات</p>
                    
                    <form method="POST" action="<?= BASE_URL ?>/admin/settings/update">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="backup">
                        
                        <button type="submit" class="btn btn-primary">تحميل النسخة الاحتياطية</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
