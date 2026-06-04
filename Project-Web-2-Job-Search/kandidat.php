<?php

session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login_perusahaan.php");
    exit;
}

if ($_SESSION['role'] != 'perusahaan') {
    header("Location: login_perusahaan.php");
    exit;
}

$id_perusahaan = $_SESSION['id_user'];

// Ambil semua lowongan milik perusahaan ini untuk filter dropdown
$query_lowongan = mysqli_query(
    $conn,
    "SELECT id_lowongan, judul FROM lowongan
     WHERE id_perusahaan='$id_perusahaan'
     ORDER BY id_lowongan DESC"
);

// Filter berdasarkan lowongan tertentu atau semua
$filter_lowongan = isset($_GET['id_lowongan']) ? (int) $_GET['id_lowongan'] : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';

// Build query utama: join lamaran + users (pelamar) + lowongan
$where = "WHERE l.id_perusahaan='$id_perusahaan'";

if ($filter_lowongan > 0) {
    $where .= " AND lmr.id_lowongan='$filter_lowongan'";
}
if ($filter_status != '') {
    $status_esc = mysqli_real_escape_string($conn, $filter_status);
    $where .= " AND lmr.status='$status_esc'";
}
if ($keyword != '') {
    $where .= " AND (u.nama LIKE '%$keyword%' OR l.judul LIKE '%$keyword%')";
}

$query_pelamar = mysqli_query(
    $conn,
    "SELECT
        lmr.id_lamaran,
        lmr.status,
        lmr.tanggal_lamaran,
        u.nama AS nama_pelamar,
        u.email AS email_pelamar,
        l.judul AS judul_lowongan,
        l.id_lowongan,
        l.lokasi
     FROM lamaran lmr
     INNER JOIN users u   ON lmr.id_pelamar  = u.id_user
     INNER JOIN lowongan l ON lmr.id_lowongan = l.id_lowongan
     $where
     ORDER BY lmr.id_lamaran DESC"
);

$total = mysqli_num_rows($query_pelamar);

