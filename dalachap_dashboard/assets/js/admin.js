// =============================================
// Admin Dashboard Functions
// =============================================

// Load admin dashboard statistics
async function loadAdminStats() {
    const result = await apiRequest(API_ENDPOINTS.adminDashboard);
    
    if (result.success && result.data) {
        document.getElementById('total-users').textContent = result.data.stats?.total_users || 0;
        document.getElementById('total-routes').textContent = result.data.stats?.total_routes || 0;
        document.getElementById('total-vehicles').textContent = result.data.stats?.total_vehicles || 0;
        document.getElementById('total-associations').textContent = result.data.stats?.total_associations || 0;
        document.getElementById('total-trips-today').textContent = result.data.stats?.trips_today || 0;
    }
}

// Load route management data
async function loadRoutes() {
    const result = await apiRequest(API_ENDPOINTS.getRoutes + '?status=all');
    
    if (result.success && result.data.routes) {
        let html = '<table class="data-table"><thead><tr><th>Code</th><th>Route Name</th><th>Start</th><th>End</th><th>Fare</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        for (const route of result.data.routes) {
            const statusClass = route.status === 'active' ? 'badge-success' : 'badge-danger';
            html += `
                <tr>
                    <td>${route.route_code}</td>
                    <td>${route.route_name}</td>
                    <td>${route.starting_point}</td>
                    <td>${route.ending_point}</td>
                    <td>TSh ${route.base_fare}</td>
                    <td><span class="badge ${statusClass}">${route.status}</span></td>
                    <td>
                        <button class="btn btn-sm" onclick="editRoute(${route.route_id})">✏️</button>
                        <button class="btn btn-sm btn-danger" onclick="toggleRouteStatus(${route.route_id})">🔘</button>
                    </td>
                </tr>
            `;
        }
        
        html += '</tbody></table>';
        document.getElementById('routes-container').innerHTML = html;
    }
}

// Load vehicles management data
async function loadVehicles() {
    const result = await apiRequest(API_ENDPOINTS.getVehicles + '?all=true');
    
    if (result.success && result.data.vehicles) {
        let html = '<table class="data-table"><thead><tr><th>Reg Number</th><th>Owner</th><th>Association</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        for (const vehicle of result.data.vehicles) {
            const statusClass = vehicle.status === 'active' ? 'badge-success' : 
                               (vehicle.status === 'maintenance' ? 'badge-warning' : 'badge-danger');
            html += `
                <tr>
                    <td>${vehicle.registration_number}</td>
                    <td>${vehicle.owner_name}</td>
                    <td>${vehicle.association_name || '-'}</td>
                    <td>${vehicle.capacity}</td>
                    <td><span class="badge ${statusClass}">${vehicle.status}</span></td>
                    <td>
                        <button class="btn btn-sm" onclick="editVehicle(${vehicle.vehicle_id})">✏️</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteVehicle(${vehicle.vehicle_id})">🗑️</button>
                    </td>
                </tr>
            `;
        }
        
        html += '</tbody></table>';
        document.getElementById('vehicles-container').innerHTML = html;
    }
}

// Load associations management data
async function loadAssociations() {
    const result = await apiRequest(API_ENDPOINTS.getAssociations);
    
    if (result.success && result.data.associations) {
        let html = '<table class="data-table"><thead><tr><th>Name</th><th>Registration No</th><th>Phone</th><th>Chairman</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        for (const assoc of result.data.associations) {
            const statusClass = assoc.status === 'active' ? 'badge-success' : 'badge-danger';
            html += `
                <tr>
                    <td>${assoc.association_name}</td>
                    <td>${assoc.registration_number}</td>
                    <td>${assoc.phone_number}</td>
                    <td>${assoc.chairman_name || '-'}</td>
                    <td><span class="badge ${statusClass}">${assoc.status}</span></td>
                    <td>
                        <button class="btn btn-sm" onclick="editAssociation(${assoc.association_id})">✏️</button>
                        <button class="btn btn-sm btn-danger" onclick="toggleAssociationStatus(${assoc.association_id})">🔘</button>
                    </td>
                </tr>
            `;
        }
        
        html += '</tbody></table>';
        document.getElementById('associations-container').innerHTML = html;
    }
}

// Load system logs
async function loadSystemLogs() {
    const result = await apiRequest(API_ENDPOINTS.getSystemLogs + '?limit=50');
    
    if (result.success && result.data.logs) {
        let html = '<table class="data-table"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>IP Address</th></tr></thead><tbody>';
        
        for (const log of result.data.logs) {
            html += `
                <tr>
                    <td>${formatDate(log.created_at)}</td>
                    <td>${log.user_name || 'System'}</td>
                    <td>${log.action}</td>
                    <td>${log.ip_address || '-'}</td>
                </tr>
            `;
        }
        
        html += '</tbody></table>';
        document.getElementById('logs-container').innerHTML = html;
    }
}

// Add new route
async function addRoute(routeData) {
    const result = await apiRequest(API_ENDPOINTS.addRoute, 'POST', routeData);
    if (result.success) {
        showToast('Route added successfully', 'success');
        loadRoutes();
        return true;
    }
    showToast(result.message, 'error');
    return false;
}

// Edit route
async function editRoute(routeId) {
    const result = await apiRequest(`${API_ENDPOINTS.getRouteDetails}?route_id=${routeId}`);
    if (result.success && result.data) {
        // Populate edit form
        document.getElementById('edit-route-id').value = result.data.route_id;
        document.getElementById('edit-route-code').value = result.data.route_code;
        document.getElementById('edit-route-name').value = result.data.route_name;
        document.getElementById('edit-starting-point').value = result.data.starting_point;
        document.getElementById('edit-ending-point').value = result.data.ending_point;
        document.getElementById('edit-distance').value = result.data.distance_km;
        document.getElementById('edit-fare').value = result.data.base_fare;
        document.getElementById('edit-route-modal').style.display = 'flex';
    }
}

// Toggle route status
async function toggleRouteStatus(routeId) {
    const result = await apiRequest(API_ENDPOINTS.toggleRouteStatus, 'POST', { route_id: routeId });
    if (result.success) {
        showToast('Route status updated', 'success');
        loadRoutes();
    } else {
        showToast(result.message, 'error');
    }
}