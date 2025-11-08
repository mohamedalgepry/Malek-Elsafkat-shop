<?php
$pageTitle = 'إدارة السلايدر';
require __DIR__ . '/../layouts/header.php';
?>

<div class="admin-header">
    <h1>إدارة السلايدر</h1>
    <a href="<?= BASE_URL ?>/admin/sliders/create" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        إضافة سلايد جديد
    </a>
</div>

<?php if (empty($sliders)): ?>
    <div class="empty-state">
        <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
            <rect x="20" y="30" width="80" height="60" rx="4" stroke="currentColor" stroke-width="3"/>
            <path d="M30 50L50 70L70 50L90 70" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <h2>لا توجد سلايدات</h2>
        <p>ابدأ بإضافة سلايد جديد للصفحة الرئيسية</p>
        <a href="<?= BASE_URL ?>/admin/sliders/create" class="btn btn-primary">إضافة سلايد</a>
    </div>
<?php else: ?>
    <!-- Desktop Table View -->
    <div class="table-container desktop-view">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="80">الترتيب</th>
                    <th width="120">الصورة</th>
                    <th>العنوان</th>
                    <th>العنوان الفرعي</th>
                    <th>نص الزر</th>
                    <th width="100">الحالة</th>
                    <th width="200">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sliders as $slider): ?>
                    <tr>
                        <td class="text-center">
                            <span class="badge badge-secondary"><?= $slider['display_order'] ?></span>
                        </td>
                        <td>
                            <?php
                            // استخدام الصورة المصغرة إذا كانت موجودة
                            $imagePath = $slider['image'];
                            $thumbPath = str_replace('/uploads/sliders/', '/uploads/sliders/thumbs/', $imagePath);
                            $thumbFullPath = __DIR__ . '/../../../public' . $thumbPath;
                            $displayImage = file_exists($thumbFullPath) ? $thumbPath : $imagePath;
                            ?>
                            <img src="<?= BASE_URL ?><?= escape($displayImage) ?>" 
                                 alt="<?= escape($slider['title']) ?>"
                                 class="table-image"
                                 style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px;">
                        </td>
                        <td>
                            <strong><?= escape($slider['title']) ?></strong>
                        </td>
                        <td><?= escape($slider['subtitle'] ?? '-') ?></td>
                        <td><?= escape($slider['button_text']) ?></td>
                        <td class="text-center">
                            <form method="POST" action="<?= BASE_URL ?>/admin/sliders/toggle-active/<?= $slider['id'] ?>" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <button type="submit" class="badge-btn <?= $slider['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $slider['is_active'] ? 'نشط' : 'معطل' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= BASE_URL ?>/admin/sliders/edit/<?= $slider['id'] ?>" 
                                   class="btn btn-sm btn-info" title="تعديل">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M11.333 2A2.827 2.827 0 0 1 14 4.667L5.333 13.333H2v-3.333L10.667 2h.666z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    تعديل
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/admin/sliders/delete/<?= $slider['id'] ?>" 
                                      style="display: inline;"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا السلايد؟')">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 0 1 1.334-1.334h2.666a1.333 1.333 0 0 1 1.334 1.334V4m2 0v9.333a1.333 1.333 0 0 1-1.334 1.334H4.667a1.333 1.333 0 0 1-1.334-1.334V4h9.334z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Mobile Cards View -->
    <div class="cards-container mobile-view">
        <?php foreach ($sliders as $slider): ?>
            <?php
            // استخدام الصورة المصغرة إذا كانت موجودة
            $imagePath = $slider['image'];
            $thumbPath = str_replace('/uploads/sliders/', '/uploads/sliders/thumbs/', $imagePath);
            $thumbFullPath = __DIR__ . '/../../../public' . $thumbPath;
            $displayImage = file_exists($thumbFullPath) ? $thumbPath : $imagePath;
            ?>
            <div class="slider-card">
                <div class="slider-card-header">
                    <img src="<?= BASE_URL ?><?= escape($displayImage) ?>" 
                         alt="<?= escape($slider['title']) ?>"
                         class="slider-card-image">
                    <div class="slider-card-badges">
                        <span class="badge badge-secondary"><?= $slider['display_order'] ?></span>
                        <form method="POST" action="<?= BASE_URL ?>/admin/sliders/toggle-active/<?= $slider['id'] ?>" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <button type="submit" class="badge-btn <?= $slider['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $slider['is_active'] ? 'نشط' : 'معطل' ?>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="slider-card-body">
                    <h3 class="slider-card-title"><?= escape($slider['title']) ?></h3>
                    <?php if (!empty($slider['subtitle'])): ?>
                        <p class="slider-card-subtitle"><?= escape($slider['subtitle']) ?></p>
                    <?php endif; ?>
                    <div class="slider-card-info">
                        <span class="info-label">نص الزر:</span>
                        <span class="info-value"><?= escape($slider['button_text']) ?></span>
                    </div>
                </div>
                <div class="slider-card-actions">
                    <a href="<?= BASE_URL ?>/admin/sliders/edit/<?= $slider['id'] ?>" 
                       class="btn btn-sm btn-info">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M11.333 2A2.827 2.827 0 0 1 14 4.667L5.333 13.333H2v-3.333L10.667 2h.666z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        تعديل
                    </a>
                    <form method="POST" action="<?= BASE_URL ?>/admin/sliders/delete/<?= $slider['id'] ?>" 
                          style="display: inline;"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذا السلايد؟')">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 0 1 1.334-1.334h2.666a1.333 1.333 0 0 1 1.334 1.334V4m2 0v9.333a1.333 1.333 0 0 1-1.334 1.334H4.667a1.333 1.333 0 0 1-1.334-1.334V4h9.334z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            حذف
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
