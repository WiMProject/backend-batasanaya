// ============================================
// ADMIN DASHBOARD
// ============================================

let selectedAssets = [];

const getToken = () => localStorage.getItem('adminToken');
const getHeaders = () => ({
    'Authorization': `Bearer ${getToken()}`,
    'Content-Type': 'application/json'
});

function checkAuth() {
    if (!getToken()) {
        window.location.href = '/admin/login';
        return false;
    }
    return true;
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('adminToken');
        window.location.href = '/admin/login';
    }
}

async function refreshStats() {
    try {
        const response = await fetch(`${API_BASE}/admin/stats`, {
            headers: getHeaders()
        });
        
        if (!response.ok) throw new Error('Failed to fetch stats');
        
        const stats = await response.json();
        
        document.getElementById('totalUsers').textContent = stats.users || 0;
        document.getElementById('totalAssets').textContent = stats.assets || 0;
        document.getElementById('storageUsed').textContent = `${stats.storage_mb || 0} MB`;
        document.getElementById('totalBackgrounds').textContent = stats.backgrounds || 0;
        document.getElementById('totalSongs').textContent = stats.songs || 0;
        document.getElementById('totalVideos').textContent = stats.videos || 0;
    } catch (error) {
        console.error('Error fetching stats:', error);
        showAlert('Failed to load statistics', 'danger');
    }
}

