<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Lumen Backend Batasanaya' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2c3e50 !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/" style="color: white !important;">
                <div class="brand-icon me-2" style="background: rgba(255,255,255,0.2); padding: 8px; border-radius: 8px;">
                    <i class="fas fa-cube"></i>
                </div>
                <span class="brand-text" style="color: white !important;">Lumen Backend</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="/docs" style="color: white !important;">
                        <i class="fas fa-book me-1"></i>API Docs
                    </a>
                    <a class="nav-link" href="/admin/login" style="color: white !important;">
                        <i class="fas fa-tachometer-alt me-1"></i>Admin Panel
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script src="/js/app.js?v=<?= time() ?>"></script>
</body>
</html>