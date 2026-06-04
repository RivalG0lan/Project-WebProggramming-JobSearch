<?php

session_start();

if (!isset($_SESSION['id_user'])) {

    header("Location: login_pelamar.php");

}

if ($_SESSION['role'] != 'pelamar') {

    header("Location: login_pelamar.php");

}

include 'config/koneksi.php';

$query = mysqli_query(
    $conn,

    "SELECT * FROM lowongan

WHERE status='aktif'

ORDER BY id_lowongan DESC"
);

$total_lowongan = mysqli_num_rows($query);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Lowongan - Lokerin</title>
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

        /* Sidebar - Same as dashboard */
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

        /* Page Title */
        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        /* Search Section */
        .search-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .search-input-wrapper {
            flex: 1;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 14px;
            color: #111827;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        .search-input::placeholder {
            color: #9ca3af;
        }

        .btn-search {
            background: #14b8a6;
            color: white;
            padding: 12px 32px;
            border-radius: 10px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-search:hover {
            background: #0d9488;
        }

        .btn-filter {
            background: white;
            color: #14b8a6;
            padding: 12px 24px;
            border-radius: 10px;
            border: 1px solid #14b8a6;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-filter:hover {
            background: #f0fdfa;
        }

        /* Results Header */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .results-count {
            font-size: 14px;
            color: #6b7280;
        }

        .results-count strong {
            color: #111827;
            font-weight: 600;
        }

        .sort-select {
            padding: 8px 36px 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            background: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 12 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M3 5L6 8L9 5' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        /* Job List */
        .job-list {
            display: flex;
            flex-direction: column;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .job-card-header {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .job-logo {
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

        .job-info {
            flex: 1;
        }

        .job-badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .job-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .job-company {
            font-size: 14px;
            color: #6b7280;
        }

        .job-actions {
            display: flex;
            gap: 8px;
        }

        .btn-save {
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

        .btn-save:hover {
            background: #f9fafb;
            border-color: #0d9488;
            color: #0d9488;
        }

        .btn-apply {
            background: #14b8a6;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-apply:hover {
            background: #0d9488;
        }

        .job-meta {
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

        .job-description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .job-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .job-tag {
            background: #f3f4f6;
            color: #4b5563;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .job-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }

        .job-salary {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }

        /* Load More */
        .load-more {
            text-align: center;
            margin-top: 32px;
        }

        .btn-load-more {
            background: white;
            color: #0d9488;
            padding: 12px 32px;
            border-radius: 10px;
            border: 1px solid #0d9488;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-load-more:hover {
            background: #f0fdfa;
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

            .search-bar {
                flex-direction: column;
            }

            .job-meta {
                flex-wrap: wrap;
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

                <a href="cari_lowongan.php" class="nav-item active">
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
                    Jakarta, Indonesia
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
                <h1 class="page-title">Cari Lowongan Pekerjaan</h1>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <form method="GET">

                    <div class="search-bar">
                        <div class="search-input-wrapper">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <input type="text" name="keyword" class="search-input"
                                placeholder="Posisi, skill, atau perusahaan...">
                        </div>

                        <div class="search-input-wrapper">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <input type="text" name="location" class="search-input" placeholder="Lokasi...">
                        </div>

                        <button type="submit" class="btn-search">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            Cari
                        </button>

                        <button class="btn-filter">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Header -->
            <div class="results-header">
                <div class="results-count">
                    Menampilkan <strong> <?= $total_lowongan; ?> lowongan </strong>
                </div>

                <select class="sort-select">
                    <option>Paling Relevan</option>
                    <option>Terbaru</option>
                    <option>Gaji Tertinggi</option>
                    <option>Match Tertinggi</option>
                </select>
            </div>

            <!-- Job List -->
            <div class="job-list">
                <?php
                if (mysqli_num_rows($query) > 0) {
                    ?>
                    <?php
                    while ($data = mysqli_fetch_assoc($query)) {
                        ?>
                        <div class="job-card">
                            <div class="job-card-header">
                                <div class="job-logo">
                                    💼
                                </div>

                                <div class="job-info">
                                    <h3 class="job-title">
                                        <?= $data['judul']; ?>
                                    </h3>
                                    <p class="job-company">
                                        <?= $data['kategori']; ?>
                                    </p>
                                </div>

                                <div class="job-actions">
                                    <a href="detail_lowongan.php?id=<?= $data['id_lowongan']; ?>" class="btn-apply">

                                        Lihat Detail
                                    </a>
                                </div>

                            </div>

                            <div class="job-meta">

                                <div class="meta-item">

                                    📍 <?= $data['lokasi']; ?>

                                </div>

                            </div>

                            <p class="job-description">

                                <?= $data['deskripsi']; ?>

                            </p>

                            <div class="job-footer">

                                <div class="job-salary">

                                    Rp <?= $data['gaji']; ?>

                                </div>

                            </div>

                        </div>

                        <?php
                    }
                } else {
                    ?>
                    <p>
                        Tidak ada lowongan tersedia
                    </p>
                    <?php
                }
                ?>
            </div>


            <!-- Load More Button -->
            <div class="load-more">
                <button class="btn-load-more">Muat Lebih Banyak</button>
            </div>
        </main>
    </div>

    <script>
        // Navigation functions
        function goToDashboard(e) {
            e.preventDefault();
            // Switch to dashboard artifact
        }

        function goToCariLowongan(e) {
            e.preventDefault();
            // Already on cari lowongan
        }

        function handleNavClick(e) {
            e.preventDefault();
            // Placeholder for future navigation
        }






        // Handle search
        document.querySelector('.btn-search').addEventListener('click', function (e) {
            e.preventDefault();
        });

        // Handle filter
        document.querySelector('.btn-filter').addEventListener('click', function (e) {
            e.preventDefault();
        });

        // Handle load more
        document.querySelector('.btn-load-more').addEventListener('click', function (e) {
            e.preventDefault();
        });

        // Handle icon buttons
        document.querySelectorAll('.icon-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
            });
        });
    </script>
</body>

</html>