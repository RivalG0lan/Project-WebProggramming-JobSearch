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

$result = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE id_user='$id_pelamar'
");

$user = mysqli_fetch_assoc($result);

$initials = strtoupper(substr($user['nama'] ?? 'U', 0, 2));

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

$total_lowongan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM lowongan WHERE status='aktif'"));

$lamaran_terbaru_query = mysqli_query($conn, "
    SELECT l.*, low.judul, u.nama AS perusahaan, low.kategori
    FROM lamaran l
    JOIN lowongan low ON l.id_lowongan = low.id_lowongan
    JOIN users u ON low.id_perusahaan = u.id_user
    WHERE l.id_pelamar = '$id_pelamar'
    ORDER BY l.tanggal_lamaran DESC
    LIMIT 3
");

$bidang_keahlian = $user['bidang_keahlian'] ?? '';
$user_skills_raw = $user['skills'] ?? '';

$where_rekomendasi = "WHERE low.status='aktif'";
$conditions = [];

// Match Bidang Keahlian
if (!empty($bidang_keahlian)) {
    $bidang_esc = mysqli_real_escape_string($conn, $bidang_keahlian);
    $conditions[] = "(low.judul LIKE '%$bidang_esc%' OR low.kategori LIKE '%$bidang_esc%')";
}

// Match Skills
if (!empty($user_skills_raw)) {
    $skills_arr = array_filter(array_map('trim', explode(',', $user_skills_raw)));
    $skill_conditions = [];
    foreach ($skills_arr as $skill) {
        if (!empty($skill)) {
            $skill_esc = mysqli_real_escape_string($conn, $skill);
            $skill_conditions[] = "low.skills_required LIKE '%$skill_esc%'";
        }
    }
    if (count($skill_conditions) > 0) {
        $conditions[] = "(" . implode(' OR ', $skill_conditions) . ")";
    }
}

// Combine conditions with OR (so either role matches OR skills match)
if (count($conditions) > 0) {
    $where_rekomendasi .= " AND (" . implode(' OR ', $conditions) . ")";
}

$rekomendasi_query = mysqli_query($conn, "
    SELECT low.*, u.nama AS perusahaan
    FROM lowongan low
    JOIN users u ON low.id_perusahaan = u.id_user
    $where_rekomendasi
    ORDER BY low.id_lowongan DESC
    LIMIT 3
");

$skill_dictionary = [
    'Frontend Developer'  => ['HTML', 'CSS', 'JavaScript', 'React', 'TypeScript', 'Tailwind', 'Git'],
    'Backend Developer'   => ['PHP', 'MySQL', 'Node.js', 'Python', 'REST API', 'Git', 'Docker'],
    'Fullstack Developer' => ['HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL', 'React', 'Node.js'],
    'UI/UX Designer'      => ['Figma', 'Adobe XD', 'Wireframing', 'Prototyping', 'UI Design', 'UX Research', 'CSS'],
    'Network Engineer'    => ['Cisco', 'Networking', 'Linux', 'TCP/IP', 'Firewall', 'Routing', 'Security'],
    'Data Scientist'      => ['Python', 'SQL', 'Machine Learning', 'Data Analysis', 'R', 'Pandas', 'Statistics'],
    'Mobile Developer'    => ['Flutter', 'React Native', 'Swift', 'Kotlin', 'Android', 'iOS', 'Java']
];

$target_skills = $skill_dictionary[$bidang_keahlian] ?? ['Communication', 'Teamwork', 'Problem Solving'];
$user_skills_arr = array_filter(array_map('trim', array_map('strtolower', explode(',', $user['skills'] ?? ''))));

$missing_skills = [];
foreach ($target_skills as $req) {
    if (!in_array(strtolower($req), $user_skills_arr)) {
        $missing_skills[] = $req;
    }
}
$top_missing_skills = array_slice($missing_skills, 0, 3);

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
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e5e7eb;
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

                <a href="pesan_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Pesan
                </a>

                <div class="nav-divider"></div>



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
                    <button class="btn-complete" onclick="window.location.href='profil_pelamar.php'">
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
                    <a href="lamaran_saya.php" class="btn-link" onclick="">
                        Lihat Semua
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="job-list">
                    <?php if (mysqli_num_rows($lamaran_terbaru_query) > 0): ?>
                        <?php while ($lamaran = mysqli_fetch_assoc($lamaran_terbaru_query)): 
                            $status_class = '';
                            if ($lamaran['status'] == 'terkirim') $status_class = 'match'; // Hijau
                            elseif ($lamaran['status'] == 'review' || $lamaran['status'] == 'interview') $status_class = 'sedang'; // Biru
                            elseif ($lamaran['status'] == 'ditolak') $status_class = 'ditolak'; // Merah
                        ?>
                        <div class="job-card">
                            <div class="job-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                            <div class="job-info">
                                <div class="job-title"><?= htmlspecialchars($lamaran['judul']) ?></div>
                                <div class="job-company"><?= htmlspecialchars($lamaran['perusahaan']) ?></div>
                            </div>
                            <div class="job-meta">
                                <span class="job-badge <?= $status_class ?>"><?= ucfirst(htmlspecialchars($lamaran['status'])) ?></span>
                                <span class="job-time"><?= date('d M Y', strtotime($lamaran['tanggal_lamaran'])) ?></span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="padding: 24px; text-align: center; color: #6b7280; font-size: 14px;">
                            Belum ada lamaran pekerjaan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grid: Rekomendasi & Skills -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <!-- Rekomendasi untuk Anda -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">Rekomendasi untuk Anda <?= !empty($bidang_keahlian) ? "(".htmlspecialchars($bidang_keahlian).")" : "" ?></h2>
                        <a href="cari_lowongan.php?rekomendasi=1" class="btn-link">
                            Lihat Semua
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    </div>

                    <div class="recommendation-grid">
                        <?php if (mysqli_num_rows($rekomendasi_query) > 0): ?>
                            <?php while ($rek = mysqli_fetch_assoc($rekomendasi_query)): ?>
                            <div class="recommendation-card" onclick="window.location.href='detail_lowongan.php?id=<?= $rek['id_lowongan'] ?>'">
                                <span class="rec-badge">Sesuai Profil</span>
                                <div class="rec-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                </div>
                                <div class="rec-title"><?= htmlspecialchars($rek['judul']) ?></div>
                                <div class="rec-company"><?= htmlspecialchars($rek['perusahaan']) ?></div>
                                <div class="rec-location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <?= htmlspecialchars($rek['lokasi']) ?>
                                </div>
                                <div class="rec-salary">Rp <?= number_format($rek['gaji'], 0, ',', '.') ?></div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="grid-column: span 3; text-align: center; color: #6b7280; font-size: 14px; padding: 24px;">
                                Belum ada rekomendasi yang sesuai dengan profil Anda saat ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Skill yang Dibutuhkan -->
                <div class="skills-card">
                    <div class="skills-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                        <h3 class="skills-title">Skill yang Perlu Ditingkatkan</h3>
                    </div>
                    <p class="skills-subtitle">Tingkatkan skill ini untuk profesi <?= htmlspecialchars($bidang_keahlian) ?></p>

                    <?php if (!empty($top_missing_skills)): ?>
                        <?php foreach ($top_missing_skills as $skill): ?>
                            <div class="skill-item">
                                <div class="skill-header">
                                    <span class="skill-name"><?= htmlspecialchars($skill) ?></span>
                                    <span class="skill-percentage">0% (Belum Dimiliki)</span>
                                </div>
                                <div class="skill-bar">
                                    <div class="skill-progress" style="width: 0%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 16px; text-align: center; color: #059669; font-weight: 500; font-size: 14px; background: #dcfce7; border-radius: 8px;">
                            🎉 Luar Biasa! Anda sudah menguasai semua skill dasar untuk bidang ini.
                        </div>
                    <?php endif; ?>

                    <button class="btn-analyze" onclick="window.location.href='skill_gap_analyzer.php'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6"></path>
                            <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                            <path d="M1 12h6m6 0h6"></path>
                            <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                        </svg>
                        Analisa Skill Gap Anda
                    </button>
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