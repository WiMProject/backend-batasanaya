<?php
$title = 'Admin Dashboard - Lumen Backend Batasanaya';
ob_start();
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2">
            <div class="admin-sidebar p-3">
                <h6 class="text-muted mb-3">ADMIN PANEL</h6>
                <ul class="nav nav-pills flex-column" id="adminTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="pill" href="#dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#users">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#assets">
                            <i class="fas fa-images me-2"></i>Assets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#songs">
                            <i class="fas fa-music me-2"></i>Songs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#videos">
                            <i class="fas fa-video me-2"></i>Videos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#backgrounds">
                            <i class="fas fa-image me-2"></i>Backgrounds
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#settings">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Content -->
        <div class="col-lg-10">
            <div class="tab-content">
                <!-- Dashboard Tab -->
                <div class="tab-pane fade show active" id="dashboard">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Dashboard Overview</h2>
                        <button class="btn btn-primary" onclick="refreshStats()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4" id="statsCards">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">Total Users</h6>
                                        <h3 class="mb-0" id="totalUsers">-</h3>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card success p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">Total Assets</h6>
                                        <h3 class="mb-0" id="totalAssets">-</h3>
                                    </div>
                                    <i class="fas fa-images fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card warning p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">Storage Used</h6>
                                        <h3 class="mb-0" id="storageUsed">-</h3>
                                    </div>
                                    <i class="fas fa-hdd fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stats-card info p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">Total Songs</h6>
                                        <h3 class="mb-0" id="totalSongs">-</h3>
                                    </div>
                                    <i class="fas fa-music fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="stats-card p-3" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">Total Videos</h6>
                                        <h3 class="mb-0" id="totalVideos">-</h3>
                                    </div>
                                    <i class="fas fa-video fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="stats-card p-3" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">Backgrounds</h6>
                                        <h3 class="mb-0" id="totalBackgrounds">-</h3>
                                    </div>
                                    <i class="fas fa-image fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="admin-content p-3 mb-3">
                                <h5 class="mb-3">Recent Users</h5>
                                <div id="recentUsers">Loading...</div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="admin-content p-3 mb-3">
                                <h5 class="mb-3">Recent Assets</h5>
                                <div id="recentAssets">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Tab -->
                <div class="tab-pane fade" id="users">
                    <div class="admin-content p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>User Management</h3>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                <i class="fas fa-plus me-1"></i>Add User
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Game Progress</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="text-center">Loading users...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Assets Tab -->
                <div class="tab-pane fade" id="assets">
                    <div class="admin-content p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3><i class="fas fa-images me-2"></i>Asset Management</h3>
                            <button class="btn btn-danger" onclick="bulkDelete()" id="bulkDeleteBtn" disabled>
                                <i class="fas fa-trash me-1"></i>Delete Selected
                            </button>
                        </div>

                        <!-- Category Selection - Outside Upload Zone -->
                        <div class="category-selector">
                            <h6><i class="fas fa-tags me-2"></i>Select Category & Subcategory First</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="uploadCategory">
                                        <option value="">-- Choose Category --</option>
                                        <option value="hijaiyyah">🕌 Hijaiyyah</option>
                                        <option value="ui">🎨 UI Elements</option>
                                        <option value="sound_effects">🔊 Sound Effects</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Subcategory</label>
                                    <select class="form-select" id="uploadSubcategory" disabled>
                                        <option value="">-- Choose Subcategory --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Zone -->
                        <div class="upload-zone mb-4" id="uploadZone">
                            <i class="fas fa-cloud-upload-alt fa-4x mb-3" style="color: #667eea;"></i>
                            <h4 class="mb-2">Drag & Drop Files Here</h4>
                            <p class="text-muted mb-0">or click to browse</p>
                            <small class="text-muted">Supported: JPG, PNG, MP3, WAV (Max 5MB)</small>
                            <input type="file" id="fileInput" multiple accept=".jpg,.jpeg,.png,.mp3,.wav" style="display: none;">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="assetsTable">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>Preview</th>
                                        <th>Filename</th>
                                        <th>Category</th>
                                        <th>Subcategory</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="10" class="text-center">Loading assets...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="assetsPaginationInfo"></div>
                            <nav>
                                <ul class="pagination mb-0" id="assetsPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Songs Tab -->
                <div class="tab-pane fade" id="songs">
                    <div class="admin-content p-4">
                        <h3 class="mb-4">Song Management</h3>
                        
                        <!-- Upload Zone -->
                        <div class="upload-zone mb-4" id="songUploadZone">
                            <i class="fas fa-music fa-3x text-muted mb-3"></i>
                            <h5>Drag & Drop Songs Here</h5>
                            <p class="text-muted">or click to browse (MP3, WAV, M4A - Max 10MB)</p>
                            <input type="file" id="songFileInput" multiple accept=".mp3,.wav,.m4a" style="display: none;">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="songsTable">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>File</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="6" class="text-center">Loading songs...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Videos Tab -->
                <div class="tab-pane fade" id="videos">
                    <div class="admin-content p-4">
                        <h3 class="mb-4">Video Management</h3>
                        
                        <!-- Upload Zone -->
                        <div class="upload-zone mb-4" id="videoUploadZone">
                            <i class="fas fa-video fa-3x text-muted mb-3"></i>
                            <h5>Drag & Drop Videos Here</h5>
                            <p class="text-muted">or click to browse (MP4, AVI, MOV - Max 50MB)</p>
                            <input type="file" id="videoFileInput" multiple accept=".mp4,.avi,.mov" style="display: none;">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="videosTable">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>File</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="6" class="text-center">Loading videos...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Backgrounds Tab -->
                <div class="tab-pane fade" id="backgrounds">
                    <div class="admin-content p-4">
                        <h3 class="mb-4">Background Management</h3>
                        
                        <!-- Upload Zone -->
                        <div class="upload-zone mb-4" id="backgroundUploadZone">
                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                            <h5>Drag & Drop Backgrounds Here</h5>
                            <p class="text-muted">or click to browse (JPG, PNG - Max 5MB)</p>
                            <input type="file" id="backgroundFileInput" multiple accept=".jpg,.jpeg,.png" style="display: none;">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="backgroundsTable">
                                <thead>
                                    <tr>
                                        <th>Preview</th>
                                        <th>Name</th>
                                        <th>Size</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="6" class="text-center">Loading backgrounds...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div class="tab-pane fade" id="settings">
                    <div class="admin-content p-4">
                        <h3>System Settings</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>API Configuration</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Base URL:</strong> <code>http://lumen-backend-batasanaya.test/api</code></p>
                                        <p><strong>JWT Expiry:</strong> 1 hour</p>
                                        <p><strong>Max File Size:</strong> 5MB</p>
                                        <p><strong>Supported Formats:</strong> JPG, PNG, MP3, WAV</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>System Info</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Framework:</strong> Lumen 10.0</p>
                                        <p><strong>PHP Version:</strong> 8.1+</p>
                                        <p><strong>Database:</strong> MySQL</p>
                                        <p><strong>Authentication:</strong> JWT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="fullName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createUser()">Create User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editFullName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="editPhoneNumber">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">Update User</button>
            </div>
        </div>
    </div>
