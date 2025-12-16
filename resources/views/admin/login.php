<?php
$title = 'Admin Login - Lumen Backend Batasanaya';
ob_start();
?>

<div class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="login-card">
                    <div class="login-header text-center mb-4">
                        <div class="login-icon mb-3">
                            <i class="fas fa-shield-alt fa-4x"></i>
                        </div>
                        <h2 class="fw-bold mb-2">Admin Login</h2>
                        <p class="text-muted">Enter your credentials to continue</p>
                    </div>

                    <form id="adminLoginForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" value="admin@test.com" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" value="admin123" placeholder="Enter your password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard
                        </button>
                    </form>

                    <div class="login-footer text-center">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            <small><strong>Default:</strong> admin@test.com / admin123</small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="/" class="text-white text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Check if already logged in
if (localStorage.getItem('adminToken')) {
    window.location.href = '/admin';
}

document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Logging in...';
    
    try {
        const response = await fetch('/api/auth/admin-login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (data.access_token) {
            localStorage.setItem('adminToken', data.access_token);
            window.location.href = '/admin';
        } else {
            alert('Login failed: ' + (data.error || 'Invalid credentials'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-1"></i>Login';
        }
    } catch (error) {
        alert('Login failed: Network error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-1"></i>Login';
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>