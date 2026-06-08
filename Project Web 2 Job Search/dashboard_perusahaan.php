<?php

session_start();

if (!isset($_SESSION['id_user'])) {

    header("Location: login_perusahaan.php");
    exit;
}

if ($_SESSION['role'] != 'perusahaan') {

    header("Location: login_perusahaan.php");
    exit;
}

include 'config/koneksi.php';

$id_perusahaan = (int) $_SESSION['id_user'];

// === STAT CARDS ===
// Total lowongan milik perusahaan ini
$q_total_lowongan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM lowongan WHERE id_perusahaan = '$id_perusahaan'");
$total_lowongan = mysqli_fetch_assoc($q_total_lowongan)['total'];

// Total pelamar ke lowongan milik perusahaan ini
$q_total_pelamar = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM lamaran l
    JOIN lowongan low ON l.id_lowongan = low.id_lowongan
    WHERE low.id_perusahaan = '$id_perusahaan'
");
$total_pelamar = mysqli_fetch_assoc($q_total_pelamar)['total'];

// Lamaran menunggu review (status = dikirim)
$q_menunggu = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM lamaran l
    JOIN lowongan low ON l.id_lowongan = low.id_lowongan
    WHERE low.id_perusahaan = '$id_perusahaan' AND l.status = 'dikirim'
");
$total_menunggu = mysqli_fetch_assoc($q_menunggu)['total'];

// Interview terjadwal
$q_interview = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM lamaran l
    JOIN lowongan low ON l.id_lowongan = low.id_lowongan
    WHERE low.id_perusahaan = '$id_perusahaan' AND l.status = 'interview'
");
$total_interview = mysqli_fetch_assoc($q_interview)['total'];

