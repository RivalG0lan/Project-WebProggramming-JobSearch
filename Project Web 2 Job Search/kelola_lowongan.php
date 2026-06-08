<?php

session_start();

include 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {

    header("Location: login_perusahaan.php");

}

if ($_SESSION['role'] != 'perusahaan') {

    header("Location: login_perusahaan.php");

}

$query = mysqli_query(
    $conn,

    "SELECT * FROM lowongan

WHERE id_perusahaan='" . $_SESSION['id_user'] . "'

ORDER BY id_lowongan DESC

"
);

if (isset($_GET['hapus'])) {

    $id_hapus = $_GET['hapus'];

    mysqli_query(
        $conn,

        "DELETE FROM lowongan
    WHERE id_lowongan='$id_hapus'"
    );

    header("Location: kelola_lowongan.php");

}
$id_perusahaan = (int) $_SESSION['id_user'];

// === STAT CARDS ===
$q_total_lowongan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM lowongan WHERE id_perusahaan = '$id_perusahaan'");
$total_lowongan = mysqli_fetch_assoc($q_total_lowongan)['total'];

$q_lowongan_aktif = mysqli_query($conn, "SELECT COUNT(*) AS total FROM lowongan WHERE id_perusahaan = '$id_perusahaan' AND status = 'aktif'");
$total_aktif = mysqli_fetch_assoc($q_lowongan_aktif)['total'];

$q_lowongan_ditutup = mysqli_query($conn, "SELECT COUNT(*) AS total FROM lowongan WHERE id_perusahaan = '$id_perusahaan' AND status = 'ditutup'");
$total_ditutup = mysqli_fetch_assoc($q_lowongan_ditutup)['total'];

$q_total_pelamar = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM lamaran l
    JOIN lowongan low ON l.id_lowongan = low.id_lowongan
    WHERE low.id_perusahaan = '$id_perusahaan'
");
$total_pelamar = mysqli_fetch_assoc($q_total_pelamar)['total'];

