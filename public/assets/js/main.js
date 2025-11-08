// ===== Theme Toggle =====
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

// Load saved theme
const savedTheme = localStorage.getItem('theme') || 'dark';
body.className = savedTheme === 'light' ? 'light-mode' : 'dark-mode';

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const isLight = body.classList.contains('light-mode');
        body.className = isLight ? 'dark-mode' : 'light-mode';
        localStorage.setItem('theme', isLight ? 'dark' : 'light');
        document.cookie = `theme=${isLight ? 'dark' : 'light'}; path=/; max-age=31536000`;
    });
}

// ===== Mobile Menu =====
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const mobileMenu = document.getElementById('mobileMenu');
const mobileOverlay = document.getElementById('mobileOverlay');

if (mobileMenuToggle && mobileMenu && mobileOverlay) {
    // Toggle menu
    mobileMenuToggle.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        mobileMenuToggle.classList.toggle('active');
        document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
    });
    
    // Close menu when clicking overlay
    mobileOverlay.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        mobileOverlay.classList.remove('active');
        mobileMenuToggle.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Close menu when clicking a link
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            mobileMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
}

// ===== Mobile Search Toggle =====
const mobileSearchToggle = document.getElementById('mobileSearchToggle');
const searchBox = document.getElementById('searchBox');

if (mobileSearchToggle && searchBox) {
    mobileSearchToggle.addEventListener('click', () => {
        searchBox.classList.toggle('active');
        if (searchBox.classList.contains('active')) {
            searchBox.querySelector('.search-input').focus();
        }
    });
    
    // Close search when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchBox.contains(e.target) && !mobileSearchToggle.contains(e.target)) {
            searchBox.classList.remove('active');
        }
    });
}

// ===== Hero Slider =====
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.slider-dot');
const prevBtn = document.querySelector('.slider-prev');
const nextBtn = document.querySelector('.slider-next');
let currentSlide = 0;
let slideInterval;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    if (index >= slides.length) currentSlide = 0;
    if (index < 0) currentSlide = slides.length - 1;
    
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function nextSlide() {
    currentSlide++;
    showSlide(currentSlide);
}

function prevSlide() {
    currentSlide--;
    showSlide(currentSlide);
}

function startSlider() {
    slideInterval = setInterval(nextSlide, 5000);
}

function stopSlider() {
    clearInterval(slideInterval);
}

if (slides.length > 0) {
    showSlide(currentSlide);
    startSlider();
    
    if (nextBtn) nextBtn.addEventListener('click', () => {
        stopSlider();
        nextSlide();
        startSlider();
    });
    
    if (prevBtn) prevBtn.addEventListener('click', () => {
        stopSlider();
        prevSlide();
        startSlider();
    });
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            stopSlider();
            currentSlide = index;
            showSlide(currentSlide);
            startSlider();
        });
    });
}

// ===== Carousel =====
const carousel = document.getElementById('newArrivalsCarousel');
const carouselPrev = document.querySelector('.carousel-prev');
const carouselNext = document.querySelector('.carousel-next');

if (carousel) {
    let scrollAmount = 0;
    const scrollStep = 300;
    
    if (carouselNext) {
        carouselNext.addEventListener('click', () => {
            scrollAmount += scrollStep;
            carousel.scrollTo({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });
    }
    
    if (carouselPrev) {
        carouselPrev.addEventListener('click', () => {
            scrollAmount -= scrollStep;
            if (scrollAmount < 0) scrollAmount = 0;
            carousel.scrollTo({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });
    }
}

// ===== Toast Notifications =====
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===== Lazy Loading Images =====
const lazyImages = document.querySelectorAll('img[loading="lazy"]');

if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
}

// ===== Smooth Scroll =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ===== Form Validation =====
const forms = document.querySelectorAll('form[data-validate]');

forms.forEach(form => {
    form.addEventListener('submit', function(e) {
        const inputs = form.querySelectorAll('[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showToast('يرجى ملء جميع الحقول المطلوبة', 'error');
        }
    });
});

// ===== Search Debounce =====
const searchInput = document.querySelector('.search-input');
let searchTimeout;

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Implement live search here if needed
            console.log('Search:', this.value);
        }, 300);
    });
}

// ===== Add to Cart Animation =====
document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const icon = this.querySelector('svg');
        if (icon) {
            icon.style.animation = 'bounce 0.5s';
            setTimeout(() => {
                icon.style.animation = '';
            }, 500);
        }
    });
});

// ===== Quantity Selector =====
document.querySelectorAll('.qty-minus').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.qty-input');
        const current = parseInt(input.value);
        const min = parseInt(input.min) || 1;
        if (current > min) {
            input.value = current - 1;
            input.dispatchEvent(new Event('change'));
        }
    });
});

document.querySelectorAll('.qty-plus').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.qty-input');
        const current = parseInt(input.value);
        const max = parseInt(input.max) || 999;
        if (current < max) {
            input.value = current + 1;
            input.dispatchEvent(new Event('change'));
        }
    });
});

// ===== Image Zoom =====
const zoomableImages = document.querySelectorAll('.zoomable');

zoomableImages.forEach(img => {
    img.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        this.style.transformOrigin = `${x}% ${y}%`;
        this.style.transform = 'scale(1.5)';
    });
    
    img.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});

// ===== Confirm Delete =====
document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', function(e) {
        const message = this.dataset.confirm || 'هل أنت متأكد؟';
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
});

