<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batasanaya Backend</title>
    <style>
        body { margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #0f172a; font-family: 'Segoe UI', sans-serif; color: #f8fafc; }
        .card { text-align: center; background: #1e293b; padding: 3rem 4rem; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); border: 1px solid #334155; }
        h1 { margin: 0 0 1rem 0; font-size: 2rem; font-weight: 700; background: linear-gradient(to right, #38bdf8, #818cf8); -webkit-background-clip: text; color: transparent; }
        .status-badge { display: inline-flex; align-items: center; padding: 0.5rem 1rem; background: rgba(34, 197, 94, 0.1); color: #4ade80; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; border: 1px solid rgba(34, 197, 94, 0.2); }
        .dot { width: 8px; height: 8px; background: #4ade80; border-radius: 50%; margin-right: 0.5rem; box-shadow: 0 0 8px #4ade80; animation: pulse 2s infinite; }
        p { color: #94a3b8; margin-top: 2rem; font-size: 0.9rem; }
        .version { margin-top: 0.5rem; font-family: monospace; color: #64748b; font-size: 0.8rem; }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Batasanaya API</h1>
        <div class="status-badge">
            <span class="dot"></span>
            System Operational
        </div>
        <p>Server is running securely.</p>
        <div class="version">v1.2.0-stable</div>
    </div>
</body>
</html>
