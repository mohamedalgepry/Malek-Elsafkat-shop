<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Title & Description -->
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? escape($pageDescription) : APP_DESCRIPTION ?>">
    <meta name="keywords" content="<?= isset($pageKeywords) ? escape($pageKeywords) : APP_KEYWORDS ?>">
    <meta name="author" content="<?= APP_AUTHOR ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= APP_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= isset($pageTitle) ? escape($pageTitle) . ' - ' : '' ?><?= APP_NAME ?>">
    <meta property="og:description" content="<?= isset($pageDescription) ? escape($pageDescription) : APP_DESCRIPTION ?>">
    <meta property="og:image" content="<?= isset($pageImage) ? $pageImage : APP_LOGO ?>">
    <meta property="og:site_name" content="<?= APP_NAME ?>">
    <meta property="og:locale" content="ar_EG">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= APP_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta name="twitter:title" content="<?= isset($pageTitle) ? escape($pageTitle) . ' - ' : '' ?><?= APP_NAME ?>">
    <meta name="twitter:description" content="<?= isset($pageDescription) ? escape($pageDescription) : APP_DESCRIPTION ?>">
    <meta name="twitter:image" content="<?= isset($pageImage) ? $pageImage : APP_LOGO ?>">
    <meta name="twitter:creator" content="@<?= APP_AUTHOR ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= APP_URL . $_SERVER['REQUEST_URI'] ?>">
    
    <!-- Robots -->
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    
    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= ASSETS_URL ?>/images/logo/logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= ASSETS_URL ?>/images/logo/logo.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= ASSETS_URL ?>/images/logo/logo.png">
    <link rel="shortcut icon" href="<?= ASSETS_URL ?>/images/logo/logo.png">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#FFD700">
    <meta name="msapplication-TileColor" content="#FFD700">
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/cart-checkout.css">
    
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?= APP_NAME ?>",
        "url": "<?= APP_URL ?>",
        "description": "<?= APP_DESCRIPTION ?>",
        "author": {
            "@type": "Person",
            "name": "<?= APP_AUTHOR ?>"
        },
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?= APP_URL ?>/shop?search={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'light-mode' : 'dark-mode' ?>">
    
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="<?= BASE_URL ?>/" class="logo">
                    <?php 
                    $logoPath = PUBLIC_PATH . '/assets/images/logo/logo.png';
                    $logoUrl = file_exists($logoPath) 
                        ? ASSETS_URL . '/images/logo/logo.png' 
                        : ASSETS_URL . '/images/logo/logo.svg';
                    ?>
                    <img src="<?= $logoUrl ?>" alt="<?= APP_NAME ?>" width="40" height="40" style="object-fit: contain;">
                    <span><?= APP_NAME ?></span>
                </a>
                
                <!-- Navigation -->
                <nav class="nav">
                    <a href="<?= BASE_URL ?>/" class="nav-link <?= $uri === '/' ? 'active' : '' ?>">الرئيسية</a>
                    <a href="<?= BASE_URL ?>/shop" class="nav-link <?= strpos($uri, '/shop') === 0 ? 'active' : '' ?>">المتجر</a>
                    <a href="<?= BASE_URL ?>/track-order" class="nav-link <?= strpos($uri, '/track-order') === 0 ? 'active' : '' ?>">تتبع الطلب</a>
                </nav>
                
                <!-- Actions -->
                <div class="header-actions">
                    <!-- Mobile Search Toggle -->
                    <button class="mobile-search-toggle" id="mobileSearchToggle" aria-label="بحث">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    
                    <!-- Search -->
                    <div class="search-box" id="searchBox">
                        <form action="<?= BASE_URL ?>/search" method="GET" class="search-form">
                            <input type="text" name="q" placeholder="ابحث عن منتج..." class="search-input" value="<?= escape($_GET['q'] ?? '') ?>">
                            <button type="submit" class="search-btn">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Cart -->
                    <a href="<?= BASE_URL ?>/cart" class="cart-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M1 1H5L7.68 14.39C7.77 14.78 8.11 15 8.5 15H19.5C19.89 15 20.23 14.78 20.32 14.39L23 5H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="20" r="1.5" fill="currentColor"/>
                            <circle cx="18" cy="20" r="1.5" fill="currentColor"/>
                        </svg>
                        <?php $cartCount = getCartCount(); ?>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Theme Toggle -->
                    <button class="theme-toggle" id="themeToggle" aria-label="تبديل الوضع">
                        <svg class="sun-icon" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 1V3M12 21V23M23 12H21M3 12H1M20.49 20.49L19.07 19.07M4.93 4.93L3.51 3.51M20.49 3.51L19.07 4.93M4.93 19.07L3.51 20.49" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <svg class="moon-icon" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div class="mobile-menu" id="mobileMenu">
                <nav class="mobile-nav">
                    <a href="<?= BASE_URL ?>/" class="mobile-nav-link <?= $uri === '/' ? 'active' : '' ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M3 10H8V3H3V10ZM3 17H8V12H3V17ZM10 17H15V10H10V17ZM10 3V8H15V3H10Z" fill="currentColor"/>
                        </svg>
                        الرئيسية
                    </a>
                    <a href="<?= BASE_URL ?>/shop" class="mobile-nav-link <?= strpos($uri, '/shop') === 0 ? 'active' : '' ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 2L2 6V14L10 18L18 14V6L10 2Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        المتجر
                    </a>
                    <a href="<?= BASE_URL ?>/track-order" class="mobile-nav-link <?= strpos($uri, '/track-order') === 0 ? 'active' : '' ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M3 3H17L15 13H5L3 3Z" stroke="currentColor" stroke-width="2"/>
                            <circle cx="6" cy="17" r="1" fill="currentColor"/>
                            <circle cx="14" cy="17" r="1" fill="currentColor"/>
                        </svg>
                        تتبع الطلب
                    </a>
                    <a href="<?= BASE_URL ?>/cart" class="mobile-nav-link">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M1 1H5L7.68 14.39C7.77 14.78 8.11 15 8.5 15H19.5C19.89 15 20.23 14.78 20.32 14.39L23 5H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="20" r="1.5" fill="currentColor"/>
                            <circle cx="18" cy="20" r="1.5" fill="currentColor"/>
                        </svg>
                        السلة
                        <?php if ($cartCount > 0): ?>
                            <span style="margin-right: auto; background: var(--primary); color: #000; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 800;"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
            </div>
            
            <!-- Mobile Overlay -->
            <div class="mobile-overlay" id="mobileOverlay"></div>
        </div>
    </header>
    
    <!-- Toast Notifications -->
    <div class="toast-container" id="toastContainer"></div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= escape($_SESSION['success']) ?>', 'success');
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= escape($_SESSION['error']) ?>', 'error');
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="main-content">
