<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Lokerin</title>
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

        /* Sidebar - Same as previous pages */
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

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #0d9488;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }

        .user-email {
            font-size: 12px;
            color: #6b7280;
        }

        .career-score {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .career-score-label {
            font-size: 12px;
            color: #6b7280;
        }

        .career-score-value {
            font-size: 14px;
            font-weight: 600;
            color: #0d9488;
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

        .nav-section-title {
            font-size: 12px;
            color: #9ca3af;
            padding: 8px 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            padding: 24px 32px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .location {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #6b7280;
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
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
            position: relative;
            transition: all 0.2s;
        }

        .icon-btn:hover {
            background: #f9fafb;
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            background: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: white;
            font-weight: 600;
        }

        /* Career Score Card */
        .career-score-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        .career-score-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .career-score-title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
        }

        .career-score-percentage {
            font-size: 48px;
            font-weight: 700;
            color: #0d9488;
        }

        .career-score-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .career-score-progress {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .career-score-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #0d9488 0%, #14b8a6 100%);
            border-radius: 6px;
            width: 30%;
            transition: width 0.3s ease;
        }

        .career-checklist {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        .checklist-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #0d9488;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        .profile-header {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 24px;
        }

        .profile-avatar-wrapper {
            position: relative;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #0d9488;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: 600;
        }

        .avatar-edit-btn {
            position: absolute;
            bottom: -8px;
            right: -8px;
            width: 36px;
            height: 36px;
            background: #14b8a6;
            border-radius: 50%;
            border: 3px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .avatar-edit-btn:hover {
            background: #0d9488;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .profile-bio {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .profile-contact {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        .btn-edit-profile {
            background: white;
            color: #14b8a6;
            padding: 10px 24px;
            border-radius: 10px;
            border: 2px solid #14b8a6;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit-profile:hover {
            background: #f0fdfa;
        }

        /* Section Card */
        .section-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .section-icon {
            color: #f59e0b;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
        }

        /* Skills */
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-tag {
            background: #e0f2f1;
            color: #0d9488;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Experience & Education */
        .timeline-item {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-header {
            margin-bottom: 8px;
        }

        .timeline-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .timeline-company {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .timeline-period {
            font-size: 13px;
            color: #9ca3af;
        }

        .timeline-description {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin-top: 12px;
        }

        /* CV Upload */
        .cv-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 48px 32px;
            text-align: center;
            background: #fafafa;
            transition: all 0.2s;
            cursor: pointer;
        }

        .cv-upload-area:hover {
            border-color: #0d9488;
            background: #f0fdfa;
        }

        .upload-icon {
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .upload-text {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .btn-upload-cv {
            background: white;
            color: #14b8a6;
            padding: 10px 24px;
            border-radius: 10px;
            border: 2px solid #14b8a6;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-upload-cv:hover {
            background: #f0fdfa;
        }

        .upload-info {
            font-size: 13px;
            color: #9ca3af;
            margin-top: 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .career-checklist {
                grid-template-columns: repeat(2, 1fr);
            }

            .profile-header {
                flex-direction: column;
            }

            .profile-contact {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img class="logo-img" src="assets/logo_lokerin.png" alt="L">
                <span class="logo-text">LokerIn</span>
            </div>

            <div class="user-profile">
                <div class="user-avatar">LA</div>
                <div class="user-info">
                    <div class="user-name">Leeya Alikha Wardana</div>
                    <div class="user-email">eyyakhawrd@gmail.com</div>
                </div>
            </div>

            <div class="career-score">
                <span class="career-score-label">Career Score</span>
                <span class="career-score-value">30%</span>
            </div>

            <nav>
                <a href="dashboard_pelamar.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>

                <a href="cari_lowongan.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Cari Lowongan
                </a>

                <a href="lamaran_saya.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Lamaran Saya
                </a>

                <a href="profil_pelamar.html" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Profil
                </a>

                <a href="career_roadmap.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3v18h18"></path>
                        <path d="m19 9-5 5-4-4-3 3"></path>
                    </svg>
                    Career Roadmap
                </a>

                <a href="skill_gap_analyzer.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6m-9-9h6m6 0h6"></path>
                    </svg>
                    Skill Gap Analyzer
                </a>

                <a href="pesan_pelamar.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Pesan
                </a>

                <div class="nav-divider"></div>

                <div class="nav-section-title">Pengaturan</div>

                <a href="pengaturan_pelamar.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6"></path>
                        <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                        <path d="M1 12h6m6 0h6"></path>
                        <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                    </svg>
                    Pengaturan
                </a>

                <a href="login_pelamar.html" class="nav-item nav-item-logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Keluar
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="location">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    Jakarta, Indonesia
                </div>

                <div class="header-actions">
                    <button class="icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="notification-badge">3</span>
                    </button>

                    <button class="icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Career Score Card -->
            <div class="career-score-card">
                <div class="career-score-header">
                    <div>
                        <h2 class="career-score-title">Career Score</h2>
                    </div>
                    <div class="career-score-percentage">30%</div>
                </div>
                
                <p class="career-score-subtitle">Lengkapi profil untuk meningkatkan skor</p>
                
                <div class="career-score-progress">
                    <div class="career-score-progress-bar"></div>
                </div>

                <div class="career-checklist">
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        Foto Profil
                    </div>
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        Data Diri
                    </div>
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        Pengalaman Kerja
                    </div>
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        Skills
                    </div>
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        CV/Resume
                    </div>
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        Sertifikasi
                    </div>
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        Pendidikan
                    </div>
                    <div class="checklist-item">
                        <div class="checklist-icon">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        Portfolio
                    </div>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar">LA</div>
                        <button class="avatar-edit-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="profile-info">
                        <h2 class="profile-name">Leeya Alikha Wardana</h2>
                        <p class="profile-bio">Passionate Frontend Developer with 5+ years of experience building modern web applications.</p>
                        
                        <div class="profile-contact">
                            <div class="contact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                                email@example.com
                            </div>
                            <div class="contact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                +62 812 3456 7890
                            </div>
                            <div class="contact-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Jakarta, Indonesia
                            </div>
                        </div>
                    </div>

                    <button class="btn-edit-profile">Edit Profil</button>
                </div>
            </div>

            <!-- Skills Section -->
            <div class="section-card">
                <div class="section-header">
                    <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <h3 class="section-title">Skills</h3>
                </div>

                <div class="skills-list">
                    <span class="skill-tag">React</span>
                    <span class="skill-tag">TypeScript</span>
                    <span class="skill-tag">Node.js</span>
                    <span class="skill-tag">TailwindCSS</span>
                    <span class="skill-tag">Next.js</span>
                </div>
            </div>

            <!-- Experience Section -->
            <div class="section-card">
                <div class="section-header">
                    <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <h3 class="section-title">Pengalaman Kerja</h3>
                </div>

                <div class="timeline-item">
                    <div class="timeline-header">
                        <h4 class="timeline-title">Senior Frontend Developer</h4>
                        <p class="timeline-company">Tech Startup</p>
                        <p class="timeline-period">2022 - Present</p>
                    </div>
                    <p class="timeline-description">Leading frontend development team and building scalable React applications.</p>
                </div>

                <div class="timeline-item">
                    <div class="timeline-header">
                        <h4 class="timeline-title">Frontend Developer</h4>
                        <p class="timeline-company">Digital Agency</p>
                        <p class="timeline-period">2019 - 2022</p>
                    </div>
                    <p class="timeline-description">Developed responsive websites and web applications for various clients.</p>
                </div>
            </div>

            <!-- Education Section -->
            <div class="section-card">
                <div class="section-header">
                    <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                    </svg>
                    <h3 class="section-title">Pendidikan</h3>
                </div>

                <div class="timeline-item">
                    <div class="timeline-header">
                        <h4 class="timeline-title">S1 Teknik Informatika</h4>
                        <p class="timeline-company">Universitas Indonesia</p>
                        <p class="timeline-period">2015 - 2019</p>
                    </div>
                </div>
            </div>

            <!-- CV/Resume Section -->
            <div class="section-card">
                <div class="section-header">
                    <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <h3 class="section-title">CV / Resume</h3>
                </div>

                <div class="cv-upload-area">
                    <div class="upload-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <p class="upload-text">Drag & drop file CV Anda di sini atau klik untuk upload</p>
                    <button class="btn-upload-cv">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        Upload CV
                    </button>
                    <p class="upload-info">Format: PDF, DOC, DOCX. Max 5MB</p>
                </div>
            </div>
        </main>
    </div>

    <script>
    

        // Handle icon buttons
        document.querySelectorAll('.icon-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
            });
        });

        // Handle edit profile button
        document.querySelector('.btn-edit-profile').addEventListener('click', function(e) {
            e.preventDefault();
        });

        // Handle avatar edit button
        document.querySelector('.avatar-edit-btn').addEventListener('click', function(e) {
            e.preventDefault();
        });

        // Handle CV upload
        document.querySelector('.btn-upload-cv').addEventListener('click', function(e) {
            e.preventDefault();
        });

        document.querySelector('.cv-upload-area').addEventListener('click', function(e) {
            if (e.target === this || e.target.closest('.upload-icon, .upload-text')) {
                // Trigger file upload
            }
        });
    </script>
</body>
</html>