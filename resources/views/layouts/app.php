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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-cube me-2"></i>Lumen Backend Batasanaya
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/docs"><i class="fas fa-book me-1"></i>API Docs</a>
                <a class="nav-link" href="/admin/login"><i class="fas fa-tachometer-alt me-1"></i>Admin</a>
            </div>
        </div>
    </nav>

    <main>
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>