async function loadUsers() {
    try {
        const response = await fetch(`${API_BASE}/admin/users`, {
            headers: getHeaders()
        });
        
        if (!response.ok) throw new Error('Failed to fetch users');
        
        const data = await response.json();
        const tbody = document.querySelector('#usersTable tbody');
        
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No users found</td></tr>';
            return;
        }
        
        tbody.innerHTML = data.data.map(user => `
            <tr>
                <td>${user.full_name}</td>
                <td>${user.email}</td>
                <td>${user.phone_number || '-'}</td>
                <td><span class="badge bg-${user.role?.name === 'admin' ? 'danger' : 'primary'}">${user.role?.name || 'user'}</span></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewGameProgress('${user.id}', '${user.full_name}')">
                        <i class="fas fa-gamepad"></i> View Progress
                    </button>
                </td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editUser('${user.id}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser('${user.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading users:', error);
        showAlert('Failed to load users', 'danger');
    }
}

async function viewGameProgress(userId, userName) {
    document.getElementById('gameProgressUserName').textContent = userName;
    
    try {
        const response = await fetch(`${API_BASE}/admin/users/${userId}/game-progress`, {
            headers: getHeaders()
        });
        
        if (!response.ok) throw new Error('Failed to fetch game progress');
        
        const data = await response.json();
        
        const cariStats = data.carihijaiyah?.stats || {};
        document.getElementById('cariStats').innerHTML = `
            <div class="row text-center">
                <div class="col-md-4">
                    <h4>${cariStats.total_levels_completed || 0}/15</h4>
                    <small class="text-muted">Levels Completed</small>
                </div>
                <div class="col-md-4">
                    <h4>${cariStats.total_sessions || 0}</h4>
                    <small class="text-muted">Total Sessions</small>
                </div>
                <div class="col-md-4">
                    <h4>${Math.round((cariStats.total_levels_completed || 0) / 15 * 100)}%</h4>
                    <small class="text-muted">Completion Rate</small>
                </div>
            </div>
        `;
        
        const cariProgress = data.carihijaiyah?.progress || [];
        document.getElementById('cariProgress').innerHTML = cariProgress.map(level => `
            <tr>
                <td>Level ${level.level_number}</td>
                <td><span class="badge bg-${level.is_unlocked ? 'success' : 'secondary'}">${level.is_unlocked ? 'Unlocked' : 'Locked'}</span></td>
                <td><span class="badge bg-${level.is_completed ? 'primary' : 'warning'}">${level.is_completed ? 'Completed' : 'Not Completed'}</span></td>
                <td>${level.attempts || 0}</td>
            </tr>
        `).join('');
        
        const cariSessions = data.carihijaiyah?.recent_sessions || [];
        document.getElementById('cariSessions').innerHTML = cariSessions.length > 0 
            ? cariSessions.map(session => `
                <tr>
                    <td>Level ${session.level_number}</td>
                    <td>${new Date(session.completed_at).toLocaleString()}</td>
                </tr>
            `).join('')
            : '<tr><td colspan="2" class="text-center">No sessions yet</td></tr>';
        
        const pasangkanStats = data.pasangkanhuruf?.stats || {};
        document.getElementById('pasangkanStats').innerHTML = `
            <div class="row text-center">
                <div class="col-md-4">
                    <h4>${pasangkanStats.total_levels_completed || 0}/15</h4>
                    <small class="text-muted">Levels Completed</small>
                </div>
                <div class="col-md-4">
                    <h4>${pasangkanStats.total_sessions || 0}</h4>
                    <small class="text-muted">Total Sessions</small>
                </div>
                <div class="col-md-4">
                    <h4>${Math.round((pasangkanStats.total_levels_completed || 0) / 15 * 100)}%</h4>
                    <small class="text-muted">Completion Rate</small>
                </div>
            </div>
        `;
        
        const pasangkanProgress = data.pasangkanhuruf?.progress || [];
        document.getElementById('pasangkanProgress').innerHTML = pasangkanProgress.map(level => `
            <tr>
                <td>Level ${level.level_number}</td>
                <td><span class="badge bg-${level.is_unlocked ? 'success' : 'secondary'}">${level.is_unlocked ? 'Unlocked' : 'Locked'}</span></td>
                <td><span class="badge bg-${level.is_completed ? 'primary' : 'warning'}">${level.is_completed ? 'Completed' : 'Not Completed'}</span></td>
                <td>${level.attempts || 0}</td>
            </tr>
        `).join('');
        
        const pasangkanSessions = data.pasangkanhuruf?.recent_sessions || [];
        document.getElementById('pasangkanSessions').innerHTML = pasangkanSessions.length > 0 
            ? pasangkanSessions.map(session => `
                <tr>
                    <td>Level ${session.level_number}</td>
                    <td>${new Date(session.completed_at).toLocaleString()}</td>
                </tr>
            `).join('')
            : '<tr><td colspan="2" class="text-center">No sessions yet</td></tr>';
        
        new bootstrap.Modal(document.getElementById('gameProgressModal')).show();
    } catch (error) {
        console.error('Error loading game progress:', error);
        showAlert('Failed to load game progress', 'danger');
    }
}

const subcategoryMap = {
    game: ['carihijaiyah', 'pasangkanhuruf', 'level1', 'level2'],
    ui: ['buttons', 'icons', 'panels', 'menus'],
    menu: ['main', 'settings', 'profile'],
    tutorial: ['step1', 'step2', 'step3']
};

async function loadAssets() {
    try {
        const search = document.getElementById('assetSearch')?.value || '';
        const category = document.getElementById('assetCategory')?.value || '';
        const subcategory = document.getElementById('assetSubcategory')?.value || '';
        
        const params = new URLSearchParams({
            ...(search && { search }),
            ...(category && { category }),
            ...(subcategory && { subcategory })
        });
        
        const response = await fetch(`${API_BASE}/admin/assets?${params}`, {
            headers: getHeaders()
        });
        
        if (!response.ok) throw new Error('Failed to fetch assets');
        
        const data = await response.json();
        const tbody = document.querySelector('#assetsTable tbody');
        
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">No assets found</td></tr>';
            return;
        }
        
        tbody.innerHTML = data.data.map(asset => `
            <tr>
                <td><input type="checkbox" class="asset-checkbox" value="${asset.id}" onchange="updateSelectedAssets()"></td>
                <td>
                    ${asset.type === 'image' 
                        ? `<img src="/api/assets/${asset.id}/file" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">` 
                        : '<i class="fas fa-file-audio fa-2x text-muted"></i>'}
                </td>
                <td>${asset.file_name}</td>
                <td><span class="badge bg-info">${asset.category || '-'}</span></td>
                <td><span class="badge bg-secondary">${asset.subcategory || '-'}</span></td>
                <td><span class="badge bg-primary">${asset.type}</span></td>
                <td>${(asset.size / 1024).toFixed(2)} KB</td>
                <td>${asset.created_by?.full_name || '-'}</td>
                <td>${new Date(asset.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editAsset('${asset.id}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteAsset('${asset.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading assets:', error);
        showAlert('Failed to load assets', 'danger');
    }
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => alertDiv.remove(), 3000);
}

function uploadAsset() {
    const category = document.getElementById('assetCategory')?.value;
    const subcategory = document.getElementById('assetSubcategory')?.value;
    
    if (!category) {
        showAlert('Please select a category first!', 'warning');
        return;
    }
    
    document.getElementById('uploadAssetFile').click();
}

async function handleAssetUpload(files) {
    if (!files || files.length === 0) return;
    
    const category = document.getElementById('assetCategory').value;
    const subcategory = document.getElementById('assetSubcategory').value;
    
    if (!category) {
        showAlert('Please select a category!', 'warning');
        return;
    }
    
    const formData = new FormData();
    Array.from(files).forEach(file => {
        formData.append('files[]', file);
    });
    formData.append('category', category);
    if (subcategory) formData.append('subcategory', subcategory);
    
    try {
        showAlert(`Uploading ${files.length} file(s)...`, 'info');
        
        const response = await fetch(`${API_BASE}/assets/batch`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${getToken()}` },
            body: formData
        });
        
        if (!response.ok) {
            const text = await response.text();
            console.error('Backend error:', text);
            throw new Error('Upload failed');
        }
        
        const result = await response.json();
        showAlert(`✅ ${result.uploaded?.length || 0} files uploaded`, 'success');
        loadAssets();
        document.getElementById('uploadAssetFile').value = '';
    } catch (error) {
        console.error('Upload error:', error);
        showAlert('Upload failed', 'danger');
    }
}

async function updateAsset() {
    const assetId = document.getElementById('editAssetId').value;
    const category = document.getElementById('editAssetCategory').value;
    const subcategory = document.getElementById('editAssetSubcategory').value;
    const fileInput = document.getElementById('editAssetFile');
    
    const formData = new FormData();
    if (category) formData.append('category', category);
    if (subcategory) formData.append('subcategory', subcategory);
    if (fileInput.files.length > 0) formData.append('file', fileInput.files[0]);
    
    try {
        const response = await fetch(`${API_BASE}/assets/${assetId}`, {
            method: 'PATCH',
            headers: { 'Authorization': `Bearer ${getToken()}` },
            body: formData
        });
        
        if (!response.ok) throw new Error('Failed to update asset');
        
        showAlert('Asset updated successfully', 'success');
        bootstrap.Modal.getInstance(document.getElementById('editAssetModal')).hide();
        loadAssets();
    } catch (error) {
        console.error('Error updating asset:', error);
        showAlert('Failed to update asset', 'danger');
    }
}

function editAsset(assetId) {
    fetch(`${API_BASE}/assets`, { headers: getHeaders() })
    .then(res => res.json())
    .then(data => {
        const asset = data.data.find(a => a.id === assetId);
        if (asset) {
            document.getElementById('editAssetId').value = asset.id;
            document.getElementById('editAssetCategory').value = asset.category || '';
            document.getElementById('editAssetSubcategory').value = asset.subcategory || '';
            
            new bootstrap.Modal(document.getElementById('editAssetModal')).show();
        }
    });
}

async function deleteAsset(assetId) {
    if (!confirm('Delete this asset?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/assets/${assetId}`, {
            method: 'DELETE',
            headers: getHeaders()
        });
        
        if (!response.ok) throw new Error('Failed to delete asset');
        
        showAlert('Asset deleted successfully', 'success');
        loadAssets();
    } catch (error) {
        console.error('Error deleting asset:', error);
        showAlert('Failed to delete asset', 'danger');
    }
}

function updateSelectedAssets() {
    selectedAssets = Array.from(document.querySelectorAll('.asset-checkbox:checked')).map(cb => cb.value);
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.disabled = selectedAssets.length === 0;
    }
}


document.addEventListener('DOMContentLoaded', function() {
    if (!checkAuth()) return;
    
    refreshStats();
    
    const categorySelect = document.getElementById('assetCategory');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const subcategorySelect = document.getElementById('assetSubcategory');
            const category = this.value;
            
            if (category) {
                subcategorySelect.disabled = false;
                subcategorySelect.innerHTML = '<option value="">-- All Subcategories --</option>';
                
                if (subcategoryMap[category]) {
                    subcategoryMap[category].forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub;
                        option.textContent = sub.charAt(0).toUpperCase() + sub.slice(1);
                        subcategorySelect.appendChild(option);
                    });
                }
            } else {
                subcategorySelect.disabled = true;
                subcategorySelect.innerHTML = '<option value="">-- All Subcategories --</option>';
            }
            
            loadAssets();
        });
    }
    
    document.getElementById('assetSearch')?.addEventListener('input', () => loadAssets());
    document.getElementById('assetSubcategory')?.addEventListener('change', () => loadAssets());
    
    document.getElementById('uploadAssetFile')?.addEventListener('change', (e) => {
        handleAssetUpload(e.target.files);
    });
    
    document.querySelectorAll('#adminTabs a[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('href');
            
            if (target === '#users') loadUsers();
            else if (target === '#assets') loadAssets();
        });
    });
});
