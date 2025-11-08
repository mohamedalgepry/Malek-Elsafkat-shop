<?php
$pageTitle = 'الرئيسية';
require __DIR__ . '/layouts/header.php';
?>

<h1 class="page-title">لوحة التحكم</h1>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <path d="M16 4L4 10V20L16 26L28 20V10L16 4Z" stroke="currentColor" stroke-width="2"/>
            </svg>
        </div>
        <div class="stat-info">
            <h3>إجمالي المنتجات</h3>
            <p class="stat-value"><?= $totalProducts ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <path d="M6 6H26L24 22H10L6 6Z" stroke="currentColor" stroke-width="2"/>
            </svg>
        </div>
        <div class="stat-info">
            <h3>طلبات اليوم</h3>
            <p class="stat-value"><?= $todayStats['total_orders'] ?? 0 ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2"/>
                <path d="M16 8V16L20 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="stat-info">
            <h3>طلبات قيد الانتظار</h3>
            <p class="stat-value"><?= $todayStats['pending_orders'] ?? 0 ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <path d="M8 12H24M8 16H24M8 20H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="stat-info">
            <h3>إيرادات الشهر</h3>
            <p class="stat-value"><?= formatPrice($monthStats['total_revenue'] ?? 0) ?></p>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card">
    <div class="card-header">
        <h2>أحدث الطلبات</h2>
        <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-sm btn-outline">عرض الكل</a>
    </div>
    <div class="card-body">
        <?php if (empty($recentOrders)): ?>
            <p class="text-muted">لا توجد طلبات</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>العميل</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?= escape($order['invoice_number']) ?></td>
                                <td><?= escape($order['customer_name']) ?></td>
                                <td><?= formatPrice($order['total_amount']) ?></td>
                                <td><span class="badge <?= getStatusBadgeClass($order['status']) ?>"><?= getStatusLabel($order['status']) ?></span></td>
                                <td><?= formatDate($order['created_at'], 'd/m/Y H:i') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/orders/view/<?= $order['id'] ?>" class="btn btn-sm btn-primary">عرض</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Low Stock Alert -->
<?php if (!empty($lowStockProducts)): ?>
    <div class="card">
        <div class="card-header">
            <h2>تنبيه: منتجات قاربت على النفاذ</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>التصنيف</th>
                            <th>الكمية المتبقية</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockProducts as $product): ?>
                            <tr>
                                <td><?= escape($product['name']) ?></td>
                                <td><?= escape($product['category_name'] ?? '-') ?></td>
                                <td><span class="badge badge-warning"><?= $product['total_stock'] ?></span></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/products/edit/<?= $product['id'] ?>" class="btn btn-sm btn-primary">تعديل</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/layouts/footer.php'; ?>
