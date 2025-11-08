<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= isset($pageTitle) ? escape($pageTitle) . ' - ' : '' ?>لوحة التحكم</title>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
        <script>
            window.BASE_URL = '<?= BASE_URL ?>';
        </script>
    </head>
<body class="admin-panel">
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h2>لوحة التحكم</h2>
                <button class="sidebar-close" id="sidebarClose" aria-label="إغلاق القائمة">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-item <?= strpos($uri ?? '', '/admin/dashboard') === 0 ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M3 10H8V3H3V10ZM3 17H8V12H3V17ZM10 17H15V10H10V17ZM10 3V8H15V3H10Z" fill="currentColor"/>
                    </svg>
                    الرئيسية
                </a>
                
                <a href="<?= BASE_URL ?>/admin/products" class="nav-item <?= strpos($uri ?? '', '/admin/products') === 0 ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 2L2 6V14L10 18L18 14V6L10 2Z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    المنتجات
                </a>
                
                <a href="<?= BASE_URL ?>/admin/categories" class="nav-item <?= strpos($uri ?? '', '/admin/categories') === 0 ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <rect x="2" y="2" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="11" y="2" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="2" y="11" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                        <rect x="11" y="11" width="7" height="7" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    التصنيفات
                </a>
                
                <a href="<?= BASE_URL ?>/admin/orders" class="nav-item <?= strpos($uri ?? '', '/admin/orders') === 0 ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M3 3H17L15 13H5L3 3Z" stroke="currentColor" stroke-width="2"/>
                        <circle cx="6" cy="17" r="1" fill="currentColor"/>
                        <circle cx="14" cy="17" r="1" fill="currentColor"/>
                    </svg>
                    الطلبات
                </a>
                
                <a href="<?= BASE_URL ?>/admin/sliders" class="nav-item <?= strpos($uri ?? '', '/admin/sliders') === 0 ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <rect x="2" y="4" width="16" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M6 9L9 12L14 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    السلايدر
                </a>
                
                <a href="<?= BASE_URL ?>/admin/shipping" class="nav-item <?= strpos($uri ?? '', '/admin/shipping') === 0 ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M1 3L1 13L5 13M5 13L5 17L9 17M5 13L9 13M9 13L9 17M9 13L13 13M9 17L13 17M13 13L13 17M13 13L17 13L19 10L19 7L17 7L17 3L1 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="7" cy="17" r="1.5" stroke="currentColor" stroke-width="1.5"/>
                        <circle cx="15" cy="17" r="1.5" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    الشحن والمحافظات
                </a>
                
                <a href="<?= BASE_URL ?>/admin/settings" class="nav-item <?= strpos($uri ?? '', '/admin/settings') === 0 ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 12C11.1046 12 12 11.1046 12 10C12 8.89543 11.1046 8 10 8C8.89543 8 8 8.89543 8 10C8 11.1046 8.89543 12 10 12Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M16 10C16 10.7 15.9 11.4 15.7 12L17.6 13.5L16.4 15.5L14.1 14.8C13.5 15.4 12.8 15.9 12 16.2V18.5H8V16.2C7.2 15.9 6.5 15.4 5.9 14.8L3.6 15.5L2.4 13.5L4.3 12C4.1 11.4 4 10.7 4 10C4 9.3 4.1 8.6 4.3 8L2.4 6.5L3.6 4.5L5.9 5.2C6.5 4.6 7.2 4.1 8 3.8V1.5H12V3.8C12.8 4.1 13.5 4.6 14.1 5.2L16.4 4.5L17.6 6.5L15.7 8C15.9 8.6 16 9.3 16 10Z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    الإعدادات
                </a>
                
                <a href="<?= BASE_URL ?>/admin/logout" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M7 17H3V3H7M13 13L17 9L13 5M17 9H7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    تسجيل الخروج
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="admin-main">
            <header class="admin-header">
                <button class="mobile-menu-toggle" id="adminMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
                <div class="header-right">
                    <div class="notifications-wrapper">
                        <button class="notification-btn" id="notificationBtn">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                        </button>
                        
                        <div class="notifications-dropdown" id="notificationsDropdown">
                            <div class="notifications-header">
                                <h3>الإشعارات</h3>
                                <button class="mark-all-read" id="markAllRead">تعليم الكل كمقروء</button>
                            </div>
                            <div class="notifications-list" id="notificationsList">
                                <div class="notification-loading">جاري التحميل...</div>
                            </div>
                        </div>
                    </div>
                    
                    <span class="admin-user">مرحباً، <?= escape($_SESSION['admin_username'] ?? 'Admin') ?></span>
                </div>
            </header>
            
            <div class="admin-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= escape($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= escape($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
