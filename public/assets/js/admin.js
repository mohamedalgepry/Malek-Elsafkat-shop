// ===== Admin Panel JavaScript =====

// Mobile Menu Toggle
const adminMenuToggle = document.getElementById('adminMenuToggle');
const adminSidebar = document.getElementById('adminSidebar');
const adminOverlay = document.getElementById('adminOverlay');
const sidebarClose = document.getElementById('sidebarClose');

if (adminMenuToggle && adminSidebar && adminOverlay) {
    // Open sidebar
    adminMenuToggle.addEventListener('click', () => {
        adminSidebar.classList.add('active');
        adminOverlay.classList.add('active');
        adminMenuToggle.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
    
    // Close sidebar when clicking overlay
    adminOverlay.addEventListener('click', () => {
        adminSidebar.classList.remove('active');
        adminOverlay.classList.remove('active');
        adminMenuToggle.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Close sidebar when clicking close button
    if (sidebarClose) {
        sidebarClose.addEventListener('click', () => {
            adminSidebar.classList.remove('active');
            adminOverlay.classList.remove('active');
            adminMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Close sidebar when clicking on nav links
    const navLinks = adminSidebar.querySelectorAll('.nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            adminSidebar.classList.remove('active');
            adminOverlay.classList.remove('active');
            adminMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
}

// Confirm Delete
document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', function(e) {
        const message = this.dataset.confirm || 'هل أنت متأكد من الحذف؟';
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
});

// Image Preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Add Color Field
let colorIndex = 0;

function addColorField() {
    const container = document.getElementById('colorsContainer');
    if (!container) return;
    
    const colorField = document.createElement('div');
    colorField.className = 'color-field';
    colorField.innerHTML = `
        <input type="text" name="colors[${colorIndex}][name]" placeholder="اسم اللون" required>
        <input type="color" name="colors[${colorIndex}][hex]" required>
        <input type="number" name="colors[${colorIndex}][stock]" placeholder="الكمية" min="0" required>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">حذف</button>
    `;
    container.appendChild(colorField);
    colorIndex++;
}

// Auto-hide Alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    });
}, 5000);

// Table Row Highlight
document.querySelectorAll('.table tbody tr').forEach(row => {
    row.addEventListener('click', function() {
        this.classList.toggle('selected');
    });
});

// Stats Animation
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.textContent = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animate stats on load
document.querySelectorAll('.stat-value').forEach(stat => {
    const value = parseInt(stat.textContent);
    if (!isNaN(value)) {
        animateValue(stat, 0, value, 1000);
    }
});

// Form Validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('يرجى ملء جميع الحقول المطلوبة');
        }
    });
});

// Search Filter
const searchInput = document.querySelector('[data-search]');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Bulk Actions
const selectAll = document.getElementById('selectAll');
if (selectAll) {
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
}

// Export Table
function exportTable(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => col.textContent);
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename + '.csv';
    a.click();
}

// ===== Mobile Enhancements =====

// Detect Mobile Device
const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

// Add Mobile Class to Body
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

// Swipe to Close Sidebar
if (isTouch && adminSidebar) {
    let touchStartX = 0;
    let touchEndX = 0;
    
    adminSidebar.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    adminSidebar.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeDistance = touchEndX - touchStartX;
        // Swipe right to close (more than 50px)
        if (swipeDistance > 50) {
            adminSidebar.classList.remove('active');
            adminOverlay.classList.remove('active');
            if (adminMenuToggle) adminMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
}

// Smooth Scroll for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== '#!') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// Table Horizontal Scroll Indicator
const tables = document.querySelectorAll('.table-responsive');
tables.forEach(table => {
    if (table.scrollWidth > table.clientWidth) {
        table.classList.add('has-scroll');
        
        // Add scroll indicator
        const indicator = document.createElement('div');
        indicator.className = 'scroll-indicator';
        indicator.innerHTML = '← مرر للمزيد';
        indicator.style.cssText = `
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--admin-primary);
            color: #000;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            pointer-events: none;
            opacity: 0.8;
            z-index: 10;
        `;
        
        table.style.position = 'relative';
        table.appendChild(indicator);
        
        // Hide indicator on scroll
        table.addEventListener('scroll', () => {
            if (table.scrollLeft > 10) {
                indicator.style.opacity = '0';
            } else {
                indicator.style.opacity = '0.8';
            }
        });
    }
});

// Auto-resize Textarea
const textareas = document.querySelectorAll('textarea');
textareas.forEach(textarea => {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});

// Pull to Refresh (Mobile)
if (isMobile) {
    let startY = 0;
    let isPulling = false;
    
    document.addEventListener('touchstart', (e) => {
        if (window.scrollY === 0) {
            startY = e.touches[0].pageY;
            isPulling = true;
        }
    });
    
    document.addEventListener('touchmove', (e) => {
        if (!isPulling) return;
        
        const currentY = e.touches[0].pageY;
        const pullDistance = currentY - startY;
        
        if (pullDistance > 100) {
            // Show refresh indicator
            const refreshIndicator = document.getElementById('refreshIndicator');
            if (refreshIndicator) {
                refreshIndicator.style.display = 'block';
            }
        }
    });
    
    document.addEventListener('touchend', (e) => {
        if (!isPulling) return;
        
        const endY = e.changedTouches[0].pageY;
        const pullDistance = endY - startY;
        
        if (pullDistance > 100) {
            // Reload page
            location.reload();
        }
        
        isPulling = false;
    });
}

// Keyboard Navigation
document.addEventListener('keydown', (e) => {
    // ESC to close sidebar
    if (e.key === 'Escape' && adminSidebar && adminSidebar.classList.contains('active')) {
        adminSidebar.classList.remove('active');
        adminOverlay.classList.remove('active');
        if (adminMenuToggle) adminMenuToggle.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Lazy Load Images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// Network Status Indicator
window.addEventListener('online', () => {
    showNotification('تم الاتصال بالإنترنت', 'success');
});

window.addEventListener('offline', () => {
    showNotification('لا يوجد اتصال بالإنترنت', 'error');
});

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'var(--admin-success)' : type === 'error' ? 'var(--admin-error)' : 'var(--admin-info)'};
        color: #000;
        padding: 15px 20px;
        border-radius: 8px;
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Optimize Performance on Mobile
if (isMobile) {
    // Disable animations on low-end devices
    if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
        document.body.classList.add('reduce-motion');
    }
    
    // Debounce Scroll Events
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            // Scroll ended
        }, 100);
    }, { passive: true });
}

// Initialize
console.log('Admin Panel - Loaded');
console.log('Mobile Device:', isMobile);
console.log('Touch Support:', isTouch);