// ===== Auto-hide Alerts =====
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.animation = 'fadeOut 0.5s';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);

// ===== Animations on Scroll =====
const animateOnScroll = () => {
    const elements = document.querySelectorAll('[data-animate]');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementTop < windowHeight - 100) {
            element.classList.add('animated');
        }
    });
};

window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);

// ===== Copy to Clipboard =====
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('تم النسخ بنجاح', 'success');
    }).catch(() => {
        showToast('فشل النسخ', 'error');
    });
}

// ===== Loading State =====
function setLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner"></span> جاري التحميل...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText;
    }
}

// ===== Mobile Performance Enhancements =====

// Detect Mobile Device
const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

// Add Mobile Class
if (isMobile || isTouch) {
    document.body.classList.add('is-mobile');
}

// Prevent Zoom on Input Focus (iOS)
if (isMobile) {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            const viewport = document.querySelector('meta[name=viewport]');
            if (viewport) {
                viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0');
            }
        });
        
        input.addEventListener('blur', () => {
            const viewport = document.querySelector('meta[name=viewport]');
            if (viewport) {
                viewport.setAttribute('content', 'width=device-width, initial-scale=1.0');
            }
        });
    });
}

// Swipe to Close Mobile Menu
if (isTouch && mobileMenu) {
    let touchStartX = 0;
    let touchEndX = 0;
    
    mobileMenu.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    mobileMenu.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeDistance = touchStartX - touchEndX;
        // Swipe left to close (more than 50px)
        if (swipeDistance > 50) {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            if (mobileMenuToggle) mobileMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
}

// Touch Swipe for Hero Slider
if (isTouch && slides.length > 0) {
    const slider = document.querySelector('.hero-slider');
    if (slider) {
        let touchStartX = 0;
        let touchEndX = 0;
        
        slider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            stopSlider();
        });
        
        slider.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSliderSwipe();
            startSlider();
        });
        
        function handleSliderSwipe() {
            const swipeDistance = touchStartX - touchEndX;
            if (Math.abs(swipeDistance) > 50) {
                if (swipeDistance > 0) {
                    nextSlide(); // Swipe left
                } else {
                    prevSlide(); // Swipe right
                }
            }
        }
    }
}

// Optimize Scroll Performance
let scrollTimeout;
let lastScrollTop = 0;
const header = document.querySelector('.header');

window.addEventListener('scroll', () => {
    clearTimeout(scrollTimeout);
    
    scrollTimeout = setTimeout(() => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Hide header on scroll down, show on scroll up (mobile only)
        if (isMobile && header) {
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
        }
        
        lastScrollTop = scrollTop;
    }, 100);
}, { passive: true });

// Add transition to header
if (header) {
    header.style.transition = 'transform 0.3s ease';
}

// Network Status Indicator
window.addEventListener('online', () => {
    showToast('تم الاتصال بالإنترنت', 'success');
});

window.addEventListener('offline', () => {
    showToast('لا يوجد اتصال بالإنترنت', 'error');
});

// Keyboard Navigation
document.addEventListener('keydown', (e) => {
    // ESC to close mobile menu
    if (e.key === 'Escape') {
        if (mobileMenu && mobileMenu.classList.contains('active')) {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            if (mobileMenuToggle) mobileMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Close search
        if (searchBox && searchBox.classList.contains('active')) {
            searchBox.classList.remove('active');
        }
    }
    
    // Arrow keys for slider
    if (slides.length > 0) {
        if (e.key === 'ArrowLeft') {
            stopSlider();
            nextSlide();
            startSlider();
        } else if (e.key === 'ArrowRight') {
            stopSlider();
            prevSlide();
            startSlider();
        }
    }
});

// Debounce Resize Events
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        // Handle resize
        const width = window.innerWidth;
        
        // Close mobile menu on desktop
        if (width > 768 && mobileMenu) {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            if (mobileMenuToggle) mobileMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
    }, 250);
});

// Optimize Images on Mobile
if (isMobile) {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        // Add loading="lazy" if not present
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
    });
}

// Reduce Animations on Low-End Devices
if (isMobile && navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
    document.body.classList.add('reduce-motion');
}

// Service Worker Registration (for PWA)
if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            console.log('SW registered:', registration);
        }).catch(error => {
            console.log('SW registration failed:', error);
        });
    });
}

// Add to Home Screen Prompt (PWA)
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install button if needed
    const installBtn = document.getElementById('installBtn');
    if (installBtn) {
        installBtn.style.display = 'block';
        installBtn.addEventListener('click', () => {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                }
                deferredPrompt = null;
            });
        });
    }
});

// Vibration Feedback on Touch (if supported)
function vibrate(duration = 10) {
    if ('vibrate' in navigator && isMobile) {
        navigator.vibrate(duration);
    }
}

// Add vibration to buttons
document.querySelectorAll('.btn, button').forEach(btn => {
    btn.addEventListener('click', () => {
        vibrate(10);
    });
});

// Battery Status (optimize for low battery)
if ('getBattery' in navigator) {
    navigator.getBattery().then(battery => {
        if (battery.level < 0.2 && !battery.charging) {
            // Reduce animations and effects
            document.body.classList.add('low-battery');
            console.log('Low battery mode activated');
        }
    });
}

// ===== Initialize =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('ملك الصفقات - تم التحميل');
    console.log('Mobile:', isMobile);
    console.log('Touch:', isTouch);
    console.log('Screen:', window.innerWidth + 'x' + window.innerHeight);
});
