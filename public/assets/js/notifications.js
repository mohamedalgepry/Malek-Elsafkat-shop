// Notifications System
(function() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    const notificationsList = document.getElementById('notificationsList');
    const notificationBadge = document.getElementById('notificationBadge');
    const markAllReadBtn = document.getElementById('markAllRead');
    const notificationsWrapper = document.querySelector('.notifications-wrapper');
    
    let notificationSound = null;
    let lastNotificationCount = 0;
    let audioUnlocked = false;
    
    // Initialize notification sound
    function initSound() {
        notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIGGe77OmfTQ==');
        notificationSound.preload = 'auto';
        notificationSound.volume = 0.6;
    }
    
    // Play notification sound
    function playSound() {
        if (!notificationSound) return;
        // Restart from beginning for rapid notifications
        try { notificationSound.currentTime = 0; } catch(e) {}
        notificationSound.play().catch(e => {
            // Will try again after unlocking on first user gesture
            // console.log('Sound play deferred until unlock:', e);
        });
    }
    
    // Unlock audio on first user interaction (mobile browsers policy)
    function unlockAudio() {
        if (audioUnlocked || !notificationSound) return;
        const tryPlay = () => {
            notificationSound.muted = true;
            notificationSound.play().then(() => {
                notificationSound.pause();
                notificationSound.muted = false;
                audioUnlocked = true;
                removeListeners();
            }).catch(() => {
                // keep listeners until success
            });
        };
        const events = ['click','touchstart','keydown'];
        const handlers = events.map(evt => [evt, tryPlay, { once: false, passive: true }]);
        function removeListeners() { handlers.forEach(([evt, fn, opt]) => document.removeEventListener(evt, fn, opt)); }
        handlers.forEach(([evt, fn, opt]) => document.addEventListener(evt, fn, opt));
    }
    
    // Toggle dropdown
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationsDropdown.classList.toggle('show');
            
            if (notificationsDropdown.classList.contains('show')) {
                // prevent background scroll on small screens
                if (window.matchMedia('(max-width: 768px)').matches) {
                    document.body.style.overflow = 'hidden';
                }
                loadNotifications();
            } else {
                document.body.style.overflow = '';
            }
        });
    }
    
    // Close dropdown when clicking outside (use wrapper to include button & dropdown)
    document.addEventListener('click', function(e) {
        if (notificationsWrapper && !notificationsWrapper.contains(e.target)) {
            notificationsDropdown.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    // Close with ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && notificationsDropdown.classList.contains('show')) {
            notificationsDropdown.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    const API_BASE = (window.BASE_URL || '').replace(/\/$/, '');

    // Load notifications
    function loadNotifications() {
        fetch(`${API_BASE}/admin/notifications`)
            .then(async (response) => {
                // Handle unauthorized
                if (response.status === 401) {
                    notificationsList.innerHTML = '<div class="notification-error">الرجاء تسجيل الدخول للوحة الإدارة</div>';
                    notificationBadge.style.display = 'none';
                    throw new Error('Unauthorized');
                }
                // Ensure JSON
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Invalid response type');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    displayNotifications(data.notifications);
                    updateBadge(data.count);
                } else {
                    notificationsList.innerHTML = '<div class="notification-error">فشل تحميل الإشعارات</div>';
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                if (!notificationsList.innerHTML) {
                    notificationsList.innerHTML = '<div class="notification-error">فشل تحميل الإشعارات</div>';
                }
            });
    }
    
    // Display notifications
    function displayNotifications(notifications) {
        if (notifications.length === 0) {
            notificationsList.innerHTML = '<div class="notification-empty">لا توجد إشعارات جديدة</div>';
            return;
        }
        
        notificationsList.innerHTML = notifications.map(notification => `
            <div class="notification-item ${notification.is_read ? 'read' : 'unread'}" data-id="${notification.id}">
                <div class="notification-icon ${notification.type}">
                    ${getNotificationIcon(notification.type)}
                </div>
                <div class="notification-content">
                    <h4>${notification.title}</h4>
                    <p>${notification.message}</p>
                    <span class="notification-time">${formatTime(notification.created_at)}</span>
                </div>
                ${notification.link ? `<a href="${notification.link}" class="notification-link">عرض</a>` : ''}
            </div>
        `).join('');
        
        // Add click handlers
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const id = this.dataset.id;
                markAsRead(id);
                
                const link = this.querySelector('.notification-link');
                if (link) {
                    window.location.href = link.href;
                }
            });
        });
    }
    
    // Get notification icon
    function getNotificationIcon(type) {
        const icons = {
            'new_order': '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M3 3H17L15 13H5L3 3Z" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="17" r="1" fill="currentColor"/><circle cx="14" cy="17" r="1" fill="currentColor"/></svg>',
            'order_status': '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4 10L8 14L16 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'low_stock': '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2L2 6V14L10 18L18 14V6L10 2Z" stroke="currentColor" stroke-width="2"/></svg>'
        };
        return icons[type] || icons['new_order'];
    }
    
    // Format time
    function formatTime(datetime) {
        const date = new Date(datetime);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);
        
        if (diff < 60) return 'الآن';
        if (diff < 3600) return Math.floor(diff / 60) + ' دقيقة';
        if (diff < 86400) return Math.floor(diff / 3600) + ' ساعة';
        if (diff < 604800) return Math.floor(diff / 86400) + ' يوم';
        
        return date.toLocaleDateString('ar-EG');
    }
    
    // Update badge
    function updateBadge(count) {
        if (count > 0) {
            notificationBadge.textContent = count > 99 ? '99+' : count;
            notificationBadge.style.display = 'flex';
            
            // Play sound if count increased
            if (count > lastNotificationCount) {
                playSound();
            }
            lastNotificationCount = count;
        } else {
            notificationBadge.style.display = 'none';
            lastNotificationCount = 0;
        }
    }
    
    // Mark as read
    function markAsRead(id) {
        fetch(`${API_BASE}/admin/notifications/read/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }
    
    // Mark all as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            fetch(`${API_BASE}/admin/notifications/read-all`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => console.error('Error marking all as read:', error));
        });
    }
    
    // Check for new notifications every 30 seconds
    function checkNotifications() {
        fetch(`${API_BASE}/admin/notifications`)
            .then(async (response) => {
                if (response.status === 401) {
                    // Stop updating badge if not authorized
                    notificationBadge.style.display = 'none';
                    throw new Error('Unauthorized');
                }
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Invalid response type');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    updateBadge(data.count);
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
    }
    
    // Initialize
    initSound();
    unlockAudio();
    loadNotifications();
    setInterval(checkNotifications, 30000); // Check every 30 seconds
})();
