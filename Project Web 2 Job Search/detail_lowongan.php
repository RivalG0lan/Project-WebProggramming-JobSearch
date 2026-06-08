<?php

session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login_pelamar.php");
    exit;
}

if ($_SESSION['role'] != 'pelamar') {
    header("Location: login_pelamar.php");
    exit;
}

$id_pelamar = $_SESSION['id_user'];

/* =========================
   DATA USER UNTUK SIDEBAR
   ========================= */
$result = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE id_user='$id_pelamar'
");

$user = mysqli_fetch_assoc($result);

$initials = strtoupper(substr($user['nama'] ?? 'U', 0, 2));


// data lowongan
$id_lowongan = (int) $_GET['id'];

$query = mysqli_query(
    $conn,
    "SELECT l.*, u.nama AS nama_perusahaan
     FROM lowongan l
     INNER JOIN users u ON l.id_perusahaan = u.id_user
     WHERE l.id_lowongan='$id_lowongan'
     AND l.status='aktif'
     LIMIT 1"
);

$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Lowongan tidak ditemukan atau sudah ditutup.');window.location='cari_lowongan.php';</script>";
    exit;
}

// Cek apakah sudah melamar
$cek_lamar = mysqli_query(
    $conn,
    "SELECT id_lamaran FROM lamaran
     WHERE id_pelamar='$id_pelamar' AND id_lowongan='$id_lowongan'
     LIMIT 1"
);
$sudah_lamar = mysqli_num_rows($cek_lamar) > 0;

// Lowongan lain dari perusahaan yang sama
$other = mysqli_query(
    $conn,
    "SELECT id_lowongan, judul, lokasi, gaji
     FROM lowongan
     WHERE id_perusahaan='{$data['id_perusahaan']}'
     AND id_lowongan != '$id_lowongan'
     AND status='aktif'
     ORDER BY id_lowongan DESC
     LIMIT 3"
);

/* =========================
   HITUNG KELENGKAPAN PROFIL
   ========================= */

$fields_cek = [
    'nama',
    'telepon',
    'lokasi',
    'bio',
    'pendidikan',
    'pengalaman',
    'skills',
    'foto_profil',
    'cv_path'
];

$isi = 0;

foreach ($fields_cek as $field) {
    if (!empty($user[$field])) {
        $isi++;
    }
}

$kelengkapan = round(
    ($isi / count($fields_cek)) * 100
);

