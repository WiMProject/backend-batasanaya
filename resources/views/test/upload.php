<?php
$title = 'Asset Upload Testing';
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1>🧪 Asset Upload Testing</h1>
                <p class="text-muted">Test category/subcategory structure</p>
            </div>

            <!-- Login -->
            <div class="card mb-4" id="loginSection">
                <div class="card-body">
                    <h5>🔐 Login</h5>
                    <input type="email" class="form-control mb-2" id="email" value="admin@mail.com">
                    <input type="password" class="form-control mb-2" id="password" value="admin123">
                    <button class="btn btn-primary w-100" onclick="login()">Login</button>
                </div>
            </div>

            <!-- Upload -->
            <div class="card" id="uploadSection" style="display:none;">
                <div class="card-body">
                    <h5>📤 Upload Assets</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Category *</label>
                            <select class="form-select" id="category">
                                <option value="">Select</option>
                                <option value="game">Game</option>
                                <option value="ui">UI</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Subcategory</label>
                            <select class="form-select" id="subcategory" disabled>
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <input type="file" class="form-control" id="files" multiple>
                    </div>

                    <button class="btn btn-success w-100" onclick="upload()">Upload</button>
                    
                    <div id="result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API = '/api/test/assets';
let token = '';

const subcats = {
    game: ['carihijaiyah', 'pasangkanhuruf'],
    ui: ['buttons', 'icons']
};

document.getElementById('category').addEventListener('change', function() {
    const sub = document.getElementById('subcategory');
    sub.disabled = !this.value;
    sub.innerHTML = '<option value="">Select</option>';
    if (subcats[this.value]) {
        subcats[this.value].forEach(s => {
            sub.innerHTML += `<option value="${s}">${s}</option>`;
        });
    }
});

async function login() {
    const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        })
    });
    const data = await res.json();
    token = data.access_token;
    document.getElementById('loginSection').style.display = 'none';
    document.getElementById('uploadSection').style.display = 'block';
}

async function upload() {
    const category = document.getElementById('category').value;
    if (!category) return alert('Select category!');
    
    const formData = new FormData();
    const files = document.getElementById('files').files;
    
    for (let file of files) {
        formData.append('files[]', file);
    }
    formData.append('category', category);
    
    const sub = document.getElementById('subcategory').value;
    if (sub) formData.append('subcategory', sub);
    
    const res = await fetch(`${API}/batch`, {
        method: 'POST',
        headers: {'Authorization': `Bearer ${token}`},
        body: formData
    });
    
    const data = await res.json();
    document.getElementById('result').innerHTML = `
        <div class="alert alert-success">
            ✅ ${data.uploaded?.length || 0} uploaded, ${data.updated?.length || 0} updated
            <pre>${JSON.stringify(data, null, 2)}</pre>
        </div>
    `;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
