<?php
$title = 'Lumen Backend Batasanaya - Hijaiyyah Learning API';
ob_start();
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container py-5">
        <div class="row justify-content-center align-items-center min-vh-75">
            <div class="col-lg-10 text-center">
                <div class="hero-content">
                    <div class="hero-icon mb-4">
                        <i class="fas fa-cube fa-5x"></i>
                    </div>
                    <h1 class="display-3 fw-bold mb-4 text-gradient">Lumen Backend Batasanaya</h1>
                    <p class="lead mb-5" style="color: rgba(255,255,255,0.9); font-size: 1.25rem;">API backend untuk aplikasi pembelajaran Hijaiyyah dengan sistem autentikasi JWT lengkap, manajemen file multimedia, dan fitur download batch assets.</p>
                    
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="/docs" class="btn btn-light btn-lg px-4 py-3">
                            <i class="fas fa-book me-2"></i>API Documentation
                        </a>
                        <a href="/admin/login" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Admin Panel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="features-section py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Powerful Features</h2>
            <p class="lead text-muted">Everything you need for your Hijaiyyah learning app</p>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">JWT Authentication</h5>
                    <p class="text-muted small">Secure token-based authentication with role management</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-cloud-upload-alt fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">File Management</h5>
                    <p class="text-muted small">Upload, download, and batch operations for assets</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-mobile-alt fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Game Integration</h5>
                    <p class="text-muted small">Asset manifest and sync for mobile game/app</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-users-cog fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Admin Dashboard</h5>
                    <p class="text-muted small">Professional panel for managing everything</p>
                </div>
            </div>
        </div>

        <!-- Quick Start & System Info -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="info-card">
                    <h3 class="mb-4"><i class="fas fa-rocket me-2"></i>Quick Start</h3>
                    <div class="quick-start-item">
                        <div class="step-number">1</div>
                        <div>
                            <h6 class="mb-1">Admin Login</h6>
                            <code class="d-block mb-1">POST /api/auth/admin-login</code>
                            <small class="text-muted">Default: admin@test.com / admin123</small>
                        </div>
                    </div>
                    <div class="quick-start-item">
                        <div class="step-number">2</div>
                        <div>
                            <h6 class="mb-1">Upload Asset</h6>
                            <code class="d-block mb-1">POST /api/assets</code>
                            <small class="text-muted">Requires JWT token in Authorization header</small>
                        </div>
                    </div>
                    <div class="quick-start-item">
                        <div class="step-number">3</div>
                        <div>
                            <h6 class="mb-1">Download Assets</h6>
                            <code class="d-block mb-1">GET /api/assets/download-all</code>
                            <small class="text-muted">Public endpoint, returns ZIP file</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="info-card">
                    <h3 class="mb-4"><i class="fas fa-info-circle me-2"></i>System Info</h3>
                    <div class="system-info-grid">
                        <div class="system-info-item">
                            <i class="fas fa-code"></i>
                            <div>
                                <strong>Framework</strong>
                                <span>Laravel Lumen 10.0</span>
                            </div>
                        </div>
                        <div class="system-info-item">
                            <i class="fab fa-php"></i>
                            <div>
                                <strong>PHP Version</strong>
                                <span>8.1+</span>
                            </div>
                        </div>
                        <div class="system-info-item">
                            <i class="fas fa-database"></i>
                            <div>
                                <strong>Database</strong>
                                <span>MySQL 8.0+</span>
                            </div>
                        </div>
                        <div class="system-info-item">
                            <i class="fas fa-key"></i>
                            <div>
                                <strong>Authentication</strong>
                                <span>JWT</span>
                            </div>
                        </div>
                        <div class="system-info-item">
                            <i class="fas fa-file-image"></i>
                            <div>
                                <strong>File Support</strong>
                                <span>JPG, PNG, MP3, WAV</span>
                            </div>
                        </div>
                        <div class="system-info-item">
                            <i class="fas fa-hdd"></i>
                            <div>
                                <strong>Max File Size</strong>
                                <span>5MB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/app.php';
?>