<?php
$title = 'API Documentation - Lumen Backend Batasanaya';
ob_start();
?>

<style>
.doc-sidebar {
    position: sticky;
    top: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
}
.doc-sidebar .nav-link {
    color: #495057;
    padding: 8px 16px;
    border-radius: 4px;
    transition: all 0.2s;
}
.doc-sidebar .nav-link:hover {
    background: #e9ecef;
    color: #007bff;
}
.doc-sidebar .nav-link.active {
    background: #007bff;
    color: white;
}
.endpoint-card {
    margin-bottom: 20px;
    border-left: 4px solid #007bff;
}
.method-badge {
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 12px;
}
.method-get { background: #28a745; color: white; }
.method-post { background: #007bff; color: white; }
.method-patch { background: #ffc107; color: black; }
.method-delete { background: #dc3545; color: white; }
.method-put { background: #17a2b8; color: white; }
pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    overflow-x: auto;
}
code {
    color: #e83e8c;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
}
pre code {
    color: #212529;
    background: transparent;
    padding: 0;
}
.section-header {
    border-bottom: 3px solid #007bff;
    padding-bottom: 10px;
    margin-bottom: 30px;
}
.feature-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}
.quick-link {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s;
}
.quick-link:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.2);
}
</style>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="doc-sidebar p-3">
                <h5 class="mb-3">API Documentation</h5>
                <nav class="nav flex-column">
                    <a class="nav-link" href="#overview">Overview</a>
                    <a class="nav-link" href="#authentication">Authentication</a>
                    <a class="nav-link" href="#users">User Management</a>
                    <a class="nav-link" href="#assets">Asset Management</a>
                    <a class="nav-link" href="#songs">Songs & Videos</a>
                    <a class="nav-link" href="#backgrounds">Backgrounds</a>
                    <a class="nav-link" href="#game-cari">Game Cari Hijaiyyah</a>
                    <a class="nav-link" href="#game-pasangkan">Game Pasangkan Huruf</a>
                    <a class="nav-link" href="#admin">Admin Endpoints</a>
                    <a class="nav-link" href="#errors">Error Codes</a>
                </nav>
            </div>
        </div>

        <!-- Content -->
        <div class="col-lg-9">
            <div class="doc-content p-4">
                <!-- Overview -->
                <section id="overview" class="mb-5">
                    <div class="feature-box">
                        <h1 class="mb-3"><i class="fas fa-book-open me-2"></i>API Documentation</h1>
                        <p class="lead mb-0">Complete REST API untuk aplikasi pembelajaran Hijaiyyah dengan game interaktif, manajemen konten multimedia, dan sistem tracking progress.</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="alert alert-primary">
                                <strong><i class="fas fa-server me-2"></i>Base URL (Local):</strong><br>
                                <code>http://lumen-backend-batasanaya.test/api</code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <strong><i class="fas fa-globe me-2"></i>Base URL (Ngrok):</strong><br>
                                <code>https://your-ngrok-url.ngrok-free.app/api</code>
                            </div>
                        </div>
                    </div>

                    <h3 class="section-header"><i class="fas fa-star me-2"></i>Key Features</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="quick-link mb-3">
                                <h5><i class="fas fa-lock text-primary me-2"></i>Authentication & Security</h5>
                                <ul class="mb-0">
                                    <li>JWT Token Authentication</li>
                                    <li>OTP Verification (6-digit, 5 min expiry)</li>
                                    <li>Role-Based Access (Admin & User)</li>
                                    <li>Password Hashing (bcrypt)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="quick-link mb-3">
                                <h5><i class="fas fa-gamepad text-success me-2"></i>Game Features</h5>
                                <ul class="mb-0">
                                    <li>Game Cari Hijaiyyah (15 Levels)</li>
                                    <li>Game Pasangkan Huruf (15 Levels)</li>
                                    <li>Progress Tracking & Unlock System</li>
                                    <li>Session History & Attempts Counter</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="quick-link mb-3">
                                <h5><i class="fas fa-folder text-warning me-2"></i>Content Management</h5>
                                <ul class="mb-0">
                                    <li>Asset Upload (Batch: 50 files, 100MB each)</li>
                                    <li>Song Management (MP3, WAV, M4A - 10MB)</li>
                                    <li>Video Streaming (MP4, AVI, MOV - 50MB)</li>
                                    <li>Background Management (JPG, PNG - 20MB)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="quick-link mb-3">
                                <h5><i class="fas fa-chart-line text-danger me-2"></i>Admin Dashboard</h5>
                                <ul class="mb-0">
                                    <li>Real-time Statistics</li>
                                    <li>User Management with Game Progress</li>
                                    <li>Dual Game Monitoring (Tabs)</li>
                                    <li>Bulk Operations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="section-header mt-4"><i class="fas fa-rocket me-2"></i>Quick Start Guide</h3>
                    <div class="card">
                        <div class="card-body">
                            <h5>1. Register User</h5>
                            <pre><code>POST /api/auth/register
Content-Type: application/json

{
  "fullName": "John Doe",
  "email": "john@example.com",
  "phone_number": "081234567890",
  "password": "password123"
}</code></pre>

                            <h5 class="mt-3">2. Login & Get Token</h5>
                            <pre><code>POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}

