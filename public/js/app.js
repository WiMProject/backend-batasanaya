// Global variables
/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD - JavaScript
|--------------------------------------------------------------------------
| Organized sections:
| 1. Configuration & Global Variables
| 2. Initialization
| 3. Authentication & Token Management
| 4. Dashboard & Statistics
| 5. User Management
| 6. Asset Management
| 7. Background Management
| 8. Game Progress Monitoring
| 9. Utility Functions
| 10. Window Exports
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| 1. CONFIGURATION & GLOBAL VARIABLES
|--------------------------------------------------------------------------
*/
let adminToken = localStorage.getItem('adminToken');

// API Base URL - detect if using ngrok
const API_BASE = window.location.hostname.includes('ngrok') 
    ? window.location.origin + '/api'
    : '/api';

// Ngrok headers
const NGROK_HEADERS = window.location.hostname.includes('ngrok') 
    ? { 'ngrok-skip-browser-warning': 'true' }
    : {};

console.log('API Base URL:', API_BASE);
console.log('Using ngrok:', window.location.hostname.includes('ngrok'));



// Initialize app

/*
|--------------------------------------------------------------------------
| 2. INITIALIZATION
|--------------------------------------------------------------------------
*/
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on admin page
    if (window.location.pathname === '/admin') {
        initAdmin();
    } else if (window.location.pathname === '/admin/login') {
        return;
    }
    
    initAllUploadZones();
    
    document.getElementById('uploadSongFile')?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const title = prompt('Enter song title:');
            if (title) uploadSongFile(file, title);
        }
    });
    
    document.getElementById('uploadVideoFile')?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const title = prompt('Enter video title:');
            if (title) uploadVideoFile(file, title);
        }
    });
    
    document.getElementById('uploadBackgroundFile')?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const name = prompt('Enter background name:', file.name.replace(/\.[^/.]+$/, ''));
            if (name) uploadBackgroundFile(file, name);
        }
    });
    
    const editCategorySelect = document.getElementById('editAssetCategory');
    if (editCategorySelect) {
        editCategorySelect.addEventListener('change', function() {
            updateEditSubcategories(this.value);
        });
    }
});

// Admin initialization

/*
|--------------------------------------------------------------------------
| 3. AUTHENTICATION & TOKEN MANAGEMENT
|--------------------------------------------------------------------------
*/
// Initialize admin dashboard - verify token and setup event listeners
function initAdmin() {
    console.log('Initializing admin, token:', adminToken ? 'exists' : 'missing');
    
    // Check if user has valid token
    if (!adminToken) {
        console.log('No token, redirecting to login');
        window.location.replace('/admin/login');
        return;
    }
    
    // Verify token is still valid
    verifyTokenAndLoad();
    
    // Tab change handler
    document.querySelectorAll('#adminTabs a').forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('href').substring(1);
            console.log('Loading tab:', target);
            loadTabContent(target);
        });
    });
}

