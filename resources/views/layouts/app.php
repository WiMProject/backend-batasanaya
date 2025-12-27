<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Lumen Backend Batasanaya' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/app.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/">
                <div class="brand-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <span>Lumen Backend</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="/">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                    <a class="nav-link" href="/docs">
                        <i class="fas fa-book me-1"></i>API Docs
                    </a>
                    <a class="nav-link" href="/admin/login">
                        <i class="fas fa-tachometer-alt me-1"></i>Admin Panel
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <?= $content ?>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> Lumen Backend Batasanaya. Built with <i class="fas fa-heart text-danger"></i> using Lumen 10.0</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script src="/js/app.js?v=<?= time() ?>"></script>
    <?php if (isset($isAdminPage) && $isAdminPage): ?>
    <script src="/js/bulk-delete.js?v=<?= time() ?>"></script>
    <script src="/js/admin.js?v=<?= time() ?>"></script>
    <?php endif; ?>
</body>
</html>