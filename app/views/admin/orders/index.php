<?php
$pageTitle = 'إدارة الطلبات';
require __DIR__ . '/../layouts/header.php';
?>

<h1 class="page-title">إدارة الطلبات</h1>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <input type="text" name="search" placeholder="بحث برقم الفاتورة أو العميل..." class="form-control" value="<?= escape($_GET['search'] ?? '') ?>">
            
            <select name="status" class="form-control">
                <option value="">جميع الحالات</option>
                <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : '' ?>>قيد الانتظار</option>
                <option value="confirmed" <?= (isset($_GET['status']) && $_GET['status'] === 'confirmed') ? 'selected' : '' ?>>مؤكد</option>
                <option value="shipped" <?= (isset($_GET['status']) && $_GET['status'] === 'shipped') ? 'selected' : '' ?>>تم الشحن</option>
                <option value="delivered" <?= (isset($_GET['status']) && $_GET['status'] === 'delivered') ? 'selected' : '' ?>>تم التوصيل</option>
                <option value="cancelled" <?= (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : '' ?>>ملغي</option>
            </select>
            
            <input type="date" name="date_from" class="form-control" value="<?= escape($_GET['date_from'] ?? '') ?>">
            <input type="date" name="date_to" class="form-control" value="<?= escape($_GET['date_to'] ?? '') ?>">
            
            <button type="submit" class="btn btn-primary">بحث</button>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <p class="text-muted">لا توجد طلبات</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>العميل</th>
                            <th>الهاتف</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?= escape($order['invoice_number']) ?></strong></td>
                                <td><?= formatDate($order['created_at'], 'd/m/Y H:i') ?></td>
                                <td><?= escape($order['customer_name']) ?></td>
                                <td><?= escape($order['customer_phone']) ?></td>
                                <td><?= formatPrice($order['total_amount']) ?></td>
                                <td><span class="badge <?= getStatusBadgeClass($order['status']) ?>"><?= getStatusLabel($order['status']) ?></span></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/orders/view/<?= $order['id'] ?>" class="btn btn-sm btn-primary">عرض</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
