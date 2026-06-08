<?php
include 'config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login_perusahaan.php");
    exit;
}

include 'employer_score.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Perusahaan - Lokerin</title>
    <link rel="icon" type="image/png" href="assets/icon_head_lokerin.png">
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
            color: #0D9488;
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
            padding: 24px 32px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .header-left h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .header-left p {
            font-size: 14px;
            color: #6b7280;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-primary {
            background: #0d9488;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #0f766e;
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        /* Profile Header */
        .profile-header {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .profile-cover {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 120px;
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
        }

        .profile-header-content {
            position: relative;
            display: flex;
            gap: 24px;
            padding-top: 60px;
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            background: #f59e0b;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 48px;
            border: 4px solid white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .profile-header-info {
            flex: 1;
            padding-top: 40px;
        }

        .profile-header-name {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .profile-header-industry {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .profile-header-meta {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #6b7280;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        /* Card */
        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .edit-btn {
            color: #0d9488;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            background: none;
            border: none;
            padding: 0;
        }

        .edit-btn:hover {
            color: #0f766e;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: #0d9488;
        }

        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .form-textarea:focus {
            border-color: #0d9488;
        }

        /* Info List */
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding-bottom: 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 13px;
            color: #6b7280;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            text-align: right;
        }

        /* Social Links */
        .social-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .social-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s;
        }

        .social-link:hover {
            border-color: #0d9488;
            background: #f0fdfa;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
        }

        .social-info {
            flex: 1;
        }

        .social-name {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }

        .social-url {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Benefits */
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            font-size: 13px;
            color: #374151;
        }

        .benefit-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: #e0f2f1;
            color: #0d9488;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Verification Status */
        .verification-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .verification-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .verification-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .verification-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e40af;
        }

        .verification-text {
            font-size: 14px;
            color: #1e40af;
            margin-bottom: 16px;
        }

        .verification-progress {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .progress-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .progress-check {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #22c55e;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress-check.pending {
            background: #e5e7eb;
        }

        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .profile-header-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .benefits-grid {
                grid-template-columns: 1fr;
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

            <div class="company-profile">
                <div class="company-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                </div>
                <div class="company-info">
                    <div class="company-name">
                        <?= htmlspecialchars($_SESSION['nama']) ?>
                    </div>
                    <div class="company-industry">
                        <?= htmlspecialchars($_SESSION['email']) ?>
                    </div>
                </div>
            </div>

            <div class="employer-score">
                <div class="employer-score-header">
                    <span class="employer-score-label">
                        Employer Score
                    </span>

                    <span class="employer-score-value">
                        <?= $employer_score ?>%
                    </span>
                </div>

                <div class="employer-score-bar">
                    <div class="employer-score-fill" style="width: <?= $employer_score ?>%;">
                    </div>
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

                <a href="kandidat.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Kandidat
                </a>

                <a href="profil_perusahaan.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Profil Perusahaan
                </a>

                <a href="analytics.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    Analytics
                </a>

                <a href="pesan_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Pesan
                </a>

                <div class="nav-divider"></div>

                <a href="pengaturan_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6"></path>
                        <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                        <path d="M1 12h6m6 0h6"></path>
                        <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                    </svg>
                    Pengaturan
                </a>

                <a href="logout.php" class="nav-item nav-item-logout">
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
                <div class="header-left">
                    <h1>Profil Perusahaan</h1>
                    <p>Kelola informasi profil perusahaan Anda</p>
                </div>

                <div class="header-actions">
                    <button class="btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Preview Profil
                    </button>
                    <button class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </header>

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-cover"></div>
                <div class="profile-header-content">
                    <div class="profile-avatar-large">P</div>
                    <div class="profile-header-info">
                        <h2 class="profile-header-name">
                            <?= htmlspecialchars($_SESSION['nama']) ?>
                        </h2>
                        <p class="profile-header-industry">Technology • Software Development</p>
                        <div class="profile-header-meta">
                            <span class="profile-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Jakarta, Indonesia
                            </span>
                            <span class="profile-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                </svg>
                                50-200 Karyawan
                            </span>
                            <span class="profile-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Bergabung sejak 2020
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="verification-card">
                <div class="verification-header">
                    <div class="verification-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="verification-title">Status Verifikasi</div>
                    </div>
                </div>
                <p class="verification-text">Tingkatkan kredibilitas perusahaan dengan melengkapi verifikasi profil</p>
                <div class="verification-progress">
                    <div class="progress-item">
                        <div class="progress-check">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <span style="color: #1e40af;">Email Terverifikasi</span>
                    </div>
                    <div class="progress-item">
                        <div class="progress-check">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <span style="color: #1e40af;">Nomor Telepon Terverifikasi</span>
                    </div>
                    <div class="progress-item">
                        <div class="progress-check pending"></div>
                        <span style="color: #6b7280;">Dokumen Perusahaan (Pending)</span>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Left Column -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- About Company -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tentang Perusahaan</h3>
                            <button class="edit-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Edit
                            </button>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Deskripsi Perusahaan</label>
                            <textarea class="form-textarea"
                                rows="6"><?= htmlspecialchars($_SESSION['nama']) ?> adalah perusahaan teknologi yang berfokus pada pengembangan software dan solusi digital inovatif. Kami berkomitmen untuk menciptakan produk berkualitas tinggi yang memberikan dampak positif bagi klien dan pengguna kami.

Dengan tim profesional yang berpengalaman, kami telah melayani berbagai industri dan membantu perusahaan dalam transformasi digital mereka.</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">V