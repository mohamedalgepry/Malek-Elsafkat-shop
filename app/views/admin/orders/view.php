<?php
$pageTitle = 'تفاصيل الطلب';
require __DIR__ . '/../layouts/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1 class="page-title">تفاصيل الطلب: <?= escape($order['invoice_number']) ?></h1>
    <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-outline">العودة للطلبات</a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
    <!-- Order Details -->
    <div>
        <!-- Customer Info -->
        <div class="card">
            <div class="card-header"><h2>معلومات العميل</h2></div>
            <div class="card-body">
                <p><strong>الاسم:</strong> <?= escape($order['customer_name']) ?></p>
                <p><strong>الهاتف:</strong> <?= escape($order['customer_phone']) ?></p>
                <?php if ($order['customer_email']): ?>
                    <p><strong>البريد:</strong> <?= escape($order['customer_email']) ?></p>
                <?php endif; ?>
                <p><strong>العنوان:</strong> <?= escape($order['shipping_address']) ?>, <?= escape($order['shipping_city']) ?></p>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="card">
            <div class="card-header"><h2>المنتجات</h2></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>كود المنتج</th>
                            <th>المنتج</th>
                            <th>اللون</th>
                            <th>المقاس</th>
                            <th>السعر</th>
                            <th>الكمية</th>
                            <th>المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 600; color: var(--admin-primary); font-size: 12px;">
                                        <?= escape($item['product_sku'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= escape($item['product_name']) ?></td>
                                <td>
                                    <span class="color-dot" style="background: <?= escape($item['color_hex']) ?>; display: inline-block; width: 20px; height: 20px; border-radius: 50%; margin-left: 5px;"></span>
                                    <?= escape($item['color_name']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($item['size_name'])): ?>
                                        <span style="background: var(--admin-primary); color: #000; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 13px;">
                                            <?= escape($item['size_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--admin-text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatPrice($item['unit_price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= formatPrice($item['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6"><strong>المجموع الفرعي:</strong></td>
                            <td><strong><?= formatPrice($order['subtotal']) ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="6"><strong>الشحن:</strong></td>
                            <td><strong><?= formatPrice($order['shipping_fee']) ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="6"><strong>الإجمالي:</strong></td>
                            <td><strong><?= formatPrice($order['total_amount']) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Order Status -->
    <div>
        <div class="card">
            <div class="card-header"><h2>حالة الطلب</h2></div>
            <div class="card-body">
                <p><strong>الحالة الحالية:</strong></p>
                <p><span class="badge <?= getStatusBadgeClass($order['status']) ?>"><?= getStatusLabel($order['status']) ?></span></p>
                
                <form method="POST" action="<?= BASE_URL ?>/admin/orders/update-status/<?= $order['id'] ?>" style="margin-top: 20px;">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    
                    <div class="form-group">
                        <label class="form-label">تحديث الحالة:</label>
                        <select name="status" class="form-control" required>
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
                            <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>مؤكد</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>تم الشحن</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>تم التوصيل</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>ملغي</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ملاحظة:</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">تحديث الحالة</button>
                </form>
                
                <form method="POST" action="<?= BASE_URL ?>/admin/orders/delete/<?= $order['id'] ?>" style="margin-top: 15px;">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-danger btn-block" data-confirm="هل أنت متأكد من حذف هذا الطلب؟">حذف الطلب</button>
                </form>
            </div>
        </div>
        
        <!-- Status History -->
        <?php if (!empty($order['status_history'])): ?>
            <div class="card">
                <div class="card-header"><h2>سجل الحالات</h2></div>
                <div class="card-body">
                    <?php foreach ($order['status_history'] as $history): ?>
                        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--admin-border);">
                            <p><strong><?= getStatusLabel($history['status']) ?></strong></p>
                            <p style="font-size: 12px; color: var(--admin-text-muted);"><?= formatDate($history['created_at'], 'd/m/Y H:i') ?></p>
                            <?php if ($history['note']): ?>
                                <p style="font-size: 14px;"><?= escape($history['note']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
