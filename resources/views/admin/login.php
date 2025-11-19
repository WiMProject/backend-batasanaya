<?php
$title = 'Admin Login - Lumen Backend Batasanaya';
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h3>Admin Login</h3>
                        <p class="text-muted">Enter your admin credentials</p>
                    </div>

                    <form id="adminLoginForm">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="admin@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" value="admin123" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </button>
                    </form>

                    <div class="text-center">
                        <small class="text-muted">
                            Default: admin@example.com / admin123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('/api/auth/login', {
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
            alert('Login failed: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Login failed: Network error');
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>