// Hitung per status
$count = [];
foreach (['dikirim', 'review', 'interview', 'accepted', 'rejected'] as $s) {
    $w2 = "WHERE l.id_perusahaan='$id_perusahaan'";
    if ($filter_lowongan > 0)
        $w2 .= " AND lmr.id_lowongan='$filter_lowongan'";
    $r = mysqli_query(
        $conn,
        "SELECT COUNT(*) as c FROM lamaran lmr
         INNER JOIN lowongan l ON lmr.id_lowongan=l.id_lowongan
         $w2 AND lmr.status='$s'"
    );
    $count[$s] = mysqli_fetch_assoc($r)['c'];
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kandidat - LokerIn</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f9fafb;
            color: #111827;
            line-height: 1.6;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
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

        /* ── Main ── */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 24px 32px;
        }

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
            text-decoration: none;
        }

        .btn-post:hover {
            background: #0f766e;
        }

        /* Page header */
        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .page-subtitle {
            font-size: 14px;
            color: #6b7280;
        }

        /* Stats row */
        .stats-row {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .stat-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .stat-pill:hover {
            border-color: #0d9488;
            color: #0d9488;
        }

        .stat-pill.active {
            background: #e0f2f1;
            border-color: #0d9488;
            color: #0d9488;
        }

        .stat-pill .pill-num {
            background: #f3f4f6;
            color: #111827;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }

        .stat-pill.active .pill-num {
            background: #0d9488;
            color: white;
        }

        /* Filter bar */
        .filter-bar {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-wrap {
            flex: 1;
            min-width: 220px;
            position: relative;
        }

        .search-wrap svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: #0d9488;
        }

        .filter-select {
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            outline: none;
            min-width: 200px;
        }

        .filter-select:focus {
            border-color: #0d9488;
        }

        .btn-filter-submit {
            padding: 10px 20px;
            background: #0d9488;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter-submit:hover {
            background: #0f766e;
        }

        /* AI banner */
        .ai-banner {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ai-banner svg {
            color: #0d9488;
            flex-shrink: 0;
        }

        .ai-banner-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .ai-banner-sub {
            font-size: 13px;
            color: #6b7280;
        }

        /* Result count */
        .result-count {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .result-count strong {
            color: #111827;
        }

        /* Candidate card */
        .candidate-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
            transition: all 0.2s;
        }

        .candidate-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .cand-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #0d9488;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }

        .cand-info {
            flex: 1;
        }

        .cand-name-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .cand-name {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .cand-pos {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .cand-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #6b7280;
        }

        .cand-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cand-applied {
            margin-left: auto;
            font-size: 12px;
            color: #9ca3af;
            white-space: nowrap;
        }

        /* Status badge */
        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-dikirim {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-review {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-interview {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-accepted {
            background: #dcfce7;
            color: #166534;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Update status form */
        .update-section {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }

        .update-section label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
        }

        .status-select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: #f9fafb;
            cursor: pointer;
            outline: none;
            min-width: 160px;
        }

        .status-select:focus {
            border-color: #0d9488;
        }

        .btn-update {
            padding: 8px 18px;
            background: #0d9488;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-update:hover {
            background: #0f766e;
        }

        /* Divider between meta and actions */
        .card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
            margin-top: 16px;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-outline-teal {
            background: white;
            color: #0d9488;
            border-color: #0d9488;
        }

        .btn-outline-teal:hover {
            background: #f0fdfa;
        }

        .btn-outline-red {
            background: white;
            color: #ef4444;
            border-color: #ef4444;
        }

        .btn-outline-red:hover {
            background: #fef2f2;
        }

        .btn-teal {
            background: #0d9488;
            color: white;
            border-color: #0d9488;
        }

        .btn-teal:hover {
            background: #0f766e;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            color: #6b7280;
        }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @media(max-width:768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .filter-bar {
                flex-direction: column;
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
                <div class="company-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                <div>
                    <div class="company-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="company-industry"><?= htmlspecialchars($_SESSION['email']) ?></div>
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
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg> Dashboard
                </a>
                <a href="posting_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="1" />
                        <circle cx="12" cy="12" r="8" />
                        <line x1="12" y1="1" x2="12" y2="3" />
                    </svg> Posting Lowongan
                </a>
                <a href="kelola_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg> Kelola Lowongan
                </a>
                <a href="kandidat.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg> Kandidat
                </a>
                <a href="profil_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    </svg> Profil Perusahaan
                </a>
                <a href="analytics.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10" />
                        <line x1="12" y1="20" x2="12" y2="4" />
                        <line x1="6" y1="20" x2="6" y2="14" />
                    </svg> Analytics
                </a>
                <a href="pesan_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg> Pesan
                </a>
                <div class="nav-divider"></div>
                <a href="pengaturan_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6"></path>
                        <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                        <path d="M1 12h6m6 0h6"></path>
                        <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                    </svg> Pengaturan
                </a>
                <a href="logout.php" class="nav-item nav-item-logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg> Keluar
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">

            <!-- Header -->
            <header class="header">
                <div class="greeting">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?></div>
                <a href="posting_lowongan.php" class="btn-post">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="16" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                    </svg>
                    Post Lowongan
                </a>
            </header>

            <!-- Page title -->
            <div class="page-header">
                <h1 class="page-title">Kelola Kandidat</h1>
                <p class="page-subtitle">Review dan update status semua pelamar yang masuk</p>
            </div>

            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">✓ Status lamaran berhasil diperbarui.</div>
            <?php endif; ?>

            <!-- Stats pills -->
            <div class="stats-row">
                <a href="kandidat.php<?= $filter_lowongan ? '?id_lowongan=' . $filter_lowongan : '' ?>"
                    class="stat-pill <?= $filter_status === '' ? 'active' : '' ?>">
                    Semua <span class="pill-num"><?= $total ?></span>
                </a>
                <?php
                $status_labels = ['dikirim' => 'Dikirim', 'review' => 'Review', 'interview' => 'Interview', 'accepted' => 'Accepted', 'rejected' => 'Ditolak'];
                foreach ($status_labels as $sk => $sl):
                    $q = $filter_lowongan ? '?id_lowongan=' . $filter_lowongan . '&status=' . $sk : '?status=' . $sk;
                    ?>
                    <a href="kandidat.php<?= $q ?>" class="stat-pill <?= $filter_status === $sk ? 'active' : '' ?>">
                        <?= $sl ?> <span class="pill-num"><?= $count[$sk] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Filter bar -->
            <form method="GET" action="kandidat.php">
                <div class="filter-bar">
                    <div class="search-wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input type="text" name="keyword" class="search-input" placeholder="Cari nama pelamar..."
                            value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                    <select name="id_lowongan" class="filter-select">
                        <option value="0">Semua Lowongan</option>
                        <?php
                        mysqli_data_seek($query_lowongan, 0);
                        while ($lw = mysqli_fetch_assoc($query_lowongan)):
                            ?>
                            <option value="<?= $lw['id_lowongan'] ?>" <?= $filter_lowongan == $lw['id_lowongan'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lw['judul']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if ($filter_status): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn-filter-submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        Filter
                    </button>
                </div>
            </form>

            <!-- AI Banner -->
            <div class="ai-banner">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor"
                    stroke-width="1">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
                <div>
                    <div class="ai-banner-title">AI Candidate Ranking Aktif</div>
                    <div class="ai-banner-sub">Kandidat diurutkan berdasarkan waktu lamaran terbaru. Update status
                        langsung dari kartu di bawah.</div>
                </div>
            </div>

            <!-- Result count -->
            <div class="result-count">
                Menampilkan <strong><?= $total ?> kandidat</strong>
                <?= $filter_lowongan ? ' untuk lowongan ini' : '' ?>
                <?= $filter_status ? ' — status: <strong>' . htmlspecialchars($status_labels[$filter_status] ?? $filter_status) . '</strong>' : '' ?>
            </div>

            <!-- Candidate list -->
            <?php if ($total > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($query_pelamar)):
                    $inisial = strtoupper(substr($row['nama_pelamar'], 0, 2));
                    $st = $row['status'];
                    $badge_class = 'badge-' . $st;
                    $tanggal = date('d M Y', strtotime($row['tanggal_lamaran']));
                    ?>
                    <div class="candidate-card">
                        <div class="card-header">
                            <div class="cand-avatar"><?= $inisial ?></div>
                            <div class="cand-info">
                                <div class="cand-name-row">
                                    <span class="cand-name"><?= htmlspecialchars($row['nama_pelamar']) ?></span>
                                    <span class="status-badge <?= $badge_class ?>">
                                        <?= ucfirst($st) ?>
                                    </span>
                                </div>
                                <div class="cand-pos">Melamar: <?= htmlspecialchars($row['judul_lowongan']) ?></div>
                                <div class="cand-meta">
                                    <span class="cand-meta-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <rect x="2" y="4" width="20" height="16" rx="2" />
                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                        </svg>
                                        <?= htmlspecialchars($row['email_pelamar']) ?>
                                    </span>
                                    <span class="cand-meta-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                        <?= htmlspecialchars($row['lokasi']) ?>
                                    </span>
                                    <span class="cand-applied">Melamar: <?= $tanggal ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Update Status -->
                        <form method="POST" action="update_status_lamaran.php" style="display:inline;">
                            <input type="hidden" name="id_lamaran" value="<?= $row['id_lamaran'] ?>">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                            <div class="update-section">
                                <label>Update Status:</label>
                                <select name="status" class="status-select">
                                    <option value="dikirim" <?= $st == 'dikirim' ? 'selected' : '' ?>>📩 Dikirim</option>
                                    <option value="review" <?= $st == 'review' ? 'selected' : '' ?>>🔍 Review</option>
                                    <option value="interview" <?= $st == 'interview' ? 'selected' : '' ?>>🎤 Interview</option>
                                    <option value="accepted" <?= $st == 'accepted' ? 'selected' : '' ?>>✅ Accepted</option>
                                    <option value="rejected" <?= $st == 'rejected' ? 'selected' : '' ?>>❌ Ditolak</option>
                                </select>
                                <button type="submit" class="btn-update">Simpan</button>
                            </div>
                        </form>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"
                        style="margin:0 auto 16px; display:block;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <h3>Belum Ada Pelamar</h3>
                    <p>Belum ada yang melamar ke lowongan Anda<?= $filter_status ? ' dengan status ini' : '' ?>.</p>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>

</html>