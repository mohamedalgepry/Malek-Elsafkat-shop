<?php
$pageTitle = 'إدارة الشحن والمحافظات';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1 class="page-title">إدارة الشحن والمحافظات</h1>
    <a href="<?= BASE_URL ?>/admin/shipping/add" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M10 5V15M5 10H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        إضافة محافظة جديدة
    </a>
</div>

<!-- Statistics -->
<?php if (isset($stats)): ?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">إجمالي المحافظات</div>
            <div class="stat-value"><?= $stats['total_governorates'] ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M22 11.08V12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2C14.76 2 17.24 3.04 19.07 4.72" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">محافظات نشطة</div>
            <div class="stat-value"><?= $stats['active_governorates'] ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2V22M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">متوسط تكلفة الشحن</div>
            <div class="stat-value"><?= number_format($stats['avg_cost'], 0) ?> ج.م</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M21 10H3M16 2V6M8 2V6M7.8 22H16.2C17.8802 22 18.7202 22 19.362 21.673C19.9265 21.3854 20.3854 20.9265 20.673 20.362C21 19.7202 21 18.8802 21 17.2V8.8C21 7.11984 21 6.27976 20.673 5.63803C20.3854 5.07354 19.9265 4.6146 19.362 4.32698C18.7202 4 17.8802 4 16.2 4H7.8C6.11984 4 5.27976 4 4.63803 4.32698C4.07354 4.6146 3.6146 5.07354 3.32698 5.63803C3 6.27976 3 7.11984 3 8.8V17.2C3 18.8802 3 19.7202 3.32698 20.362C3.6146 20.9265 4.07354 21.3854 4.63803 21.673C5.27976 22 6.11984 22 7.8 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">نطاق الأسعار</div>
            <div class="stat-value" style="font-size: 16px;"><?= number_format($stats['min_cost'], 0) ?> - <?= number_format($stats['max_cost'], 0) ?> ج.م</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Search -->
<div class="card">
    <div class="card-body">
        <form method="GET" class="filters-form">
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 15px;">
                <input type="text" name="search" placeholder="البحث عن محافظة..." class="form-control" value="<?= escape($_GET['search'] ?? '') ?>">
                <button type="submit" class="btn btn-primary">بحث</button>
            </div>
        </form>
    </div>
</div>

<!-- Governorates Table -->
<div class="card">
    <div class="card-body">
        <?php if (!empty($_GET['search'])): ?>
            <div style="padding: 15px; background: #e3f2fd; border-radius: 8px; margin-bottom: 20px;">
                <strong>🔍 نتائج البحث عن:</strong> "<?= escape($_GET['search']) ?>" 
                - تم العثور على <strong><?= count($governorates) ?></strong> محافظة
                <a href="<?= BASE_URL ?>/admin/shipping" style="margin-right: 15px; color: #1976d2;">✕ مسح البحث</a>
            </div>
        <?php endif; ?>
        
        <?php if (empty($governorates)): ?>
            <p class="text-muted">لا توجد محافظات</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المحافظة</th>
                            <th>الاسم بالإنجليزية</th>
                            <th>تكلفة الشحن</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($governorates as $gov): ?>
                            <tr>
                                <td><?= $gov['id'] ?></td>
                                <td><strong><?= escape($gov['name_ar']) ?></strong></td>
                                <td><?= escape($gov['name_en']) ?></td>
                                <td>
                                    <span style="font-weight: bold; color: var(--admin-primary); font-size: 16px;">
                                        <?= formatPrice($gov['shipping_cost']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($gov['is_active']): ?>
                                        <span class="badge badge-success">نشط</span>
                                    <?php else: ?>
                                        <span class="badge badge-error">غير نشط</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatDate($gov['created_at'], 'Y-m-d') ?></td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="<?= BASE_URL ?>/admin/shipping/edit/<?= $gov['id'] ?>" class="btn btn-sm btn-primary">تعديل</a>
                                        
                                        <form method="POST" action="<?= BASE_URL ?>/admin/shipping/toggle/<?= $gov['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <button type="submit" class="btn btn-sm <?= $gov['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                <?= $gov['is_active'] ? 'تعطيل' : 'تفعيل' ?>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="<?= BASE_URL ?>/admin/shipping/delete/<?= $gov['id'] ?>" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="هل أنت متأكد من حذف هذه المحافظة؟">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>



<?php require __DIR__ . '/../layouts/footer.php'; ?>
