<?php
$title = 'Lumen Backend Batasanaya - Hijaiyyah Learning API';
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <div class="mb-5">
                <i class="fas fa-cube fa-4x text-primary mb-4"></i>
                <h1 class="display-4 fw-bold mb-3">Lumen Backend Batasanaya</h1>
                <p class="lead text-muted">API backend untuk aplikasi pembelajaran Hijaiyyah dengan sistem autentikasi JWT lengkap, manajemen file, dan fitur download batch assets.</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-book fa-3x text-primary mb-3"></i>
                            <h4>API Documentation</h4>
                            <p class="text-muted">Comprehensive API documentation with examples and testing tools</p>
                            <a href="/docs" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-1"></i>View Docs
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-tachometer-alt fa-3x text-success mb-3"></i>
                            <h4>Admin Dashboard</h4>
                            <p class="text-muted">Professional admin panel for managing users, assets, and system settings</p>
                            <a href="/admin/login" class="btn btn-success">
                                <i class="fas fa-arrow-right me-1"></i>Admin Panel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-shield-alt fa-2x text-info mb-2"></i>
                        <h5>JWT Authentication</h5>
                        <p class="text-muted small">Secure token-based authentication with role management</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-cloud-upload-alt fa-2x text-warning mb-2"></i>
                        <h5>File Management</h5>
                        <p class="text-muted small">Upload, download, and batch operations for assets</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-mobile-alt fa-2x text-danger mb-2"></i>
                        <h5>Game Integration</h5>
                        <p class="text-muted small">Asset manifest and sync for mobile game/app</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h3>Quick Start</h3>
                <div class="mb-3">
                    <h6>1. Admin Login</h6>
                    <code>POST /api/auth/admin-login</code>
                    <small class="d-block text-muted">Default: admin@example.com / admin123</small>
                </div>
                <div class="mb-3">
                    <h6>2. Upload Asset</h6>
                    <code>POST /api/assets</code>
                    <small class="d-block text-muted">Requires JWT token in Authorization header</small>
                </div>
                <div class="mb-3">
                    <h6>3. Download Assets</h6>
                    <code>GET /api/assets/download-all</code>
                    <small class="d-block text-muted">Public endpoint, returns ZIP file</small>
                </div>
            </div>
            <div class="col-lg-6">
                <h3>System Info</h3>
                <ul class="list-unstyled">
                    <li><strong>Framework:</strong> Laravel Lumen 10.0</li>
                    <li><strong>PHP Version:</strong> 8.1+</li>
                    <li><strong>Database:</strong> MySQL 8.0+</li>
                    <li><strong>Authentication:</strong> JWT (tymon/jwt-auth)</li>
                    <li><strong>File Support:</strong> JPG, PNG, MP3, WAV</li>
                    <li><strong>Max File Size:</strong> 5MB</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/app.php';
?>