// Employer Score
$q_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_perusahaan'");
$user_perusahaan = mysqli_fetch_assoc($q_user);
$fields_cek_perusahaan = ['nama', 'email', 'telepon', 'lokasi', 'bio', 'foto_profil'];
$isi_perusahaan = 0;
foreach ($fields_cek_perusahaan as $f) {
    if (!empty($user_perusahaan[$f])) $isi_perusahaan++;
}
$employer_score = round(($isi_perusahaan / count($fields_cek_perusahaan)) * 100);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lowongan - Lokerin</title>
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

        .btn-post {
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

        .btn-post:hover {
            background: #0f766e;
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.blue {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-icon.green {
            background: #f0fdf4;
            color: #22c55e;
        }

        .stat-icon.orange {
            background: #fff7ed;
            color: #f59e0b;
        }

        .stat-icon.red {
            background: #fef2f2;
            color: #ef4444;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }

        /* Filters */
        .filters-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }

        .filters-row {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .search-box input:focus {
            border-color: #0d9488;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
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

        /* Job Cards */
        .jobs-grid {
            display: grid;
            gap: 16px;
        }

        .job-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .job-card:hover {
            border-color: #0d9488;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .job-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }

        .job-info {
            flex: 1;
        }

        .job-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .job-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #6b7280;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .job-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: #f9fafb;
        }

        .action-btn.edit:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .action-btn.delete:hover {
            background: #fef2f2;
            border-color: #ef4444;
            color: #ef4444;
        }

        .job-stats {
            display: flex;
            gap: 24px;
            padding: 16px 0;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 16px;
        }

        .job-stat {
            flex: 1;
        }

        .job-stat-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .job-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .job-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.active {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-badge.closed {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-badge.draft {
            background: #f3f4f6;
            color: #6b7280;
        }

        .job-date {
            font-size: 13px;
            color: #9ca3af;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 64px 24px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: #9ca3af;
        }

        .empty-state h3 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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

            .filters-row {
                flex-direction: column;
            }

            .job-stats {
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

            <div class="company-profile">
                <div class="company-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                </div>
                <div class="company-info">
                    <div class="company-name">

                        <?= $_SESSION['nama']; ?>

                    </div>
                    <div class="company-industry">
                        <?= $_SESSION['email']; ?>
                    </div>
                </div>
            </div>

            <div class="employer-score">
                <div class="employer-score-header">
                    <span class="employer-score-label">Employer Score</span>
                    <span class="employer-score-value"><?= $employer_score ?>%</span>
                </div>
                <div class="employer-score-bar">
                    <div class="employer-score-fill" style="width: <?= $employer_score ?>%;"></div>
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

                <a href="kelola_lowongan.php" class="nav-item active">
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

                <a href="profil_perusahaan.php" class="nav-item">
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
                    <h1>Kelola Lowongan</h1>
                    <p>Manage semua lowongan kerja Anda</p>
                </div>

                <div class="header-actions">
                    <button class="btn-post" onclick="window.location.href='posting_lowongan.php'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                        Posting Lowongan Baru
                    </button>

                    <button class="icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </button>

                    <button class="icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon blue">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-label">Total Lowongan</div>
                    <div class="stat-value"><?= $total_lowongan ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-label">Lowongan Aktif</div>
                    <div class="stat-value"><?= $total_aktif ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon orange">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-label">Total Pelamar</div>
                    <div class="stat-value"><?= $total_pelamar ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon red">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-label">Lowongan Ditutup</div>
                    <div class="stat-value"><?= $total_ditutup ?></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <div class="filters-row">
                    <div class="search-box">
                        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" placeholder="Cari lowongan...">
                    </div>
                    <button class="filter-btn active">Semua</button>
                    <button class="filter-btn">Aktif</button>
                    <button class="filter-btn">Draft</button>
                    <button class="filter-btn">Ditutup</button>
                    <button class="filter-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="4" y1="21" x2="4" y2="14"></line>
                            <line x1="4" y1="10" x2="4" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="3"></line>
                            <line x1="20" y1="21" x2="20" y2="16"></line>
                            <line x1="20" y1="12" x2="20" y2="3"></line>
                            <line x1="1" y1="14" x2="7" y2="14"></line>
                            <line x1="9" y1="8" x2="15" y2="8"></line>
                            <line x1="17" y1="16" x2="23" y2="16"></line>
                        </svg>
                        Filter
                    </button>
                </div>
            </div>
            <!-- Jobs Grid -->
            <div class="jobs-grid">
                <?php
                while ($data = mysqli_fetch_assoc($query)) {
                    ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <div class="job-info">
                                <h3 class="job-title">
                                    <?= $data['judul']; ?>
                                </h3>
                                <div class="job-meta">
                                    <span class="job-meta-item">
                                        📁 <?= $data['kategori']; ?>
                                    </span>
                                    <span class="job-meta-item">
                                        📍 <?= $data['lokasi']; ?>
                                    </span>
                                    <span class="job-meta-item">
                                        💰 Rp <?= $data['gaji']; ?>
                                    </span>
                                    <p>
                                        📌 Status: <?= $data['status']; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="job-actions">

                                <a href="edit_lowongan.php?id=<?= $data['id_lowongan']; ?>" class="action-btn edit">
                                    ✏️
                                </a>
                                <a href="kelola_lowongan.php?hapus=<?= $data['id_lowongan']; ?>"
                                    onclick="return confirm('Yakin ingin menghapus lowongan ini?')"
                                    class="action-btn delete">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">

                                        <polyline points="3 6 5 6 21 6"></polyline>

                                        <path d="M19 6l-1 14H6L5 6"></path>

                                        <path d="M10 11v6"></path>

                                        <path d="M14 11v6"></path>

                                        <path d="M9 6V4h6v2"></path>

                                    </svg>
                                </a>
                            </div>

                        </div>

                    </div>

                    <div style="margin-top:16px; color:#6b7280;">
                        <?= $data['deskripsi']; ?>
                    </div>
                </div>
                <?php
                }
                ?>
    </div>
    </main>
    </div>
</body>

</html>