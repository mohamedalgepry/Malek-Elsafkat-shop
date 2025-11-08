<?php
$pageTitle = 'إدارة المنتجات';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1 class="page-title">إدارة المنتجات</h1>
    <a href="<?= BASE_URL ?>/admin/products/add" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M10 5V15M5 10H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        إضافة منتج جديد
    </a>
</div>

<!-- Search & Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" class="filters-form">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px;">
                <input type="text" name="search" placeholder="البحث بالاسم، الكود، أو الوصف..." class="form-control" value="<?= escape($_GET['search'] ?? '') ?>" style="font-size: 14px;">
                
                <select name="category" class="form-control">
                    <option value="">جميع التصنيفات</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= escape($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="sort" class="form-control">
                    <option value="newest">الأحدث</option>
                    <option value="oldest">الأقدم</option>
                    <option value="name">الاسم</option>
                    <option value="price">السعر</option>
                </select>
                
                <button type="submit" class="btn btn-primary">بحث</button>
            </div>
            <div style="margin-top: 10px; font-size: 13px; color: #666;">
                💡 <strong>نصيحة:</strong> يمكنك البحث باسم المنتج، كود المنتج (SKU)، الوصف، أو التصنيف
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <?php if (!empty($_GET['search'])): ?>
            <div style="padding: 15px; background: #e3f2fd; border-radius: 8px; margin-bottom: 20px;">
                <strong>🔍 نتائج البحث عن:</strong> "<?= escape($_GET['search']) ?>" 
                - تم العثور على <strong><?= $totalProducts ?></strong> منتج
                <a href="<?= BASE_URL ?>/admin/products" style="margin-right: 15px; color: #1976d2;">✕ مسح البحث</a>
            </div>
        <?php endif; ?>
        
        <?php if (empty($products)): ?>
            <p class="text-muted">لا توجد منتجات</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>كود المنتج</th>
                            <th>الاسم</th>
                            <th>التصنيف</th>
                            <th>السعر</th>
                            <th>المخزون</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?= UPLOAD_URL ?>/products/<?= escape($product['main_image']) ?>" 
                                         alt="<?= escape($product['name']) ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                </td>
                                <td>
                                    <?php 
                                    $sku = escape($product['sku'] ?? 'N/A');
                                    $searchQuery = $_GET['search'] ?? '';
                                    if ($searchQuery && stripos($sku, $searchQuery) !== false) {
                                        $sku = str_ireplace($searchQuery, '<mark style="background: #ffeb3b; padding: 2px 4px; border-radius: 3px;">' . $searchQuery . '</mark>', $sku);
                                    }
                                    ?>
                                    <span style="font-family: monospace; font-weight: 600; color: var(--admin-primary);">
                                        <?= $sku ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $name = escape($product['name']);
                                    if ($searchQuery && stripos($name, $searchQuery) !== false) {
                                        $name = str_ireplace($searchQuery, '<mark style="background: #ffeb3b; padding: 2px 4px; border-radius: 3px;">' . $searchQuery . '</mark>', $name);
                                    }
                                    echo $name;
                                    ?>
                                </td>
                                <td><?= escape($product['category_name']) ?></td>
                                <td><?= formatPrice($product['regular_price']) ?></td>
                                <td>
                                    <?php if ($product['total_stock'] <= 0): ?>
                                        <span class="badge badge-error">نفذت الكمية</span>
                                    <?php elseif ($product['total_stock'] < 5): ?>
                                        <span class="badge badge-warning"><?= $product['total_stock'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?= $product['total_stock'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="badge badge-info">مميز</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/products/edit/<?= $product['id'] ?>" class="btn btn-sm btn-primary">تعديل</a>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/products/delete/<?= $product['id'] ?>" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" data-confirm="هل أنت متأكد من حذف هذا المنتج؟">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
                           class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
