<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Digital City - Smart Parking Service A Dashboard</title>
    <!-- Google Fonts: Inter for UI, JetBrains Mono for Code -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-main: #0a0c10;
            --bg-card: #11151d;
            --bg-input: #1b212c;
            --border: #222b3c;
            --border-focus: #3b82f6;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --text-muted-dark: #6b7280;
            
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --primary-glow: rgba(59, 130, 246, 0.15);
            
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.15);
            
            --warning: #f59e0b;
            --danger: #ef4444;
            --danger-glow: rgba(239, 68, 68, 0.15);
            
            --rabbitmq: #f37022;
            --rabbitmq-glow: rgba(243, 112, 34, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            line-height: 1.5;
            padding: 2rem;
            min-height: 100vh;
        }

        /* Container Layout */
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Card */
        header {
            background: linear-gradient(135deg, #11151d 0%, #171d28 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--success), var(--rabbitmq));
        }

        .header-title h1 {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(90deg, #ffffff 0%, #d1d5db 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .header-title p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .developer-badge {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            font-size: 0.85rem;
        }

        .developer-badge div {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .developer-badge strong {
            color: #ffffff;
        }

        .badge-tag {
            background-color: var(--primary-glow);
            color: var(--primary);
            padding: 0.1rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* Config Card Grid */
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .config-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .config-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            shrink: 0;
        }

        .config-icon.sso { background-color: var(--primary-glow); color: var(--primary); }
        .config-icon.soap { background-color: var(--success-glow); color: var(--success); }
        .config-icon.mq { background-color: var(--rabbitmq-glow); color: var(--rabbitmq); }
        .config-icon.team { background-color: rgba(245, 158, 11, 0.1); color: var(--warning); }

        .config-info h4 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.2rem;
        }

        .config-info code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }

        /* Main Workspace Grid */
        .workspace-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .workspace-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .section-header {
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Forms styling */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .form-control {
            background-color: var(--bg-input);
            border: 1px solid var(--border);
            color: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 2px var(--primary-glow);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: inherit;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-success {
            background-color: var(--success);
            color: #ffffff;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* SSO Profile Panel */
        .token-display {
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            font-size: 0.8rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .token-scroll {
            font-family: 'JetBrains Mono', monospace;
            max-height: 80px;
            overflow-y: auto;
            word-break: break-all;
            color: var(--text-muted);
            padding: 0.5rem;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 6px;
        }

        .profile-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .profile-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
            padding-bottom: 0.3rem;
        }

        .profile-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .profile-row span {
            color: var(--text-muted);
        }

        .profile-row strong {
            color: #ffffff;
        }

        /* Locations and Logs Lists */
        .list-container {
            max-height: 400px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding-right: 0.25rem;
        }

        .location-item {
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .location-item:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .location-info h4 {
            font-size: 0.95rem;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .location-info p {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .location-meta {
            text-align: right;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .price-tag {
            font-weight: 700;
            color: var(--success);
            font-size: 0.95rem;
        }

        .spot-badge {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            font-size: 0.75rem;
            color: var(--text-main);
        }

        /* Logs Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            text-align: left;
        }

        th {
            background-color: rgba(255, 255, 255, 0.02);
            color: var(--text-muted);
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 2px solid var(--border);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tr:hover td {
            background-color: rgba(255, 255, 255, 0.01);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.success {
            background-color: var(--success-glow);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-badge.failed {
            background-color: var(--danger-glow);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* XML Modal and Logs Panel */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 2rem;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            width: 100%;
            max-width: 900px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .close-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #ffffff;
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .xml-pane {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .xml-pane h4 {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .xml-box {
            background-color: #05070a;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #a7f3d0;
            overflow-x: auto;
            white-space: pre-wrap;
            max-height: 250px;
        }

        .xml-box.request {
            color: #93c5fd;
        }

        /* RabbitMQ Alert Information */
        .info-box {
            background-color: var(--rabbitmq-glow);
            border: 1px solid rgba(243, 112, 34, 0.2);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .info-icon {
            font-size: 1.75rem;
            color: var(--rabbitmq);
        }

        .info-text h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .info-text p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .info-text a {
            color: var(--rabbitmq);
            font-weight: 600;
            text-decoration: underline;
        }

        .info-text a:hover {
            color: #f97316;
        }

        /* Integration Status Badges on Creation */
        .int-status-container {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .int-status-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .int-status-badge.active {
            background-color: var(--success-glow);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .int-status-badge.inactive {
            background-color: var(--danger-glow);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Spinner */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.1);
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border-left-color: #ffffff;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header Card -->
    <header>
        <div class="header-title">
            <h1>Enterprise Digital City</h1>
            <p>Service A: Lahan & Lokasi Parkir Dashboard • Tugas 3 IAE</p>
        </div>
        <div class="developer-badge">
            <div>
                <strong>Farid Maulana</strong>
                <span class="badge-tag">Student Developer</span>
            </div>
            <div>NIM: 102022400039 • Kelas: IAE-2026</div>
        </div>
    </header>

    <!-- Configuration Values Bar -->
    <div class="config-grid">
        <div class="config-card">
            <div class="config-icon sso">👤</div>
            <div class="config-info">
                <h4>SSO Endpoint</h4>
                <code>{{ env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id') }}</code>
            </div>
        </div>
        <div class="config-card">
            <div class="config-icon soap">📜</div>
            <div class="config-info">
                <h4>SOAP Audit System</h4>
                <code>/soap/v1/audit</code>
            </div>
        </div>
        <div class="config-card">
            <div class="config-icon mq">🐇</div>
            <div class="config-info">
                <h4>RabbitMQ Exchange</h4>
                <code>iae.central.exchange</code>
            </div>
        </div>
        <div class="config-card">
            <div class="config-icon team">👥</div>
            <div class="config-info">
                <h4>Kelompok / Team ID</h4>
                <code>{{ env('IAE_TEAM_ID', 'TEAM-06') }}</code>
            </div>
        </div>
    </div>

    <!-- Main Workspace Grid -->
    <div class="workspace-grid">
        <!-- LEFT COLUMN: SSO and Location form -->
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            <!-- Federated SSO Card -->
            <div class="section-card">
                <div class="section-header">
                    <h2>🔑 Federated SSO Authentication</h2>
                    <span id="auth-status-dot" style="width: 10px; height: 10px; border-radius: 50%; background-color: var(--danger);"></span>
                </div>
                
                <!-- If not authenticated -->
                <div id="sso-login-forms">
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <button class="btn btn-secondary" style="flex: 1;" onclick="toggleAuthType('user')" id="btn-toggle-user">SSO Warga</button>
                        <button class="btn btn-secondary" style="flex: 1;" onclick="toggleAuthType('m2m')" id="btn-toggle-m2m">SSO Machine-to-Machine</button>
                    </div>

                    <!-- User Login Form -->
                    <form id="form-sso-user" onsubmit="handleSsoLogin(event, 'user')">
                        <div class="form-group">
                            <label>Email Warga</label>
                            <input type="email" id="sso-email" class="form-control" placeholder="warga20@ktp.iae.id" value="{{ env('IAE_SSO_EMAIL', 'warga20@ktp.iae.id') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" id="sso-password" class="form-control" placeholder="••••••••" value="{{ env('IAE_SSO_PASSWORD', 'KtpDigital2026!') }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="btn-login-user">
                            <span class="btn-text">Authenticate via SSO Warga</span>
                        </button>
                    </form>

                    <!-- M2M Login Form -->
                    <form id="form-sso-m2m" onsubmit="handleSsoLogin(event, 'm2m')" style="display: none;">
                        <div class="form-group">
                            <label>API Key Mahasiswa</label>
                            <input type="text" id="sso-api-key" class="form-control" placeholder="KEY-MHS-XX" value="{{ env('IAE_API_KEY', 'KEY-MHS-67') }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="btn-login-m2m">
                            <span class="btn-text">Authenticate via SSO M2M</span>
                        </button>
                    </form>
                </div>

                <!-- If authenticated -->
                <div id="sso-logged-in" style="display: none; flex-direction: column; gap: 1rem;">
                    <div class="profile-card">
                        <div class="profile-row">
                            <span>Auth Method:</span>
                            <strong id="profile-method">-</strong>
                        </div>
                        <div class="profile-row">
                            <span>Email / Client:</span>
                            <strong id="profile-email">-</strong>
                        </div>
                        <div class="profile-row">
                            <span>Name / Org:</span>
                            <strong id="profile-name">-</strong>
                        </div>
                        <div class="profile-row">
                            <span>Local Role Mapping:</span>
                            <strong id="profile-role" class="badge-tag" style="display: inline-block;">-</strong>
                        </div>
                    </div>

                    <div class="token-display">
                        <span style="font-weight: 600; color: var(--text-main);">Current JWT Token:</span>
                        <div class="token-scroll" id="token-raw">-</div>
                    </div>

                    <button class="btn btn-secondary" onclick="handleLogout()">Logout / Clear Token</button>
                </div>
            </div>

            <!-- Create Location Form Card -->
            <div class="section-card">
                <div class="section-header">
                    <h2>🏢 Add Location & Trigger Integrations</h2>
                </div>

                <form id="form-location" onsubmit="handleCreateLocation(event)">
                    <div class="form-group">
                        <label>Auth Option for API Request</label>
                        <select id="location-auth-opt" class="form-control" onchange="updateLocationFormAuthWarning()">
                            <option value="jwt">SSO JWT Bearer (Recommended for SSO flow)</option>
                            <option value="apikey">X-IAE-KEY API Key (Tugas 2 Legacy Auth)</option>
                        </select>
                        <span id="auth-warn-msg" style="font-size: 0.75rem; color: var(--warning); display: none; margin-top: 0.25rem;">⚠️ You need to log in to SSO first to use SSO JWT.</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Location Name</label>
                            <input type="text" id="loc-name" class="form-control" placeholder="Gedung Kuliah Umum Parkir" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" id="loc-address" class="form-control" placeholder="Jl. Telekomunikasi No.1" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Type</label>
                            <select id="loc-type" class="form-control">
                                <option value="indoor">Indoor (Gedung)</option>
                                <option value="outdoor">Outdoor (Lapang)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Parking Category</label>
                            <select id="loc-parking-type" class="form-control">
                                <option value="regular">Regular</option>
                                <option value="vip">VIP</option>
                                <option value="motorcycle">Motorcycle Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Total Spots</label>
                            <input type="number" id="loc-spots" class="form-control" value="120" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Base Rate (IDR/Hour)</label>
                            <input type="number" id="loc-rate" class="form-control" value="3000" min="0" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success" id="btn-create-location">
                        <span class="btn-text">Create Location & Fire Integrations</span>
                    </button>
                </form>

                <!-- Integration Result Preview (Hidden until location is created) -->
                <div id="integration-result" style="display: none; background-color: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 12px; padding: 1rem;">
                    <h4 style="font-size: 0.85rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.75rem;">⚡ Integration Execution Output</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>SOAP Audit Receipt:</span>
                            <strong id="res-soap-receipt" style="color: var(--success);">-</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>SOAP Status Response:</span>
                            <strong id="res-soap-status">-</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>RabbitMQ Queue Status:</span>
                            <strong id="res-amqp-status">-</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Locations list and SOAP audit logs -->
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            <!-- Master Locations List Card -->
            <div class="section-card">
                <div class="section-header">
                    <h2>🏢 Registered Locations</h2>
                    <span class="badge-tag" id="loc-count">{{ count($locations) }} Locations</span>
                </div>
                <div class="list-container" id="locations-list">
                    @forelse($locations as $loc)
                        <div class="location-item">
                            <div class="location-info">
                                <h4>{{ $loc->name }}</h4>
                                <p>{{ $loc->address }} • <span style="text-transform: capitalize;">{{ $loc->type }}</span></p>
                            </div>
                            <div class="location-meta">
                                <span class="price-tag">Rp {{ number_format($loc->base_rate, 0, ',', '.') }}/jam</span>
                                <div>
                                    <span class="spot-badge">{{ $loc->total_spots }} Spots</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 2rem;">No locations created yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Local SOAP Audit Logs Card -->
            <div class="section-card">
                <div class="section-header">
                    <h2>📝 Local SOAP Audit Logs (Verification)</h2>
                    <span class="badge-tag" style="background-color: var(--success-glow); color: var(--success); border-color: rgba(16, 185, 129, 0.2);" id="log-count">
                        {{ count($auditLogs) }} SOAP Receipts
                    </span>
                </div>
                
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: -0.5rem;">
                    As required by instructions, audit responses from SOAP hit are captured and logged locally below to prove connection success.
                </p>

                <div style="overflow-x: auto;">
                    <table id="audit-logs-table">
                        <thead>
                            <tr>
                                <th>Ref ID</th>
                                <th>Activity</th>
                                <th>Receipt Number</th>
                                <th>SOAP Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditLogs as $log)
                                <tr>
                                    <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">{{ $log->reference_id }}</td>
                                    <td>{{ $log->transaction_type }}</td>
                                    <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">{{ $log->receipt_number }}</td>
                                    <td>
                                        <span class="status-badge {{ strtolower($log->status) == 'success' || strtolower($log->status) == 'active' ? 'success' : 'failed' }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="viewXml('{{ $log->id }}')">
                                            View XML
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="no-logs-row">
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No audit logs captured locally yet. Try creating a location.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- RabbitMQ Notification Papan Pengumuman Alert -->
                <div class="info-box">
                    <div class="info-icon">🐇</div>
                    <div class="info-text">
                        <h4>Verify RabbitMQ Broadcasts</h4>
                        <p>
                            Every location creation is automatically broadcasted as a JSON payload to <code>iae.central.exchange</code>. 
                            Ensure time formats are in UTC+7 and match perfectly on the central board.
                        </p>
                        <a href="https://iae-sso.virtualfri.id/rabbitmq-announcement-board" target="_blank">🔗 Open Cloud RabbitMQ Dashboard &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- XML INSPECTION MODAL -->
<div class="modal" id="xml-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">SOAP Audit XML Details</h3>
            <button class="close-btn" onclick="closeXmlModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <div>Reference ID: <strong id="modal-ref-id" style="font-family: monospace;">-</strong></div>
                <div>Receipt Number: <strong id="modal-receipt-num" style="font-family: monospace; color: var(--success);">-</strong></div>
            </div>
            
            <div class="xml-pane">
                <h4>1. Outgoing SOAP XML Request Envelope</h4>
                <div class="xml-box request" id="modal-xml-request">
                    <!-- Loaded dynamically -->
                </div>
            </div>
            
            <div class="xml-pane">
                <h4>2. Incoming SOAP XML Response Envelope</h4>
                <div class="xml-box" id="modal-xml-response">
                    <!-- Loaded dynamically -->
                </div>
            </div>
        </div>
        <div class="modal-body" style="background-color: rgba(255,255,255,0.01); border-top: 1px solid var(--border); padding: 1rem 1.5rem; justify-content: flex-end; display: flex;">
            <button class="btn btn-secondary" style="width: auto;" onclick="closeXmlModal()">Close Viewer</button>
        </div>
    </div>
</div>

<!-- Log Data for XML Modals (Passed from Blade to Javascript securely) -->
<script>
    const auditLogsData = {
        @foreach($auditLogs as $log)
            "{{ $log->id }}": {
                referenceId: "{{ $log->reference_id }}",
                receiptNumber: "{{ $log->receipt_number }}",
                request: `{!! addslashes($log->soap_request) !!}`,
                response: `{!! addslashes($log->soap_response) !!}`
            },
        @endforeach
    };
</script>

<script>
    // SSO State Management
    let currentAuthType = 'user';
    let jwtToken = localStorage.getItem('sso_jwt_token') || null;
    let ssoUserPayload = localStorage.getItem('sso_user_payload') ? JSON.parse(localStorage.getItem('sso_user_payload')) : null;

    document.addEventListener('DOMContentLoaded', () => {
        updateAuthUi();
        updateLocationFormAuthWarning();
    });

    function toggleAuthType(type) {
        currentAuthType = type;
        const formUser = document.getElementById('form-sso-user');
        const formM2m = document.getElementById('form-sso-m2m');
        const btnUser = document.getElementById('btn-toggle-user');
        const btnM2m = document.getElementById('btn-toggle-m2m');

        if (type === 'user') {
            formUser.style.display = 'block';
            formM2m.style.display = 'none';
            btnUser.style.backgroundColor = 'rgba(59, 130, 246, 0.15)';
            btnUser.style.borderColor = 'var(--primary)';
            btnM2m.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
            btnM2m.style.borderColor = 'var(--border)';
        } else {
            formUser.style.display = 'none';
            formM2m.style.display = 'block';
            btnUser.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
            btnUser.style.borderColor = 'var(--border)';
            btnM2m.style.backgroundColor = 'rgba(59, 130, 246, 0.15)';
            btnM2m.style.borderColor = 'var(--primary)';
        }
    }

    // SSO Authentication Handler
    async function handleSsoLogin(event, type) {
        event.preventDefault();
        const btn = type === 'user' ? document.getElementById('btn-login-user') : document.getElementById('btn-login-m2m');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="loading-text"><span class="spinner"></span> Contacting SSO Central Cloud...</span>`;

        try {
            let response, data;
            
            if (type === 'user') {
                const email = document.getElementById('sso-email').value;
                const password = document.getElementById('sso-password').value;
                
                response = await fetch('/api/v1/sso/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
            } else {
                const apiKey = document.getElementById('sso-api-key').value;
                
                response = await fetch('/api/v1/sso/login-m2m', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ api_key: apiKey })
                });
            }

            data = await response.json();

            if (response.ok && data.status === 'success') {
                const tokenData = data.data;
                jwtToken = tokenData.token || tokenData.access_token;
                
                // Fetch profile info using /me
                const profileResponse = await fetch('/api/v1/sso/me', {
                    headers: { 'Authorization': `Bearer ${jwtToken}` }
                });
                
                const profileData = await profileResponse.json();
                
                if (profileResponse.ok && profileData.status === 'success') {
                    ssoUserPayload = profileData.data;
                    
                    localStorage.setItem('sso_jwt_token', jwtToken);
                    localStorage.setItem('sso_user_payload', JSON.stringify(ssoUserPayload));
                    
                    updateAuthUi();
                    updateLocationFormAuthWarning();
                } else {
                    alert('Gagal mengambil data profil /me setelah login SSO: ' + JSON.stringify(profileData));
                }
            } else {
                alert('SSO Login Gagal: ' + (data.message || 'Error occurred'));
            }
        } catch (error) {
            console.error('SSO Error:', error);
            alert('Gagal terhubung dengan server SSO: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    function handleLogout() {
        jwtToken = null;
        ssoUserPayload = null;
        localStorage.removeItem('sso_jwt_token');
        localStorage.removeItem('sso_user_payload');
        updateAuthUi();
        updateLocationFormAuthWarning();
    }

    function updateAuthUi() {
        const loggedInPanel = document.getElementById('sso-logged-in');
        const loginForms = document.getElementById('sso-login-forms');
        const dot = document.getElementById('auth-status-dot');

        if (jwtToken && ssoUserPayload) {
            loggedInPanel.style.display = 'flex';
            loginForms.style.display = 'none';
            dot.style.backgroundColor = 'var(--success)';
            
            // Populate profile
            const isM2m = ssoUserPayload.sso_payload && ssoUserPayload.sso_payload.grant_type === 'client_credentials';
            document.getElementById('profile-method').innerText = isM2m ? 'Machine-to-Machine (API Key)' : 'Federated User (SSO)';
            document.getElementById('profile-email').innerText = ssoUserPayload.email || ssoUserPayload.sso_payload.sub || 'Client ID';
            
            let displayName = 'System Service Client';
            if (ssoUserPayload.sso_payload && ssoUserPayload.sso_payload.profile) {
                const prof = ssoUserPayload.sso_payload.profile;
                displayName = prof.name ? `${prof.name} (${prof.nim || 'M2M'})` : displayName;
            }
            document.getElementById('profile-name').innerText = displayName;
            document.getElementById('profile-role').innerText = (ssoUserPayload.local_role || 'viewer').toUpperCase();
            document.getElementById('token-raw').innerText = jwtToken;
        } else {
            loggedInPanel.style.display = 'none';
            loginForms.style.display = 'block';
            dot.style.backgroundColor = 'var(--danger)';
            toggleAuthType(currentAuthType);
        }
    }

    function updateLocationFormAuthWarning() {
        const opt = document.getElementById('location-auth-opt').value;
        const warn = document.getElementById('auth-warn-msg');
        if (opt === 'jwt' && !jwtToken) {
            warn.style.display = 'inline-block';
        } else {
            warn.style.display = 'none';
        }
    }

    // Critical Transaction Trigger
    async function handleCreateLocation(event) {
        event.preventDefault();
        
        const authOpt = document.getElementById('location-auth-opt').value;
        if (authOpt === 'jwt' && !jwtToken) {
            alert('Error: Anda harus login ke SSO terlebih dahulu untuk menggunakan opsi autentikasi JWT!');
            return;
        }

        const name = document.getElementById('loc-name').value;
        const address = document.getElementById('loc-address').value;
        const type = document.getElementById('loc-type').value;
        const parking_type = document.getElementById('loc-parking-type').value;
        const total_spots = parseInt(document.getElementById('loc-spots').value);
        const base_rate = parseInt(document.getElementById('loc-rate').value);

        const btn = document.getElementById('btn-create-location');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="loading-text"><span class="spinner"></span> Creating Location & Broadcasting to Central Cloud (SOAP + AMQP)...</span>`;

        try {
            let response;
            const headers = { 'Content-Type': 'application/json' };
            const payload = { name, address, type, parking_type, total_spots, base_rate };

            if (authOpt === 'jwt') {
                headers['Authorization'] = `Bearer ${jwtToken}`;
                response = await fetch('/api/v1/sso/locations', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(payload)
                });
            } else {
                headers['X-IAE-KEY'] = '{{ env('IAE_API_KEY', 'KEY-MHS-67') }}';
                response = await fetch('/api/v1/locations', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(payload)
                });
            }

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                // Display Integration Result Box
                const intBox = document.getElementById('integration-result');
                intBox.style.display = 'block';
                
                const soapResult = data.integration && data.integration.soap_audit;
                const amqpResult = data.integration && data.integration.amqp_publish;

                const receiptNum = (soapResult && soapResult.receipt_number) ? soapResult.receipt_number : 'FAILED_TO_GET_RECEIPT';
                document.getElementById('res-soap-receipt').innerText = receiptNum;
                
                let soapStatusText = 'Failed';
                if (soapResult && soapResult.status) {
                    soapStatusText = `Captured (${soapResult.status})`;
                } else if (soapResult && soapResult.error) {
                    soapStatusText = `Error: ${soapResult.error}`;
                }
                document.getElementById('res-soap-status').innerText = soapStatusText;
                document.getElementById('res-soap-status').style.color = (soapResult && soapResult.success) ? 'var(--success)' : 'var(--danger)';

                let amqpStatusText = 'Failed';
                if (amqpResult && amqpResult.success) {
                    amqpStatusText = 'Published (iae.central.exchange)';
                } else if (amqpResult && amqpResult.error) {
                    amqpStatusText = `Error: ${amqpResult.error}`;
                }
                document.getElementById('res-amqp-status').innerText = amqpStatusText;
                document.getElementById('res-amqp-status').style.color = (amqpResult && amqpResult.success) ? 'var(--rabbitmq)' : 'var(--danger)';

                // Append new location item to HTML list
                appendLocationHtml(data.data);
                
                // Append new SOAP audit log item to HTML table
                if (soapResult && soapResult.audit_receipt_id) {
                    appendAuditLogHtml({
                        id: soapResult.audit_receipt_id,
                        reference_id: data.data.id,
                        transaction_type: 'LocationCreated',
                        receipt_number: receiptNum,
                        status: soapResult.status || 'SUCCESS',
                        soap_request: data.integration.soap_request_raw || '',
                        soap_response: data.integration.soap_response_raw || ''
                    });
                }
                
                alert('Lokasi berhasil ditambahkan! SOAP Audit + RabbitMQ Event berhasil dipicu ke Server Pusat.');
                document.getElementById('loc-name').value = '';
                document.getElementById('loc-address').value = '';
            } else {
                alert('Gagal membuat lokasi: ' + (data.message || 'Error occurred'));
            }
        } catch (error) {
            console.error('Create Location Error:', error);
            alert('Gagal menghubungi API server: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    function appendLocationHtml(loc) {
        const container = document.getElementById('locations-list');
        const emptyMsg = container.querySelector('div[style*="text-align: center"]');
        if (emptyMsg) {
            emptyMsg.remove();
        }

        const item = document.createElement('div');
        item.className = 'location-item';
        
        const formattedRate = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(loc.base_rate);
        
        item.innerHTML = `
            <div class="location-info">
                <h4>${loc.name}</h4>
                <p>${loc.address} • <span style="text-transform: capitalize;">${loc.type}</span></p>
            </div>
            <div class="location-meta">
                <span class="price-tag">${formattedRate}/jam</span>
                <div>
                    <span class="spot-badge">${loc.total_spots} Spots</span>
                </div>
            </div>
        `;
        
        container.insertBefore(item, container.firstChild);
        
        // Update count badge
        const countBadge = document.getElementById('loc-count');
        const currentCount = parseInt(countBadge.innerText) || 0;
        countBadge.innerText = `${currentCount + 1} Locations`;
    }

    function appendAuditLogHtml(log) {
        const tableBody = document.querySelector('#audit-logs-table tbody');
        const noLogsRow = document.getElementById('no-logs-row');
        if (noLogsRow) {
            noLogsRow.remove();
        }

        // Store data locally for modal viewing
        auditLogsData[log.id] = {
            referenceId: log.reference_id,
            receiptNumber: log.receipt_number,
            request: log.soap_request || '<?xml version="1.0" encoding="UTF-8"?>\n<!-- SOAP XML Request data was saved dynamically in database -->',
            response: log.soap_response || '<!-- SOAP XML Response details -->'
        };

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">${log.reference_id}</td>
            <td>${log.transaction_type}</td>
            <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">${log.receipt_number}</td>
            <td>
                <span class="status-badge success">
                    ${log.status}
                </span>
            </td>
            <td>
                <button class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="viewXml('${log.id}')">
                    View XML
                </button>
            </td>
        `;

        tableBody.insertBefore(tr, tableBody.firstChild);

        // Update count badge
        const countBadge = document.getElementById('log-count');
        const currentCount = parseInt(countBadge.innerText) || 0;
        countBadge.innerText = `${currentCount + 1} SOAP Receipts`;
    }

    // Modal XML Handler
    function viewXml(id) {
        const log = auditLogsData[id];
        if (!log) {
            alert('Audit log data not found!');
            return;
        }

        document.getElementById('modal-ref-id').innerText = log.referenceId;
        document.getElementById('modal-receipt-num').innerText = log.receiptNumber;
        
        // Escape HTML tags for code display
        const escapeHtml = (text) => {
            if (!text) return 'N/A';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        };

        document.getElementById('modal-xml-request').innerHTML = escapeHtml(log.request);
        document.getElementById('modal-xml-response').innerHTML = escapeHtml(log.response);

        document.getElementById('xml-modal').style.display = 'flex';
    }

    function closeXmlModal() {
        document.getElementById('xml-modal').style.display = 'none';
    }
</script>
</body>
</html>