// Verify token and load dashboard
async function verifyTokenAndLoad() {
    console.log('Verifying token...');
    
    try {
        const response = await fetch('/api/auth/me', {
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        if (response.ok) {
            const user = await response.json();
            console.log('User role:', user.role?.name);
            
            if (user.role?.name === 'admin') {
                console.log('Admin verified, loading dashboard');
                loadDashboardData();
            } else {
                console.log('Not admin, clearing token');
                localStorage.removeItem('adminToken');
                adminToken = null;
                window.location.replace('/admin/login');
            }
        } else {
            console.log('Token invalid, status:', response.status);
            localStorage.removeItem('adminToken');
            adminToken = null;
            window.location.replace('/admin/login');
        }
    } catch (error) {
        console.log('Network error:', error);
        localStorage.removeItem('adminToken');
        adminToken = null;
        window.location.replace('/admin/login');
    }
}

// Admin login
async function adminLogin() {
    try {
        const response = await fetch('/api/auth/admin-login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.access_token) {
            adminToken = data.access_token;
            localStorage.setItem('adminToken', adminToken);
            loadDashboardData();
        }
    } catch (error) {
        console.error('Admin login failed:', error);
        showAlert('Admin login failed', 'danger');
    }
}

// Check and refresh token if needed
async function ensureValidToken() {
    if (!adminToken) {
        await adminLogin();
        return;
    }
    
    // Test token with a simple request
    try {
        const response = await fetch('/api/auth/me', {
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        if (response.status === 401) {
            console.log('Token expired, refreshing...');
            localStorage.removeItem('adminToken');
            adminToken = null;
            await adminLogin();
        }
    } catch (error) {
        console.log('Token validation error, refreshing...', error);
        localStorage.removeItem('adminToken');
        adminToken = null;
        await adminLogin();
    }
}

// Load dashboard data

/*
|--------------------------------------------------------------------------
| 4. DASHBOARD & STATISTICS
|--------------------------------------------------------------------------
*/
// Load dashboard statistics and recent activity
async function loadDashboardData() {
    try {
        const response = await fetch(`${API_BASE}/admin/stats`, {
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            }
        });
        
        const stats = await response.json();
        updateStatsCards(stats);
        updateRecentActivity(stats);
    } catch (error) {
        console.error('Failed to load stats:', error);
    }
}

// Update stats cards
function updateStatsCards(stats) {
    document.getElementById('totalUsers').textContent = stats.users || 0;
    document.getElementById('totalAssets').textContent = stats.assets || 0;
    document.getElementById('storageUsed').textContent = (stats.storage_mb || 0) + ' MB';
    document.getElementById('totalBackgrounds').textContent = stats.backgrounds || 0;
    document.getElementById('totalSongs').textContent = stats.songs || 0;
    document.getElementById('totalVideos').textContent = stats.videos || 0;
}

// Update recent activity
function updateRecentActivity(stats) {
    // Recent users
    const recentUsersHtml = stats.recent_users?.map(user => `
        <div class="d-flex align-items-center mb-2">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                <i class="fas fa-user text-white"></i>
            </div>
            <div>
                <div class="fw-bold">${user.full_name}</div>
                <small class="text-muted">${user.email}</small>
            </div>
        </div>
    `).join('') || '<p class="text-muted">No recent users</p>';
    
    document.getElementById('recentUsers').innerHTML = recentUsersHtml;
    
    // Recent assets
    const recentAssetsHtml = stats.recent_assets?.map(asset => `
        <div class="d-flex align-items-center mb-2">
            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                <i class="fas fa-${asset.type === 'image' ? 'image' : 'music'} text-white"></i>
            </div>
            <div>
                <div class="fw-bold">${asset.file_name}</div>
                <small class="text-muted">by ${asset.created_by?.full_name || 'Unknown'}</small>
            </div>
        </div>
    `).join('') || '<p class="text-muted">No recent assets</p>';
    
    document.getElementById('recentAssets').innerHTML = recentAssetsHtml;
}

// Load tab content
async function loadTabContent(tab) {
    switch(tab) {
        case 'users':
            await loadUsers();
            break;
        case 'assets':
            await loadAssets();
            break;
        case 'songs':
            await loadSongs();
            break;
        case 'videos':
            await loadVideos();
            break;
        case 'backgrounds':
            await loadBackgrounds();
            break;
        case 'dashboard':
            await loadDashboardData();
            break;
    }
}

// Load users

/*
|--------------------------------------------------------------------------
| 5. USER MANAGEMENT
|--------------------------------------------------------------------------
*/
// Load all users with game progress for admin table
async function loadUsers() {
    try {
        const response = await fetch('/api/admin/users', {
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        const data = await response.json();
        const tbody = document.querySelector('#usersTable tbody');
        
        if (data.data && data.data.length > 0) {
            tbody.innerHTML = data.data.map(user => {
                const progress = user.game_progress || {};
                const cari = progress.carihijaiyah || {};
                const pasangkan = progress.pasangkanhuruf || {};
                
                return `
                <tr>
                    <td>${user.full_name}</td>
                    <td>${user.email}</td>
                    <td>${user.phone_number || '-'}</td>
                    <td><span class="badge bg-${user.role?.name === 'admin' ? 'danger' : 'primary'}">${user.role?.name || 'user'}</span></td>
                    <td>
                        <div class="small">
                            <div class="mb-1">
                                <span class="badge bg-info">Cari</span> ${cari.completed || 0}/15
                            </div>
                            <div>
                                <span class="badge bg-success">Pasangkan</span> ${pasangkan.completed || 0}/15 <span class="text-warning">⭐${pasangkan.stars || 0}</span>
                            </div>
                        </div>
                    </td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-info me-1" onclick="viewGameProgress('${user.id}')" title="View Game Progress">
                            <i class="fas fa-gamepad"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser('${user.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No users found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    }
}

// View game progress

/*
|--------------------------------------------------------------------------
| 8. GAME PROGRESS MONITORING
|--------------------------------------------------------------------------
*/
// Display detailed game progress modal for both games (Cari Hijaiyyah & Pasangkan Huruf)
async function viewGameProgress(userId) {
    try {
        const response = await fetch(`/api/admin/users/${userId}/game-progress`, {
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        const data = await response.json();
        
        document.getElementById('gameProgressUserName').textContent = data.user.full_name;
        
        // Cari Hijaiyyah Stats
        const cariStats = data.carihijaiyah.stats;
        document.getElementById('cariStats').innerHTML = `
            <div class="row text-center">
                <div class="col-6"><h5>${cariStats.total_completed}/15</h5><small>Completed</small></div>
                <div class="col-6"><h5>${cariStats.total_sessions}</h5><small>Sessions</small></div>
            </div>
        `;
        
        // Cari Hijaiyyah Progress
        document.getElementById('cariProgress').innerHTML = data.carihijaiyah.progress.map(p => `
            <tr>
                <td>Level ${p.level_number}</td>
                <td><span class="badge bg-${p.is_unlocked ? 'success' : 'secondary'}">${p.is_unlocked ? 'Unlocked' : 'Locked'}</span></td>
                <td><span class="badge bg-${p.is_completed ? 'primary' : 'secondary'}">${p.is_completed ? 'Completed' : 'Not Completed'}</span></td>
                <td>${p.attempts || 0}</td>
            </tr>
        `).join('');
        
        // Cari Hijaiyyah Sessions
        document.getElementById('cariSessions').innerHTML = data.carihijaiyah.recent_sessions.map(s => `
            <tr>
                <td>Level ${s.level_number}</td>
                <td>${new Date(s.completed_at).toLocaleString()}</td>
            </tr>
        `).join('') || '<tr><td colspan="2" class="text-center text-muted">No sessions yet</td></tr>';
        
        // Pasangkan Huruf Stats
        const pasangkanStats = data.pasangkanhuruf.stats;
        document.getElementById('pasangkanStats').innerHTML = `
            <div class="row text-center">
                <div class="col-6"><h5>${pasangkanStats.total_completed}/15</h5><small>Completed</small></div>
                <div class="col-6"><h5>${pasangkanStats.total_sessions}</h5><small>Sessions</small></div>
            </div>
        `;
        
        // Pasangkan Huruf Progress
        document.getElementById('pasangkanProgress').innerHTML = data.pasangkanhuruf.progress.map(p => `
            <tr>
                <td>Level ${p.level_number}</td>
                <td><span class="badge bg-${p.is_unlocked ? 'success' : 'secondary'}">${p.is_unlocked ? 'Unlocked' : 'Locked'}</span></td>
                <td><span class="badge bg-${p.is_completed ? 'primary' : 'secondary'}">${p.is_completed ? 'Completed' : 'Not Completed'}</span></td>
                <td>${p.attempts || 0}</td>
            </tr>
        `).join('');
        
        // Pasangkan Huruf Sessions
        document.getElementById('pasangkanSessions').innerHTML = data.pasangkanhuruf.recent_sessions.map(s => `
            <tr>
                <td>Level ${s.level_number}</td>
                <td>${new Date(s.completed_at).toLocaleString()}</td>
            </tr>
        `).join('') || '<tr><td colspan="2" class="text-center text-muted">No sessions yet</td></tr>';
        
        const modal = new bootstrap.Modal(document.getElementById('gameProgressModal'));
        modal.show();
    } catch (error) {
        console.error('Failed to load game progress:', error);
        showAlert('Failed to load game progress', 'danger');
    }
}

// Load assets
let currentAssetsPage = 1;


/*
|--------------------------------------------------------------------------
| 6. ASSET MANAGEMENT
|--------------------------------------------------------------------------
*/
// Load assets with pagination for admin management
async function loadAssets(page = 1) {
    try {
        // Build query params
        const params = new URLSearchParams({ page });
        
        const search = document.getElementById('assetSearch')?.value;
        if (search) params.append('search', search);
        
        const type = document.getElementById('filterType')?.value;
        if (type) params.append('type', type);
        
        const category = document.getElementById('filterCategory')?.value;
        if (category) params.append('category', category);
        
        const subcategory = document.getElementById('filterSubcategory')?.value;
        if (subcategory) params.append('subcategory', subcategory);
        
        const response = await fetch(`/api/admin/assets?${params}`, {
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        const data = await response.json();
        const tbody = document.querySelector('#assetsTable tbody');
        
        currentAssetsPage = data.current_page;
        
        if (data.data && data.data.length > 0) {
            tbody.innerHTML = data.data.map(asset => `
                <tr>
                    <td><input type="checkbox" class="asset-checkbox" value="${asset.id}"></td>
                    <td>
                        ${asset.type === 'image' 
                            ? `<img src="/api/assets/${asset.id}/file" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">` 
                            : `<i class="fas fa-music fa-2x text-muted"></i>`
                        }
                    </td>
                    <td>${asset.file_name}</td>
                    <td><span class="badge bg-primary">${asset.category || 'general'}</span></td>
                    <td><span class="badge bg-secondary">${asset.subcategory || '-'}</span></td>
                    <td><span class="badge bg-${asset.type === 'image' ? 'success' : 'info'}">${asset.type}</span></td>
                    <td>${formatFileSize(asset.size)}</td>
                    <td>${asset.created_by?.full_name || 'Unknown'}</td>
                    <td>${new Date(asset.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-warning me-1" onclick="editAsset('${asset.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="/api/assets/${asset.id}/file" class="btn btn-sm btn-outline-primary me-1" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAsset('${asset.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            
            // Add checkbox listeners
            document.querySelectorAll('.asset-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedAssets);
            });
            
            // Select all checkbox
            document.getElementById('selectAll').addEventListener('change', function() {
                document.querySelectorAll('.asset-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedAssets();
            });
            
            // Render pagination
            renderAssetsPagination(data);
            
        } else {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No assets found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load assets:', error);
    }
}

// Render pagination
function renderAssetsPagination(data) {
    const paginationInfo = document.getElementById('assetsPaginationInfo');
    const pagination = document.getElementById('assetsPagination');
    
    // Info
    paginationInfo.innerHTML = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total || 0} assets`;
    
    // Pagination buttons
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadAssets(${data.current_page - 1}); return false;">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            paginationHTML += `
                <li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadAssets(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadAssets(${data.current_page + 1}); return false;">Next</a>
        </li>
    `;
    
    pagination.innerHTML = paginationHTML;
}

// Initialize upload zone
function initUploadZone() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const categorySelect = document.getElementById('uploadCategory');
    const subcategorySelect = document.getElementById('uploadSubcategory');
    
    // Category options
    const subcategories = {
        game: ['carihijaiyah', 'berlatih', 'pasangkan'],
        ui: ['button', 'icon', 'frame'],
        sound_effects: ['feedback', 'ui', 'pronunciation']
    };
    
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
        
        if (category && subcategories[category]) {
            subcategorySelect.disabled = false;
            subcategories[category].forEach(sub => {
                subcategorySelect.innerHTML += `<option value="${sub}">${sub}</option>`;
            });
        } else {
            subcategorySelect.disabled = true;
        }
    });
    
    uploadZone.addEventListener('click', () => {
        const category = document.getElementById('uploadCategory')?.value;
        if (!category) {
            showAlert('Pilih kategori terlebih dahulu!', 'warning');
            return;
        }
        fileInput.click();
    });
    
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        
        const category = document.getElementById('uploadCategory')?.value;
        if (!category) {
            showAlert('Pilih kategori terlebih dahulu!', 'warning');
            return;
        }
        
        handleFiles(e.dataTransfer.files);
    });
    
    fileInput.addEventListener('change', (e) => {
        const category = document.getElementById('uploadCategory')?.value;
        if (!category) {
            showAlert('Pilih kategori terlebih dahulu!', 'warning');
            e.target.value = ''; // Reset file input
            return;
        }
        
        handleFiles(e.target.files);
    });
}

// Handle file uploads (batch)
let isUploading = false;

// Handle batch file upload with category validation (max 50 files)
async function handleFiles(files) {
    if (isUploading) {
        showAlert('Upload sedang berlangsung, tunggu sebentar...', 'warning');
        return;
    }
    
    const category = document.getElementById('uploadCategory')?.value;
    const subcategory = document.getElementById('uploadSubcategory')?.value;
    
    if (!category) {
        showAlert('Pilih kategori terlebih dahulu sebelum upload!', 'warning');
        return;
    }
    
    isUploading = true;
    
    // Convert FileList to Array and limit to 50 files
    const filesArray = Array.from(files).slice(0, 50);
    
    if (files.length > 50) {
        showAlert('Maximum 50 files per upload. Only first 50 files will be uploaded.', 'warning');
    }
    
    const formData = new FormData();
    
    // Append each file with array notation
    for (let i = 0; i < filesArray.length; i++) {
        formData.append(`files[${i}]`, filesArray[i]);
    }
    
    formData.append('category', category);
    if (subcategory) formData.append('subcategory', subcategory);
    
    // Debug log
    console.log('Uploading files:', filesArray.length);
    console.log('Category:', category);
    console.log('Subcategory:', subcategory);
    console.log('FormData entries:', Array.from(formData.entries()).map(([k, v]) => k));
    
    try {
        showAlert(`Uploading ${filesArray.length} file(s)...`, 'info');
        
        const response = await fetch('/api/assets/batch', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`
            },
            body: formData
        });
        
        if (response.ok) {
            const result = await response.json();
            showAlert(result.message, 'success');
            
            // Reset file input
            document.getElementById('fileInput').value = '';
            
            loadAssets(); // Refresh assets list
        } else {
            const responseText = await response.text();
            console.error('Upload error response:', responseText);
            try {
                const error = JSON.parse(responseText);
                showAlert(`Failed to upload: ${JSON.stringify(error)}`, 'danger');
            } catch (e) {
                showAlert(`Failed to upload: ${responseText.substring(0, 200)}`, 'danger');
            }
        }
    } catch (error) {
        console.error('Upload exception:', error);
        showAlert('Failed to upload files: ' + error.message, 'danger');
    } finally {
        isUploading = false;
    }
}

// Update selected assets
function updateSelectedAssets() {
    selectedAssets = Array.from(document.querySelectorAll('.asset-checkbox:checked')).map(cb => cb.value);
    document.getElementById('bulkDeleteBtn').disabled = selectedAssets.length === 0;
}

// Bulk delete assets
async function bulkDelete() {
    if (selectedAssets.length === 0) return;
    
    if (!confirm(`Delete ${selectedAssets.length} selected assets?`)) return;
    
    try {
        const response = await fetch('/api/admin/assets/bulk', {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ asset_ids: selectedAssets })
        });
        
        if (response.ok) {
            showAlert('Assets deleted successfully', 'success');
            loadAssets();
        } else {
            showAlert('Failed to delete assets', 'danger');
        }
    } catch (error) {
        showAlert('Failed to delete assets', 'danger');
    }
}

// Create user
async function createUser() {
    const form = document.getElementById('createUserForm');
    const formData = new FormData(form);
    const userData = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/users', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        if (response.ok) {
            showAlert('User created successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
            form.reset();
            loadUsers();
        } else {
            const error = await response.json();
            showAlert('Failed to create user: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        showAlert('Failed to create user', 'danger');
    }
}

// Utility functions

/*
|--------------------------------------------------------------------------
| 9. UTILITY FUNCTIONS
|--------------------------------------------------------------------------
*/
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Upload functions


function uploadBackground() {
    document.getElementById('uploadBackgroundFile').click();
}

// Upload file functions


async function uploadBackgroundFile(file, name) {
    console.log('Starting background upload:', file.name, file.size, file.type);
    
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!validTypes.includes(file.type)) {
        showAlert('Please select a valid image file (JPG, JPEG, PNG)', 'danger');
        return;
    }
    
    if (file.size > 20 * 1024 * 1024) {
        showAlert(`File too large: ${formatFileSize(file.size)}. Max 20MB`, 'danger');
        return;
    }
    
    await ensureValidToken();
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('name', name);
    
    console.log('FormData entries:', Array.from(formData.entries()).map(([k,v]) => `${k}: ${v instanceof File ? v.name : v}`));
    console.log('Sending request to:', `${API_BASE}/backgrounds`);
    
    try {
        const response = await fetch(`${API_BASE}/backgrounds`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            },
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response content-type:', response.headers.get('content-type'));
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            showAlert('Server error: Invalid response format', 'danger');
            return;
        }
        
        if (response.ok) {
            const result = await response.json();
            console.log('Upload success:', result);
            showAlert('Background uploaded successfully', 'success');
            loadBackgrounds();
        } else {
            const error = await response.json();
            console.log('Upload error response:', error);
            let errorMsg = 'Failed to upload background: ';
            if (error.message) {
                errorMsg += error.message;
            } else if (error.file) {
                errorMsg += Array.isArray(error.file) ? error.file.join(', ') : error.file;
            } else if (error.name) {
                errorMsg += Array.isArray(error.name) ? error.name.join(', ') : error.name;
            } else {
                console.log('Full error object:', error);
                errorMsg += JSON.stringify(error);
            }
            showAlert(errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Upload network error:', error);
        showAlert('Failed to upload background: Network error - ' + error.message, 'danger');
    }
}



// Load backgrounds

/*
|--------------------------------------------------------------------------
| 7. BACKGROUND MANAGEMENT
|--------------------------------------------------------------------------
*/
// Load all backgrounds with status for admin management
async function loadBackgrounds() {
    try {
        const response = await fetch(`${API_BASE}/backgrounds`, {
            headers: { 
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            }
        });
        
        if (!response.ok) {
            console.error('Load backgrounds failed:', response.status);
            return;
        }
        
        const data = await response.json();
        const tbody = document.querySelector('#backgroundsTable tbody');
        
        if (data.data && data.data.length > 0) {
            tbody.innerHTML = data.data.map(bg => `
                <tr>
                    <td><img src="/api/backgrounds/${bg.id}/file" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;"></td>
                    <td>${bg.name || 'N/A'}</td>
                    <td><span class="badge bg-${bg.is_active ? 'success' : 'secondary'}">${bg.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td>${formatFileSize(bg.size)}</td>
                    <td>${bg.created_by?.full_name || 'Unknown'}</td>
                    <td>${new Date(bg.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-warning me-1" onclick="editBackground('${bg.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="/api/backgrounds/${bg.id}/file" class="btn btn-sm btn-outline-primary me-1" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteBackground('${bg.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No backgrounds found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load backgrounds:', error);
    }
}

// Delete functions


async function deleteBackground(id) {
    if (!confirm('Delete this background?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/backgrounds/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            }
        });
        
        if (response.ok) {
            showAlert('Background deleted successfully', 'success');
            loadBackgrounds();
        } else {
            showAlert('Failed to delete background', 'danger');
        }
    } catch (error) {
        showAlert('Failed to delete background', 'danger');
    }
}

function initAllUploadZones() {
    const uploadZone = document.getElementById('uploadZone');
    if (uploadZone) {
        initUploadZone();
    }
}

// Edit user functions
function editUser(userId) {
    // Get user data and populate modal
    fetch(`/api/users/${userId}`, {
        headers: { 'Authorization': `Bearer ${adminToken}` }
    })
    .then(response => response.json())
    .then(user => {
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editFullName').value = user.full_name;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editPhoneNumber').value = user.phone_number || '';
        
        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    })
    .catch(error => {
        showAlert('Failed to load user data', 'danger');
    });
}

async function updateUser() {
    const userId = document.getElementById('editUserId').value;
    const userData = {
        fullName: document.getElementById('editFullName').value,
        email: document.getElementById('editEmail').value,
        phone_number: document.getElementById('editPhoneNumber').value
    };
    
    try {
        const response = await fetch(`/api/users/${userId}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        if (response.ok) {
            showAlert('User updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            loadUsers();
        } else {
            const error = await response.json();
            showAlert('Failed to update user: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        showAlert('Failed to update user', 'danger');
    }
}

function refreshStats() {
    loadDashboardData();
    showAlert('Statistics refreshed', 'success');
}





// Delete user function
async function deleteUser(userId) {
    if (!confirm('Delete this user?')) return;
    
    try {
        const response = await fetch(`/api/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        if (response.ok) {
            showAlert('User deleted successfully', 'success');
            loadUsers();
        } else {
            showAlert('Failed to delete user', 'danger');
        }
    } catch (error) {
        showAlert('Failed to delete user', 'danger');
    }
}

// Edit background
function editBackground(bgId) {
    fetch(`${API_BASE}/backgrounds/${bgId}`, {
        headers: { 
            'Authorization': `Bearer ${adminToken}`,
            ...NGROK_HEADERS
        }
    })
    .then(response => response.json())
    .then(bg => {
        document.getElementById('editBackgroundId').value = bg.id;
        document.getElementById('editBackgroundName').value = bg.name;
        document.getElementById('editBackgroundActive').checked = bg.is_active;
        
        const modal = new bootstrap.Modal(document.getElementById('editBackgroundModal'));
        modal.show();
    })
    .catch(error => {
        showAlert('Failed to load background data', 'danger');
    });
}

async function updateBackground() {
    const bgId = document.getElementById('editBackgroundId').value;
    const bgData = {
        name: document.getElementById('editBackgroundName').value,
        is_active: document.getElementById('editBackgroundActive').checked
    };
    
    try {
        const response = await fetch(`${API_BASE}/backgrounds/${bgId}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json',
                ...NGROK_HEADERS
            },
            body: JSON.stringify(bgData)
        });
        
        if (response.ok) {
            showAlert('Background updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editBackgroundModal')).hide();
            loadBackgrounds();
        } else {
            const error = await response.json();
            showAlert('Failed to update background: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        showAlert('Failed to update background', 'danger');
    }
}

// Edit asset
function editAsset(id) {
    fetch(`${API_BASE}/admin/assets`, {
        headers: { 
            'Authorization': `Bearer ${adminToken}`,
            ...NGROK_HEADERS
        }
    })
    .then(response => response.json())
    .then(data => {
        const asset = data.data.find(a => a.id === id);
        if (asset) {
            document.getElementById('editAssetId').value = asset.id;
            document.getElementById('editAssetCategory').value = asset.category || '';
            
            // Populate subcategory based on category
            const category = asset.category;
            if (category) {
                updateEditSubcategoryOptions(category);
                setTimeout(() => {
                    document.getElementById('editAssetSubcategory').value = asset.subcategory || '';
                }, 100);
            }
            
            const modal = new bootstrap.Modal(document.getElementById('editAssetModal'));
            modal.show();
        }
    })
    .catch(error => {
        showAlert('Failed to load asset data', 'danger');
    });
}

// Update asset
async function updateAsset() {
    const id = document.getElementById('editAssetId').value;
    const category = document.getElementById('editAssetCategory').value;
    const subcategory = document.getElementById('editAssetSubcategory').value;
    
    try {
        const response = await fetch(`${API_BASE}/assets/${id}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json',
                ...NGROK_HEADERS
            },
            body: JSON.stringify({ category, subcategory })
        });
        
        if (response.ok) {
            showAlert('Asset updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editAssetModal')).hide();
            loadAssets(currentAssetsPage);
        } else {
            const error = await response.json();
            showAlert('Failed to update asset: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        showAlert('Failed to update asset', 'danger');
    }
}

// Delete asset
async function deleteAsset(id) {
    if (!confirm('Delete this asset?')) return;
    
    try {
        const response = await fetch(`/api/assets/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        if (response.ok) {
            showAlert('Asset deleted successfully', 'success');
            loadAssets();
        } else {
            showAlert('Failed to delete asset', 'danger');
        }
    } catch (error) {
        showAlert('Failed to delete asset', 'danger');
    }
}

// Update subcategory options in edit modal
function updateEditSubcategoryOptions(category) {
    const subcategorySelect = document.getElementById('editAssetSubcategory');
    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
    
    const subcategories = {
        'game': ['characters', 'items', 'backgrounds', 'effects'],
        'ui': ['buttons', 'icons', 'panels', 'fonts'],
        'sound_effects': ['click', 'success', 'error', 'ambient']
    };
    
    if (subcategories[category]) {
        subcategories[category].forEach(sub => {
            subcategorySelect.innerHTML += `<option value="${sub}">${sub}</option>`;
        });
    }
}

// Update filter subcategory options
function updateFilterSubcategoryOptions(category) {
    const subcategorySelect = document.getElementById('filterSubcategory');
    subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
    
    const subcategories = {
        'game': ['characters', 'items', 'backgrounds', 'effects'],
        'ui': ['buttons', 'icons', 'panels', 'fonts'],
        'sound_effects': ['click', 'success', 'error', 'ambient']
    };
    
    if (subcategories[category]) {
        subcategories[category].forEach(sub => {
            subcategorySelect.innerHTML += `<option value="${sub}">${sub}</option>`;
        });
    }
}

// Setup asset search & filter listeners
const assetSearch = document.getElementById('assetSearch');
if (assetSearch) {
    let searchTimeout;
    assetSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadAssets(1), 500);
    });
}

const filterType = document.getElementById('filterType');
if (filterType) {
    filterType.addEventListener('change', () => loadAssets(1));
}

const filterCategory = document.getElementById('filterCategory');
if (filterCategory) {
    filterCategory.addEventListener('change', function() {
        const filterSubcategory = document.getElementById('filterSubcategory');
        if (this.value) {
            updateFilterSubcategoryOptions(this.value);
            filterSubcategory.disabled = false;
        } else {
            filterSubcategory.innerHTML = '<option value="">All Subcategories</option>';
            filterSubcategory.disabled = true;
        }
        loadAssets(1);
    });
}

const filterSubcategory = document.getElementById('filterSubcategory');
if (filterSubcategory) {
    filterSubcategory.addEventListener('change', () => loadAssets(1));
}

console.log('✅ All functions assigned to window object');



/*
|--------------------------------------------------------------------------
| 11. SONG & VIDEO MANAGEMENT
|--------------------------------------------------------------------------
*/

// Load songs
async function loadSongs() {
    try {
        const response = await fetch(`${API_BASE}/songs`, {
            headers: { 
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            }
        });
        const data = await response.json();
        const tbody = document.querySelector('#songsTable tbody');
        
        if (data.data && data.data.length > 0) {
            tbody.innerHTML = data.data.map(song => {
                const fileUrl = song.file ? `/${song.file}` : '#';
                
                return `
                <tr>
                    <td>${song.title}</td>
                    <td><a href="${fileUrl}" target="_blank" class="text-truncate d-inline-block" style="max-width: 300px;">${song.file || 'N/A'}</a></td>
                    <td>${song.created_by?.full_name || 'Unknown'}</td>
                    <td>${new Date(song.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="playSong('${song.id}')" title="Play">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSong('${song.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `}).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No songs found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load songs:', error);
    }
}

// Load videos
async function loadVideos() {
    try {
        const response = await fetch(`${API_BASE}/videos`, {
            headers: { 
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            }
        });
        const data = await response.json();
        const tbody = document.querySelector('#videosTable tbody');
        
        if (data.data && data.data.length > 0) {
            tbody.innerHTML = data.data.map(video => {
                const fileUrl = video.file ? `/${video.file}` : '#';
                const qualities = video.qualities ? JSON.parse(video.qualities) : [];
                const qualityBadges = qualities.map(q => `<span class="badge bg-info me-1">${q.quality}</span>`).join('');
                
                return `
                <tr>
                    <td>${video.title}</td>
                    <td>
                        <a href="${fileUrl}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">${video.file || 'N/A'}</a>
                        <div class="mt-1">${qualityBadges}</div>
                    </td>
                    <td>${video.created_by?.full_name || 'Unknown'}</td>
                    <td>${new Date(video.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="playVideo('${video.id}')" title="Play">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteVideo('${video.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `}).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No videos found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load videos:', error);
    }
}

function uploadAsset() {
    const category = document.getElementById('assetCategory')?.value;
    if (!category) {
        showAlert('Please select a category first!', 'warning');
        return;
    }
    document.getElementById('uploadAssetFile').click();
}

function uploadSong() {
    document.getElementById('uploadSongFile').click();
}

function uploadVideo() {
    document.getElementById('uploadVideoFile').click();
}

async function uploadSongFile(file, title) {
    try {
        showAlert('Uploading song...', 'info');
        const formData = new FormData();
        formData.append('file', file);
        formData.append('title', title);
        
        const response = await fetch(`${API_BASE}/songs`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            },
            body: formData
        });
        
        if (response.ok) {
            showAlert('Song uploaded successfully!', 'success');
            loadSongs();
        } else {
            const error = await response.json();
            showAlert('Failed to upload song: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        showAlert('Failed to save song: ' + error.message, 'danger');
    }
}

async function uploadVideoFile(file, title) {
    try {
        showAlert('Uploading video... This may take several minutes.', 'info');
        const formData = new FormData();
        formData.append('file', file);
        formData.append('title', title);
        
        const response = await fetch(`${API_BASE}/videos`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            },
            body: formData
        });
        
        if (response.ok) {
            showAlert('Video uploaded and processed successfully!', 'success');
            loadVideos();
        } else {
            const error = await response.json();
            showAlert('Failed to upload video: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        showAlert('Failed to upload video: ' + error.message, 'danger');
    }
}

// Delete song
async function deleteSong(id) {
    if (!confirm('Delete this song?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/songs/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            }
        });
        
        if (response.ok) {
            showAlert('Song deleted successfully', 'success');
            loadSongs();
        } else {
            showAlert('Failed to delete song', 'danger');
        }
    } catch (error) {
        showAlert('Failed to delete song', 'danger');
    }
}

// Delete video
async function deleteVideo(id) {
    if (!confirm('Delete this video?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/videos/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            }
        });
        
        if (response.ok) {
            showAlert('Video deleted successfully', 'success');
            loadVideos();
        } else {
            showAlert('Failed to delete video', 'danger');
        }
    } catch (error) {
        showAlert('Failed to delete video', 'danger');
    }
}

// Play song
function playSong(id) {
    window.open(`${API_BASE}/songs/${id}/file`, '_blank');
}

// Play video
function playVideo(id) {
    fetch(`${API_BASE}/videos/${id}`, {
        headers: { 
            'Authorization': `Bearer ${adminToken}`,
            ...NGROK_HEADERS
        }
    })
    .then(response => response.json())
    .then(video => {
        const videoUrl = `${window.location.protocol}//${window.location.host}/${video.file}`;
        
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${video.title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <video id="videoPlayer" controls style="width: 100%; height: auto;"></video>
                        <div class="mt-2">
                            <small class="text-muted">HLS URL: ${videoUrl}</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Load HLS
        const videoElement = document.getElementById('videoPlayer');
        if (Hls.isSupported()) {
            const hls = new Hls();
            hls.loadSource(videoUrl);
            hls.attachMedia(videoElement);
        } else if (videoElement.canPlayType('application/vnd.apple.mpegurl')) {
            videoElement.src = videoUrl;
        }
        
        // Cleanup on close
        modal.addEventListener('hidden.bs.modal', () => {
            videoElement.pause();
            modal.remove();
        });
    })
    .catch(error => {
        showAlert('Failed to load video', 'danger');
    });
}

// Update song
async function updateSong() {
    const id = document.getElementById('editSongId').value;
    const title = document.getElementById('editSongTitle').value;
    const url = document.getElementById('editSongUrl').value;
    
    try {
        const response = await fetch(`${API_BASE}/songs/${id}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json',
                ...NGROK_HEADERS
            },
            body: JSON.stringify({ title, url })
        });
        
        if (response.ok) {
            showAlert('Song updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editSongModal')).hide();
            loadSongs();
        } else {
            const error = await response.json();
            showAlert('Failed to update song: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        showAlert('Failed to update song', 'danger');
    }
}

// Update video
async function updateVideo() {
    const id = document.getElementById('editVideoId').value;
    const title = document.getElementById('editVideoTitle').value;
    const url = document.getElementById('editVideoUrl').value;
    
    try {
        const response = await fetch(`${API_BASE}/videos/${id}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json',
                ...NGROK_HEADERS
            },
            body: JSON.stringify({ title, url })
        });
        
        if (response.ok) {
            showAlert('Video updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editVideoModal')).hide();
            loadVideos();
        } else {
            const error = await response.json();
            showAlert('Failed to update video: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        showAlert('Failed to update video', 'danger');
    }
}

// Export to window
window.uploadAsset = uploadAsset;
window.uploadSong = uploadSong;
window.uploadVideo = uploadVideo;
window.uploadBackground = uploadBackground;
window.updateSong = updateSong;
window.updateVideo = updateVideo;
window.deleteSong = deleteSong;
window.deleteVideo = deleteVideo;
window.editAsset = editAsset;
window.updateAsset = updateAsset;
window.deleteAsset = deleteAsset;
window.deleteUser = deleteUser;
window.viewGameProgress = viewGameProgress;
window.editBackground = editBackground;
window.updateBackground = updateBackground;
