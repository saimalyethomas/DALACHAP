// =============================================
// Association Functions
// =============================================

// Load association vehicles
async function loadMyVehicles() {
    const result = await apiRequest(API_ENDPOINTS.getAssociationVehicles);
    
    if (result.success && result.data.vehicles) {
        let html = '<div class="stats-grid">';
        
        for (const vehicle of result.data.vehicles) {
            const statusDot = vehicle.current_latitude ? '🟢' : '⚫';
            const statusClass = vehicle.status === 'active' ? 'badge-success' : 
                               (vehicle.status === 'maintenance' ? 'badge-warning' : 'badge-danger');
            
            html += `
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h4>${statusDot} ${vehicle.registration_number}</h4>
                            <p>Owner: ${vehicle.owner_name}</p>
                            <p>Capacity: ${vehicle.capacity} seats</p>
                            <p>Status: <span class="badge ${statusClass}">${vehicle.status}</span></p>
                            <p>Driver: ${vehicle.driver_name || 'Not assigned'}</p>
                        </div>
                        <div>
                            <button class="btn btn-sm" onclick="editVehicle(${vehicle.vehicle_id})">✏️ Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="toggleVehicleStatus(${vehicle.vehicle_id})">🔘 Status</button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
        document.getElementById('vehicles-container').innerHTML = html || '<div class="card">No vehicles registered</div>';
    }
}

// Load association drivers
async function loadDrivers() {
    const result = await apiRequest(API_ENDPOINTS.getAssociationDrivers);
    
    if (result.success && result.data.drivers) {
        let html = '<table class="data-table"><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Assigned Vehicle</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        for (const driver of result.data.drivers) {
            html += `
                <tr>
                    <td>${driver.full_name}</td>
                    <td>${driver.phone_number}</td>
                    <td>${driver.email}</td>
                    <td>${driver.assigned_vehicle || 'Not assigned'}</td>
                    <td><span class="badge ${driver.is_active ? 'badge-success' : 'badge-danger'}">${driver.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>
                        <button class="btn btn-sm" onclick="editDriver(${driver.user_id})">✏️</button>
                        <button class="btn btn-sm" onclick="assignVehicle(${driver.user_id})">🚐 Assign</button>
                    </td>
                </tr>
            `;
        }
        
        html += '</tbody></table>';
        document.getElementById('drivers-container').innerHTML = html;
    }
}

// Load trip history
async function loadTripHistory() {
    const result = await apiRequest(API_ENDPOINTS.getAssociationTrips + '?limit=50');
    
    if (result.success && result.data.trips) {
        let html = '<table class="data-table"><thead><tr><th>Date</th><th>Vehicle</th><th>Driver</th><th>Route</th><th>Passengers</th><th>Duration</th><th>Status</th></tr></thead><tbody>';
        
        for (const trip of result.data.trips) {
            const statusClass = trip.trip_status === 'completed' ? 'badge-success' : 'badge-warning';
            html += `
                <tr>
                    <td>${formatDate(trip.start_time)}</td>
                    <td>${trip.registration_number}</td>
                    <td>${trip.driver_name}</td>
                    <td>${trip.route_name}</td>
                    <td>${trip.passenger_count || 0}</td>
                    <td>${trip.duration_minutes || '-'} min</td>
                    <td><span class="badge ${statusClass}">${trip.trip_status}</span></td>
                </tr>
            `;
        }
        
        html += '</tbody></table>';
        document.getElementById('trips-container').innerHTML = html;
    }
}

// Load association reports
async function loadReports() {
    // Load summary stats
    const statsResult = await apiRequest(API_ENDPOINTS.associationDashboard);
    
    if (statsResult.success && statsResult.data) {
        document.getElementById('total-vehicles-report').textContent = statsResult.data.stats?.total_vehicles || 0;
        document.getElementById('total-drivers-report').textContent = statsResult.data.stats?.total_drivers || 0;
        document.getElementById('total-trips-month').textContent = statsResult.data.stats?.month_trips || 0;
        document.getElementById('total-passengers-month').textContent = statsResult.data.stats?.total_passengers || 0;
        document.getElementById('avg-occupancy-report').textContent = `${statsResult.data.stats?.avg_occupancy || 0}%`;
        document.getElementById('total-earnings').textContent = `TSh ${(statsResult.data.stats?.total_earnings || 0).toLocaleString()}`;
    }
    
    // Load daily trips chart data
    const tripsResult = await apiRequest(API_ENDPOINTS.getDailyTrips);
    if (tripsResult.success && tripsResult.data) {
        renderDailyTripsChart(tripsResult.data);
    }
}

// Render daily trips chart
function renderDailyTripsChart(data) {
    const container = document.getElementById('daily-trips-chart');
    if (!container) return;
    
    let html = '<div style="display: flex; align-items: flex-end; gap: 15px; justify-content: center; height: 200px;">';
    
    const maxValue = Math.max(...data.map(d => d.count), 1);
    
    for (const day of data) {
        const height = (day.count / maxValue) * 150;
        html += `
            <div style="text-align: center;">
                <div style="height: ${height}px; width: 40px; background: linear-gradient(to top, #667eea, #764ba2); border-radius: 8px;"></div>
                <div style="margin-top: 8px; font-size: 12px;">${day.day_short}</div>
                <div style="font-size: 11px; color: #666;">${day.count}</div>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
}

// Add new vehicle
async function addVehicle(vehicleData) {
    const result = await apiRequest(API_ENDPOINTS.addVehicle, 'POST', vehicleData);
    if (result.success) {
        showToast('Vehicle added successfully', 'success');
        loadMyVehicles();
        return true;
    }
    showToast(result.message, 'error');
    return false;
}

// Edit vehicle
async function editVehicle(vehicleId) {
    const result = await apiRequest(`${API_ENDPOINTS.getVehicles}?vehicle_id=${vehicleId}`);
    if (result.success && result.data.vehicle) {
        document.getElementById('edit-vehicle-id').value = result.data.vehicle.vehicle_id;
        document.getElementById('edit-reg-number').value = result.data.vehicle.registration_number;
        document.getElementById('edit-owner-name').value = result.data.vehicle.owner_name;
        document.getElementById('edit-owner-phone').value = result.data.vehicle.owner_phone;
        document.getElementById('edit-capacity').value = result.data.vehicle.capacity;
        document.getElementById('edit-vehicle-modal').style.display = 'flex';
    }
}

// Toggle vehicle status
async function toggleVehicleStatus(vehicleId) {
    const result = await apiRequest(API_ENDPOINTS.toggleVehicleStatus, 'POST', { vehicle_id: vehicleId });
    if (result.success) {
        showToast('Vehicle status updated', 'success');
        loadMyVehicles();
    } else {
        showToast(result.message, 'error');
    }
}

// Add driver
async function addDriver(driverData) {
    const result = await apiRequest(API_ENDPOINTS.addDriver, 'POST', driverData);
    if (result.success) {
        showToast('Driver added successfully', 'success');
        loadDrivers();
        return true;
    }
    showToast(result.message, 'error');
    return false;
}

// Assign vehicle to driver
async function assignVehicle(driverId) {
    // Show assignment modal
    const vehiclesResult = await apiRequest(API_ENDPOINTS.getAssociationVehicles);
    if (vehiclesResult.success) {
        let vehicleOptions = '';
        for (const vehicle of vehiclesResult.data.vehicles) {
            vehicleOptions += `<option value="${vehicle.vehicle_id}">${vehicle.registration_number}</option>`;
        }
        
        document.getElementById('assign-driver-id').value = driverId;
        document.getElementById('assign-vehicle-select').innerHTML = vehicleOptions;
        document.getElementById('assign-vehicle-modal').style.display = 'flex';
    }
}

// Submit vehicle assignment
async function submitAssignment() {
    const driverId = document.getElementById('assign-driver-id').value;
    const vehicleId = document.getElementById('assign-vehicle-select').value;
    
    const result = await apiRequest(API_ENDPOINTS.assignVehicle, 'POST', { driver_id: driverId, vehicle_id: vehicleId });
    if (result.success) {
        showToast('Vehicle assigned successfully', 'success');
        closeAssignModal();
        loadDrivers();
    } else {
        showToast(result.message, 'error');
    }
}