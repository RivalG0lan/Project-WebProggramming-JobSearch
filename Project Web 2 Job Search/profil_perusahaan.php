<?php
include 'config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login_perusahaan.php");
    exit;
}

if ($_SESSION['role'] != 'perusahaan') {
    header("Location: login_perusahaan.php");
    exit;
}

include 'employer_score.php';

$id_perusahaan = (int) $_SESSION['id_user'];
$pesan_sukses = '';
$pesan_error = '';

/* ══════════════════════════════════════════════════════
   HANDLE POST
   ══════════════════════════════════════════════════════ */

/* ── Upload Logo Perusahaan ─────────────────────────── */
if (isset($_FILES['foto_perusahaan']) && $_FILES['foto_perusahaan']['error'] === UPLOAD_ERR_OK) {
    $allowed_img = ['image/jpeg', 'image/png', 'image/webp'];
    $ftype = $_FILES['foto_perusahaan']['type'];
    $fsize = $_FILES['foto_perusahaan']['size'];

    if (!in_array($ftype, $allowed_img)) {
        $pesan_error = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
    } elseif ($fsize > 2 * 1024 * 1024) {
        $pesan_error = 'Ukuran gambar maksimal 2 MB.';
    } else {
        // Ambil foto lama
        $q_old = mysqli_query($conn, "SELECT foto_perusahaan FROM users WHERE id_user='$id_perusahaan'");
        $old = mysqli_fetch_assoc($q_old);

        $ext = pathinfo($_FILES['foto_perusahaan']['name'], PATHINFO_EXTENSION);
        $filename = 'perusahaan_' . $id_perusahaan . '_' . time() . '.' . $ext;
        $dest = 'uploads/foto_perusahaan/' . $filename;

        if (!is_dir('uploads/foto_perusahaan')) {
            mkdir('uploads/foto_perusahaan', 0755, true);
        }

        if (move_uploaded_file($_FILES['foto_perusahaan']['tmp_name'], $dest)) {
            // Hapus file lama
            if (!empty($old['foto_perusahaan'])) {
                $old_file = 'uploads/foto_perusahaan/' . $old['foto_perusahaan'];
                if (file_exists($old_file))
                    unlink($old_file);
            }
            $fn_esc = mysqli_real_escape_string($conn, $filename);
            mysqli_query($conn, "UPDATE users SET foto_perusahaan='$fn_esc' WHERE id_user='$id_perusahaan'");
            header("Location: profil_perusahaan.php?logo=ok");
            exit;
        } else {
            $pesan_error = 'Gagal mengupload logo.';
        }
    }
}

/* ── Hapus Logo ─────────────────────────────────────── */
if (isset($_POST['hapus_foto'])) {
    $q = mysqli_query($conn, "SELECT foto_perusahaan FROM users WHERE id_user='$id_perusahaan'");
    $row = mysqli_fetch_assoc($q);
    if (!empty($row['foto_perusahaan'])) {
        $f = 'uploads/foto_perusahaan/' . $row['foto_perusahaan'];
        if (file_exists($f))
            unlink($f);
        mysqli_query($conn, "UPDATE users SET foto_perusahaan=NULL WHERE id_user='$id_perusahaan'");
    }
    header("Location: profil_perusahaan.php?hapus_logo=ok");
    exit;
}

