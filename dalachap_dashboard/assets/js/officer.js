// =============================================
// Traffic Officer Functions
// =============================================

// Load congestion reports
async function loadCongestionReports() {
    const result = await apiRequest(API_ENDPOINTS.getCongestedRoutes + '?all=true');
    
    if (result.success && result.data.reports) {
        let html = '<table class="data-table"><thead><tr><th>Route</th><th>Location</th><th>Waiting Count</th><th>Reported By</th><th>Time</th><th>Status</th><th>Action</th></tr></thead><tbody>';
        
        for (const report of result.data.reports) {
            const statusClass = report.status === 'active' ? 'badge-danger' : 'badge-success';
            html += `
                <tr>
                    <td>${report.route_name}</td>
                    <td>${report.stop_name || 'Unknown'}</td>
                    <td>${report.waiting_count}</td>
                    <td>${report.reported_by_name || 'Passenger'}</td>
                    <td>${timeAgo(report.reported_at)}</td>
                    <td><span class="badge ${statusClass}">${report.status || 'Active'}</span></td>
                    <td>
                        ${report.status === 'active' ? `<button class="btn btn-sm btn-success" onclick="resolveReport(${report.report_id})">Resolve</button>` : '-'}
                    </td>
                </tr>
            `;
        }
        
        html += '</tbody></table>';
        document.getElementById('reports-container').innerHTML = html;
    }
}

// Resolve congestion report
async function resolveReport(reportId) {
    const result = await apiRequest(API_ENDPOINTS.resolveCongestion, 'POST', { report_id: reportId });
    if (result.success) {
        showToast('Report marked as resolved', 'success');
        loadCongestionReports();
    } else {
        showToast(result.message, 'error');
    }
}

// Load authorization requests
async function loadAuthorizations() {
    const result = await apiRequest(API_ENDPOINTS.getAuthorizations + '?status=pending');
    
    if (result.success && result.data.authorizations) {
        let html = '';
        
        for (const auth of result.data.authorizations) {
            html += `
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h4>🚐 Vehicle: ${auth.registration_number}</h4>
                            <p><strong>From:</strong> ${auth.original_route_name || 'Original'}</p>
                            <p><strong>To:</strong> ${auth.temporary_route_name}</p>
                            <p><strong>Reason:</strong> ${auth.reason}</p>
                            <p><strong>Duration:</strong> Until ${formatDate(auth.end_datetime)}</p>
                            <p><strong>Requested by:</strong> ${auth.requested_by_name}</p>
                        </div>
                        <div>
                            <button class="btn btn-success" onclick="approveAuthorization(${auth.authorization_id})">✅ Approve</button>
                            <button class="btn btn-danger" onclick="rejectAuthorization(${auth.authorization_id})">❌ Reject</button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        document.getElementById('auth-container').innerHTML = html || '<div class="card">No pending authorization requests</div>';
    }
}

// Approve authorization
async function approveAuthorization(authId) {
    const result = await apiRequest(API_ENDPOINTS.approveAuth, 'POST', { authorization_id: authId });
    if (result.success) {
        showToast('Authorization approved', 'success');
        loadAuthorizations();
    } else {
        showToast(result.message, 'error');
    }
}

// Reject authorization
async function rejectAuthorization(authId) {
    if (confirm('Are you sure you want to reject this authorization?')) {
        const result = await apiRequest(API_ENDPOINTS.revokeAuth, 'POST', { authorization_id: authId, revoke_reason: 'Rejected by officer' });
        if (result.success) {
            showToast('Authorization rejected', 'success');
            loadAuthorizations();
        } else {
            showToast(result.message, 'error');
        }
    }
}

// Load route monitor data
async function loadRouteMonitor() {
    const result = await apiRequest(API_ENDPOINTS.getRoutes + '?status=active');
    
    if (result.success && result.data.routes) {
        let html = '<div class="stats-grid">';
        
        for (const route of result.data.routes) {
            // Get demand for this route
            const demandResult = await apiRequest(`${API_ENDPOINTS.getDemandStatus}?route_id=${route.route_id}`);
            const demand = demandResult.success ? demandResult.data : { waiting_count: 0, demand_level: 'low' };
            
            let demandBadge = '';
            if (demand.demand_level === 'high') demandBadge = '<span class="badge badge-danger">🔥 High Demand</span>';
            else if (demand.demand_level === 'medium') demandBadge = '<span class="badge badge-warning">⚠️ Medium</span>';
            else demandBadge = '<span class="badge badge-success">✅ Normal</span>';
            
            html += `
                <div class="card">
                    <div>
                        <h4>${route.route_name}</h4>
                        <p>${route.starting_point} → ${route.ending_point}</p>
                        <p>Fare: TSh ${route.base_fare} | Duration: ${route.estimated_duration_minutes} min</p>
                        <div>${demandBadge}</div>
                        <div>Waiting passengers: <strong>${demand.waiting_count || 0}</strong></div>
                        <button class="btn btn-sm btn-primary" onclick="viewRouteOnMap(${route.route_id})" style="margin-top: 10px;">📍 View on Map</button>
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
        document.getElementById('route-monitor-container').innerHTML = html;
    }
}

// Load vehicle tracking data
async function loadVehicleTracking() {
    const result = await apiRequest(API_ENDPOINTS.getVehicles);
    
    if (result.success && result.data.vehicles) {
        let html = '<div class="stats-grid">';
        
        for (const vehicle of result.data.vehicles) {
            const location = vehicle.current_location || { latitude: null, longitude: null };
            const statusDot = location.latitude ? '🟢' : '⚫';
            
            html += `
                <div class="card">
                    <div>
                        <div style="display: flex; justify-content: space-between;">
                            <h4>${statusDot} ${vehicle.registration_number}</h4>
                            <span class="badge ${vehicle.status === 'active' ? 'badge-success' : 'badge-danger'}">${vehicle.status}</span>
                        </div>
                        <p>Owner: ${vehicle.owner_name}</p>
                        <p>Association: ${vehicle.association_name || 'Independent'}</p>
                        <p>Last seen: ${location.recorded_at ? timeAgo(location.recorded_at) : 'Unknown'}</p>
                        <button class="btn btn-sm btn-primary" onclick="trackVehicle(${vehicle.vehicle_id})">📍 Track</button>
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
        document.getElementById('vehicle-tracking-container').innerHTML = html;
    }
}

// Track specific vehicle
function trackVehicle(vehicleId) {
    window.location.href = `vehicle-tracking.html?vehicle_id=${vehicleId}`;
}

// View route on map
function viewRouteOnMap(routeId) {
    window.location.href = `route-monitor.html?route_id=${routeId}`;
}