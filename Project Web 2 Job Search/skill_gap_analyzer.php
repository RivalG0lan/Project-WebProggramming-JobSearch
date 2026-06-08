<?php
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'pelamar') {
    header("Location: login_pelamar.php");
    exit;
}

include 'config/koneksi.php';

$id_pelamar = (int) $_SESSION['id_user'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id_user=$id_pelamar");
$user = mysqli_fetch_assoc($result);

$initials = strtoupper(substr($user['nama'] ?? 'U', 0, 2));

/* =========================
   HITUNG KELENGKAPAN PROFIL
   ========================= */
$fields_cek = ['nama', 'telepon', 'lokasi', 'bio', 'pendidikan', 'pengalaman', 'skills', 'foto_profil', 'cv_path'];
$isi = 0;
foreach ($fields_cek as $field) {
    if (!empty($user[$field])) $isi++;
}
$kelengkapan = round(($isi / count($fields_cek)) * 100);

/* =========================
   SKILL GAP ANALYZER LOGIC
   ========================= */
$skill_dictionary = [
    'Frontend Developer'  => ['HTML', 'CSS', 'JavaScript', 'React', 'TypeScript', 'Tailwind', 'Git'],
    'Backend Developer'   => ['PHP', 'MySQL', 'Node.js', 'Python', 'REST API', 'Git', 'Docker'],
    'Fullstack Developer' => ['HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL', 'React', 'Node.js'],
    'UI/UX Designer'      => ['Figma', 'Adobe XD', 'Wireframing', 'Prototyping', 'UI Design', 'UX Research', 'CSS'],
    'Network Engineer'    => ['Cisco', 'Networking', 'Linux', 'TCP/IP', 'Firewall', 'Routing', 'Security'],
    'Data Scientist'      => ['Python', 'SQL', 'Machine Learning', 'Data Analysis', 'R', 'Pandas', 'Statistics'],
    'Mobile Developer'    => ['Flutter', 'React Native', 'Swift', 'Kotlin', 'Android', 'iOS', 'Java']
];

$bidang_keahlian = $user['bidang_keahlian'] ?? '';
$user_skills_raw = $user['skills'] ?? '';

$target_skills = $skill_dictionary[$bidang_keahlian] ?? [];
$user_skills_arr = array_filter(array_map('trim', array_map('strtolower', explode(',', $user_skills_raw))));

$matched_skills = [];
$missing_skills = [];

foreach ($target_skills as $req) {
    if (in_array(strtolower($req), $user_skills_arr)) {
        $matched_skills[] = $req;
    } else {
        $missing_skills[] = $req;
    }
}

$total_req = count($target_skills);
$match_rate = $total_req > 0 ? round((count($matched_skills) / $total_req) * 100) : 0;
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Skill Gap Analyzer - Lokerin</title>
    <link rel="icon" type="image/png" href="assets/icon_head_lokerin.png" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f9fafb; color: #111827; line-height: 1.6;
        }
        .layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 260px; background: white; border-right: 1px solid #e5e7eb;
            padding: 24px 16px; display: flex; flex-direction: column;
            position: fixed; height: 100vh; overflow-y: auto;
        }
        .logo { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; padding: 0 8px; }
        .logo-img { width: 27px; height: 27px; object-fit: contain; }
        .logo-text { font-size: 18px; font-weight: 700; color: #0D9488; }

        .user-profile {
            display: flex; align-items: center; gap: 12px; padding: 12px;
            background: #f9fafb; border-radius: 12px; margin-bottom: 24px;
        }
        .sidebar-avatar {
            width: 40px; height: 40px; background: #0d9488; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 14px; overflow: hidden; flex-shrink: 0;
        }
        .sidebar-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 14px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-email { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .career-score { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 24px; }
        .career-score-label { font-size: 12px; color: #6b7280; }
        .career-score-value { font-size: 14px; font-weight: 600; color: #0d9488; }

        nav { flex: 1; }
        .nav-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 12px;
            color: #6b7280; text-decoration: none; border-radius: 8px; margin-bottom: 4px;
            font-size: 14px; font-weight: 500; transition: all 0.2s;
        }
        .nav-item:hover { background: #f9fafb; color: #111827; }
        .nav-item.active { background: #e0f2f1; color: #0d9488; }
        .nav-divider { height: 1px; background: #e5e7eb; margin: 16px 0; }
        .nav-item-logout { color: #ef4444; }
        .nav-item-logout:hover { background: #fef2f2; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 32px 48px; max-width: 1200px; }

        .page-title { font-size: 32px; font-weight: 700; color: #111827; margin-bottom: 8px; }
        .page-subtitle { font-size: 16px; color: #6b7280; margin-bottom: 32px; }

        /* Target Position Card */
        .target-position-card {
            background: white; border-radius: 16px; padding: 24px; border: 2px solid #e5e7eb;
            margin-bottom: 32px; display: flex; align-items: center; gap: 20px;
        }
        .target-position-icon {
            width: 64px; height: 64px; background: #e0f2f1; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; color: #0d9488; flex-shrink: 0;
        }
        .target-position-content { flex: 1; }
        .target-position-label { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
        .target-position-title { font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .target-position-meta { font-size: 14px; color: #6b7280; }

        .target-position-match { display: flex; align-items: center; gap: 16px; }
        .match-label { font-size: 14px; color: #6b7280; }
        .match-value { font-size: 36px; font-weight: 700; color: <?= $match_rate >= 70 ? '#0d9488' : ($match_rate >= 40 ? '#f59e0b' : '#ef4444') ?>; }

        .match-progress-wrapper { flex: 1; max-width: 400px; }
        .match-progress-bar { height: 12px; background: #e5e7eb; border-radius: 6px; overflow: hidden; }
        .match-progress-fill { height: 100%; border-radius: 6px; transition: width 0.3s; background: <?= $match_rate >= 70 ? '#0d9488' : ($match_rate >= 40 ? '#f59e0b' : '#ef4444') ?>; }

        /* Skills Grid */
        .skills-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 48px; }
        .skills-card { background: white; border-radius: 16px; padding: 24px; border: 2px solid #e5e7eb; }
        .skills-card.needed { border-color: #fef3c7; }
        .skills-card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .skills-card-icon { width: 24px; height: 24px; color: #0d9488; }
        .skills-card.needed .skills-card-icon { color: #f59e0b; }
        .skills-card-title { font-size: 18px; font-weight: 700; color: #111827; }

        .skill-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        .skill-item:last-child { border-bottom: none; }
        .skill-item-left { display: flex; align-items: center; gap: 12px; }
        .skill-item-icon { width: 20px; height: 20px; color: #0d9488; }
        .skill-item.needed .skill-item-icon { color: #f59e0b; }
        .skill-item-name { font-size: 15px; font-weight: 500; color: #111827; }
        
        .skill-match-indicator { font-size: 12px; color: #0d9488; font-weight: 600; background: #e0f2f1; padding: 4px 10px; border-radius: 6px; }
        .priority-badge { font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; background: #fef2f2; color: #ef4444; }

        /* Empty State */
        .empty-state { background: white; border: 2px dashed #e5e7eb; border-radius: 16px; padding: 60px 24px; text-align: center; }
        .empty-state h3 { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
        .empty-state p { color: #6b7280; margin-bottom: 24px; }
        .btn-primary { display: inline-flex; align-items: center; gap: 8px; background: #0d9488; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .btn-primary:hover { background: #0f766e; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 24px; }
            .skills-grid { grid-template-columns: 1fr; }
            .target-position-card { flex-direction: column; text-align: center; }
            .target-position-match { flex-direction: column; }
        }
    </style>
</head>

<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img class="logo-img" src="assets/logo_lokerin.png" alt="L" />
                <span class="logo-text">LokerIn</span>
            </div>

            <div class="user-profile">
                <div class="sidebar-avatar">
                    <?php if (!empty($user['foto_profil']) && file_exists('uploads/foto_profil/' . $user['foto_profil'])): ?>
                        <img src="uploads/foto_profil/<?= htmlspecialchars($user['foto_profil']) ?>" alt="foto">
                    <?php else: ?>
                        <?= $initials ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['nama'] ?? '-') ?></div>
                    <div class="user-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                </div>
            </div>

            <div class="career-score">
                <span class="career-score-label">Kelengkapan Profil</span>
                <span class="career-score-value"><?= $kelengkapan ?>%</span>
            </div>

            <nav>
                <a href="dashboard_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
                <a href="cari_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                    Cari Lowongan
                </a>
                <a href="lamaran_saya.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Lamaran Saya
                </a>
                <a href="profil_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profil
                </a>
                <a href="career_roadmap.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                    Career Roadmap
                </a>
                <a href="skill_gap_analyzer.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v6m0 6v6m-9-9h6m6 0h6"></path></svg>
                    Skill Gap Analyzer
                </a>
                <a href="pesan_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Pesan
                </a>
                <div class="nav-divider"></div>
                <a href="pengaturan_pelamar.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v6m0 6v6"></path><path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path><path d="M1 12h6m6 0h6"></path><path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path></svg>
                    Pengaturan
                </a>
                <a href="logout.php" class="nav-item nav-item-logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Keluar
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 class="page-title">Skill Gap Analyzer</h1>
            <p class="page-subtitle">Bandingkan skill Anda dengan kebutuhan industri</p>

            <?php if (empty($bidang_keahlian)): ?>
                <div class="empty-state">
                    <h3>Pilih Bidang Keahlian Anda</h3>
                    <p>Lengkapi profil Anda dengan memilih Bidang Keahlian / Posisi Diminati untuk melihat analisis skill gap.</p>
                    <a href="profil_pelamar.php" class="btn-primary">Update Profil Sekarang</a>
                </div>
            <?php else: ?>
                <!-- Target Position Card -->
                <div class="target-position-card">
                    <div class="target-position-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </div>
                    <div class="target-position-content">
                        <div class="target-position-label">Target Posisi</div>
                        <div class="target-position-title"><?= htmlspecialchars($bidang_keahlian) ?></div>
                        <div class="target-position-meta">Berdasarkan profil Anda</div>
                    </div>
                    <div class="target-position-match">
                        <div class="match-label">Kecocokan Saat Ini</div>
                        <div class="match-value"><?= $match_rate ?>%</div>
                    </div>
                    <div class="match-progress-wrapper">
                        <div class="match-progress-bar">
                            <div class="match-progress-fill" style="width: <?= $match_rate ?>%"></div>
                        </div>
                    </div>
                </div>

                <!-- Skills Grid -->
                <div class="skills-grid">
                    <!-- Skills yang Sudah Cocok -->
                    <div class="skills-card">
                        <div class="skills-card-header">
                            <svg class="skills-card-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <h2 class="skills-card-title">Skills yang Sudah Cocok</h2>
                        </div>
                        <?php if (empty($matched_skills)): ?>
                            <p style="color: #6b7280; font-size: 14px;">Belum ada skill yang sesuai dengan target posisi ini.</p>
                        <?php else: ?>
                            <?php foreach ($matched_skills as $skill): ?>
                            <div class="skill-item">
                                <div class="skill-item-left">
                                    <svg class="skill-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span class="skill-item-name"><?= htmlspecialchars($skill) ?></span>
                                </div>
                                <span class="skill-match-indicator">✓ Sesuai</span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Skills yang Perlu Ditingkatkan -->
                    <div class="skills-card needed">
                        <div class="skills-card-header">
                            <svg class="skills-card-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <h2 class="skills-card-title">Skills yang Perlu Ditingkatkan</h2>
                        </div>
                        <?php if (empty($missing_skills)): ?>
                            <p style="color: #6b7280; font-size: 14px;">Hebat! Skill Anda sudah memenuhi kriteria utama.</p>
                        <?php else: ?>
                            <?php foreach ($missing_skills as $skill): ?>
                            <div class="skill-item needed">
                                <div class="skill-item-left">
                                    <svg class="skill-item-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <span class="skill-item-name"><?= htmlspecialchars($skill) ?></span>
                                </div>
                                <span class="priority-badge">Belum Ada</span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>