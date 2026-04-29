// =============================================
// Common Functions
// =============================================

// Make authenticated API request
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Authorization': 'Bearer ' + getToken(),
            'Content-Type': 'application/json'
        }
    };
    
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(endpoint, options);
        const result = await response.json();
        
        if (result.success) {
            return { success: true, data: result.data };
        } else {
            return { success: false, message: result.message };
        }
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Network error. Please try again.' };
    }
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-TZ', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Format time ago
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return `${seconds} seconds ago`;
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} minutes ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hours ago`;
    const days = Math.floor(hours / 24);
    return `${days} days ago`;
}

// Show loading indicator
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="loading-spinner"><div class="spinner"></div> Loading...</div>';
    }
}

// Hide loading indicator
function hideLoading(elementId, content) {
    const element = document.getElementById(elementId);
    if (element && content) {
        element.innerHTML = content;
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        // Create container if not exists
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.bottom = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    document.getElementById('toast-container').appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentElement) toast.remove();
    }, 3000);
}

// Update notification badge
async function updateNotificationBadge() {
    const badge = document.getElementById('notification-badge');
    if (!badge) return;
    
    const result = await apiRequest(API_ENDPOINTS.getNotifications + '?limit=1&unread_only=1');
    if (result.success && result.data.unread_count > 0) {
        badge.style.display = 'inline-block';
        badge.textContent = result.data.unread_count > 9 ? '9+' : result.data.unread_count;
    } else {
        badge.style.display = 'none';
    }
}

// Load notifications dropdown
async function loadNotifications() {
    const container = document.getElementById('notifications-dropdown');
    if (!container) return;
    
    const result = await apiRequest(API_ENDPOINTS.getNotifications + '?limit=10');
    if (result.success && result.data.notifications.length > 0) {
        let html = '';
        for (const notif of result.data.notifications) {
            html += `
                <div class="notification-item ${notif.is_read ? 'read' : 'unread'}" data-id="${notif.notification_id}">
                    <div class="notification-icon">${getNotificationIcon(notif.notification_type)}</div>
                    <div class="notification-content">
                        <div class="notification-title">${notif.title}</div>
                        <div class="notification-message">${notif.message}</div>
                        <div class="notification-time">${timeAgo(notif.created_at)}</div>
                    </div>
                </div>
            `;
        }
        container.innerHTML = html;
        
        // Add click handlers
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => markNotificationRead(item.dataset.id));
        });
    } else {
        container.innerHTML = '<div class="notification-empty">No notifications</div>';
    }
}

function getNotificationIcon(type) {
    const icons = {
        'demand_alert': '🚨',
        'route_change': '🔄',
        'authorization': '📋',
        'system': '⚙️',
        'general': '📢'
    };
    return icons[type] || '🔔';
}

async function markNotificationRead(notificationId) {
    await apiRequest(API_ENDPOINTS.markNotificationRead, 'POST', { notification_id: notificationId });
    updateNotificationBadge();
    loadNotifications();
}

// Load user profile
function loadUserProfile() {
    const user = getCurrentUser();
    if (user) {
        const userNameElement = document.getElementById('user-name');
        const userRoleElement = document.getElementById('user-role');
        const userAvatar = document.getElementById('user-avatar');
        
        if (userNameElement) userNameElement.textContent = user.full_name;
        if (userRoleElement) {
            let roleDisplay = user.role.replace('_', ' ').toUpperCase();
            userRoleElement.textContent = roleDisplay;
        }
        if (userAvatar) {
            userAvatar.textContent = user.full_name.charAt(0).toUpperCase();
        }
    }
}

// Initialize common elements
function initCommon() {
    loadUserProfile();
    updateNotificationBadge();
    
    // Load notifications every 30 seconds
    setInterval(() => {
        updateNotificationBadge();
        loadNotifications();
    }, 30000);
    
    // Setup logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            logout();
        });
    }
    
    // Setup mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }
}