<?php
$title = 'Lumen Backend Batasanaya - Hijaiyyah Learning API';
ob_start();
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container py-5">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
            <div class="col-lg-10 text-center">
                <div class="hero-content">
                    <div class="hero-icon mb-4">
                        <i class="fas fa-cube fa-5x"></i>
                    </div>
                    <h1 class="display-3 fw-bold mb-4 text-gradient">Lumen Backend Batasanaya</h1>
                    <p class="lead mb-5" style="color: rgba(255,255,255,0.95); font-size: 1.3rem; max-width: 800px; margin: 0 auto;">Complete REST API untuk aplikasi pembelajaran Hijaiyyah dengan JWT authentication, file management, game integration, dan admin dashboard yang powerful.</p>
                    
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="/docs" class="btn btn-light btn-lg px-5 py-3">
                            <i class="fas fa-book me-2"></i>Explore API Docs
                        </a>
                        <a href="/admin/login" class="btn btn-outline-light btn-lg px-5 py-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
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
            <p class="lead text-muted">Everything you need for your Hijaiyyah learning application</p>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">JWT Authentication</h5>
                    <p class="text-muted small mb-0">Secure token-based auth with role management & OTP verification</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-cloud-upload-alt fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">File Management</h5>
                    <p class="text-muted small mb-0">Upload, download, batch operations with category organization</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-gamepad fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Game Integration</h5>
                    <p class="text-muted small mb-0">15 levels tracking, progress monitoring, session history</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-users-cog fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Admin Dashboard</h5>
                    <p class="text-muted small mb-0">Professional web panel for complete system management</p>
                </div>
            </div>
        </div>

        <!-- Quick Start & System Info -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="info-card">
                    <h3 class="mb-4"><i class="fas fa-rocket me-2 text-primary"></i>Quick Start</h3>
                    <div class="quick-start-item">
                        <div class="step-number">1</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">Register / Login</h6>
                            <code class="d-block mb-1">POST /api/auth/login</code>
                            <small class="text-muted">Get JWT token for authentication</small>
                        </div>
                    </div>
                    <div class="quick-start-item">
                        <div class="step-number">2</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">Upload Assets</h6>
                            <code class="d-block mb-1">POST /api/assets</code>
                            <small class="text-muted">Upload images, audio files with categories</small>
                        </div>
                    </div>
                    <div class="quick-start-item">
                        <div class="step-number">3</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">Download & Sync</h6>
                            <code class="d-block mb-1">GET /api/assets/manifest</code>
                            <small class="text-muted">Get manifest for game/app synchronization</small>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <a href="/docs" class="btn btn-primary w-100">
                            <i class="fas fa-book me-2"></i>View Full Documentation
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="info-card">
                    <h3 class="mb-4"><i class="fas fa-info-circle me-2 text-primary"></i>System Information</h3>
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
                                <span>JWT (tymon/jwt-auth)</span>
                            </div>
                        </div>
                        <div class="system-info-item">
                            <i class="fas fa-file-image"></i>
                            <div>
                                <strong>File Support</strong>
                                <span>JPG, PNG, MP3, WAV, MP4</span>
                            </div>
                        </div>
                        <div class="system-info-item">
                            <i class="fas fa-hdd"></i>
                            <div>
                                <strong>Max Upload</strong>
                                <span>100MB per file</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="/admin/login" class="btn btn-outline-primary flex-fill">
                                <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                            </a>
                            <a href="https://github.com" target="_blank" class="btn btn-outline-secondary flex-fill">
                                <i class="fab fa-github me-2"></i>GitHub
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="row g-4 mt-4">
            <div class="col-md-3">
                <div class="text-center p-4 bg-white rounded-4 shadow-sm">
                    <h2 class="fw-bold text-primary mb-2">70+</h2>
                    <p class="text-muted mb-0">API Endpoints</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-4 bg-white rounded-4 shadow-sm">
                    <h2 class="fw-bold text-success mb-2">15</h2>
                    <p class="text-muted mb-0">Game Levels</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-4 bg-white rounded-4 shadow-sm">
                    <h2 class="fw-bold text-warning mb-2">100%</h2>
                    <p class="text-muted mb-0">Type Safe</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center p-4 bg-white rounded-4 shadow-sm">
                    <h2 class="fw-bold text-danger mb-2">24/7</h2>
                    <p class="text-muted mb-0">Available</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/app.php';
?>