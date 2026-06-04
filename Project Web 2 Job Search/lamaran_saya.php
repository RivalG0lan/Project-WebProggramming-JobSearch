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

$query = mysqli_query(
    $conn,

    "SELECT

lamaran.*,

lowongan.judul,
lowongan.kategori,
lowongan.lokasi

FROM lamaran

INNER JOIN lowongan
ON lamaran.id_lowongan = lowongan.id_lowongan

WHERE lamaran.id_pelamar='$id_pelamar'

ORDER BY lamaran.id_lamaran DESC"
);

$total_lamaran = mysqli_num_rows($query);

$total_review = mysqli_num_rows(mysqli_query(
    $conn,

    "SELECT * FROM lamaran

WHERE id_pelamar='$id_pelamar'

AND status='review'"
));

$total_accepted = mysqli_num_rows(mysqli_query(
    $conn,

    "SELECT * FROM lamaran

WHERE id_pelamar='$id_pelamar'

AND status='accepted'"
));

$total_interview = mysqli_num_rows(mysqli_query(
    $conn,

    "SELECT * FROM lamaran

WHERE id_pelamar='$id_pelamar'

AND status='interview'"
));

$total_rejected = mysqli_num_rows(mysqli_query(
    $conn,

    "SELECT * FROM lamaran

WHERE id_pelamar='$id_pelamar'

AND status='rejected'"
));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lamaran Saya - LokerIn</title>
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

        /* Page Header */
        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 14px;
            color: #6b7280;
        }

        /* Stats Section */
        .stats-section {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            flex: 1;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .stat-card.active {
            border-color: #0d9488;
            background: #f0fdfa;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .stat-card.active .stat-number {
            color: #0d9488;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
        }

        /* Application List */
        .application-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .application-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            transition: all 0.2s;
        }

        .application-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .application-header {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .application-logo {
            width: 56px;
            height: 56px;
            background: #e0f2f1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d9488;
            flex-shrink: 0;
        }

        .application-info {
            flex: 1;
        }

        .application-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .application-company {
            font-size: 14px;
            color: #6b7280;
        }

        .application-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.interview {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-badge.dikirim {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.review {
            background: #fef3c7;
            color: #92400e;
        }



        .status-badge.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-badge.accepted {
            background: #dcfce7;
            color: #166534;
        }

        .application-meta {
            display: flex;
            gap: 24px;
            margin-bottom: 16px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
        }

        .application-actions {
            display: flex;
            gap: 8px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }

        .btn-detail {
            flex: 1;
            background: white;
            color: #0d9488;
            padding: 10px 24px;
            border-radius: 8px;
            border: 1px solid #0d9488;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-detail:hover {
            background: #f0fdfa;
        }

        .btn-chat {
            flex: 1;
            background: white;
            color: #0d9488;
            padding: 10px 24px;
            border-radius: 8px;
            border: 1px solid #0d9488;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-chat:hover {
            background: #f0fdfa;
        }

        .btn-feedback {
            background: white;
            color: #6b7280;
            padding: 10px 24px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-feedback:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .update-time {
            font-size: 12px;
            color: #9ca3af;
            text-align: right;
        }

        .btn-cancel {

            display: inline-block;
            margin-top: 10px;
            padding: 8px 14px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;

        }

        .btn-cancel:hover {

            background: #dc2626;
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

            .stats-section {
                flex-wrap: wrap;
            }

            .stat-card {
                min-width: calc(50% - 8px);
            }

            .application-meta {
                flex-wrap: wrap;
                gap: 12px;
            }

            .application-actions {
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

            <div class="user-profile">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <div class="user-name">
                        <?= $_SESSION['nama']; ?>
                    </div>
                    <div class="user-email">
                        <?= $_SESSION['email']; ?>
                    </div>
                </div>
            </div>

            <div class="career-score">
                <span class="career-score-label">Career Score</span>
                <span class="career-score-value">30%</span>
            </div>

            <nav>
                <a href="dashboard_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>

                <a href="cari_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Cari Lowongan
                </a>

                <a href="lamaran_saya.php" class="nav-item active">
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

                <a href="career_roadmap.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3v18h18"></path>
                        <path d="m19 9-5 5-4-4-3 3"></path>
                    </svg>
                    Career Roadmap
                </a>

                <a href="skill_gap_analyzer.php" class="nav-item">
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
                    <?= $_SESSION['email']; ?>
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

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Lamaran Saya</h1>
                <p class="page-subtitle">Pantau status semua lamaran Anda di sini</p>
            </div>

            <!-- Stats Section -->
            <div class="stats-section">
                <div class="stat-card active">
                    <div class="stat-number">

                        <?= $total_lamaran; ?>

                    </div>
                    <div class="stat-label">Semua</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">

                        <?= $total_review; ?>

                    </div>
                    <div class="stat-label">Direview</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?= $total_accepted; ?>
                    </div>
                    <div class="stat-label">Accepted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?= $total_interview; ?>
                    </div>
                    <div class="stat-label">Interview</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?= $total_rejected; ?>
                    </div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>

            <!-- Application List -->
            <div class="application-list">


                <?php
                if (mysqli_num_rows($query) > 0) {
                    while ($data = mysqli_fetch_assoc($query)) {
                        ?>
                        <div class="application-card">
                            <div class="application-header">
                                <div class="application-logo">
                                    💼
                                </div>
                                <div class="application-info">
                                    <h3 class="application-title">
                                        <?= $data['judul']; ?>
                                    </h3>
                                    <p class="application-company">
                                        <?= $data['kategori']; ?>
                                    </p>
                                </div>
                                <div class="application-status">
                                    <span class="status-badge <?= $data['status']; ?>">
                                        <?= ucfirst($data['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="application-meta">
                                <div class="meta-item">
                                    📍 <?= $data['lokasi']; ?>
                                </div>
                                <div class="meta-item">
                                    📅 <?= date('d M Y', strtotime($data['tanggal_lamaran'])); ?>
                                </div>
                            </div>
                            <div class="update-time">
                                Status lamaran:
                                <?= ucfirst($data['status']); ?>
                            </div>

                            <a <?php
                            if (
                                $data['status'] == 'dikirim'
                                || $data['status'] == 'review'
                            ) {
                                ?>

                                href="batal_lamaran.php?id=<?= $data['id_lamaran']; ?>"
                                    onclick="return confirm('Yakin ingin membatalkan lamaran ini?')" class="btn-cancel">

                                    Batalkan

                                    <?php
                            }
                            ?>

                            </a>
                        </div>
                        <?php
                    }
                } else {
                    ?>

                    <div style="text-align:center; padding:40px;">

                        <h3>Belum Ada Lamaran</h3>

                        <p>

                            Silakan lamar pekerjaan terlebih dahulu

                        </p>

                    </div>
                    <?php
                }
                ?>






            </div>
        </main>
    </div>

    <script>


        // Handle stat card clicks
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function () {
                document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
            });
        });

    </script>
</body>