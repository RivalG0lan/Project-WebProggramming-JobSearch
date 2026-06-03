<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan - Lokerin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f9fafb;
            color: #111827;
            line-height: 1.6;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid #e5e7eb;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            padding: 0 8px;
        }

        .logo-img {
            width: 27px;
            height: 27px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .company-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .company-avatar {
            width: 40px;
            height: 40px;
            background: #f59e0b;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }

        .company-industry {
            font-size: 12px;
            color: #6b7280;
        }

        .employer-score {
            padding: 8px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .employer-score-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .employer-score-label {
            font-size: 12px;
            color: #6b7280;
        }

        .employer-score-value {
            font-size: 14px;
            font-weight: 600;
            color: #f59e0b;
        }

        .employer-score-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }

        .employer-score-fill {
            height: 100%;
            background: #f59e0b;
            width: 50%;
            border-radius: 2px;
        }

        nav {
            flex: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: #6b7280;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background: #f9fafb;
            color: #111827;
        }

        .nav-item.active {
            background: #e0f2f1;
            color: #0d9488;
        }

        .nav-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 16px 0;
        }

        .nav-item-logout {
            color: #ef4444;
        }

        .nav-item-logout:hover {
            background: #fef2f2;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            display: flex;
            height: 100vh;
        }

        /* Message List */
        .message-list-container {
            width: 360px;
            background: white;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
        }

        .message-list-header {
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .message-list-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 16px;
        }

        .message-search {
            position: relative;
        }

        .message-search input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }

        .message-search input:focus {
            border-color: #0d9488;
        }

        .message-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .message-filter {
            display: flex;
            gap: 8px;
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
            overflow-x: auto;
        }

        .filter-btn {
            padding: 6px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: white;
            color: #6b7280;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            background: #f9fafb;
        }

        .filter-btn.active {
            background: #0d9488;
            color: white;
            border-color: #0d9488;
        }

        .message-list {
            flex: 1;
            overflow-y: auto;
        }

        .message-item {
            padding: 16px 24px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            gap: 12px;
        }

        .message-item:hover {
            background: #f9fafb;
        }

        .message-item.active {
            background: #e0f2f1;
        }

        .message-item.unread {
            background: #f0fdfa;
        }

        .message-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #0d9488;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            flex-shrink: 0;
        }

        .message-content {
            flex: 1;
            min-width: 0;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 4px;
        }

        .message-sender {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
        }

        .message-time {
            font-size: 12px;
            color: #9ca3af;
            white-space: nowrap;
        }

        .message-preview {
            font-size: 14px;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 4px;
        }

        .message-item.unread .message-preview {
            color: #111827;
            font-weight: 500;
        }

        .message-label {
            display: inline-block;
            padding: 2px 8px;
            background: #f0fdfa;
            color: #0d9488;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .unread-badge {
            width: 8px;
            height: 8px;
            background: #0d9488;
            border-radius: 50%;
            margin-top: 4px;
        }

        /* Chat Container */
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f9fafb;
        }

        .chat-header {
            padding: 20px 32px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .chat-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #0d9488;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .chat-user-info h3 {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2px;
        }

        .chat-user-info p {
            font-size: 14px;
            color: #6b7280;
        }

        .chat-actions {
            display: flex;
            gap: 12px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: white;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .icon-btn:hover {
            background: #f9fafb;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px 32px;
        }

        .chat-date-divider {
            text-align: center;
            margin: 24px 0;
            font-size: 13px;
            color: #9ca3af;
        }

        .message-group {
            margin-bottom: 24px;
        }

        .message-bubble {
            max-width: 60%;
            margin-bottom: 8px;
        }

        .message-bubble.received {
            display: flex;
            gap: 12px;
        }

        .message-bubble.sent {
            margin-left: auto;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .bubble-content {
            background: white;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            color: #111827;
            line-height: 1.5;
        }

        .message-bubble.sent .bubble-content {
            background: #0d9488;
            color: white;
        }

        .bubble-time {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
            padding: 0 4px;
        }

        .chat-input-container {
            padding: 20px 32px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .chat-input-wrapper {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .chat-input {
            flex: 1;
        }

        .chat-input textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: none;
            outline: none;
            min-height: 44px;
            max-height: 120px;
        }

        .chat-input textarea:focus {
            border-color: #0d9488;
        }

        .input-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .attach-btn {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: white;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s;
        }

        .attach-btn:hover {
            background: #f9fafb;
            color: #0d9488;
        }

        .send-btn {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: #0d9488;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: all 0.2s;
        }

        .send-btn:hover {
            background: #0f766e;
        }

        /* Candidate Info Sidebar */
        .candidate-info-sidebar {
            width: 320px;
            background: white;
            border-left: 1px solid #e5e7eb;
            overflow-y: auto;
            padding: 24px;
        }

        .candidate-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .candidate-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #0d9488;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 32px;
            margin: 0 auto 16px;
        }

        .candidate-name {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .candidate-position {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .candidate-status {
            display: inline-block;
            padding: 4px 12px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .info-section {
            margin-bottom: 24px;
        }

        .info-section-title {
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            font-size: 14px;
            color: #6b7280;
        }

        .info-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d9488;
            flex-shrink: 0;
        }

        .info-text {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 2px;
        }

        .info-value {
            color: #111827;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .btn-action {
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0d9488;
            color: white;
        }

        .btn-primary:hover {
            background: #0f766e;
        }

        .btn-secondary {
            background: white;
            color: #0d9488;
            border: 1px solid #0d9488;
        }

        .btn-secondary:hover {
            background: #f0fdfa;
        }

        @media (max-width: 1400px) {
            .candidate-info-sidebar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }

            .message-list-container {
                width: 100%;
            }

            .chat-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="logo">
                <img class="logo-img" src="assets/logo_lokerin.png" alt="L">
                <span class="logo-text">Lokerin</span>
            </div>

            <div class="company-profile">
                <div class="company-avatar">P</div>
                <div class="company-info">
                    <div class="company-name">PT NADYA</div>
                    <div class="company-industry">Industry</div>
                </div>
            </div>

            <div class="employer-score">
                <div class="employer-score-header">
                    <span class="employer-score-label">Employer Score</span>
                    <span class="employer-score-value">50%</span>
                </div>
                <div class="employer-score-bar">
                    <div class="employer-score-fill"></div>
                </div>
            </div>

            <nav>
                <a href="dashboard_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>

                <a href="posting_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="12" r="8"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                    </svg>
                    Posting Lowongan
                </a>

                <a href="kelola_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Kelola Lowongan
                </a>

                <a href="kandidat.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Kandidat
                </a>

                <a href="profil_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Profil Perusahaan
                </a>

                <a href="analytics.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    Analytics
                </a>

                <a href="pesan_perusahaan.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Pesan
                </a>

                <div class="nav-divider"></div>

                <a href="pengaturan_perusahaan.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6"></path>
                        <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                        <path d="M1 12h6m6 0h6"></path>
                        <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                    </svg>
                    Pengaturan
                </a>

                <a href="login_perusahaan.php" class="nav-item nav-item-logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Keluar
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="message-list-container">
                <div class="message-list-header">
                    <h1 class="message-list-title">Pesan</h1>
                    <div class="message-search">
                        <svg class="message-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" placeholder="Cari pesan...">
                    </div>
                </div>

                <div class="message-filter">
                    <button class="filter-btn active">Semua</button>
                    <button class="filter-btn">Belum Dibaca</button>
                    <button class="filter-btn">Kandidat</button>
                </div>

                <div class="message-list">
                    <div class="message-item unread active">
                        <div class="message-avatar">JD</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">John Doe</span>
                                <span class="message-time">10:30</span>
                            </div>
                            <p class="message-preview">Terima kasih! Saya sangat tertarik dengan posisi ini...</p>
                            <span class="message-label">Frontend Developer</span>
                        </div>
                        <div class="unread-badge"></div>
                    </div>

                    <div class="message-item unread">
                        <div class="message-avatar">AS</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">Ahmad Saputra</span>
                                <span class="message-time">Kemarin</span>
                            </div>
                            <p class="message-preview">Selamat pagi, saya ingin bertanya tentang...</p>
                            <span class="message-label">Backend Developer</span>
                        </div>
                        <div class="unread-badge"></div>
                    </div>

                    <div class="message-item">
                        <div class="message-avatar">SR</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">Siti Rahmawati</span>
                                <span class="message-time">2 hari lalu</span>
                            </div>
                            <p class="message-preview">Baik, saya siap untuk interview. Terima kasih...</p>
                            <span class="message-label">UI/UX Designer</span>
                        </div>
                    </div>

                    <div class="message-item">
                        <div class="message-avatar">BW</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">Budi Wijaya</span>
                                <span class="message-time">3 hari lalu</span>
                            </div>
                            <p class="message-preview">Saya sudah mengirimkan portfolio...</p>
                            <span class="message-label">Product Manager</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chat-container">
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="chat-avatar">JD</div>
                        <div class="chat-user-info">
                            <h3>John Doe</h3>
                            <p>Senior Frontend Developer</p>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="icon-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                        <button class="icon-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                                <circle cx="5" cy="12" r="1"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="chat-messages">
                    <div class="chat-date-divider">Hari ini</div>

                    <div class="message-group">
                        <div class="message-bubble sent">
                            