// Response:
{
  "access_token": "eyJ0eXAiOiJKV1Qi...",
  "token_type": "bearer",
  "expires_in": 3600
}</code></pre>

                            <h5 class="mt-3">3. Use Token in Requests</h5>
                            <pre><code>GET /api/carihijaiyah/progress
Authorization: Bearer eyJ0eXAiOiJKV1Qi...</code></pre>

                            <div class="alert alert-warning mt-3">
                                <strong><i class="fas fa-exclamation-triangle me-2"></i>Important:</strong> Token expires in 1 hour. Use <code>POST /api/auth/refresh</code> to get new token.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Authentication -->
                <section id="authentication" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-key me-2"></i>Authentication</h2>
                    <p class="text-muted mb-4">Sistem autentikasi menggunakan JWT (JSON Web Token) dengan expiry 1 jam. Semua protected endpoints membutuhkan token di header.</p>
                    
                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/auth/register</code>
                        </div>
                        <div class="card-body">
                            <p>Register a new user</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "fullName": "John Doe",
  "email": "john@example.com",
  "phone_number": "081234567890",
  "password": "password123"
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/auth/login</code>
                        </div>
                        <div class="card-body">
                            <p>Login user and get JWT token</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "email": "john@example.com",
  "password": "password123"
}</code></pre>
                            <h6>Response:</h6>
                            <pre><code>{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/auth/admin-login</code>
                        </div>
                        <div class="card-body">
                            <p>Quick admin login (admin@example.com / admin123)</p>
                        </div>
                    </div>
                </section>

                <!-- Users -->
                <section id="users" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-users me-2"></i>User Management</h2>
                    
                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/auth/me</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Get current authenticated user data</p>
                            <h6>Response:</h6>
                            <pre><code>{
  "id": "uuid",
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone_number": "081234567890",
  "role": {
    "id": "uuid",
    "name": "user"
  },
  "created_at": "2025-11-25T10:00:00.000000Z"
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-patch me-2">PATCH</span>
                            <code>/users/{id}</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Update user profile</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "fullName": "John Updated",
  "email": "john.new@example.com",
  "phone_number": "081234567891"
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/user/profile-picture</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Upload profile picture (JPG, PNG - Max 5MB)</p>
                            <h6>Form Data:</h6>
                            <pre><code>profile_picture: [image file]</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/auth/request-otp</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Request OTP for verification (6-digit, expires in 5 minutes)</p>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/auth/verify-otp</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Verify OTP code</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "otp": "123456"
}</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Assets -->
                <section id="assets" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-images me-2"></i>Asset Management</h2>
                    <p class="text-muted mb-4">Upload dan kelola assets untuk game (gambar huruf hijaiyyah, audio, dll). Support batch upload sampai 50 files dengan category/subcategory.</p>
                    
                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/assets</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Upload single asset (JPG, PNG, MP3, WAV - Max 100MB)</p>
                            <h6>Form Data:</h6>
                            <pre><code>file: [file]
category: game|ui|sound_effects
subcategory: carihijaiyah|berlatih|pasangkan (for game category)</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/assets/batch</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Upload multiple assets (Max 50 files, 100MB each)</p>
                            <h6>Form Data:</h6>
                            <pre><code>files[0]: [file]
files[1]: [file]
...
files[49]: [file]
category: game
subcategory: carihijaiyah</code></pre>
                            <div class="alert alert-info mt-2">
                                <strong>Tip:</strong> Files akan disimpan dengan nama asli dan diorganisir dalam folder category/subcategory di ZIP download.
                            </div>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/assets</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Get all assets with pagination (20 per page)</p>
                            <h6>Query Parameters:</h6>
                            <pre><code>?page=1</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-patch me-2">PATCH</span>
                            <code>/assets/{id}</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Update asset category/subcategory</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "category": "game",
  "subcategory": "pasangkan"
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-delete me-2">DELETE</span>
                            <code>/assets/{id}</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Delete single asset</p>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/assets/download-all</code>
                            <span class="badge bg-success ms-2">Public</span>
                        </div>
                        <div class="card-body">
                            <p>Download all assets as ZIP</p>
                            <h6>Query Parameters:</h6>
                            <pre><code>?type=image  // Optional: image, audio, or all</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/assets/manifest</code>
                            <span class="badge bg-success ms-2">Public</span>
                        </div>
                        <div class="card-body">
                            <p>Get assets manifest for game/app sync</p>
                            <h6>Response:</h6>
                            <pre><code>{
  "version": 1699123456,
  "total_assets": 10,
  "total_size": 5242880,
  "assets": [
    {
      "id": "uuid",
      "filename": "image.jpg",
      "type": "image",
      "size": 1024000,
      "checksum": "md5hash",
      "download_url": "https://your-url/api/assets/uuid/file"
    }
  ]
}</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Songs & Videos -->
                <section id="songs" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-music me-2"></i>Songs & Videos Management</h2>
                    <p class="text-muted mb-4">Upload dan streaming audio/video files dengan thumbnail support. Cocok untuk konten pembelajaran.</p>
                    
                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/songs</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Upload song with optional thumbnail (MP3, WAV, M4A - Max 10MB)</p>
                            <h6>Form Data:</h6>
                            <pre><code>title: "Song Title"
file: [audio file]
thumbnail: [image file] (optional)</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/songs</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Get all songs with pagination</p>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/songs/{id}/file</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Stream song audio file</p>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/videos</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Upload video (MP4, AVI, MOV - Max 50MB)</p>
                            <h6>Form Data:</h6>
                            <pre><code>title: "Video Title"
file: [video file]</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/videos/{id}/file</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Stream video file</p>
                        </div>
                    </div>
                </section>

                <!-- Backgrounds -->
                <section id="backgrounds" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-image me-2"></i>Background Management</h2>
                    <p class="text-muted mb-4">Kelola background images untuk game dengan status active/inactive. Support file sampai 20MB.</p>
                    
                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/backgrounds</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Upload background image (JPG, PNG - Max 20MB)</p>
                            <h6>Form Data:</h6>
                            <pre><code>name: "Background Name"
file: [image file]</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/backgrounds</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Get all backgrounds with pagination</p>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-patch me-2">PATCH</span>
                            <code>/backgrounds/{id}</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Update background name or status</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "name": "New Name",
  "is_active": true
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/backgrounds/{id}/file</code>
                            <span class="badge bg-success ms-2">Public</span>
                        </div>
                        <div class="card-body">
                            <p>Download background image (public access)</p>
                        </div>
                    </div>
                </section>

                <!-- Game Cari Hijaiyyah -->
                <section id="game-cari" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-search me-2"></i>Game Cari Hijaiyyah API</h2>
                    <div class="alert alert-primary">
                        <strong><i class="fas fa-info-circle me-2"></i>Game Flow:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Get Progress → Check unlocked levels</li>
                            <li>Start Game → Get session_id</li>
                            <li>Play Game → Game logic di mobile app</li>
                            <li>Finish Game → Save score, unlock next level</li>
                        </ol>
                    </div>
                    <p class="text-muted mb-4">15 levels game dengan unlock system (level 1 auto-unlock). Backend hanya menyimpan completion status dan attempts, game logic ada di mobile app.</p>
                    
                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>api/carihijaiyah/progress</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Get user progress for all 15 levels</p>
                            <h6>Response:</h6>
                            <pre><code>{
  "levels": [
    {
      "level_number": 1,
      "is_unlocked": true,
      "is_completed": true,
      "attempts": 2
    }
  ]
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>api/carihijaiyah/start</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Start game session</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "level_number": 1
}</code></pre>
                            <h6>Response:</h6>
                            <pre><code>{
  "session_id": "uuid",
  "level_number": 1,
  "started_at": "2025-11-25 10:30:00"
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>api/carihijaiyah/finish</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Finish game and save session</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "session_id": "uuid",
  "level_number": 1
}</code></pre>
                            <h6>Response:</h6>
                            <pre><code>{
  "message": "Level completed!",
  "level_number": 1,
  "next_level_unlocked": true
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>api/carihijaiyah/stats</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Get user statistics</p>
                            <h6>Response:</h6>
                            <pre><code>{
  "total_levels_completed": 5,
  "total_sessions": 8
}</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Game Pasangkan Huruf -->
                <section id="game-pasangkan" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-puzzle-piece me-2"></i>Game Pasangkan Huruf API</h2>
                    <p class="text-muted mb-4">15 levels game dengan unlock system, stars, dan best score tracking. API endpoints identik dengan Cari Hijaiyyah.</p>
                    
                    <div class="alert alert-info">
                        <strong>Note:</strong> API endpoints sama dengan Cari Hijaiyyah, hanya ganti prefix <code>/carihijaiyah/</code> dengan <code>/pasangkanhuruf/</code>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/pasangkanhuruf/progress</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Get user progress for all 15 levels</p>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>api/pasangkanhuruf/start</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Start game session</p>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>api/pasangkanhuruf/finish</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Finish game and save session</p>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>api/pasangkanhuruf/stats</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Get user statistics</p>
                        </div>
                    </div>
                </section>

                <!-- Admin -->
                <section id="admin" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-user-shield me-2"></i>Admin Endpoints</h2>
                    
                    <div class="alert alert-danger">
                        <strong><i class="fas fa-lock me-2"></i>Admin Only:</strong> Semua endpoint di section ini membutuhkan JWT token dengan role <code>admin</code>. Default admin: <code>admin@example.com</code> / <code>admin123</code>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/admin/stats</code>
                            <span class="badge bg-danger ms-2">Admin Only</span>
                        </div>
                        <div class="card-body">
                            <p>Get system statistics</p>
                            <h6>Response:</h6>
                            <pre><code>{
  "users": 5,
  "assets": 25,
  "songs": 10,
  "videos": 5,
  "backgrounds": 8,
  "storage_mb": 125.5
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/admin/users</code>
                            <span class="badge bg-danger ms-2">Admin Only</span>
                        </div>
                        <div class="card-body">
                            <p>Get all users with game progress summary</p>
                            <h6>Response:</h6>
                            <pre><code>{
  "data": [
    {
      "id": "uuid",
      "full_name": "John Doe",
      "email": "john@example.com",
      "game_progress": {
        "carihijaiyah": {
          "completed": 5
        },
        "pasangkanhuruf": {
          "completed": 3
        }
      }
    }
  ]
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/admin/users/{userId}/game-progress</code>
                            <span class="badge bg-danger ms-2">Admin Only</span>
                        </div>
                        <div class="card-body">
                            <p>Get detailed game progress for specific user (both games)</p>
                            <h6>Response:</h6>
                            <pre><code>{
  "user": {...},
  "carihijaiyah": {
    "progress": [...15 levels],
    "recent_sessions": [...10 sessions],
    "stats": {...}
  },
  "pasangkanhuruf": {
    "progress": [...15 levels],
    "recent_sessions": [...10 sessions],
    "stats": {...}
  }
}</code></pre>
                        </div>
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-delete me-2">DELETE</span>
                            <code>/admin/assets/bulk</code>
                            <span class="badge bg-danger ms-2">Admin Only</span>
                        </div>
                        <div class="card-body">
                            <p>Bulk delete assets</p>
                            <h6>Request Body:</h6>
                            <pre><code>{
  "asset_ids": ["uuid1", "uuid2", "uuid3"]
}</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Error Codes -->
                <section id="errors" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-exclamation-triangle me-2"></i>Error Codes & Responses</h2>
                    <p class="text-muted mb-4">Semua error response menggunakan format JSON dengan field <code>error</code> atau <code>message</code>.</p>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Message</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>401</code></td>
                                    <td>Unauthorized</td>
                                    <td>Invalid or missing JWT token</td>
                                </tr>
                                <tr>
                                    <td><code>403</code></td>
                                    <td>Forbidden</td>
                                    <td>Insufficient permissions</td>
                                </tr>
                                <tr>
                                    <td><code>404</code></td>
                                    <td>Not Found</td>
                                    <td>Resource not found</td>
                                </tr>
                                <tr>
                                    <td><code>422</code></td>
                                    <td>Validation Error</td>
                                    <td>Invalid input data</td>
                                </tr>
                                <tr>
                                    <td><code>500</code></td>
                                    <td>Server Error</td>
                                    <td>Internal server error</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h4 class="mt-4">Example Error Response</h4>
                    <pre><code>{
  "error": "Unauthorized",
  "message": "Token has expired"
}

// Or validation error:
{
  "email": ["The email field is required."],
  "password": ["The password must be at least 6 characters."]
}</code></pre>
                </section>

                <!-- Postman Collection -->
                <section id="postman" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-download me-2"></i>Postman Collections</h2>
                    <p class="text-muted mb-4">Import Postman collections untuk testing API dengan mudah. Collections sudah include environment variables dan auto-save token.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-file-code me-2"></i>Main API Collection</h5>
                                </div>
                                <div class="card-body">
                                    <p>Complete API endpoints untuk authentication, users, assets, songs, videos, backgrounds, dan admin.</p>
                                    <ul>
                                        <li>70+ endpoints</li>
                                        <li>Auto JWT token management</li>
                                        <li>Environment variables</li>
                                        <li>Example requests & responses</li>
                                    </ul>
                                    <a href="/BaTaTsaNaYa_API.postman_collection.json" class="btn btn-primary" download>
                                        <i class="fas fa-download me-2"></i>Download Collection
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-gamepad me-2"></i>Game API Collection</h5>
                                </div>
                                <div class="card-body">
                                    <p>Dedicated collection untuk testing Game Cari Hijaiyyah API dengan flow lengkap.</p>
                                    <ul>
                                        <li>7 endpoints (Login, Progress, Start, Finish, Stats)</li>
                                        <li>Auto-save session_id</li>
                                        <li>Example gameplay flow</li>
                                        <li>Test data included</li>
                                    </ul>
                                    <a href="/Game_Cari_Hijaiyyah_API.postman_collection.json" class="btn btn-success" download>
                                        <i class="fas fa-download me-2"></i>Download Collection
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <h5><i class="fas fa-lightbulb me-2"></i>How to Use Postman Collections:</h5>
                        <ol class="mb-0">
                            <li>Download collection JSON file</li>
                            <li>Open Postman → Import → Upload file</li>
                            <li>Create environment with variable <code>base_url</code> = <code>http://lumen-backend-batasanaya.test</code></li>
                            <li>Run "Login" request first to get token (auto-saved)</li>
                            <li>Test other endpoints</li>
                        </ol>
                    </div>
                </section>

                <!-- Code Examples -->
                <section id="examples" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-code me-2"></i>Code Examples</h2>
                    
                    <h4>JavaScript (Fetch API)</h4>
                    <pre><code>// Login
const login = async () => {
  const response = await fetch('http://lumen-backend-batasanaya.test/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      email: 'user@example.com',
      password: 'password123'
    })
  });
  const data = await response.json();
  localStorage.setItem('token', data.access_token);
};

// Get Game Progress
const getProgress = async () => {
  const token = localStorage.getItem('token');
  const response = await fetch('http://lumen-backend-batasanaya.test/api/carihijaiyah/progress', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  const data = await response.json();
  console.log(data.levels);
};</code></pre>

                    <h4 class="mt-4">Flutter (Dart)</h4>
                    <pre><code>import 'package:http/http.dart' as http;
import 'dart:convert';

// Login
Future<String> login(String email, String password) async {
  final response = await http.post(
    Uri.parse('http://lumen-backend-batasanaya.test/api/auth/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email, 'password': password}),
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return data['access_token'];
  }
  throw Exception('Login failed');
}

// Get Game Progress
Future<List<dynamic>> getProgress(String token) async {
  final response = await http.get(
    Uri.parse('http://lumen-backend-batasanaya.test/api/carihijaiyah/progress'),
    headers: {'Authorization': 'Bearer $token'},
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return data['levels'];
  }
  throw Exception('Failed to load progress');
}</code></pre>

                    <h4 class="mt-4">cURL</h4>
                    <pre><code># Login
curl -X POST http://lumen-backend-batasanaya.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Get Progress (with token)
curl -X GET http://lumen-backend-batasanaya.test/api/carihijaiyah/progress \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Upload Asset
curl -X POST http://lumen-backend-batasanaya.test/api/assets \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "file=@/path/to/image.png" \
  -F "category=game" \
  -F "subcategory=carihijaiyah"</code></pre>
                </section>

                <!-- Support -->
                <section id="support" class="mb-5">
                    <h2 class="section-header"><i class="fas fa-question-circle me-2"></i>Support & Resources</h2>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-book fa-3x text-primary mb-3"></i>
                                    <h5>README</h5>
                                    <p class="text-muted">Complete documentation dengan installation guide dan features</p>
                                    <a href="https://github.com/WiMProject/backend-batasanaya" class="btn btn-outline-primary btn-sm" target="_blank">
                                        View on GitHub
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-tachometer-alt fa-3x text-success mb-3"></i>
                                    <h5>Admin Dashboard</h5>
                                    <p class="text-muted">Web-based admin panel untuk monitoring dan management</p>
                                    <a href="/admin" class="btn btn-outline-success btn-sm">
                                        Open Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fab fa-gitlab fa-3x text-warning mb-3"></i>
                                    <h5>GitLab Repository</h5>
                                    <p class="text-muted">Source code dan version control</p>
                                    <a href="https://gitlab.com/wildanm/backend-batasanaya" class="btn btn-outline-warning btn-sm" target="_blank">
                                        View on GitLab
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-light mt-4 text-center">
                        <p class="mb-2"><strong>Tech Stack:</strong></p>
                        <span class="badge bg-primary me-2">PHP 8.4</span>
                        <span class="badge bg-secondary me-2">Lumen 10.0</span>
                        <span class="badge bg-success me-2">MySQL 8.0</span>
                        <span class="badge bg-info me-2">JWT Auth</span>
                        <span class="badge bg-warning text-dark me-2">Nginx</span>
                        <span class="badge bg-danger">Composer</span>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
// Smooth scroll for sidebar links
document.querySelectorAll('.doc-sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Update active link
            document.querySelectorAll('.doc-sidebar .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

// Highlight active section on scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section[id]');
    let current = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        if (pageYOffset >= sectionTop - 100) {
            current = section.getAttribute('id');
        }
    });
    
    document.querySelectorAll('.doc-sidebar .nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>