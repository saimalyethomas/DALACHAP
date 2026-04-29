// =============================================
// Authentication Functions
// =============================================

// Store auth data
function setAuth(token, user) {
    localStorage.setItem(APP_CONFIG.tokenKey, token);
    localStorage.setItem(APP_CONFIG.userKey, JSON.stringify(user));
}

// Get auth token
function getToken() {
    return localStorage.getItem(APP_CONFIG.tokenKey);
}

// Get current user
function getCurrentUser() {
    const userStr = localStorage.getItem(APP_CONFIG.userKey);
    if (userStr) {
        return JSON.parse(userStr);
    }
    return null;
}

// Check if user is logged in
function isLoggedIn() {
    return getToken() !== null && getCurrentUser() !== null;
}

// Logout user
function logout() {
    // Call logout API
    fetch(API_ENDPOINTS.logout, {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + getToken(),
            'Content-Type': 'application/json'
        }
    }).catch(err => console.error('Logout error:', err));
    
    // Clear local storage
    localStorage.removeItem(APP_CONFIG.tokenKey);
    localStorage.removeItem(APP_CONFIG.userKey);
    
    // Redirect to login
    window.location.href = APP_BASE_URL + 'dalachap_dashboard/index.html';
}

// Login user
async function login(email, password) {
    try {
        const response = await fetch(API_ENDPOINTS.login, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            setAuth(data.data.token, data.data.user);
            return { success: true, user: data.data.user };
        } else {
            return { success: false, message: data.message };
        }
    } catch (error) {
        console.error('Login error:', error);
        return { success: false, message: 'Network error. Please try again.' };
    }
}

// Get dashboard URL based on role
function getDashboardUrl(role) {
    switch(role) {
        case 'admin':
            return APP_BASE_URL + 'pages/admin/admin-dashboard.html';
        case 'traffic_officer':
            return APP_BASE_URL + 'pages/officer/office-dashbord.html';
        case 'association_leader':
            return APP_BASE_URL + 'pages/association/association-dashbord.html';
        default:
            return APP_BASE_URL + 'dalachap_dashboard/index.html';
    }
}

// Redirect to appropriate dashboard
function redirectToDashboard() {
    const user = getCurrentUser();
    if (user && user.role) {
        window.location.href = getDashboardUrl(user.role);
    } else {
        window.location.href = APP_BASE_URL + 'dalachap_dashboard/index.html';
    }
}

// Check auth and redirect if not logged in
function requireAuth() {
    if (!isLoggedIn()) {
        window.location.href = APP_BASE_URL + 'dalachap_dashboard/index.html';
        return false;
    }
    return true;
}

// Check role and redirect if unauthorized
function requireRole(allowedRoles) {
    if (!requireAuth()) return false;
    
    const user = getCurrentUser();
    if (!allowedRoles.includes(user.role)) {
        alert('Access denied. You do not have permission to view this page.');
        window.location.href = getDashboardUrl(user.role);
        return false;
    }
    return true;
}