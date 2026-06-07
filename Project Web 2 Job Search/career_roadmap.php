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
   CAREER ROADMAP LOGIC
   ========================= */
$bidang_keahlian = $user['bidang_keahlian'] ?? '';
$user_skills_raw = strtolower($user['skills'] ?? '');

$roadmaps = [
    'Frontend Developer' => [
        'desc' => 'Fokus pada antarmuka pengguna, performa web, dan pengalaman interaktif.',
        'steps' => [
            ['title' => 'Internet & Dasar Web', 'desc' => 'Memahami cara kerja HTTP, DNS, Hosting, HTML, dan CSS dasar.', 'skills' => ['html', 'css']],
            ['title' => 'JavaScript Lanjut', 'desc' => 'Memahami ES6+, DOM Manipulation, Fetch API, dan Async/Await.', 'skills' => ['javascript', 'js']],
            ['title' => 'Framework Modern', 'desc' => 'Menguasai React, Vue, atau Angular beserta ekosistemnya.', 'skills' => ['react', 'vue', 'angular']],
            ['title' => 'Advanced Topics', 'desc' => 'Mempelajari TypeScript, Next.js, SEO, dan Web Vitals.', 'skills' => ['typescript', 'next.js', 'ssr']]
        ]
    ],
    'Backend Developer' => [
        'desc' => 'Membangun logika bisnis, manajemen database, dan arsitektur server yang skalabel.',
        'steps' => [
            ['title' => 'Bahasa Pemrograman', 'desc' => 'Menguasai PHP, Python, Java, atau Node.js.', 'skills' => ['php', 'python', 'java', 'node.js', 'golang']],
            ['title' => 'Database Management', 'desc' => 'Memahami Relational (MySQL/PostgreSQL) dan NoSQL.', 'skills' => ['mysql', 'postgresql', 'sql', 'mongodb']],
            ['title' => 'API & Arsitektur', 'desc' => 'Merancang RESTful API atau GraphQL dengan best practices keamanan.', 'skills' => ['api', 'rest api', 'graphql']],
            ['title' => 'DevOps & Deployment', 'desc' => 'Mempelajari Docker, CI/CD, AWS/GCP, dan Server Linux.', 'skills' => ['docker', 'ci/cd', 'aws', 'linux']]
        ]
    ],
    'Fullstack Developer' => [
        'desc' => 'Menguasai baik sisi Frontend maupun Backend secara menyeluruh.',
        'steps' => [
            ['title' => 'Frontend Dasar', 'desc' => 'Menguasai HTML, CSS, JavaScript, dan UI framework.', 'skills' => ['html', 'css', 'javascript']],
            ['title' => 'Backend & Database', 'desc' => 'Membuat server dengan Node.js/PHP dan menghubungkan database.', 'skills' => ['php', 'node.js', 'mysql', 'sql']],
            ['title' => 'Version Control', 'desc' => 'Menguasai Git dan alur kerja kolaborasi.', 'skills' => ['git', 'github']],
            ['title' => 'Deployment', 'desc' => 'Mendeploy aplikasi fullstack ke server atau cloud (VPS/Vercel).', 'skills' => ['docker', 'aws', 'vercel', 'deployment']]
        ]
    ],
    'UI/UX Designer' => [
        'desc' => 'Mendesain pengalaman dan antarmuka pengguna yang estetis dan fungsional.',
        'steps' => [
            ['title' => 'Prinsip Desain', 'desc' => 'Memahami teori warna, tipografi, dan layouting.', 'skills' => ['ui design', 'typography']],
            ['title' => 'UX Research', 'desc' => 'Melakukan riset pengguna, wireframing, dan user flow.', 'skills' => ['ux research', 'wireframing']],
            ['title' => 'Design Tools', 'desc' => 'Menguasai Figma, Adobe XD, atau Sketch.', 'skills' => ['figma', 'adobe xd', 'sketch']],
            ['title' => 'Prototyping & Testing', 'desc' => 'Membuat prototipe interaktif dan melakukan usability testing.', 'skills' => ['prototyping', 'usability testing']]
        ]
    ],
    'Network Engineer' => [
        'desc' => 'Merancang, mengimplementasikan, dan mengelola jaringan komputer.',
        'steps' => [
            ['title' => 'Dasar Jaringan', 'desc' => 'Memahami topologi, model OSI, dan TCP/IP.', 'skills' => ['networking', 'tcp/ip']],
            ['title' => 'Routing & Switching', 'desc' => 'Mengonfigurasi router dan switch (misal: Cisco, MikroTik).', 'skills' => ['cisco', 'mikrotik', 'routing', 'switching']],
            ['title' => 'Keamanan Jaringan', 'desc' => 'Mengatur Firewall, VPN, dan sistem deteksi intrusi.', 'skills' => ['firewall', 'security', 'vpn']],
            ['title' => 'Manajemen Server', 'desc' => 'Mengelola server Linux/Windows untuk infrastruktur jaringan.', 'skills' => ['linux', 'windows server']]
        ]
    ],
    'Data Scientist' => [
        'desc' => 'Mengolah data mentah menjadi insight berharga menggunakan statistik dan Machine Learning.',
        'steps' => [
            ['title' => 'Statistik & Math', 'desc' => 'Memahami probabilitas, aljabar linier, dan statistika.', 'skills' => ['statistics', 'math']],
            ['title' => 'Bahasa Pemrograman', 'desc' => 'Menguasai Python atau R beserta library manipulasi data.', 'skills' => ['python', 'r', 'pandas', 'numpy']],
            ['title' => 'Data Visualization', 'desc' => 'Membuat visualisasi data menggunakan Tableau, PowerBI, atau Matplotlib.', 'skills' => ['data visualization', 'tableau', 'powerbi']],
            ['title' => 'Machine Learning', 'desc' => 'Menerapkan algoritma ML, klasifikasi, dan regresi.', 'skills' => ['machine learning', 'ml', 'scikit-learn']]
        ]
    ],
    'Mobile Developer' => [
        'desc' => 'Membangun aplikasi untuk platform mobile (Android/iOS).',
        'steps' => [
            ['title' => 'Fundamental Pemrograman', 'desc' => 'Menguasai OOP dan konsep dasar pemrograman.', 'skills' => ['oop', 'java', 'dart', 'kotlin']],
            ['title' => 'Pemilihan Platform', 'desc' => 'Memilih antara Native (Kotlin/Swift) atau Cross-Platform (Flutter/React Native).', 'skills' => ['flutter', 'react native', 'android', 'ios']],
            ['title' => 'UI & API Integration', 'desc' => 'Membangun UI yang responsif dan mengkonsumsi REST API.', 'skills' => ['api', 'ui design', 'rest api']],
            ['title' => 'App Store Deployment', 'desc' => 'Proses rilis ke Google Play Store atau Apple App Store.', 'skills' => ['deployment', 'play store', 'app store']]
        ]
    ]
];

