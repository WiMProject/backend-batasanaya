<?php
$title = 'API Documentation - Lumen Backend Batasanaya';
ob_start();
?>

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
                    <a class="nav-link" href="#songs">Song Management</a>
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
                    <h2>Overview</h2>
                    <p class="lead">Lumen Backend API untuk aplikasi pembelajaran Hijaiyyah dengan sistem autentikasi JWT lengkap, manajemen file, dan fitur download batch assets.</p>
                    
                    <div class="alert alert-info">
                        <strong>Base URL:</strong> <code>http://lumen-backend-batasanaya.test/api</code>
                    </div>

                    <h4>Features</h4>
                    <ul>
                        <li>Complete JWT Authentication</li>
                        <li>OTP Verification System</li>
                        <li>Role-Based Access Control</li>
                        <li>File Management & Batch Download</li>
                        <li>Song Management with Thumbnails</li>
                        <li>Asset Manifest for Game/App Sync</li>
                    </ul>
                </section>

                <!-- Authentication -->
                <section id="authentication" class="mb-5">
                    <h2>Authentication</h2>
                    
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

                <!-- Assets -->
                <section id="assets" class="mb-5">
                    <h2>Asset Management</h2>
                    
                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-post me-2">POST</span>
                            <code>/assets</code>
                            <span class="badge bg-warning ms-2">Auth Required</span>
                        </div>
                        <div class="card-body">
                            <p>Upload single asset (image/audio, max 5MB)</p>
                            <h6>Form Data:</h6>
                            <pre><code>file: [file] (jpg,jpeg,png,mp3,wav)</code></pre>
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

                <!-- Admin -->
                <section id="admin" class="mb-5">
                    <h2>Admin Endpoints</h2>
                    
                    <div class="alert alert-warning">
                        <strong>Note:</strong> All admin endpoints require JWT token with admin role.
                    </div>

                    <div class="endpoint-card card">
                        <div class="card-header d-flex align-items-center">
                            <span class="method-badge method-get me-2">GET</span>
                            <code>/admin/dashboard</code>
                            <span class="badge bg-danger ms-2">Admin Only</span>
                        </div>
                        <div class="card-body">
                            <p>Get system statistics</p>
                            <h6>Response:</h6>
                            <pre><code>{
  "total_users": 5,
  "total_assets": 25,
  "total_images": 15,
  "total_audio": 10,
  "total_size_mb": 125.5
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
                    <h2>Error Codes</h2>
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
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>