<?php
$pageTitle = 'تتبع الطلب';
require __DIR__ . '/../layouts/header.php';
?>

<section class="section track-order-section">
    <div class="container">
        <h1 class="page-title">تتبع الطلب</h1>
        
        <!-- Search Form -->
        <div class="track-search">
            <form method="POST" action="<?= BASE_URL ?>/track-order/search" class="track-search-form">
                <div class="form-group">
                    <label for="invoice_number" class="form-label">أدخل رقم الفاتورة</label>
                    <div class="track-input-group">
                        <input type="text" 
                               id="invoice_number" 
                               name="invoice_number" 
                               class="track-input" 
                               placeholder="INV-20231103-XXXXXX"
                               value="<?= escape($_GET['invoice'] ?? '') ?>"
                               required>
                        <button type="submit" class="track-btn">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="track-btn-text">تتبع</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (isset($order) && $order): ?>
            <!-- Order Details -->
            <div class="order-details">
                <!-- Order Header -->
                <div class="order-header">
                    <div class="order-info">
                        <h2>رقم الفاتورة: <?= escape($order['invoice_number']) ?></h2>
                        <p class="order-date">تاريخ الطلب: <?= formatDate($order['created_at'], 'd/m/Y H:i') ?></p>
                    </div>
                    <div class="order-status">
                        <span class="status-badge <?= getStatusBadgeClass($order['status']) ?>">
                            <?= getStatusLabel($order['status']) ?>
                        </span>
                    </div>
                </div>
                
                <!-- Order Timeline -->
                <div class="order-timeline">
                    <h3 class="timeline-title">حالة الطلب</h3>
                    
                    <div class="timeline">
                        <?php
                        $statuses = ['pending', 'confirmed', 'shipped', 'delivered'];
                        $statusLabels = [
                            'pending' => 'تم الطلب',
                            'confirmed' => 'تم التأكيد',
                            'shipped' => 'تم الشحن',
                            'delivered' => 'تم التوصيل'
                        ];
                        
                        $currentStatusIndex = array_search($order['status'], $statuses);
                        if ($order['status'] === 'cancelled') {
                            $currentStatusIndex = -1;
                        }
                        
                        foreach ($statuses as $index => $status):
                            $isCompleted = $index <= $currentStatusIndex;
                            $isCurrent = $index === $currentStatusIndex;
                            
                            // Find timestamp from history
                            $timestamp = null;
                            foreach ($order['status_history'] as $history) {
                                if ($history['status'] === $status) {
                                    $timestamp = $history['created_at'];
                                    break;
                                }
                            }
                        ?>
                            <div class="timeline-item <?= $isCompleted ? 'completed' : '' ?> <?= $isCurrent ? 'current' : '' ?>">
                                <div class="timeline-marker">
                                    <?php if ($isCompleted): ?>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" fill="currentColor"/>
                                            <path d="M8 12L11 15L16 10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    <?php else: ?>
                                        <div class="marker-dot"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <h4><?= $statusLabels[$status] ?></h4>
                                    <?php if ($timestamp): ?>
                                        <p class="timeline-date"><?= formatDate($timestamp, 'd/m/Y H:i') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($order['status'] === 'cancelled'): ?>
                            <div class="timeline-item completed cancelled">
                                <div class="timeline-marker">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" fill="currentColor"/>
                                        <path d="M15 9L9 15M9 9L15 15" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <h4>تم الإلغاء</h4>
                                    <?php
                                    foreach ($order['status_history'] as $history) {
                                        if ($history['status'] === 'cancelled') {
                                            echo '<p class="timeline-date">' . formatDate($history['created_at'], 'd/m/Y H:i') . '</p>';
                                            break;
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Customer Info (Name Only) -->
                <div class="order-info-simple">
                    <div class="info-card">
                        <h3 class="info-title">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                <path d="M10 10C12.7614 10 15 7.76142 15 5C15 2.23858 12.7614 0 10 0C7.23858 0 5 2.23858 5 5C5 7.76142 7.23858 10 10 10Z" fill="currentColor"/>
                                <path d="M10 12C4.477 12 0 14.477 0 17.5V20H20V17.5C20 14.477 15.523 12 10 12Z" fill="currentColor"/>
                            </svg>
                            اسم العميل
                        </h3>
                        <div class="info-content">
                            <p class="customer-name"><?= escape($order['customer_name']) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="order-items">
                    <h3 class="items-title">المنتجات</h3>
                    
                    <div class="items-list">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="<?= UPLOAD_URL ?>/products/<?= escape($item['product_image']) ?>" 
                                         alt="<?= escape($item['product_name']) ?>">
                                </div>
                                <div class="item-details">
                                    <h4><?= escape($item['product_name']) ?></h4>
                                    <?php if (!empty($item['product_sku'])): ?>
                                        <p class="item-sku" style="font-family: monospace; font-size: 12px; color: var(--text-muted); margin: 4px 0;">
                                            كود المنتج: <strong style="color: var(--primary);"><?= escape($item['product_sku']) ?></strong>
                                        </p>
                                    <?php endif; ?>
                                    <p class="item-color">
                                        <span class="color-dot" style="background-color: <?= escape($item['color_hex']) ?>"></span>
                                        <?= escape($item['color_name']) ?>
                                    </p>
                                    <?php if (!empty($item['size_name'])): ?>
                                        <p class="item-size">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                                                <rect x="1" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M4 4H10M4 7H10M4 10H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            المقاس: <strong><?= escape($item['size_name']) ?></strong>
                                        </p>
                                    <?php endif; ?>
                                    <p class="item-qty">الكمية: <?= $item['quantity'] ?></p>
                                </div>
                                <div class="item-price">
                                    <?= formatPrice($item['unit_price']) ?>
                                </div>
                                <div class="item-subtotal">
                                    <?= formatPrice($item['subtotal']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>المجموع الفرعي:</span>
                            <span><?= formatPrice($order['subtotal']) ?></span>
                        </div>
                        <div class="total-row">
                            <span>الشحن:</span>
                            <span>
                                <?php if ($order['shipping_fee'] > 0): ?>
                                    <?= formatPrice($order['shipping_fee']) ?>
                                <?php else: ?>
                                    <span class="free-shipping">مجاني</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="total-row total">
                            <span>الإجمالي:</span>
                            <span class="total-amount"><?= formatPrice($order['total_amount']) ?></span>
                        </div>
                    </div>
                </div>
                
                <?php if ($order['order_notes']): ?>
                    <div class="order-notes">
                        <h3>ملاحظات الطلب</h3>
                        <p><?= nl2br(escape($order['order_notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif (isset($_GET['invoice'])): ?>
            <!-- Order Not Found -->
            <div class="empty-state">
                <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                    <circle cx="60" cy="60" r="50" stroke="currentColor" stroke-width="3" opacity="0.3"/>
                    <path d="M60 35V65M60 80V85" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <h2>لم يتم العثور على الطلب</h2>
                <p>تأكد من رقم الفاتورة وحاول مرة أخرى</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