$selected_roadmap = $roadmaps[$bidang_keahlian] ?? null;

// Tentukan status tiap langkah roadmap berdasarkan skill user
if ($selected_roadmap) {
    $has_current = false;
    foreach ($selected_roadmap['steps'] as &$step) {
        $step_completed = false;
        // Cek apakah minimal 1 skill dari step ini dimiliki user
        foreach ($step['skills'] as $req_skill) {
            if (strpos($user_skills_raw, $req_skill) !== false) {
                $step_completed = true;
                break;
            }
        }

        if ($step_completed) {
            $step['status'] = 'completed';
        } else {
            if (!$has_current) {
                $step['status'] = 'current';
                $has_current = true;
            } else {
                $step['status'] = 'locked';
            }
        }
    }
    unset($step);
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Career Roadmap - Lokerin</title>
    <link rel="icon" type="image/png" href="assets/icon_head_lokerin.png" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background-color: #f9fafb; color: #111827; line-height: 1.6; }
        .layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background: white; border-right: 1px solid #e5e7eb; padding: 24px 16px; display: flex; flex-direction: column; position: fixed; height: 100vh; overflow-y: auto; }
        .logo { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; padding: 0 8px; }
        .logo-img { width: 27px; height: 27px; object-fit: contain; }
        .logo-text { font-size: 18px; font-weight: 700; color: #111827; }

        .user-profile { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f9fafb; border-radius: 12px; margin-bottom: 24px; }
        .sidebar-avatar { width: 40px; height: 40px; background: #0d9488; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 14px; overflow: hidden; flex-shrink: 0; }
        .sidebar-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 14px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-email { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .career-score { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 24px; }
        .career-score-label { font-size: 12px; color: #6b7280; }
        .career-score-value { font-size: 14px; font-weight: 600; color: #0d9488; }

        nav { flex: 1; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: #6b7280; text-decoration: none; border-radius: 8px; margin-bottom: 4px; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .nav-item:hover { background: #f9fafb; color: #111827; }
        .nav-item.active { background: #e0f2f1; color: #0d9488; }
        .nav-divider { height: 1px; background: #e5e7eb; margin: 16px 0; }
        .nav-item-logout { color: #ef4444; }
        .nav-item-logout:hover { background: #fef2f2; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 32px 48px; max-width: 1200px; }

        .page-header { margin-bottom: 40px; }
        .page-title { font-size: 32px; font-weight: 800; color: #111827; margin-bottom: 12px; letter-spacing: -0.02em; }
        .page-subtitle { font-size: 16px; color: #6b7280; max-width: 600px; line-height: 1.6; }

        /* Roadmap container */
        .roadmap-container { position: relative; margin-top: 40px; padding-left: 24px; }
        .roadmap-line { position: absolute; top: 0; bottom: 0; left: 35px; width: 3px; background: #e5e7eb; border-radius: 3px; }

        .roadmap-step { position: relative; padding-left: 60px; margin-bottom: 40px; }
        .roadmap-step:last-child { margin-bottom: 0; }

        .step-indicator {
            position: absolute; left: -1px; top: 0; width: 26px; height: 26px; border-radius: 50%;
            background: white; border: 3px solid #e5e7eb; display: flex; align-items: center; justify-content: center;
            transition: all 0.3s; z-index: 2;
        }

        .step-content {
            background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: all 0.3s; position: relative;
        }
        .step-content::before {
            content: ''; position: absolute; top: 12px; left: -8px; width: 16px; height: 16px;
            background: white; border-left: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;
            transform: rotate(45deg);
        }

        .step-title { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 8px; }
        .step-desc { font-size: 15px; color: #6b7280; margin-bottom: 16px; line-height: 1.5; }
        .step-skills { display: flex; gap: 8px; flex-wrap: wrap; }
        .skill-tag { background: #f3f4f6; color: #4b5563; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        /* States */
        .roadmap-step.completed .step-indicator { background: #0d9488; border-color: #0d9488; color: white; }
        .roadmap-step.completed .step-content { border-color: #0d9488; background: #f0fdfa; }
        .roadmap-step.completed .step-content::before { background: #f0fdfa; border-color: #0d9488; }
        .roadmap-step.completed .skill-tag { background: #ccfbf1; color: #0f766e; }

        .roadmap-step.current .step-indicator { border-color: #0d9488; border-width: 4px; box-shadow: 0 0 0 4px #ccfbf1; }
        .roadmap-step.current .step-content { border-color: #0d9488; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.1); }
        .roadmap-step.current .step-content::before { border-color: #0d9488; }

        .roadmap-step.locked .step-indicator { background: #f3f4f6; }
        .roadmap-step.locked .step-content { opacity: 0.6; }

        /* Empty State */
        .empty-state { background: white; border: 2px dashed #e5e7eb; border-radius: 16px; padding: 60px 24px; text-align: center; }
        .empty-state h3 { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
        .empty-state p { color: #6b7280; margin-bottom: 24px; }
        .btn-primary { display: inline-flex; align-items: center; gap: 8px; background: #0d9488; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .btn-primary:hover { background: #0f766e; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 24px; }
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
                <a href="career_roadmap.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
                    Career Roadmap
                </a>
                <a href="skill_gap_analyzer.php" class="nav-item">
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
            <?php if (empty($bidang_keahlian) || !$selected_roadmap): ?>
                <div class="page-header">
                    <h1 class="page-title">Career Roadmap</h1>
                    <p class="page-subtitle">Petunjuk langkah demi langkah menuju karir impian Anda.</p>
                </div>
                <div class="empty-state">
                    <h3>Pilih Bidang Keahlian Anda</h3>
                    <p>Lengkapi profil Anda dengan memilih Bidang Keahlian / Posisi Diminati untuk melihat roadmap karir yang sesuai.</p>
                    <a href="profil_pelamar.php" class="btn-primary">Update Profil Sekarang</a>
                </div>
            <?php else: ?>
                <div class="page-header">
                    <h1 class="page-title">Roadmap: <?= htmlspecialchars($bidang_keahlian) ?></h1>
                    <p class="page-subtitle"><?= htmlspecialchars($selected_roadmap['desc']) ?></p>
                </div>

                <div class="roadmap-container">
                    <div class="roadmap-line"></div>
                    
                    <?php foreach ($selected_roadmap['steps'] as $index => $step): ?>
                        <div class="roadmap-step <?= $step['status'] ?>">
                            <div class="step-indicator">
                                <?php if ($step['status'] == 'completed'): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <?php elseif ($step['status'] == 'current'): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <?php else: ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <?php endif; ?>
                            </div>
                            <div class="step-content">
                                <h3 class="step-title">Langkah <?= $index + 1 ?>: <?= htmlspecialchars($step['title']) ?></h3>
                                <p class="step-desc"><?= htmlspecialchars($step['desc']) ?></p>
                                <div class="step-skills">
                                    <?php foreach ($step['skills'] as $skill): ?>
                                        <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>