/* ── Simpan Profil Teks ─────────────────────────────── */
if (isset($_POST['simpan_profil'])) {
    $nama = trim($_POST['nama']);
    $telepon = trim($_POST['telepon']);
    $website = trim($_POST['website']);
    $alamat = trim($_POST['alamat']);
    $deskripsi = trim($_POST['deskripsi']);
    $industri = trim($_POST['industri']);
    $ukuran_perusahaan = trim($_POST['ukuran_perusahaan']);
    $tahun_berdiri = trim($_POST['tahun_berdiri']);
    $linkedin = trim($_POST['linkedin']);

    $stmt = $conn->prepare("
        UPDATE users SET
            nama              = ?,
            telepon           = ?,
            website           = ?,
            alamat            = ?,
            deskripsi         = ?,
            industri          = ?,
            ukuran_perusahaan = ?,
            tahun_berdiri     = ?,
            linkedin          = ?
        WHERE id_user = ?
    ");

    $tahun = $tahun_berdiri ?: null;
    $stmt->bind_param(
        "sssssssssi",
        $nama,
        $telepon,
        $website,
        $alamat,
        $deskripsi,
        $industri,
        $ukuran_perusahaan,
        $tahun,
        $linkedin,
        $id_perusahaan
    );

    if ($stmt->execute()) {
        $_SESSION['nama'] = $nama;
        $_SESSION['telepon'] = $telepon;
        $_SESSION['website'] = $website;
        $_SESSION['alamat'] = $alamat;
        header("Location: profil_perusahaan.php?simpan=ok");
        exit;
    } else {
        $pesan_error = 'Gagal menyimpan profil. Coba lagi.';
    }
    $stmt->close();
}

/* ══════════════════════════════════════════════════════
   AMBIL DATA TERKINI
   ══════════════════════════════════════════════════════ */
$result = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_perusahaan'");
$user = mysqli_fetch_assoc($result);

// Hitung employer score dari sini juga (atau pakai $employer_score dari include)
$fields_score = ['nama', 'email', 'telepon', 'alamat', 'deskripsi', 'foto_perusahaan'];
$isi_score = 0;
foreach ($fields_score as $f) {
    if (!empty($user[$f]))
        $isi_score++;
}
$profil_pct = round(($isi_score / count($fields_score)) * 100);

/* Flash messages */
if (isset($_GET['simpan']) && $_GET['simpan'] === 'ok')
    $pesan_sukses = 'Profil perusahaan berhasil disimpan!';
if (isset($_GET['logo']) && $_GET['logo'] === 'ok')
    $pesan_sukses = 'Logo perusahaan berhasil diupload!';
if (isset($_GET['hapus_logo']) && $_GET['hapus_logo'] === 'ok')
    $pesan_sukses = 'Logo berhasil dihapus!';

$inisial = strtoupper(substr($user['nama'] ?? 'P', 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Perusahaan - LokerIn</title>
    <link rel="icon" type="image/png" href="assets/icon_head_lokerin.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f9fafb; color: #111827; line-height: 1.6;
        }
        .layout { display: flex; min-height: 100vh; }

        /* ── Sidebar ───────────────────────────────────── */
        .sidebar {
            width: 260px; background: white; border-right: 1px solid #e5e7eb;
            padding: 24px 16px; display: flex; flex-direction: column;
            position: fixed; height: 100vh; overflow-y: auto; z-index: 50;
        }
        .logo { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; padding: 0 8px; }
        .logo-img { width: 27px; height: 27px; object-fit: contain; }
        .logo-text { font-size: 18px; font-weight: 700; color: #0D9488; }

        .company-profile {
            display: flex; align-items: center; gap: 12px; padding: 12px;
            background: #f9fafb; border-radius: 12px; margin-bottom: 16px;
        }
        .company-avatar {
            width: 40px; height: 40px; background: #f59e0b; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 16px; overflow: hidden; flex-shrink: 0;
        }
        .company-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
        .company-info { flex: 1; overflow: hidden; }
        .company-name { font-size: 14px; font-weight: 600; color: #111827; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; }
        .company-industry { font-size: 12px; color: #6b7280; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; }

        .employer-score { padding: 8px 12px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 24px; }
        .employer-score-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .employer-score-label { font-size: 12px; color: #6b7280; }
        .employer-score-value { font-size: 14px; font-weight: 600; color: #f59e0b; }
        .employer-score-bar { height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden; }
        .employer-score-fill { height: 100%; background: #f59e0b; border-radius: 2px; }

        nav { flex: 1; }
        .nav-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 12px;
            color: #6b7280; text-decoration: none; border-radius: 8px; margin-bottom: 4px;
            font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .nav-item:hover { background: #f9fafb; color: #111827; }
        .nav-item.active { background: #e0f2f1; color: #0d9488; }
        .nav-divider { height: 1px; background: #e5e7eb; margin: 16px 0; }
        .nav-item-logout { color: #ef4444; }
        .nav-item-logout:hover { background: #fef2f2; }

        /* ── Main Content ──────────────────────────────── */
        .main-content { flex: 1; margin-left: 260px; padding: 24px 32px; }

        .header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 32px;
        }
        .header-left h1 { font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .header-left p { font-size: 14px; color: #6b7280; }
        .header-actions { display: flex; align-items: center; gap: 12px; }

        .btn-primary {
            background: #0d9488; color: white; padding: 10px 20px; border-radius: 8px;
            border: none; font-size: 14px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 8px; transition: all 0.2s; text-decoration: none;
        }
        .btn-primary:hover { background: #0f766e; }
        .btn-secondary {
            background: white; color: #6b7280; padding: 10px 20px; border-radius: 8px;
            border: 1px solid #e5e7eb; font-size: 14px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 8px; transition: all 0.2s; text-decoration: none;
        }
        .btn-secondary:hover { background: #f9fafb; }

        /* ── Alert ─────────────────────────────────────── */
        .alert {
            padding: 14px 18px; border-radius: 10px; margin-bottom: 24px;
            font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* ── Profile Cover Card ────────────────────────── */
        .profile-header {
            background: white; border: 1px solid #e5e7eb; border-radius: 12px;
            padding: 32px; margin-bottom: 24px; position: relative; overflow: hidden;
        }
        .profile-cover {
            position: absolute; top: 0; left: 0; right: 0; height: 120px;
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
        }
        .profile-header-content {
            position: relative; display: flex; gap: 24px; padding-top: 40px;
        }
        .profile-avatar-large {
            width: 120px; height: 120px; background: #f59e0b; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 48px;
            border: 4px solid white; box-shadow: 0 4px 6px -1px rgb(0 0 0/0.1);
            overflow: hidden; flex-shrink: 0; cursor: pointer; transition: transform .2s;
        }
        .profile-avatar-large:hover { transform: scale(1.04); }
        .profile-avatar-large img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
        .profile-header-info { flex: 1; padding-top: 40px; }
        .profile-header-name { font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .profile-header-industry { font-size: 14px; color: #6b7280; margin-bottom: 16px; }
        .profile-header-meta { display: flex; gap: 24px; flex-wrap: wrap; }
        .profile-meta-item { display: flex; align-items: center; gap: 6px; font-size: 14px; color: #6b7280; }

        /* Upload logo btn */
        .upload-logo-btn {
            display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,.15);
            color: white; border: 1px solid rgba(255,255,255,.4); padding: 6px 14px;
            border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;
            transition: all .2s; margin-top: 8px; backdrop-filter: blur(4px);
        }
        .upload-logo-btn:hover { background: rgba(255,255,255,.25); }
        .upload-logo-input { display: none; }

        /* ── Content Grid ──────────────────────────────── */
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }

        /* ── Card ──────────────────────────────────────── */
        .card {
            background: white; border: 1px solid #e5e7eb; border-radius: 12px;
            padding: 24px; margin-bottom: 24px;
        }
        .card:last-child { margin-bottom: 0; }
        .card-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 12px;
        }
        .card-title { font-size: 18px; font-weight: 700; color: #111827; }

        /* ── Form ──────────────────────────────────────── */
        .form-section { margin-bottom: 24px; }
        .form-section-title {
            font-size: 13px; font-weight: 700; color: #0d9488; margin-bottom: 16px;
            padding-bottom: 8px; border-bottom: 2px solid #e0f2f1;
            display: flex; align-items: center; gap: 8px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group:last-child { margin-bottom: 0; }
        .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .form-input, .form-select {
            width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px;
            font-size: 14px; outline: none; background-color: #fff; transition: all 0.2s; color: #374151;
            font-family: inherit;
        }
        .form-input:focus, .form-select:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
        .form-textarea {
            width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px;
            font-size: 14px; outline: none; transition: all 0.2s; resize: vertical;
            min-height: 120px; font-family: inherit; color: #374151;
        }
        .form-textarea:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
        .form-hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }

        /* ── Info Sidebar Cards ────────────────────────── */
        .info-list { display: flex; flex-direction: column; gap: 16px; }
        .info-item {
            display: flex; flex-direction: column; gap: 4px;
            padding-bottom: 12px; border-bottom: 1px solid #f3f4f6;
        }
        .info-item:last-child { border-bottom: none; padding-bottom: 0; }
        .info-label { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
        .info-value { font-size: 14px; color: #111827; }
        .info-value a { color: #0d9488; text-decoration: none; }
        .info-value a:hover { text-decoration: underline; }

        /* ── Verification Card ─────────────────────────── */
        .verification-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; margin-bottom: 24px;
        }
        .verification-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .verification-icon {
            width: 40px; height: 40px; border-radius: 8px; background: #3b82f6;
            color: white; display: flex; align-items: center; justify-content: center;
        }
        .verification-title { font-size: 16px; font-weight: 700; color: #1e40af; }
        .verification-text { font-size: 13px; color: #1e40af; margin-bottom: 16px; opacity: .9; }
        .verification-progress { display: flex; flex-direction: column; gap: 10px; }
        .progress-item { display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .progress-check {
            width: 20px; height: 20px; border-radius: 50%; background: #22c55e;
            color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .progress-check.pending { background: #cbd5e1; color: #64748b; }

        /* ── Kelengkapan Bar ───────────────────────────── */
        .profil-pct-bar { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; margin-top: 6px; }
        .profil-pct-fill { height: 100%; background: linear-gradient(90deg, #0d9488, #14b8a6); border-radius: 3px; transition: width .6s; }

        /* ── Hapus logo btn ────────────────────────────── */
        .btn-danger-sm {
            background: #ef4444; color: white; border: none; padding: 6px 14px;
            border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: .2s;
        }
        .btn-danger-sm:hover { background: #dc2626; }

        /* ── Responsive ────────────────────────────────── */
        @media (max-width: 1200px) { .content-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; padding: 16px; }
            .profile-header-content { flex-direction: column; align-items: center; text-align: center; }
            .profile-header-meta { justify-content: center; }
            .form-row { grid-template-columns: 1fr; }
        }

        /* Avatar modal */
        .avatar-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.8); justify-content: center; align-items: center; z-index: 9999; }
        .avatar-modal img { max-width: 90%; max-height: 90%; border-radius: 12px; box-shadow: 0 0 30px rgba(0,0,0,.5); }
    </style>
</head>
<body>
<div class="layout">

    <!-- ── Sidebar ─────────────────────────────────────── -->
    <aside class="sidebar">
        <div class="logo">
            <img class="logo-img" src="assets/logo_lokerin.png" alt="L">
            <span class="logo-text">LokerIn</span>
        </div>

        <div class="company-profile">
            <div class="company-avatar">
                <?php if (!empty($user['foto_perusahaan']) && file_exists('uploads/foto_perusahaan/' . $user['foto_perusahaan'])): ?>
                        <img src="uploads/foto_perusahaan/<?= htmlspecialchars($user['foto_perusahaan']) ?>" alt="logo">
                <?php else: ?>
                        <?= $inisial ?>
                <?php endif; ?>
            </div>
            <div class="company-info">
                <div class="company-name"><?= htmlspecialchars($user['nama']) ?></div>
                <div class="company-industry"><?= htmlspecialchars($user['email']) ?></div>
            </div>
        </div>

        <div class="employer-score">
            <div class="employer-score-header">
                <span class="employer-score-label">Employer Score</span>
                <span class="employer-score-value"><?= isset($employer_score) ? $employer_score : $profil_pct ?>%</span>
            </div>
            <div class="employer-score-bar">
                <div class="employer-score-fill" style="width: <?= isset($employer_score) ? $employer_score : $profil_pct ?>%;"></div>
            </div>
        </div>

        <nav>
            <a href="dashboard_perusahaan.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Dashboard
            </a>
            <a href="posting_lowongan.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                Posting Lowongan
            </a>
            <a href="kelola_lowongan.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                Kelola Lowongan
            </a>
            <a href="kandidat.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Kandidat
            </a>
            <a href="profil_perusahaan.php" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Profil Perusahaan
            </a>
            <a href="analytics.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Analytics
            </a>
            <a href="pesan_perusahaan.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                Pesan
            </a>
            <div class="nav-divider"></div>
            <a href="pengaturan_perusahaan.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v6m0 6v6"></path><path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path><path d="M1 12h6m6 0h6"></path><path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path></svg>
                Pengaturan
            </a>
            <a href="logout.php" class="nav-item nav-item-logout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Keluar
            </a>
        </nav>
    </aside>

    <!-- ── Main Content ────────────────────────────────── -->
    <main class="main-content">

        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <h1>Profil Perusahaan</h1>
                <p>Kelola informasi profil perusahaan Anda</p>
            </div>
            <div class="header-actions">
                <button class="btn-secondary" onclick="window.open('profil_perusahaan.php','_blank')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Preview Profil
                </button>
                <button form="form-profil" type="submit" name="simpan_profil" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Simpan Perubahan
                </button>
            </div>
        </header>

        <?php if ($pesan_sukses): ?>
                <div class="alert alert-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <?= htmlspecialchars($pesan_sukses) ?>
                </div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
                <div class="alert alert-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= htmlspecialchars($pesan_error) ?>
                </div>
        <?php endif; ?>

        <!-- Profile Header Card -->
        <div class="profile-header">
            <div class="profile-cover"></div>
            <div class="profile-header-content">

                <!-- Avatar / Logo dengan klik preview -->
                <div class="profile-avatar-large" id="logoPreview">
                    <?php if (!empty($user['foto_perusahaan']) && file_exists('uploads/foto_perusahaan/' . $user['foto_perusahaan'])): ?>
                            <img src="uploads/foto_perusahaan/<?= htmlspecialchars($user['foto_perusahaan']) ?>" alt="logo perusahaan">
                    <?php else: ?>
                            <?= $inisial ?>
                    <?php endif; ?>
                </div>

                <div class="profile-header-info">
                    <h2 class="profile-header-name"><?= htmlspecialchars($user['nama']) ?></h2>
                    <p class="profile-header-industry">
                        <?= htmlspecialchars($user['industri'] ?? 'Industri belum diisi') ?>
                        <?php if (!empty($user['ukuran_perusahaan'])): ?>
                                &bull; <?= htmlspecialchars($user['ukuran_perusahaan']) ?>
                        <?php endif; ?>
                    </p>

                    <div class="profile-header-meta">
                        <?php if (!empty($user['alamat'])): ?>
                            <span class="profile-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <?= htmlspecialchars($user['alamat']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($user['website'])): ?>
                            <span class="profile-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                <?= htmlspecialchars($user['website']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($user['tahun_berdiri'])): ?>
                            <span class="profile-meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                Berdiri sejak <?= htmlspecialchars($user['tahun_berdiri']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Upload logo -->
                    <form method="POST" enctype="multipart/form-data" style="margin-top:12px; display:inline;">
                        <label class="upload-logo-btn" for="foto_perusahaan_input">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Ganti Logo
                        </label>
                        <input class="upload-logo-input" id="foto_perusahaan_input" type="file" name="foto_perusahaan" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()">
                    </form>
                    <?php if (!empty($user['foto_perusahaan'])): ?>
                            <form method="POST" style="display:inline; margin-left:8px;">
                                <button type="submit" name="hapus_foto" class="btn-danger-sm" onclick="return confirm('Hapus logo perusahaan?')">Hapus Logo</button>
                            </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Verification Status -->
        <div class="verification-card">
            <div class="verification-header">
                <div class="verification-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div>
                    <div class="verification-title">Status Verifikasi</div>
                </div>
            </div>
            <p class="verification-text">Tingkatkan kredibilitas perusahaan dengan melengkapi verifikasi profil</p>
            <div class="verification-progress">
                <div class="progress-item">
                    <div class="progress-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                    <span style="color:#1e40af">Email Terverifikasi</span>
                </div>
                <div class="progress-item">
                    <div class="progress-check <?= empty($user['telepon']) ? 'pending' : '' ?>">
                        <?php if (!empty($user['telepon'])): ?><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg><?php endif; ?>
                    </div>
                    <span style="color:<?= empty($user['telepon']) ? '#6b7280' : '#1e40af' ?>">
                        Nomor Telepon <?= empty($user['telepon']) ? '(Belum diisi)' : 'Terverifikasi' ?>
                    </span>
                </div>
                <div class="progress-item">
                    <div class="progress-check <?= empty($user['deskripsi']) ? 'pending' : '' ?>">
                        <?php if (!empty($user['deskripsi'])): ?><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg><?php endif; ?>
                    </div>
                    <span style="color:<?= empty($user['deskripsi']) ? '#6b7280' : '#1e40af' ?>">
                        Deskripsi Perusahaan <?= empty($user['deskripsi']) ? '(Pending)' : 'Terisi' ?>
                    </span>
                </div>
                <div class="progress-item">
                    <div class="progress-check <?= empty($user['foto_perusahaan']) ? 'pending' : '' ?>">
                        <?php if (!empty($user['foto_perusahaan'])): ?><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg><?php endif; ?>
                    </div>
                    <span style="color:<?= empty($user['foto_perusahaan']) ? '#6b7280' : '#1e40af' ?>">
                        Logo Perusahaan <?= empty($user['foto_perusahaan']) ? '(Pending)' : 'Terupload' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">

            <!-- LEFT: Form edit profil -->
            <form method="POST" id="form-profil">
                <!-- Informasi Dasar -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Dasar</h3>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Data Perusahaan
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Perusahaan <span style="color:#ef4444">*</span></label>
                                <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" placeholder="Nama lengkap perusahaan" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Industri / Bidang</label>
                                <select name="industri" class="form-select">
                                    <option value="">-- Pilih Industri --</option>
                                    <?php
                                    $industri_list = [
                                        'Teknologi Informasi',
                                        'Software Development',
                                        'E-Commerce',
                                        'Fintech',
                                        'Healthcare',
                                        'Pendidikan',
                                        'Manufaktur',
                                        'Retail',
                                        'Logistik',
                                        'Perbankan & Keuangan',
                                        'Media & Kreatif',
                                        'Konsultan',
                                        'Lainnya'
                                    ];
                                    foreach ($industri_list as $ind):
                                        $sel = ($user['industri'] ?? '') === $ind ? 'selected' : '';
                                        ?>
                                            <option value="<?= $ind ?>" <?= $sel ?>><?= $ind ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="tel" name="telepon" class="form-input" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>" placeholder="021-xxxxxxxx">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ukuran Perusahaan</label>
                                <select name="ukuran_perusahaan" class="form-select">
                                    <option value="">-- Pilih Ukuran --</option>
                                    <?php
                                    $ukuran_list = ['1-10 Karyawan', '11-50 Karyawan', '51-200 Karyawan', '201-500 Karyawan', '501-1000 Karyawan', '1000+ Karyawan'];
                                    foreach ($ukuran_list as $uk):
                                        $sel = ($user['ukuran_perusahaan'] ?? '') === $uk ? 'selected' : '';
                                        ?>
                                            <option value="<?= $uk ?>" <?= $sel ?>><?= $uk ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Website</label>
                                <input type="text" name="website" class="form-input" value="<?= htmlspecialchars($user['website'] ?? '') ?>" placeholder="www.perusahaan.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tahun Berdiri</label>
                                <input type="number" name="tahun_berdiri" class="form-input" value="<?= htmlspecialchars($user['tahun_berdiri'] ?? '') ?>" placeholder="2020" min="1900" max="<?= date('Y') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap</label>
                            <input type="text" name="alamat" class="form-input" value="<?= htmlspecialchars($user['alamat'] ?? '') ?>" placeholder="Jl. Contoh No. 1, Kota, Provinsi">
                        </div>
                        <div class="form-group">
                            <label class="form-label">LinkedIn</label>
                            <input type="url" name="linkedin" class="form-input" value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/company/nama-perusahaan">
                        </div>
                    </div>
                </div>

                <!-- Tentang Perusahaan -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tentang Perusahaan</h3>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi Perusahaan</label>
                        <textarea name="deskripsi" class="form-textarea" rows="6" placeholder="Ceritakan tentang visi, misi, dan budaya kerja perusahaan Anda..."><?= htmlspecialchars($user['deskripsi'] ?? '') ?></textarea>
                        <p class="form-hint">Deskripsi yang menarik akan meningkatkan minat pelamar berkualitas.</p>
                    </div>

                    <div class="form-group" style="margin-top:16px; text-align:right;">
                        <button type="submit" name="simpan_profil" class="btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>

            <!-- RIGHT: Ringkasan info + kelengkapan -->
            <div>
                <!-- Kelengkapan Profil -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Kelengkapan Profil</h3>
                    </div>
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <span style="font-size:13px; color:#6b7280;">Terisi <?= $profil_pct ?>%</span>
                            <span style="font-size:14px; font-weight:700; color:#0d9488;"><?= $profil_pct ?>%</span>
                        </div>
                        <div class="profil-pct-bar">
                            <div class="profil-pct-fill" style="width:<?= $profil_pct ?>%"></div>
                        </div>
                        <?php if ($profil_pct < 100): ?>
                                <p style="font-size:12px; color:#6b7280; margin-top:8px;">Lengkapi profil agar lebih mudah ditemukan pelamar.</p>
                        <?php else: ?>
                                <p style="font-size:12px; color:#0d9488; margin-top:8px; font-weight:600;">✓ Profil sudah lengkap!</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Singkat -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Info Perusahaan</h3>
                    </div>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Telepon</span>
                            <span class="info-value"><?= !empty($user['telepon']) ? htmlspecialchars($user['telepon']) : '<em style="color:#9ca3af">Belum diisi</em>' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Website</span>
                            <span class="info-value">
                                <?php if (!empty($user['website'])): ?>
                                        <a href="<?= (strpos($user['website'], 'http') === 0 ? '' : 'https://') . htmlspecialchars($user['website']) ?>" target="_blank"><?= htmlspecialchars($user['website']) ?></a>
                                <?php else: ?>
                                        <em style="color:#9ca3af">Belum diisi</em>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Industri</span>
                            <span class="info-value"><?= !empty($user['industri']) ? htmlspecialchars($user['industri']) : '<em style="color:#9ca3af">Belum diisi</em>' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Ukuran</span>
                            <span class="info-value"><?= !empty($user['ukuran_perusahaan']) ? htmlspecialchars($user['ukuran_perusahaan']) : '<em style="color:#9ca3af">Belum diisi</em>' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tahun Berdiri</span>
                            <span class="info-value"><?= !empty($user['tahun_berdiri']) ? htmlspecialchars($user['tahun_berdiri']) : '<em style="color:#9ca3af">Belum diisi</em>' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Alamat</span>
                            <span class="info-value"><?= !empty($user['alamat']) ? htmlspecialchars($user['alamat']) : '<em style="color:#9ca3af">Belum diisi</em>' ?></span>
                        </div>
                        <?php if (!empty($user['linkedin'])): ?>
                            <div class="info-item">
                                <span class="info-label">LinkedIn</span>
                                <span class="info-value"><a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank">Lihat Profil LinkedIn</a></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aksi Cepat</h3>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <a href="posting_lowongan.php" class="btn-primary" style="justify-content:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                            Posting Lowongan Baru
                        </a>
                        <a href="kandidat.php" class="btn-secondary" style="justify-content:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            Kelola Kandidat
                        </a>
                        <a href="analytics.php" class="btn-secondary" style="justify-content:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            Lihat Analytics
                        </a>
                    </div>
                </div>
            </div>

        </div><!-- end content-grid -->
    </main>
</div>

<!-- Avatar preview modal -->
<div id="avatarModal" class="avatar-modal">
    <img id="avatarModalImg" src="" alt="Logo Perusahaan">
</div>

<script>
    const logoEl = document.querySelector('#logoPreview img');
    const modal  = document.getElementById('avatarModal');
    const modalImg = document.getElementById('avatarModalImg');

    if (logoEl) {
        logoEl.addEventListener('click', () => {
            modal.style.display = 'flex';
            modalImg.src = logoEl.src;
        });
        modal.addEventListener('click', () => { modal.style.display = 'none'; });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') modal.style.display = 'none';
        });
    }
</script>
</body>
</html>