//

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['judul']) ?> - LokerIn</title>
    <link rel="icon" type="image/png" href="assets/icon_head_lokerin.png">
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
            color: #0D9488;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 24px
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
            overflow: hidden;
            flex-shrink: 0;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: transform .2s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .avatar-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .8);

            justify-content: center;
            align-items: center;

            z-index: 9999;
        }

        .avatar-modal img {
            max-width: 90%;
            max-height: 90%;

            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0, 0, 0, .5);
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
            margin-bottom: 24px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #111827;
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
        }

        /* ── Detail layout ── */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 24px;
            align-items: start;
        }

        /* Left: main card */
        .main-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
        }

        .card-hero {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
            padding: 32px;
            color: white;
            position: relative;
        }

        .card-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .card-hero h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .card-hero-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 14px;
            opacity: 0.9;
        }

        .card-hero-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card-body {
            padding: 32px;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
        }

        .desc-text {
            font-size: 15px;
            color: #374151;
            line-height: 1.8;
            white-space: pre-wrap;
            margin-bottom: 32px;
        }

        /* Tags */
        .tag-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 32px;
        }

        .tag {
            padding: 6px 14px;
            background: #f3f4f6;
            color: #374151;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        .tag.teal {
            background: #e0f2f1;
            color: #0d9488;
        }

        /* Info grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 32px;
        }

        .info-box {
            background: #f9fafb;
            border-radius: 10px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-box-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d9488;
            flex-shrink: 0;
        }

        .info-box-label {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box-value {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: #f3f4f6;
            margin: 0 0 24px;
        }

        /* Apply bar */
        .apply-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 32px;
            border-top: 1px solid #f3f4f6;
            flex-wrap: wrap;
            gap: 12px;
        }

        .apply-salary {
            font-size: 24px;
            font-weight: 700;
            color: #0d9488;
        }

        .apply-salary-label {
            font-size: 12px;
            color: #9ca3af;
        }

        .btn-apply {
            padding: 14px 32px;
            background: #0d9488;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-apply:hover {
            background: #0f766e;
            transform: translateY(-1px);
        }

        .btn-apply-disabled {
            padding: 14px 32px;
            background: #f3f4f6;
            color: #9ca3af;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: not-allowed;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Right: sticky sidebar */
        .side-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .side-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 24px;
        }

        .side-card-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 16px;
        }

        .company-info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .company-logo {
            width: 56px;
            height: 56px;
            background: #fef3c7;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            color: #d97706;
            flex-shrink: 0;
        }

        .company-name {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .company-label {
            font-size: 13px;
            color: #6b7280;
        }

        .company-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        /* Other jobs */
        .other-job {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
            text-decoration: none;
            transition: all 0.2s;
        }

        .other-job:last-child {
            border-bottom: none;
        }

        .other-job:hover .other-job-title {
            color: #0d9488;
        }

        .other-job-icon {
            width: 40px;
            height: 40px;
            background: #e0f2f1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d9488;
            flex-shrink: 0;
        }

        .other-job-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 2px;
        }

        .other-job-meta {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Already applied banner */
        .already-applied {
            background: #e0f2f1;
            border: 1px solid #99f6e4;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #0d9488;
            margin-bottom: 0;
        }

        @media(max-width:1100px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width:768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .info-grid {
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
                    <?php if (!empty($user['foto_profil']) && file_exists('uploads/foto_profil/' . $user['foto_profil'])): ?>
                        <img src="uploads/foto_profil/<?= htmlspecialchars($user['foto_profil']) ?>" alt="foto">
                    <?php else: ?>
                        <?= $initials ?>
                    <?php endif; ?>
                </div>

                <div class="user-info">
                    <div class="user-name">
                        <?= htmlspecialchars($user['nama'] ?? '-') ?>
                    </div>

                    <div class="user-email">
                        <?= htmlspecialchars($user['email'] ?? '') ?>
                    </div>
                </div>
            </div>
            <div class="career-score">
                <span class="career-score-label">Kelengkapan Profil</span>
                <span class="career-score-value">
                    <?= $kelengkapan ?>%
                </span>
            </div>
            <nav>
                <a href="dashboard_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg> Dashboard
                </a>
                <a href="cari_lowongan.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg> Cari Lowongan
                </a>
                <a href="lamaran_saya.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg> Lamaran Saya
                </a>
                <a href="profil_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg> Profil
                </a>
                <a href="career_roadmap.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3v18h18" />
                        <path d="m19 9-5 5-4-4-3 3" />
                    </svg> Career Roadmap
                </a>
                <a href="skill_gap_analyzer.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 1v6m0 6v6m-9-9h6m6 0h6" />
                    </svg> Skill Gap Analyzer
                </a>
                <a href="pesan_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg> Pesan
                </a>
                <div class="nav-divider"></div>
                <!-- <div class="nav-section-title">Pengaturan</div> -->
                <a href="pengaturan_pelamar.php" class="nav-item">
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
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg> Keluar
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header / breadcrumb -->
            <header class="header">
                <a href="cari_lowongan.php" class="back-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    Kembali ke Pencarian
                </a>
                <button class="icon-btn" title="Simpan Lowongan">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                    </svg>
                </button>
            </header>

            <div class="detail-grid">
                <!-- Left: main detail -->
                <div class="main-card">
                    <!-- Hero -->
                    <div class="card-hero">
                        <div class="card-hero-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Lowongan Aktif
                        </div>
                        <h1><?= htmlspecialchars($data['judul']) ?></h1>
                        <div class="card-hero-meta">
                            <span class="card-hero-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                </svg>
                                <?= htmlspecialchars($data['nama_perusahaan']) ?>
                            </span>
                            <span class="card-hero-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <?= htmlspecialchars($data['lokasi']) ?>
                            </span>
                            <span class="card-hero-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                Full-time
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Info grid -->
                        <div class="info-grid">
                            <div class="info-box">
                                <div class="info-box-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23" />
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="info-box-label">Gaji</div>
                                    <div class="info-box-value">Rp <?= number_format($data['gaji'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="info-box-label">Lokasi</div>
                                    <div class="info-box-value"><?= htmlspecialchars($data['lokasi']) ?></div>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                        <polyline points="22,6 12,13 2,6" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="info-box-label">Kategori</div>
                                    <div class="info-box-value"><?= htmlspecialchars($data['kategori']) ?></div>
                                </div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <rect x="2" y="7" width="20" height="14" rx="2" />
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="info-box-label">Tipe</div>
                                    <div class="info-box-value">Full-time</div>
                                </div>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <!-- Deskripsi -->
                        <div class="section-label">Deskripsi Pekerjaan</div>
                        <div class="desc-text"><?= htmlspecialchars($data['deskripsi']) ?></div>

                        <!-- Tags Skills (Dari Database) -->
                        <div class="section-label">Skills yang Dibutuhkan</div>
                        <div class="tag-row">
                            <?php 
                            if (!empty($data['skills_required'])) {
                                $skills_req_arr = array_filter(array_map('trim', explode(',', $data['skills_required'])));
                                foreach ($skills_req_arr as $skill_req) {
                                    echo '<span class="tag teal">' . htmlspecialchars($skill_req) . '</span>';
                                }
                            } else {
                                echo '<span class="tag" style="background:transparent; color:#9ca3af; padding-left:0;">Belum ada spesifikasi skill</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Apply bar -->
                    <div class="apply-bar">
                        <div>
                            <div class="apply-salary-label">Gaji per bulan</div>
                            <div class="apply-salary">Rp <?= number_format($data['gaji'], 0, ',', '.') ?></div>
                        </div>
                        <?php if ($sudah_lamar): ?>
                            <div class="already-applied">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                Sudah Melamar
                            </div>
                        <?php else: ?>
                            <a href="lamar.php?id=<?= $data['id_lowongan'] ?>"
                                onclick="return confirm('Yakin ingin melamar posisi ini?')" class="btn-apply">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                                Lamar Sekarang
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: side panel -->
                <div class="side-panel">
                    <!-- Company info -->
                    <div class="side-card">
                        <div class="side-card-title">Tentang Perusahaan</div>
                        <div class="company-info-row">
                            <div class="company-logo"><?= strtoupper(substr($data['nama_perusahaan'], 0, 2)) ?></div>
                            <div>
                                <div class="company-name"><?= htmlspecialchars($data['nama_perusahaan']) ?></div>
                                <div class="company-label">Perusahaan</div>
                            </div>
                        </div>
                        <div class="company-detail">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <?= htmlspecialchars($data['lokasi']) ?>
                        </div>
                        <div class="company-detail">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                            </svg>
                            <?= htmlspecialchars($data['kategori']) ?>
                        </div>
                    </div>

                    <!-- Other jobs from same company -->
                    <?php if (mysqli_num_rows($other) > 0): ?>
                        <div class="side-card">
                            <div class="side-card-title">Lowongan Lain dari Perusahaan Ini</div>
                            <?php while ($oj = mysqli_fetch_assoc($other)): ?>
                                <a href="detail_lowongan.php?id=<?= $oj['id_lowongan'] ?>" class="other-job">
                                    <div class="other-job-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <rect x="2" y="7" width="20" height="14" rx="2" />
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="other-job-title"><?= htmlspecialchars($oj['judul']) ?></div>
                                        <div class="other-job-meta">
                                            <?= htmlspecialchars($oj['lokasi']) ?> · Rp
                                            <?= number_format($oj['gaji'], 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Quick tip -->
                    <div class="side-card" style="background:#f0fdfa; border-color:#99f6e4;">
                        <div style="display:flex; gap:10px; align-items:flex-start;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0d9488"
                                stroke-width="2" style="flex-shrink:0; margin-top:2px;">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <div>
                                <div style="font-size:14px; font-weight:700; color:#0d9488; margin-bottom:4px;">Tips
                                    Melamar</div>
                                <div style="font-size:13px; color:#374151; line-height:1.5;">
                                    Pastikan profil Anda lengkap dan CV sudah terupload sebelum melamar untuk
                                    meningkatkan peluang diterima.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- lihat foto profil -->
    <div id="avatarModal" class="avatar-modal">
        <img id="avatarModalImg" src="" alt="Foto Profil">
    </div>
    <script>
        const avatar = document.querySelector(".user-avatar img");

        const modal = document.getElementById("avatarModal");
        const modalImg = document.getElementById("avatarModalImg");

        if (avatar) {

            avatar.addEventListener("click", () => {

                modal.style.display = "flex";
                modalImg.src = avatar.src;

            });

            modal.addEventListener("click", () => {

                modal.style.display = "none";

            });

            document.addEventListener("keydown", (e) => {

                if (e.key === "Escape") {
                    modal.style.display = "none";
                }

            });

        }
    </script>
</body>

</html>