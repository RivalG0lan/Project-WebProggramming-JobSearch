<?php

session_start();

if (!isset($_SESSION['id_user'])) {

    header("Location: login_pelamar.php");

}

if ($_SESSION['role'] != 'pelamar') {

    header("Location: login_pelamar.php");

}

include 'config/koneksi.php';

$id_pelamar = $_SESSION['id_user'];

$total_lamaran = mysqli_num_rows(mysqli_query(
    $conn,

    "SELECT * FROM lamaran

WHERE id_pelamar='$id_pelamar'"
));

$total_review = mysqli_num_rows(mysqli_query(
    $conn,

    "SELECT * FROM lamaran

WHERE id_pelamar='$id_pelamar'

AND status='review'"
));

$total_interview = mysqli_num_rows(mysqli_query(
    $conn,

    "SELECT * FROM lamaran

WHERE id_pelamar='$id_pelamar'

AND status='interview'"
));

$total_lowongan = mysqli_num_rows(mysqli_query(
    $conn,

    "SELECT * FROM lowongan

WHERE status='aktif'"
));

?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lokerin</title>
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

        /* Navigation */
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

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);
            border-radius: 16px;
            padding: 32px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        }

        .welcome-card-content {
            position: relative;
            z-index: 1;
        }

        .welcome-card h2 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .welcome-card p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
        }

        .btn-complete {
            background: #fbbf24;
            color: #111827;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-complete:hover {
            background: #f59e0b;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .stat-icon.green {
            background: #d1fae5;
            color: #059669;
        }

        .stat-icon.purple {
            background: #e9d5ff;
            color: #9333ea;
        }

        .stat-icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .stat-icon.orange {
            background: #fed7aa;
            color: #ea580c;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
        }

        /* Section */
        .section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .btn-link {
            color: #0d9488;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        /* Job Card */
        .job-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .job-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .job-card:hover {
            border-color: #0d9488;
            background: #f9fafb;
        }

        .job-icon {
            width: 48px;
            height: 48px;
            background: #e0f2f1;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d9488;
        }

        .job-info {
            flex: 1;
        }

        .job-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .job-company {
            font-size: 13px;
            color: #6b7280;
        }

        .job-meta {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .job-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .job-badge.match {
            background: #dcfce7;
            color: #166534;
        }

        .job-badge.sedang {
            background: #dbeafe;
            color: #1e40af;
        }

        .job-badge.ditolak {
            background: #fee2e2;
            color: #991b1b;
        }

        .job-time {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Recommendation Cards */
        .recommendation-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .recommendation-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .recommendation-card:hover {
            border-color: #0d9488;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .rec-badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .rec-icon {
            width: 48px;
            height: 48px;
            background: #e0f2f1;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d9488;
            margin-bottom: 12px;
        }

        .rec-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .rec-company {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .rec-location {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .rec-salary {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }

        /* Skills Section */
        .skills-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
        }

        .skills-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .skills-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }

        .skills-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .skill-item {
            margin-bottom: 16px;
        }

        .skill-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .skill-name {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
        }

        .skill-percentage {
            font-size: 13px;
            font-weight: 600;
            color: #0d9488;
        }

        .skill-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .skill-progress {
            height: 100%;
            background: #0d9488;
            border-radius: 4px;
        }

        .btn-analyze {
            width: 100%;
            background: white;
            border: 1px solid #e5e7eb;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #0d9488;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
            transition: all 0.2s;
        }

        .btn-analyze:hover {
            background: #f0fdfa;
            border-color: #0d9488;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .recommendation-grid {
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

            .stats-grid {
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

            <div class="user-profile">
                <div class="user-avatar">

                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?>

                </div>
                <div class="user-info">
                    <div class="user-name"><?= $_SESSION['nama']; ?></div>
                    <div class="user-email"><?= $_SESSION['email']; ?></div>
                </div>
            </div>

            <div class="career-score">
                <span class="career-score-label">Career Score</span>
                <span class="career-score-value">Coming Soon</span>
            </div>

            <nav>
                <a href="dashboard_pelamar.php" class="nav-item active" onclick="goToDashboard(event)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>

                <a href="cari_lowongan.php" class="nav-item" onclick="goToCariLowongan(event)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Cari Lowongan
                </a>

                <a href="lamaran_saya.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Lamaran Saya
                </a>

                <a href="profil_pelamar.php" class="nav-item">
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
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Pesan
                </a>

                <div class="nav-divider"></div>



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
                <div class="location">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    Pelamar Aktif
                </div>

                <div class="header-actions">
                    <button class="icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="notification-badge">3</span>
                    </button>

                    <button class="icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="welcome-card-content">
                    <h2>

                        Selamat Datang,
                        <?= $_SESSION['nama']; ?> 👋

                    </h2>
                    <p>Profil Anda 30% lengkap - Tingkatkan untuk peluang lebih besar!</p>
                    <button class="btn-complete">
                        Lengkapi Profil
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M5 12h14"></path>
                            <path d="m12 5 7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-value">

                        <?= $total_lamaran; ?>

                    </div>
                    <div class="stat-label">Total Lamaran</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon purple">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                    <div class="stat-value">

                        <?= $total_review; ?>

                    </div>
                    <div class="stat-label">Dilihat HRD</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon blue">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="stat-value">

                        <?= $total_interview; ?>

                    </div>
                    <div class="stat-label">Interview</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="stat-value">

                        <?= $total_lowongan; ?>

                    </div>
                    <div class="stat-label">Lowongan Cocok</div>
                </div>
            </div>

            <!-- Lamaran Terbaru -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">Lamaran Terbaru</h2>
                    <a href="#" class="btn-link">
                        Lihat Semua
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="job-list">
                    <div class="job-card">
                        <div class="job-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>
                        <div class="job-info">
                            <div class="job-title">Frontend Developer</div>
                            <div class="job-company">Tokopedia</div>
                        </div>
                        <div class="job-meta">
                            <span class="job-badge match">Sedang Direview</span>
                            <span class="job-time">2 hari lalu</span>
                        </div>
                    </div>

                    <div class="job-card">
                        <div class="job-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>
                        <div class="job-info">
                            <div class="job-title">UI/UX Designer</div>
                            <div class="job-company">Gojek</div>
                        </div>
                        <div class="job-meta">
                            <span class="job-badge sedang">Interview</span>
                            <span class="job-time">5 hari lalu</span>
                        </div>
                    </div>

                    <div class="job-card">
                        <div class="job-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>
                        <div class="job-info">
                            <div class="job-title">Product Manager</div>
                            <div class="job-company">Shopee</div>
                        </div>
                        <div class="job-meta">
                            <span class="job-badge ditolak">Ditolak</span>
                            <span class="job-time">1 minggu lalu</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid: Rekomendasi & Skills -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <!-- Rekomendasi untuk Anda -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">Rekomendasi untuk Anda</h2>
                        <a href="#" class="btn-link">
                            Lihat Semua
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    </div>

                    <div class="recommendation-grid">
                        <div class="recommendation-card">
                            <span class="rec-badge">95% Match</span>
                            <div class="rec-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                            <div class="rec-title">Senior React Developer</div>
                            <div class="rec-company">Bukalapak</div>
                            <div class="rec-location">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Jakarta
                            </div>
                            <div class="rec-salary">Rp 25-35 Juta</div>
                        </div>

                        <div class="recommendation-card">
                            <span class="rec-badge">89% Match</span>
                            <div class="rec-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                            <div class="rec-title">Full Stack Engineer</div>
                            <div class="rec-company">Traveloka</div>
                            <div class="rec-location">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Remote
                            </div>
                            <div class="rec-salary">Rp 30-40 Juta</div>
                        </div>

                        <div class="recommendation-card">
                            <span class="rec-badge">82% Match</span>
                            <div class="rec-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                            <div class="rec-title">Frontend Lead</div>
                            <div class="rec-company">Goto</div>
                            <div class="rec-location">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Jakarta
                            </div>
                            <div class="rec-salary">Rp 35-50 Juta</div>
                        </div>
                    </div>
                </div>

                <!-- Skill yang Dibutuhkan -->
                <div class="skills-card">
                    <div class="skills-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                        <h3 class="skills-title">Skill yang Dibutuhkan</h3>
                    </div>
                    <p class="skills-subtitle">Pelajari ini untuk meningkatkan peluang Anda</p>

                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-name">TypeScript</span>
                            <span class="skill-percentage">92%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 92%"></div>
                        </div>
                    </div>

                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-name">Next.js</span>
                            <span class="skill-percentage">85%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 85%"></div>
                        </div>
                    </div>

                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-name">GraphQL</span>
                            <span class="skill-percentage">78%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 78%"></div>
                        </div>
                    </div>

                    <button class="btn-analyze" onclick="handleAnalyzeSkill(event)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6"></path>
                            <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                            <path d="M1 12h6m6 0h6"></path>
                            <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                        </svg>
                        Analisa Skill Gap
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Navigation functions
        function goToDashboard(e) {
            e.preventDefault();
            // Already on dashboard, do nothing
        }

        // Handle analyze skill button
        function handleAnalyzeSkill(e) {
            e.preventDefault();
            // Placeholder for future functionality
        }


        // Handle job card clicks
        document.querySelectorAll('.job-card, .recommendation-card').forEach(card => {
            card.addEventListener('click', function (e) {
                e.preventDefault();
                // Placeholder for future functionality
            });
        });

        // Handle button clicks
        document.querySelector('.btn-complete')?.addEventListener('click', function (e) {
            e.preventDefault();
            // Placeholder for future functionality
        });

        // Handle icon buttons
        document.querySelectorAll('.icon-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                // Placeholder for future functionality
            });
        });

        // Handle link buttons
        document.querySelectorAll('.btn-link').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                // Placeholder for future functionality
            });
        });
    </script>
</body>

</html>