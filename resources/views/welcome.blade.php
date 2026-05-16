<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service C — Keanggotaan &amp; Voucher | Smart Parking</title>
    <meta name="description" content="API Service C untuk mengelola data keanggotaan dan voucher Smart Parking System">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-primary: #0a0e1a;
            --bg-secondary: #111827;
            --bg-card: rgba(17, 24, 39, 0.7);
            --border: rgba(255, 255, 255, 0.06);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-blue: #3b82f6;
            --accent-emerald: #10b981;
            --accent-violet: #8b5cf6;
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --glow-blue: rgba(59, 130, 246, 0.15);
            --glow-emerald: rgba(16, 185, 129, 0.15);
            --glow-violet: rgba(139, 92, 246, 0.15);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
        }

        .bg-glow {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.3;
            z-index: 0;
            animation: float 20s ease-in-out infinite;
        }

        .bg-glow-1 {
            background: var(--accent-blue);
            top: -200px;
            right: -100px;
            animation-delay: 0s;
        }

        .bg-glow-2 {
            background: var(--accent-violet);
            bottom: -200px;
            left: -100px;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -20px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem;
        }

        /* ─── Header ─── */
        .header {
            text-align: center;
            padding: 3rem 0 2.5rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--accent-blue);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .badge .dot {
            width: 6px;
            height: 6px;
            background: var(--accent-emerald);
            border-radius: 50%;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            50% { opacity: 0.7; box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #f1f5f9 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.75rem;
        }

        .header p {
            font-size: 1.05rem;
            color: var(--text-secondary);
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ─── Info Bar ─── */
        .info-bar {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin: 1.5rem 0 2.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .info-item span {
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* ─── Navigation Cards ─── */
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }

        .nav-card {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            padding: 1.75rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            text-decoration: none;
            color: inherit;
            backdrop-filter: blur(12px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .nav-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 16px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .nav-card:hover {
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.5);
        }

        .nav-card:hover::before {
            opacity: 1;
        }

        .nav-card--swagger::before { background: var(--glow-emerald); }
        .nav-card--graphql::before { background: var(--glow-violet); }
        .nav-card--health::before { background: var(--glow-blue); }

        .nav-card__icon {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .nav-card--swagger .nav-card__icon {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.15);
            color: var(--accent-emerald);
        }

        .nav-card--graphql .nav-card__icon {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.15);
            color: var(--accent-violet);
        }

        .nav-card--health .nav-card__icon {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.15);
            color: var(--accent-blue);
        }

        .nav-card__body {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .nav-card__title {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-card__title .arrow {
            opacity: 0;
            transform: translateX(-4px);
            transition: all 0.3s;
            color: var(--text-muted);
        }

        .nav-card:hover .nav-card__title .arrow {
            opacity: 1;
            transform: translateX(0);
        }

        .nav-card__desc {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .nav-card__url {
            display: inline-block;
            margin-top: 0.65rem;
            font-size: 0.72rem;
            font-family: 'SF Mono', 'Fira Code', monospace;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.04);
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* ─── API Endpoints Section ─── */
        .section-title {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin-bottom: 1rem;
            padding-left: 0.25rem;
        }

        .endpoints-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .endpoint-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }

        .endpoint-row:last-child { border-bottom: none; }
        .endpoint-row:hover { background: rgba(255, 255, 255, 0.02); }

        .method-badge {
            flex-shrink: 0;
            width: 60px;
            text-align: center;
            padding: 0.3rem 0;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .method-badge--get {
            background: rgba(59, 130, 246, 0.12);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .method-badge--post {
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .endpoint-path {
            font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace;
            font-size: 0.82rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .endpoint-desc {
            margin-left: auto;
            font-size: 0.78rem;
            color: var(--text-muted);
            text-align: right;
        }

        /* ─── Auth Section ─── */
        .auth-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .auth-card__header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .auth-card__icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .auth-card__title {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .auth-code {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            padding: 0.85rem 1.25rem;
            font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace;
            font-size: 0.82rem;
        }

        .auth-code__label {
            color: var(--accent-amber);
            font-weight: 600;
        }

        .auth-code__value {
            color: var(--text-secondary);
        }

        .auth-code__copy {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .auth-code__copy:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        /* ─── Footer ─── */
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
        }

        .footer-left {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .footer-left strong {
            color: var(--text-secondary);
            font-weight: 600;
        }

        .footer-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.72rem;
            color: var(--text-muted);
        }

        .footer-tech {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.6rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 6px;
            font-size: 0.7rem;
            color: var(--text-secondary);
        }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .header h1 { font-size: 1.8rem; }
            .nav-grid { grid-template-columns: 1fr; }
            .endpoint-desc { display: none; }
            .info-bar { gap: 1rem; }
            .footer { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="badge">
                <span class="dot"></span>
                Service C — Smart Parking System
            </div>
            <h1>API Keanggotaan &amp; Voucher</h1>
            <p>Service untuk mengelola data membership dan voucher parkir pada sistem Smart Parking — Kelompok 6</p>
        </header>

        <!-- Info Bar -->
        <div class="info-bar">
            <div class="info-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <span>v1.0.0</span>
            </div>
            <div class="info-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Dinda Juniar</span> · 102022400023
            </div>
            <div class="info-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Kelompok 6</span>
            </div>
        </div>

        <!-- Navigation Cards -->
        <div class="nav-grid">
            <a href="/api/docs" class="nav-card nav-card--swagger" id="nav-swagger">
                <div class="nav-card__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div class="nav-card__body">
                    <div class="nav-card__title">Swagger UI <span class="arrow">→</span></div>
                    <div class="nav-card__desc">Dokumentasi API interaktif dengan OpenAPI 3.0. Uji coba endpoint langsung dari browser.</div>
                    <span class="nav-card__url">/api/docs</span>
                </div>
            </a>

            <a href="/graphiql" class="nav-card nav-card--graphql" id="nav-graphql">
                <div class="nav-card__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                </div>
                <div class="nav-card__body">
                    <div class="nav-card__title">GraphQL Playground <span class="arrow">→</span></div>
                    <div class="nav-card__desc">Query data membership dan voucher secara fleksibel menggunakan GraphQL.</div>
                    <span class="nav-card__url">/graphiql</span>
                </div>
            </a>

            <a href="/health" class="nav-card nav-card--health" id="nav-health">
                <div class="nav-card__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <div class="nav-card__body">
                    <div class="nav-card__title">Health Check <span class="arrow">→</span></div>
                    <div class="nav-card__desc">Periksa status dan ketersediaan layanan secara real-time.</div>
                    <span class="nav-card__url">/health</span>
                </div>
            </a>
        </div>

        <!-- REST API Endpoints -->
        <div class="section-title">REST API Endpoints</div>
        <div class="endpoints-card">
            <div class="endpoint-row">
                <span class="method-badge method-badge--get">GET</span>
                <span class="endpoint-path">/api/v1/memberships</span>
                <span class="endpoint-desc">Melihat daftar seluruh member</span>
            </div>
            <div class="endpoint-row">
                <span class="method-badge method-badge--get">GET</span>
                <span class="endpoint-path">/api/v1/memberships/{id}</span>
                <span class="endpoint-desc">Mengecek detail dan status aktif seorang member</span>
            </div>
            <div class="endpoint-row">
                <span class="method-badge method-badge--post">POST</span>
                <span class="endpoint-path">/api/v1/memberships</span>
                <span class="endpoint-desc">Mendaftarkan member baru</span>
            </div>
        </div>

        <!-- Auth Section -->
        <div class="section-title">Autentikasi</div>
        <div class="auth-card">
            <div class="auth-card__header">
                <div class="auth-card__icon">🔑</div>
                <div class="auth-card__title">Header X-IAE-KEY wajib dikirim pada setiap request</div>
            </div>
            <div class="auth-code">
                <span class="auth-code__label">X-IAE-KEY:</span>
                <span class="auth-code__value" id="api-key-value">102022400023</span>
                <button class="auth-code__copy" id="btn-copy" onclick="copyKey()">Copy</button>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-left">
                <strong>Tugas Besar</strong> — Integrasi Aplikasi Enterprise (BBK2HAB3)
            </div>
            <div class="footer-right">
                <span class="footer-tech">Laravel {{ app()->version() }}</span>
                <span class="footer-tech">PHP {{ PHP_VERSION }}</span>
                <span class="footer-tech">MySQL 8.0</span>
            </div>
        </footer>
    </div>

    <script>
        function copyKey() {
            const key = document.getElementById('api-key-value').textContent;
            navigator.clipboard.writeText(key).then(() => {
                const btn = document.getElementById('btn-copy');
                btn.textContent = 'Copied!';
                btn.style.color = '#10b981';
                setTimeout(() => {
                    btn.textContent = 'Copy';
                    btn.style.color = '';
                }, 1500);
            });
        }
    </script>
</body>
</html>
