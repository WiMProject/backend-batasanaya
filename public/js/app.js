// Global variables
let adminToken = localStorage.getItem('adminToken');
let selectedAssets = [];

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on admin page
    if (window.location.pathname.includes('/admin')) {
        initAdmin();
    }
    
    // Initialize upload zones
    initAllUploadZones();
});

// Admin initialization
function initAdmin() {
    // Check if user has valid token
    if (!adminToken) {
        // Redirect to login page
        window.location.href = '/admin/login';
        return;
    }
    
    // Verify token is still valid
    verifyTokenAndLoad();
    
    // Tab change handler
    document.querySelectorAll('#adminTabs a').forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('href').substring(1);
            loadTabContent(target);
        });
    });
}

// Verify token and load dashboard
async function verifyTokenAndLoad() {
    try {
        const response = await fetch('/api/auth/me', {
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        if (response.ok) {
            const user = await response.json();
            if (user.role?.name === 'admin') {
                loadDashboardData();
            } else {
                // Not admin, redirect to login
                localStorage.removeItem('adminToken');
                window.location.href = '/admin/login';
            }
        } else {
            // Token invalid, redirect to login
            localStorage.removeItem('adminToken');
            window.location.href = '/admin/login';
        }
    } catch (error) {
        // Network error, redirect to login
        localStorage.removeItem('adminToken');
        window.location.href = '/admin/login';
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
        const response = await fetch('/api/admin/stats', {
            headers: {
                'Authorization': `Bearer ${adminToken}`
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
        case 'letter-pairs':
            await loadLetterPairs();
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
            tbody.innerHTML = data.data.map(user => `
                <tr>
                    <td>${user.full_name}</td>
                    <td>${user.email}</td>
                    <td>${user.phone_number || '-'}</td>
                    <td><span class="badge bg-${user.role?.name === 'admin' ? 'danger' : 'primary'}">${user.role?.name || 'user'}</span></td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser('${user.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No users found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    }
}

// Load assets
async function loadAssets() {
    try {
        const response = await fetch('/api/admin/assets', {
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        const data = await response.json();
        const tbody = document.querySelector('#assetsTable tbody');
        
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
                    <td><span class="badge bg-${asset.type === 'image' ? 'success' : 'info'}">${asset.type}</span></td>
                    <td>${formatFileSize(asset.size)}</td>
                    <td>${asset.created_by?.full_name || 'Unknown'}</td>
                    <td>${new Date(asset.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
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
            
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No assets found</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load assets:', error);
    }
}

// Initialize upload zone
function initUploadZone() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    
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
        handleFiles(e.dataTransfer.files);
    });
    
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
}

// Handle file uploads
async function handleFiles(files) {
    for (let file of files) {
        await uploadFile(file);
    }
    loadAssets(); // Refresh assets list
}

// Upload single file
async function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch('/api/assets', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`
            },
            body: formData
        });
        
        if (response.ok) {
            showAlert(`${file.name} uploaded successfully`, 'success');
        } else {
            const error = await response.json();
            showAlert(`Failed to upload ${file.name}: ${error.message || 'Unknown error'}`, 'danger');
        }
    } catch (error) {
        showAlert(`Failed to upload ${file.name}`, 'danger');
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
            
            if (file.size > 5 * 1024 * 1024) { // 5MB
                showAlert('File size must be less than 5MB', 'danger');
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
    const formData = new FormData();
    formData.append('file', file);
    formData.append('title', title);
    
    try {
        const response = await fetch('/api/songs', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`
            },
            body: formData
        });
        
        if (response.ok) {
            showAlert('Song uploaded successfully', 'success');
        } else {
            const error = await response.json();
            let errorMsg = 'Failed to upload song: ';
            if (error.message) {
                errorMsg += error.message;
            } else if (error.file) {
                errorMsg += error.file.join(', ');
            } else if (error.title) {
                errorMsg += error.title.join(', ');
            } else {
                errorMsg += 'Unknown error';
            }
            showAlert(errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showAlert('Failed to upload song: Network error', 'danger');
    }
}

async function uploadVideoFile(file, title) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('title', title);
    
    try {
        const response = await fetch('/api/videos', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`
            },
            body: formData
        });
        
        if (response.ok) {
            showAlert('Video uploaded successfully', 'success');
        } else {
            const error = await response.json();
            let errorMsg = 'Failed to upload video: ';
            if (error.message) {
                errorMsg += error.message;
            } else if (error.file) {
                errorMsg += error.file.join(', ');
            } else if (error.title) {
                errorMsg += error.title.join(', ');
            } else {
                errorMsg += 'Unknown error';
            }
            showAlert(errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showAlert('Failed to upload video: Network error', 'danger');
    }
}

async function uploadBackgroundFile(file, name) {
    console.log('Starting background upload:', file.name, file.size, file.type);
    
    await ensureValidToken();
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('name', name);
    
    console.log('FormData created, sending request...');
    
    try {
        const response = await fetch('/api/backgrounds', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`
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
        const response = await fetch('/api/songs', {
            headers: { 'Authorization': `Bearer ${adminToken}` }
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
        const response = await fetch('/api/videos', {
            headers: { 'Authorization': `Bearer ${adminToken}` }
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
        const response = await fetch('/api/backgrounds', {
            headers: { 'Authorization': `Bearer ${adminToken}` }
        });
        const data = await response.json();
        const tbody = document.querySelector('#backgroundsTable tbody');
        
        if (data.data && data.data.length > 0) {
            tbody.innerHTML = data.data.map(bg => `
                <tr>
                    <td><img src="/api/backgrounds/${bg.id}/file" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;"></td>
                    <td>${bg.name}</td>
                    <td>${formatFileSize(bg.size)}</td>
                    <td>${bg.created_by?.full_name || 'Unknown'}</td>
                    <td>${new Date(bg.created_at).toLocaleDateString()}</td>
                    <td class="table-actions">
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
        const response = await fetch(`/api/songs/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`
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
        const response = await fetch(`/api/videos/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`
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
        const response = await fetch(`/api/backgrounds/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`
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
    for (let file of files) {
        const title = prompt('Enter song title:', file.name.replace(/\.[^/.]+$/, ''));
        if (title) {
            await uploadSongFile(file, title);
        }
    }
    loadSongs();
}

async function handleVideoFiles(files) {
    for (let file of files) {
        const title = prompt('Enter video title:', file.name.replace(/\.[^/.]+$/, ''));
        if (title) {
            await uploadVideoFile(file, title);
        }
    }
    loadVideos();
}

async function handleBackgroundFiles(files) {
    for (let file of files) {
        const name = prompt('Enter background name:', file.name.replace(/\.[^/.]+$/, ''));
        if (name) {
            await uploadBackgroundFile(file, name);
        }
    }
    loadBackgrounds();
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

// Letter Pairs functions
function showLetterPairModal() {
    new bootstrap.Modal(document.getElementById('letterPairModal')).show();
}

function uploadLetterPair() {
    const form = document.getElementById('letterPairForm');
    const formData = new FormData(form);

    fetch('/api/letter-pairs', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${adminToken}`
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            showAlert('Letter pair uploaded successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('letterPairModal')).hide();
            form.reset();
            loadLetterPairs();
        } else {
            showAlert('Error uploading letter pair: ' + JSON.stringify(data), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error uploading letter pair', 'danger');
    });
}

function loadLetterPairs() {
    fetch('/api/letter-pairs', {
        headers: {
            'Authorization': `Bearer ${adminToken}`
        }
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector('#letterPairsTable tbody');
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No letter pairs found</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(pair => `
            <tr>
                <td><strong>${pair.letter_name}</strong></td>
                <td><img src="${pair.outline_url}" alt="Outline" style="width: 50px; height: 50px; object-fit: cover;"></td>
                <td><img src="${pair.complete_url}" alt="Complete" style="width: 50px; height: 50px; object-fit: cover;"></td>
                <td><span class="badge bg-info">Level ${pair.difficulty_level}</span></td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deleteLetterPair('${pair.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    })
    .catch(error => {
        console.error('Error loading letter pairs:', error);
        document.querySelector('#letterPairsTable tbody').innerHTML = 
            '<tr><td colspan="6" class="text-center text-danger">Error loading letter pairs</td></tr>';
    });
}

function deleteLetterPair(id) {
    if (!confirm('Are you sure you want to delete this letter pair?')) return;

    fetch(`/api/letter-pairs/${id}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${adminToken}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            showAlert('Letter pair deleted successfully!', 'success');
            loadLetterPairs();
        } else {
            showAlert('Error deleting letter pair', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error deleting letter pair', 'danger');
    });
}

function getToken() {
    return adminToken;
}