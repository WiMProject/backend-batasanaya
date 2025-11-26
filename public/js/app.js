// Global variables
let adminToken = localStorage.getItem('adminToken');
let selectedAssets = [];

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
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on admin page
    if (window.location.pathname === '/admin') {
        initAdmin();
    } else if (window.location.pathname === '/admin/login') {
        // Don't initialize admin on login page
        return;
    }
    
    // Initialize upload zones
    initAllUploadZones();
    
    // Add event listener for edit asset category change
    const editCategorySelect = document.getElementById('editAssetCategory');
    if (editCategorySelect) {
        editCategorySelect.addEventListener('change', function() {
            updateEditSubcategories(this.value);
        });
    }
});

// Admin initialization
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
    document.getElementById('totalSongs').textContent = stats.songs || 0;
    document.getElementById('totalVideos').textContent = stats.videos || 0;
    document.getElementById('totalBackgrounds').textContent = stats.backgrounds || 0;
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
            initUploadZone();
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
                                <span class="badge bg-info">Cari</span> ${cari.completed || 0}/15 <span class="text-warning">⭐${cari.stars || 0}</span>
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
                <div class="col-3"><h5>${cariStats.total_completed}/15</h5><small>Completed</small></div>
                <div class="col-3"><h5 class="text-warning">⭐ ${cariStats.total_stars}</h5><small>Stars</small></div>
                <div class="col-3"><h5>${cariStats.total_score}</h5><small>Score</small></div>
                <div class="col-3"><h5>${Math.round(cariStats.avg_accuracy || 0)}%</h5><small>Accuracy</small></div>
            </div>
        `;
        
        // Cari Hijaiyyah Progress
        document.getElementById('cariProgress').innerHTML = data.carihijaiyah.progress.map(p => `
            <tr>
                <td>Level ${p.level_number}</td>
                <td><span class="badge bg-${p.is_unlocked ? 'success' : 'secondary'}">${p.is_unlocked ? 'Unlocked' : 'Locked'}</span></td>
                <td><span class="badge bg-${p.is_completed ? 'primary' : 'secondary'}">${p.is_completed ? 'Completed' : 'Not Completed'}</span></td>
                <td>${p.best_score || 0}</td>
                <td>${p.best_time || '-'}s</td>
                <td class="text-warning">${'⭐'.repeat(p.stars || 0)}</td>
                <td>${p.attempts || 0}</td>
            </tr>
        `).join('');
        
        // Cari Hijaiyyah Sessions
        document.getElementById('cariSessions').innerHTML = data.carihijaiyah.recent_sessions.map(s => {
            const accuracy = ((s.correct_matches / (s.correct_matches + s.wrong_matches)) * 100).toFixed(1);
            return `
            <tr>
                <td>Level ${s.level_number}</td>
                <td>${s.score}</td>
                <td>${s.time_taken}s</td>
                <td>${s.correct_matches}/${s.correct_matches + s.wrong_matches}</td>
                <td>${accuracy}%</td>
                <td class="text-warning">${'⭐'.repeat(s.stars || 0)}</td>
                <td>${new Date(s.completed_at).toLocaleString()}</td>
            </tr>
            `;
        }).join('') || '<tr><td colspan="7" class="text-center text-muted">No sessions yet</td></tr>';
        
        // Pasangkan Huruf Stats
        const pasangkanStats = data.pasangkanhuruf.stats;
        document.getElementById('pasangkanStats').innerHTML = `
            <div class="row text-center">
                <div class="col-3"><h5>${pasangkanStats.total_completed}/15</h5><small>Completed</small></div>
                <div class="col-3"><h5 class="text-warning">⭐ ${pasangkanStats.total_stars}</h5><small>Stars</small></div>
                <div class="col-3"><h5>${pasangkanStats.total_score}</h5><small>Score</small></div>
                <div class="col-3"><h5>${Math.round(pasangkanStats.avg_accuracy || 0)}%</h5><small>Accuracy</small></div>
            </div>
        `;
        
        // Pasangkan Huruf Progress
        document.getElementById('pasangkanProgress').innerHTML = data.pasangkanhuruf.progress.map(p => `
            <tr>
                <td>Level ${p.level_number}</td>
                <td><span class="badge bg-${p.is_unlocked ? 'success' : 'secondary'}">${p.is_unlocked ? 'Unlocked' : 'Locked'}</span></td>
                <td><span class="badge bg-${p.is_completed ? 'primary' : 'secondary'}">${p.is_completed ? 'Completed' : 'Not Completed'}</span></td>
                <td>${p.best_score || 0}</td>
                <td>${p.best_time || '-'}s</td>
                <td class="text-warning">${'⭐'.repeat(p.stars || 0)}</td>
                <td>${p.attempts || 0}</td>
            </tr>
        `).join('');
        
        // Pasangkan Huruf Sessions
        document.getElementById('pasangkanSessions').innerHTML = data.pasangkanhuruf.recent_sessions.map(s => {
            const accuracy = ((s.correct_matches / (s.correct_matches + s.wrong_matches)) * 100).toFixed(1);
            return `
            <tr>
                <td>Level ${s.level_number}</td>
                <td>${s.score}</td>
                <td>${s.time_taken}s</td>
                <td>${s.correct_matches}/${s.correct_matches + s.wrong_matches}</td>
                <td>${accuracy}%</td>
                <td class="text-warning">${'⭐'.repeat(s.stars || 0)}</td>
                <td>${new Date(s.completed_at).toLocaleString()}</td>
            </tr>
            `;
        }).join('') || '<tr><td colspan="7" class="text-center text-muted">No sessions yet</td></tr>';
        
        const modal = new bootstrap.Modal(document.getElementById('gameProgressModal'));
        modal.show();
    } catch (error) {
        console.error('Failed to load game progress:', error);
        showAlert('Failed to load game progress', 'danger');
    }
}

// Load assets
let currentAssetsPage = 1;

async function loadAssets(page = 1) {
    try {
        const response = await fetch(`/api/admin/assets?page=${page}`, {
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
function uploadSong() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.mp3,.wav,.m4a';
    input.multiple = false;
    input.onchange = async (e) => {
        const file = e.target.files[0];
        if (file) {
            const title = prompt('Enter song title:', file.name.replace(/\.[^/.]+$/, ''));
            if (title) {
                await uploadSongFile(file, title);
                loadSongs();
            }
        }
    };
    input.click();
}

function uploadVideo() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.mp4,.avi,.mov';
    input.multiple = false;
    input.onchange = async (e) => {
        const file = e.target.files[0];
        if (file) {
            const title = prompt('Enter video title:', file.name.replace(/\.[^/.]+$/, ''));
            if (title) {
                await uploadVideoFile(file, title);
                loadVideos();
            }
        }
    };
    input.click();
}

function uploadBackground() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.jpg,.jpeg,.png';
    input.multiple = false;
    input.onchange = async (e) => {
        const file = e.target.files[0];
        if (file) {
            // Validate file
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                showAlert('Please select a valid image file (JPG, JPEG, PNG)', 'danger');
                return;
            }
            
            if (file.size > 20 * 1024 * 1024) { // 20MB
                showAlert('File size must be less than 20MB', 'danger');
                return;
            }
            
            const name = prompt('Enter background name:', file.name.replace(/\.[^/.]+$/, ''));
            if (name) {
                await uploadBackgroundFile(file, name);
                loadBackgrounds();
            }
        }
    };
    input.click();
}

// Upload file functions
async function uploadSongFile(file, title) {
    // Validate file
    const validTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-m4a'];
    if (!validTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|m4a)$/i)) {
        showAlert('Please select a valid audio file (MP3, WAV, M4A)', 'danger');
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) { // 10MB
        showAlert('File size must be less than 10MB', 'danger');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('title', title);
    
    console.log('Uploading song:', file.name, file.size, file.type);
    
    try {
        const response = await fetch(`${API_BASE}/songs`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            },
            body: formData
        });
        
        console.log('Song upload response status:', response.status);
        
        if (response.ok) {
            showAlert('Song uploaded successfully', 'success');
        } else {
            const error = await response.json();
            console.error('Song upload error:', error);
            let errorMsg = 'Failed to upload song: ';
            if (error.message) {
                errorMsg += error.message;
            } else if (error.file) {
                errorMsg += Array.isArray(error.file) ? error.file.join(', ') : error.file;
            } else if (error.title) {
                errorMsg += Array.isArray(error.title) ? error.title.join(', ') : error.title;
            } else {
                errorMsg += JSON.stringify(error);
            }
            showAlert(errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showAlert('Failed to upload song: Network error - ' + error.message, 'danger');
    }
}

async function uploadVideoFile(file, title) {
    // Validate file
    const validTypes = ['video/mp4', 'video/avi', 'video/quicktime'];
    if (!validTypes.includes(file.type) && !file.name.match(/\.(mp4|avi|mov)$/i)) {
        showAlert('Please select a valid video file (MP4, AVI, MOV)', 'danger');
        return;
    }
    
    if (file.size > 50 * 1024 * 1024) { // 50MB
        showAlert('File size must be less than 50MB', 'danger');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('title', title);
    
    console.log('Uploading video:', file.name, file.size, file.type);
    
    try {
        const response = await fetch(`${API_BASE}/videos`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                ...NGROK_HEADERS
            },
            body: formData
        });
        
        console.log('Video upload response status:', response.status);
        
        if (response.ok) {
            showAlert('Video uploaded successfully', 'success');
        } else {
            const error = await response.json();
            console.error('Video upload error:', error);
            let errorMsg = 'Failed to upload video: ';
            if (error.message) {
                errorMsg += error.message;
            } else if (error.file) {
                errorMsg += Array.isArray(error.file) ? error.file.join(', ') : error.file;
            } else if (error.title) {
                errorMsg += Array.isArray(error.title) ? error.title.join(', ') : error.title;
            } else {
                errorMsg += JSON.stringify(error);
            }
            showAlert(errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showAlert('Failed to upload video: Network error - ' + error.message, 'danger');
    }
}

async function uploadBackgroundFile(file, name) {
    console.log('Starting background upload:', file.name, file.size, file.type);
    
    // Validate file
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!validTypes.includes(file.type)) {
        showAlert('Please select a valid image file (JPG, JPEG, PNG)', 'danger');
        return;
    }
    
    if (file.size > 20 * 1024 * 1024) { // 20MB
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
        
        if (response.ok) {
            const result = await response.json();
            console.log('Upload success:', result);
            showAlert('Background uploaded successfully', 'success');
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
            tbody.innerHTML = data.data.map(song => `
                <tr>
                    <td>
                        ${song.thumbnail 
                            ? `<img src="/${song.thumbnail}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">` 
                            : `<i class="fas fa-music fa-2x text-muted"></i>`
                        }
                    </td>
                    <td>${song.title}</td>
                    <td><a href="/api/songs/${song.id}/file" target="_blank">${song.file.split('/').pop()}</a></td>
                    <td>${song.created_by?.full_name || 'Unknown'}</td>
                    <td>${new Date(song.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSong('${song.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No songs found</td></tr>';
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
            tbody.innerHTML = data.data.map(video => `
                <tr>
                    <td>
                        ${video.thumbnail 
                            ? `<img src="/${video.thumbnail}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">` 
                            : `<i class="fas fa-video fa-2x text-muted"></i>`
                        }
                    </td>
                    <td>${video.title}</td>
                    <td><a href="/api/videos/${video.id}/file" target="_blank">${video.file.split('/').pop()}</a></td>
                    <td>${video.created_by?.full_name || 'Unknown'}</td>
                    <td>${new Date(video.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteVideo('${video.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No videos found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load videos:', error);
    }
}

// Load backgrounds
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
                    <td>${bg.name}</td>
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

// Initialize all upload zones
function initAllUploadZones() {
    // Assets upload zone
    const uploadZone = document.getElementById('uploadZone');
    if (uploadZone) {
        initUploadZone();
    }
    
    // Songs upload zone
    const songUploadZone = document.getElementById('songUploadZone');
    if (songUploadZone) {
        initSongUploadZone();
    }
    
    // Videos upload zone
    const videoUploadZone = document.getElementById('videoUploadZone');
    if (videoUploadZone) {
        initVideoUploadZone();
    }
    
    // Backgrounds upload zone
    const backgroundUploadZone = document.getElementById('backgroundUploadZone');
    if (backgroundUploadZone) {
        initBackgroundUploadZone();
    }
}

// Song upload zone
function initSongUploadZone() {
    const uploadZone = document.getElementById('songUploadZone');
    const fileInput = document.getElementById('songFileInput');
    
    uploadZone.addEventListener('click', () => fileInput.click());
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
        handleSongFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', (e) => {
        handleSongFiles(e.target.files);
    });
}

// Video upload zone
function initVideoUploadZone() {
    const uploadZone = document.getElementById('videoUploadZone');
    const fileInput = document.getElementById('videoFileInput');
    
    uploadZone.addEventListener('click', () => fileInput.click());
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
        handleVideoFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', (e) => {
        handleVideoFiles(e.target.files);
    });
}

// Background upload zone
function initBackgroundUploadZone() {
    const uploadZone = document.getElementById('backgroundUploadZone');
    const fileInput = document.getElementById('backgroundFileInput');
    
    uploadZone.addEventListener('click', () => fileInput.click());
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
        handleBackgroundFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', (e) => {
        handleBackgroundFiles(e.target.files);
    });
}

// Handle file uploads
async function handleSongFiles(files) {
    let uploadedCount = 0;
    for (let file of files) {
        // Validate first
        const validTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-m4a'];
        if (!validTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|m4a)$/i)) {
            showAlert(`${file.name}: Invalid file type. Please select MP3, WAV, or M4A`, 'danger');
            continue;
        }
        if (file.size > 10 * 1024 * 1024) {
            showAlert(`${file.name}: File too large (${formatFileSize(file.size)}). Max 10MB`, 'danger');
            continue;
        }
        
        const title = prompt('Enter song title:', file.name.replace(/\.[^/.]+$/, ''));
        if (title) {
            await uploadSongFile(file, title);
            uploadedCount++;
        }
    }
    
    // Reset file input
    const fileInput = document.getElementById('songFileInput');
    if (fileInput) fileInput.value = '';
    
    if (uploadedCount > 0) {
        loadSongs();
    }
}

async function handleVideoFiles(files) {
    let uploadedCount = 0;
    for (let file of files) {
        // Validate first
        const validTypes = ['video/mp4', 'video/avi', 'video/quicktime'];
        if (!validTypes.includes(file.type) && !file.name.match(/\.(mp4|avi|mov)$/i)) {
            showAlert(`${file.name}: Invalid file type. Please select MP4, AVI, or MOV`, 'danger');
            continue;
        }
        if (file.size > 50 * 1024 * 1024) {
            showAlert(`${file.name}: File too large (${formatFileSize(file.size)}). Max 50MB`, 'danger');
            continue;
        }
        
        const title = prompt('Enter video title:', file.name.replace(/\.[^/.]+$/, ''));
        if (title) {
            await uploadVideoFile(file, title);
            uploadedCount++;
        }
    }
    
    // Reset file input
    const fileInput = document.getElementById('videoFileInput');
    if (fileInput) fileInput.value = '';
    
    if (uploadedCount > 0) {
        loadVideos();
    }
}

async function handleBackgroundFiles(files) {
    let uploadedCount = 0;
    for (let file of files) {
        // Validate first
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            showAlert(`${file.name}: Invalid file type. Please select JPG or PNG`, 'danger');
            continue;
        }
        if (file.size > 20 * 1024 * 1024) {
            showAlert(`${file.name}: File too large (${formatFileSize(file.size)}). Max 20MB`, 'danger');
            continue;
        }
        
        const name = prompt('Enter background name:', file.name.replace(/\.[^/.]+$/, ''));
        if (name) {
            await uploadBackgroundFile(file, name);
            uploadedCount++;
        }
    }
    
    // Reset file input
    const fileInput = document.getElementById('backgroundFileInput');
    if (fileInput) fileInput.value = '';
    
    if (uploadedCount > 0) {
        loadBackgrounds();
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



// Edit asset function
function editAsset(assetId) {
    fetch(`${API_BASE}/admin/assets`, {
        headers: { 
            'Authorization': `Bearer ${adminToken}`,
            ...NGROK_HEADERS
        }
    })
    .then(response => response.json())
    .then(data => {
        const asset = data.data.find(a => a.id === assetId);
        if (asset) {
            document.getElementById('editAssetId').value = asset.id;
            document.getElementById('editAssetCategory').value = asset.category || '';
            
            // Update subcategory options
            updateEditSubcategories(asset.category, asset.subcategory);
            
            const modal = new bootstrap.Modal(document.getElementById('editAssetModal'));
            modal.show();
        }
    })
    .catch(error => {
        console.error('Edit asset error:', error);
        showAlert('Failed to load asset data', 'danger');
    });
}

// Update subcategories for edit modal
function updateEditSubcategories(category, selectedSubcategory = '') {
    const subcategorySelect = document.getElementById('editAssetSubcategory');
    const subcategories = {
        game: ['carihijaiyah', 'berlatih', 'pasangkan'],
        ui: ['button', 'icon', 'frame'],
        sound_effects: ['feedback', 'ui', 'pronunciation']
    };
    
    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
    
    if (category && subcategories[category]) {
        subcategorySelect.disabled = false;
        subcategories[category].forEach(sub => {
            const selected = sub === selectedSubcategory ? 'selected' : '';
            subcategorySelect.innerHTML += `<option value="${sub}" ${selected}>${sub}</option>`;
        });
    } else {
        subcategorySelect.disabled = true;
    }
}

// Update asset function
async function updateAsset() {
    const assetId = document.getElementById('editAssetId').value;
    const assetData = {
        category: document.getElementById('editAssetCategory').value,
        subcategory: document.getElementById('editAssetSubcategory').value
    };
    
    try {
        const response = await fetch(`${API_BASE}/assets/${assetId}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json',
                ...NGROK_HEADERS
            },
            body: JSON.stringify(assetData)
        });
        
        if (response.ok) {
            showAlert('Asset updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editAssetModal')).hide();
            loadAssets();
        } else {
            const error = await response.json();
            showAlert('Failed to update asset: ' + (error.message || 'Unknown error'), 'danger');
        }
    } catch (error) {
        console.error('Update asset error:', error);
        showAlert('Failed to update asset', 'danger');
    }
}

// Delete asset function
async function deleteAsset(assetId) {
    if (!confirm('Delete this asset?')) return;
    
    try {
        const response = await fetch(`/api/assets/${assetId}`, {
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

// Assign all functions to window for global access
window.editAsset = editAsset;
window.updateAsset = updateAsset;
window.deleteAsset = deleteAsset;
window.deleteUser = deleteUser;
window.viewGameProgress = viewGameProgress;
window.editBackground = editBackground;
window.updateBackground = updateBackground;

console.log('✅ All functions assigned to window object');