// === LOWONGAN AKTIF (max 3) ===
$q_lowongan_aktif = mysqli_query($conn, "
    SELECT low.*, 
           (SELECT COUNT(*) FROM lamaran WHERE id_lowongan = low.id_lowongan) AS jumlah_pelamar
    FROM lowongan low
    WHERE low.id_perusahaan = '$id_perusahaan' AND low.status = 'aktif'
    ORDER BY low.tanggal_post DESC
    LIMIT 3
");

// === KANDIDAT TERBAIK (pelamar yang sudah melamar) ===
$q_kandidat = mysqli_query($conn, "
    SELECT u.id_user, u.nama, u.bidang_keahlian, u.skills, u.foto_profil,
           l.status AS status_lamaran, low.judul AS lowongan_judul
    FROM lamaran l
    JOIN lowongan low ON l.id_lowongan = low.id_lowongan
    JOIN users u ON l.id_pelamar = u.id_user
    WHERE low.id_perusahaan = '$id_perusahaan'
    ORDER BY l.tanggal_lamaran DESC
    LIMIT 3
");

// Employer Score (berdasarkan kelengkapan profil perusahaan)
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
    <title>Dashboard Employer - Lokerin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 24px;
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
            margin-top: 8px;
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

        .greeting {
            font-size: 16px;
            color: #6b7280;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
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

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
            color: #111827;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .welcome-subtitle {
            font-size: 16px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .btn-improve {
            background: white;
            color: #111827;
            padding: 12px 24px;
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

        .btn-improve:hover {
            background: #f9fafb;
        }

        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            transition: all 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .stat-icon.teal {
            background: #ccfbf1;
            color: #0d9488;
        }

        .stat-icon.yellow {
            background: #fef3c7;
            color: #f59e0b;
        }

        .stat-icon.blue {
            background: #dbeafe;
            color: #3b82f6;
        }

        .stat-icon.purple {
            background: #e9d5ff;
            color: #a855f7;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        /* Active Jobs Section */
        .section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .btn-see-all {
            color: #0d9488;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
        }

        .btn-see-all:hover {
            color: #0f766e;
        }

        /* Job Item */
        .job-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .job-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .job-item:last-child {
            margin-bottom: 0;
        }

        .job-icon {
            width: 48px;
            height: 48px;
            background: #e0f2f1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d9488;
            flex-shrink: 0;
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

        .job-time {
            font-size: 13px;
            color: #6b7280;
        }

        .job-stats {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .job-stat-row {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .job-stat {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .job-stat-number {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .job-stat-label {
            font-size: 12px;
            color: #6b7280;
        }

        .btn-arrow {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: transparent;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s;
        }

        .btn-arrow:hover {
            background: #f9fafb;
        }

        /* Post Job Button */
        .btn-post-full {
            width: 100%;
            background: white;
            color: #0d9488;
            padding: 16px;
            border-radius: 12px;
            border: 2px dashed #0d9488;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            margin-top: 12px;
        }

        .btn-post-full:hover {
            background: #f0fdfa;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-item:hover {
            background: #f3f4f6;
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .action-icon.teal {
            background: #ccfbf1;
            color: #0d9488;
        }

        .action-icon.yellow {
            background: #fef3c7;
            color: #f59e0b;
        }

        .action-icon.blue {
            background: #dbeafe;
            color: #3b82f6;
        }

        .action-info {
            flex: 1;
        }

        .action-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 2px;
        }

        .action-subtitle {
            font-size: 12px;
            color: #6b7280;
        }

        /* Top Candidates Section */
        .top-candidates {
            margin-top: 24px;
        }

        .section-title-with-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .candidate-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
        }

        .candidate-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .match-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .match-badge.high {
            background: #dcfce7;
            color: #166534;
        }

        .match-badge.medium {
            background: #dbeafe;
            color: #1e40af;
        }

        .match-badge.good {
            background: #fef3c7;
            color: #92400e;
        }

        .candidate-avatar {
            width: 56px;
            height: 56px;
            background: #0d9488;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
            margin: 0 auto 12px;
        }

        .candidate-name {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .candidate-role {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .candidate-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin-bottom: 16px;
        }

        .skill-tag {
            padding: 4px 10px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
        }

        .candidate-actions {
            display: flex;
            gap: 8px;
        }

        .btn-shortlist {
            flex: 1;
            background: #0d9488;
            color: white;
            padding: 8px;
            border-radius: 8px;
            border: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-shortlist:hover {
            background: #0f766e;
        }

        .btn-profile {
            flex: 1;
            background: white;
            color: #0d9488;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #0d9488;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-profile:hover {
            background: #f0fdfa;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .candidates-grid {
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

            .stats-section {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .candidates-grid {
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
                <div class="company-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)) ?></div>
                <div class="company-info">
                    <div class="company-name"><?= htmlspecialchars($_SESSION['nama']); ?></div>
                    <div class="company-industry"><?= htmlspecialchars($_SESSION['email']); ?></div>
                </div>
            </div>

            <div class="employer-score">
                <div style="flex: 1;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span class="employer-score-label">Employer Score</span>
                        <span class="employer-score-value"><?= $employer_score ?>%</span>
                    </div>
                    <div class="employer-score-bar">
                        <div class="employer-score-fill" style="width: <?= $employer_score ?>%"></div>
                    </div>
                </div>
            </div>

            <nav>
                <a href="dashboard_perusahaan.php" class="nav-item active">
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
                <div class="greeting">Selamat datang, <?= $_SESSION['nama']; ?></div>

                <div class="header-actions">
                    <button class="btn-post">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                        Post Lowongan
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

            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-title">
                    Selamat Datang, <?= $_SESSION['nama']; ?>!
                </div>
                <div class="welcome-subtitle">
                    Employer Score Anda <?= $employer_score ?>%. <?= $employer_score < 80 ? 'Tingkatkan untuk menarik lebih banyak kandidat berkualitas!' : 'Profil Anda sudah sangat lengkap!' ?>
                </div>
                <button class="btn-improve" onclick="window.location.href='profil_perusahaan.php'">
                    Tingkatkan Profil
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </div>

            <!-- Stats Section -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon teal">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </div>
                    <div class="stat-number"><?= $total_lowongan ?></div>
                    <div class="stat-label">Total Lowongan</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon yellow">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-number"><?= $total_pelamar ?></div>
                    <div class="stat-label">Total Pelamar</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon blue">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M9 11l3 3L22 4"></path>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                    </div>
                    <div class="stat-number"><?= $total_menunggu ?></div>
                    <div class="stat-label">Menunggu Review</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon purple">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="stat-number"><?= $total_interview ?></div>
                    <div class="stat-label">Interview Terjadwal</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Left Column -->
                <div>
                    <!-- Active Jobs Section -->
                    <div class="section">
                        <div class="section-header">
                            <h2 class="section-title">Lowongan Aktif</h2>
                            <a href="" class="btn-see-all">
                                Lihat Semua
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        </div>

                        <?php
                        if (mysqli_num_rows($q_lowongan_aktif) > 0):
                            while ($low = mysqli_fetch_assoc($q_lowongan_aktif)):
                                // Format waktu relatif (sederhana)
                                $waktu = date('d M Y', strtotime($low['tanggal_post']));
                        ?>
                                <div class="job-item">
                                    <div class="job-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                        </svg>
                                    </div>
                                    <div class="job-info">
                                        <div class="job-title"><?= htmlspecialchars($low['judul']) ?></div>
                                        <div class="job-time"><?= $waktu ?></div>
                                    </div>
                                    <div class="job-stats">
                                        <div class="job-stat-row">
                                            <div class="job-stat">
                                                <div class="job-stat-number"><?= $low['jumlah_pelamar'] ?></div>
                                                <div class="job-stat-label">Pelamar</div>
                                            </div>
                                            <!-- Views removed, as it's not in DB -->
                                        </div>
                                    </div>
                                    <button class="btn-arrow" onclick="window.location.href='kelola_lowongan.php?id=<?= $low['id_lowongan'] ?>'">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </button>
                                </div>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <div style="text-align: center; padding: 32px; color: #6b7280;">
                                <p>Belum ada lowongan aktif.</p>
                            </div>
                        <?php endif; ?>

                        <a href="posting_lowongan.php" style="text-decoration: none;"></a>
                        <button class="btn-post-full">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Posting Lowongan Baru
                        </button>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Quick Actions -->
                    <div class="section">
                        <h2 class="section-title" style="margin-bottom: 16px;">Aksi Cepat</h2>

                        <div class="quick-actions">
                            <div class="action-item">
                                <div class="action-icon teal">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="16"></line>
                                        <line x1="8" y1="12" x2="16" y2="12"></line>
                                    </svg>
                                </div>
                                <div class="action-info">
                                    <div class="action-title">Post Lowongan</div>
                                    <div class="action-subtitle">Buat lowongan baru</div>
                                </div>
                            </div>

                            <div class="action-item">
                                <div class="action-icon yellow">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <div class="action-info">
                                    <div class="action-title">Review Kandidat</div>
                                    <div class="action-subtitle"><?= $total_menunggu ?> kandidat menunggu</div>
                                </div>
                            </div>

                            <div class="action-item">
                                <div class="action-icon blue">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <div class="action-info">
                                    <div class="action-title">Lihat Analytics</div>
                                    <div class="action-subtitle">Performa lowongan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Candidates Section -->
            <div class="top-candidates">
                <div class="section">
                    <div class="section-header">
                        <div class="section-title-with-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                                </polygon>
                            </svg>
                            Kandidat Terbaik (AI Ranking)
                        </div>
                        <a href="" class="btn-see-all">
                            Lihat Semua
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    </div>

                    <div class="candidates-grid">
                        <?php
                        if (mysqli_num_rows($q_kandidat) > 0):
                            while ($kan = mysqli_fetch_assoc($q_kandidat)):
                                $inisial = strtoupper(substr($kan['nama'], 0, 2));
                                
                                // Parse skills
                                $skills_arr = [];
                                if (!empty($kan['skills'])) {
                                    $skills_arr = explode(',', $kan['skills']);
                                    $skills_arr = array_slice(array_map('trim', $skills_arr), 0, 3); // Ambil max 3
                                }

                                // Dummy match badge since real AI matching is complex
                                $match_score = rand(70, 95);
                                $badge_class = $match_score >= 90 ? 'high' : ($match_score >= 80 ? 'medium' : 'good');
                        ?>
                                <div class="candidate-card">
                                    <span class="match-badge <?= $badge_class ?>"><?= $match_score ?>% Match</span>
                                    <div class="candidate-avatar"><?= $inisial ?></div>
                                    <div class="candidate-name"><?= htmlspecialchars($kan['nama']) ?></div>
                                    <div class="candidate-role"><?= htmlspecialchars($kan['bidang_keahlian'] ?? 'Belum ada bidang') ?></div>
                                    <div class="candidate-skills">
                                        <?php foreach ($skills_arr as $skill): ?>
                                            <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (empty($skills_arr)): ?>
                                            <span class="skill-tag" style="background:transparent; color:#9ca3af;">Belum ada skill</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="candidate-actions">
                                        <button class="btn-shortlist" onclick="window.location.href='pesan_perusahaan.php?id=<?= $kan['id_user'] ?>'">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                            </svg>
                                            Pesan
                                        </button>
                                        <button class="btn-profile" onclick="window.location.href='kandidat.php'">Profil</button>
                                    </div>
                                </div>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <div style="grid-column: 1 / -1; text-align: center; padding: 32px; color: #6b7280; background: white; border-radius: 12px; border: 1px solid #e5e7eb;">
                                <p>Belum ada kandidat yang melamar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>

    </script>
</body>

</html>