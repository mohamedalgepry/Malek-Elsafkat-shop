<?php
$pageTitle = 'إدارة التصنيفات';
require __DIR__ . '/../layouts/header.php';
?>

<h1 class="page-title">إدارة التصنيفات</h1>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
    <!-- Add Category Form -->
    <div class="card">
        <div class="card-header"><h2>إضافة تصنيف جديد</h2></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/categories/store" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="form-group">
                    <label class="form-label">اسم التصنيف <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">الصورة</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="display_order" class="form-control" value="0" min="0">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">إضافة التصنيف</button>
            </form>
        </div>
    </div>
    
    <!-- Categories List -->
    <div class="card">
        <div class="card-header"><h2>التصنيفات الحالية</h2></div>
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <p class="text-muted">لا توجد تصنيفات</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>عدد المنتجات</th>
                                <th>الترتيب</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <?php if ($category['image']): ?>
                                            <img src="<?= UPLOAD_URL ?>/products/<?= escape($category['image']) ?>" 
                                                 style="width: 30px; height: 30px; object-fit: cover; border-radius: 50%; margin-left: 10px; vertical-align: middle;">
                                        <?php endif; ?>
                                        <strong><?= escape($category['name']) ?></strong>
                                    </td>
                                    <td><?= $category['product_count'] ?></td>
                                    <td><?= $category['display_order'] ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editCategory(<?= $category['id'] ?>, '<?= escape($category['name']) ?>', '<?= escape($category['description'] ?? '') ?>', <?= $category['display_order'] ?>)">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align: middle; margin-left: 4px;">
                                                    <path d="M10 1L13 4L5 12H2V9L10 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                تعديل
                                            </button>
                                            
                                            <?php if ($category['product_count'] == 0): ?>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $category['id'] ?>, '<?= escape($category['name']) ?>')">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align: middle; margin-left: 4px;">
                                                        <path d="M2 4H12M5 4V2H9V4M3 4L4 12H10L11 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    حذف
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline" disabled title="لا يمكن حذف تصنيف يحتوي على منتجات">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align: middle; margin-left: 4px;">
                                                        <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.5"/>
                                                        <path d="M4 4L10 10M10 4L4 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    حذف
                                                </button>
                                            <?php endif; ?>
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
</div>

<!-- Edit Modal (Simple) -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 500px; max-width: 90%;">
        <div class="card-header">
            <h2>تعديل التصنيف</h2>
            <button onclick="closeEditModal()" style="background: none; border: none; color: var(--admin-text); cursor: pointer; font-size: 24px;">&times;</button>
        </div>
        <div class="card-body">
            <form method="POST" id="editForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="form-group">
                    <label class="form-label">اسم التصنيف</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">الصورة</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="display_order" id="editOrder" class="form-control" min="0">
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <button type="button" class="btn btn-outline" onclick="closeEditModal()">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(id, name, description, order) {
    document.getElementById('editName').value = name;
    document.getElementById('editDescription').value = description;
    document.getElementById('editOrder').value = order;
    document.getElementById('editForm').action = '<?= BASE_URL ?>/admin/categories/update/' + id;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function confirmDelete(id, name) {
    // Create custom confirmation dialog
    const confirmed = confirm(
        '⚠️ تأكيد الحذف\n\n' +
        'هل أنت متأكد من حذف التصنيف: "' + name + '"؟\n\n' +
        '⚠️ هذا الإجراء لا يمكن التراجع عنه!'
    );
    
    if (confirmed) {
        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/admin/categories/delete/' + id;
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= generateCsrfToken() ?>';
        form.appendChild(csrfInput);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