</div>

<!-- Game Progress Modal -->
<div class="modal fade" id="gameProgressModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-gamepad me-2"></i>Game Progress - <span id="gameProgressUserName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Stats Summary -->
                <div class="card mb-4">
                    <div class="card-body" id="gameProgressStats">
                        Loading stats...
                    </div>
                </div>
                
                <!-- Level Progress -->
                <h6 class="mb-3"><i class="fas fa-list me-2"></i>Level Progress (17 Levels)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Completion</th>
                                <th>Best Score</th>
                                <th>Best Time</th>
                                <th>Stars</th>
                                <th>Attempts</th>
                            </tr>
                        </thead>
                        <tbody id="gameProgressLevels">
                            <tr><td colspan="7" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Recent Sessions -->
                <h6 class="mb-3"><i class="fas fa-history me-2"></i>Recent Sessions (Last 10)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Score</th>
                                <th>Time</th>
                                <th>Matches</th>
                                <th>Accuracy</th>
                                <th>Stars</th>
                                <th>Completed At</th>
                            </tr>
                        </thead>
                        <tbody id="gameProgressSessions">
                            <tr><td colspan="7" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Asset Modal -->
<div class="modal fade" id="editAssetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAssetForm">
                    <input type="hidden" id="editAssetId">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="editAssetCategory">
                            <option value="">Select Category</option>
                            <option value="hijaiyyah">Hijaiyyah</option>
                            <option value="ui">UI</option>
                            <option value="sound_effects">Sound Effects</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subcategory</label>
                        <select class="form-select" id="editAssetSubcategory">
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateAsset()">Update Asset</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>