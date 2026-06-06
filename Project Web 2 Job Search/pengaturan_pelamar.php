<?php

session_start();

if (!isset($_SESSION['id_user'])) {
  header("Location: login_pelamar.php");
  exit;
}

if ($_SESSION['role'] != 'pelamar') {
  header("Location: login_pelamar.php");
  exit;
}

include 'config/koneksi.php';

$id_pelamar = (int) $_SESSION['id_user'];

$result = mysqli_query($conn, "SELECT * FROM users WHERE id_user=$id_pelamar");
$user = mysqli_fetch_assoc($result);
$initials = strtoupper(substr($user['nama'] ?? 'U', 0, 2));

/* ── Kelengkapan Profil ─────────────────────────────────────────── */
$fields_cek = ['nama', 'telepon', 'lokasi', 'bio', 'pendidikan', 'pengalaman', 'skills', 'foto_profil', 'cv_path'];
$isi = 0;
foreach ($fields_cek as $f) {
  if (!empty($user[$f]))
    $isi++;
}
$kelengkapan = round(($isi / count($fields_cek)) * 100);

/* ── Statistik lamaran ──────────────────────────────────────────── */
$total_lamaran = mysqli_num_rows(mysqli_query($conn, "SELECT id_lamaran FROM lamaran WHERE id_pelamar=$id_pelamar"));
$total_review = mysqli_num_rows(mysqli_query($conn, "SELECT id_lamaran FROM lamaran WHERE id_pelamar=$id_pelamar AND status='review'"));
$total_interview = mysqli_num_rows(mysqli_query($conn, "SELECT id_lamaran FROM lamaran WHERE id_pelamar=$id_pelamar AND status='interview'"));
$total_lowongan = mysqli_num_rows(mysqli_query($conn, "SELECT id_lowongan FROM lowongan WHERE status='aktif'"));

/* ══════════════════════════════════════════════════════════════════
   HANDLE POST ACTIONS
   ══════════════════════════════════════════════════════════════════ */

$alert_type = '';
$alert_message = '';

/* ── 1. Simpan Info Akun (nama, telepon, lokasi) ────────────────── */
if (isset($_POST['simpan_akun'])) {
  $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
  $telepon = mysqli_real_escape_string($conn, trim($_POST['telepon']));
  $lokasi = mysqli_real_escape_string($conn, trim($_POST['lokasi']));

  $q = mysqli_query($conn, "UPDATE users SET nama='$nama', telepon='$telepon', lokasi='$lokasi' WHERE id_user=$id_pelamar");
  if ($q) {
    $_SESSION['nama'] = $nama;
    header("Location: pengaturan_pelamar.php?tab=umum&ok=akun");
    exit;
  } else {
    $alert_type = 'error';
    $alert_message = 'Gagal menyimpan informasi akun.';
  }
}

/* ── 2. Ganti Password ──────────────────────────────────────────── */
if (isset($_POST['ganti_password'])) {
  $password_lama = md5($_POST['password_lama']);
  $password_baru = $_POST['password_baru'];
  $password_konfirm = $_POST['password_konfirm'];

  // Cek password lama
  $cek = mysqli_query($conn, "SELECT id_user FROM users WHERE id_user=$id_pelamar AND password='$password_lama'");
  if (mysqli_num_rows($cek) === 0) {
    $alert_type = 'error';
    $alert_message = 'Password lama tidak sesuai.';
  } elseif (strlen($password_baru) < 6) {
    $alert_type = 'error';
    $alert_message = 'Password baru minimal 6 karakter.';
  } elseif ($password_baru !== $password_konfirm) {
    $alert_type = 'error';
    $alert_message = 'Konfirmasi password tidak cocok.';
  } else {
    $hash = md5($password_baru);
    $q = mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id_user=$id_pelamar");
    if ($q) {
      header("Location: pengaturan_pelamar.php?tab=keamanan&ok=password");
      exit;
    } else {
      $alert_type = 'error';
      $alert_message = 'Gagal mengubah password.';
    }
  }
}

/* ── 3. Hapus Akun ──────────────────────────────────────────────── */
if (isset($_POST['hapus_akun'])) {
  $konfirm_hapus = $_POST['konfirm_hapus'] ?? '';
  if ($konfirm_hapus !== 'HAPUS') {
    $alert_type = 'error';
    $alert_message = 'Ketik HAPUS untuk mengkonfirmasi penghapusan akun.';
  } else {
    // Hapus lamaran dulu, lalu user
    mysqli_query($conn, "DELETE FROM lamaran WHERE id_pelamar=$id_pelamar");

    // Hapus foto & cv jika ada
    if (!empty($user['foto_profil']) && file_exists('uploads/foto_profil/' . $user['foto_profil']))
      unlink('uploads/foto_profil/' . $user['foto_profil']);
    if (!empty($user['cv_path']) && file_exists('uploads/cv/' . $user['cv_path']))
      unlink('uploads/cv/' . $user['cv_path']);

    mysqli_query($conn, "DELETE FROM users WHERE id_user=$id_pelamar");
    session_destroy();
    header("Location: index.html?akun=terhapus");
    exit;
  }
}

/* ── Flash messages dari redirect ──────────────────────────────── */
if (isset($_GET['ok'])) {
  $alert_type = 'success';
  $msgs = ['akun' => 'Informasi akun berhasil disimpan!', 'password' => 'Password berhasil diubah!'];
  $alert_message = $msgs[$_GET['ok']] ?? 'Perubahan berhasil disimpan.';
}

/* ── Active tab dari URL ────────────────────────────────────────── */
$active_tab = $_GET['tab'] ?? 'umum';
$allowed_tabs = ['umum', 'notifikasi', 'privasi', 'keamanan'];
if (!in_array($active_tab, $allowed_tabs))
  $active_tab = 'umum';

?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pengaturan - Lokerin</title>
  <link rel="icon" type="image/png" href="assets/icon_head_lokerin.png" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background-color: #f9fafb;
      color: #111827;
      line-height: 1.6;
    }

    .layout {
      display: flex;
      min-height: 100vh;
    }

    /* ── Sidebar ─────────────────────────────────────────────── */
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

    .sidebar-avatar {
      width: 40px;
      height: 40px;
      background: #0d9488;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700;
      font-size: 14px;
      overflow: hidden;
      flex-shrink: 0;
      cursor: pointer;
      transition: transform .2s;
    }

    .sidebar-avatar:hover {
      transform: scale(1.05);
    }

    .sidebar-avatar img {
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

    /* ── Main Content ────────────────────────────────────────── */
    .main-content {
      flex: 1;
      margin-left: 260px;
      padding: 24px 32px;
      max-width: 1200px;
    }

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

    .page-title {
      font-size: 32px;
      font-weight: 700;
      color: #111827;
      margin-bottom: 8px;
    }

    .page-subtitle {
      font-size: 16px;
      color: #6b7280;
      margin-bottom: 32px;
    }

    /* ── Alert ───────────────────────────────────────────────── */
    .alert {
      padding: 14px 18px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .alert-error {
      background: #fef2f2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    /* ── Tabs ────────────────────────────────────────────────── */
    .settings-tabs {
      display: flex;
      gap: 8px;
      border-bottom: 2px solid #e5e7eb;
      margin-bottom: 32px;
      overflow-x: auto;
    }

    .tab-btn {
      padding: 12px 20px;
      background: none;
      border: none;
      border-bottom: 2px solid transparent;
      color: #6b7280;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      white-space: nowrap;
      margin-bottom: -2px;
      text-decoration: none;
      display: inline-block;
    }

    .tab-btn:hover {
      color: #111827;
    }

    .tab-btn.active {
      color: #0d9488;
      border-bottom-color: #0d9488;
    }

    /* ── Settings Sections ───────────────────────────────────── */
    .settings-section {
      background: white;
      border-radius: 16px;
      padding: 32px;
      margin-bottom: 24px;
      border: 1px solid #e5e7eb;
    }

    /* Show/hide sections based on active tab */
    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .section-header {
      margin-bottom: 24px;
    }

    .section-title {
      font-size: 20px;
      font-weight: 700;
      color: #111827;
      margin-bottom: 4px;
    }

    .section-description {
      font-size: 14px;
      color: #6b7280;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-group:last-child {
      margin-bottom: 0;
    }

    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #111827;
      margin-bottom: 8px;
    }

    .form-input {
      width: 100%;
      padding: 10px 16px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      font-size: 14px;
      outline: none;
      transition: all 0.2s;
      font-family: inherit;
    }

    .form-input:focus {
      border-color: #0d9488;
    }

    .form-input:disabled {
      background: #f9fafb;
      color: #9ca3af;
      cursor: not-allowed;
    }

    .form-select {
      width: 100%;
      padding: 10px 16px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      font-size: 14px;
      outline: none;
      transition: all 0.2s;
      background: white;
    }

    .form-select:focus {
      border-color: #0d9488;
    }

    .form-helper {
      font-size: 13px;
      color: #6b7280;
      margin-top: 6px;
    }

    /* ── Toggle ──────────────────────────────────────────────── */
    .toggle-group {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 0;
      border-bottom: 1px solid #f3f4f6;
    }

    .toggle-group:last-child {
      border-bottom: none;
    }

    .toggle-info {
      flex: 1;
    }

    .toggle-label {
      font-size: 15px;
      font-weight: 600;
      color: #111827;
      margin-bottom: 4px;
    }

    .toggle-description {
      font-size: 13px;
      color: #6b7280;
    }

    .toggle-switch {
      position: relative;
      width: 48px;
      height: 28px;
      background: #d1d5db;
      border-radius: 14px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .toggle-switch.active {
      background: #0d9488;
    }

    .toggle-slider {
      position: absolute;
      top: 4px;
      left: 4px;
      width: 20px;
      height: 20px;
      background: white;
      border-radius: 50%;
      transition: all 0.3s;
    }

    .toggle-switch.active .toggle-slider {
      left: 24px;
    }

    /* ── Buttons ─────────────────────────────────────────────── */
    .button-group {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 24px;
      padding-top: 24px;
      border-top: 1px solid #e5e7eb;
    }

    .btn {
      padding: 10px 24px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      border: none;
      font-family: inherit;
    }

    .btn-secondary {
      background: white;
      color: #6b7280;
      border: 1px solid #e5e7eb;
    }

    .btn-secondary:hover {
      background: #f9fafb;
    }

    .btn-primary {
      background: #0d9488;
      color: white;
    }

    .btn-primary:hover {
      background: #0f766e;
    }

    .btn-danger {
      background: #ef4444;
      color: white;
    }

    .btn-danger:hover {
      background: #dc2626;
    }

    /* ── Danger Zone ─────────────────────────────────────────── */
    .danger-zone {
      background: #fef2f2;
      border: 1px solid #fecaca;
    }

    .danger-zone .section-title {
      color: #991b1b;
    }

    .danger-zone .section-description {
      color: #dc2626;
    }

    /* ── Confirm input (hapus akun) ─────────────────────────── */
    .confirm-input-wrapper {
      margin-top: 16px;
    }

    .confirm-label {
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 6px;
      display: block;
    }

    .confirm-input {
      width: 100%;
      max-width: 300px;
      padding: 10px 16px;
      border: 1px solid #fecaca;
      border-radius: 8px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s;
      font-family: inherit;
    }

    .confirm-input:focus {
      border-color: #ef4444;
    }

    /* ── Info link ke profil ─────────────────────────────────── */
    .profil-link-note {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: #0d9488;
      text-decoration: none;
      margin-top: 8px;
      transition: color 0.2s;
    }

    .profil-link-note:hover {
      color: #0f766e;
    }

    /* ── Responsive ──────────────────────────────────────────── */
    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }

      .main-content {
        margin-left: 0;
        padding: 16px;
      }

      .settings-section {
        padding: 20px;
      }
    }
  </style>
</head>

<body>
  <div class="layout">

    <!-- ── Sidebar ──────────────────────────────────────────────────── -->
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
        <a href="pengaturan_pelamar.php" class="nav-item active">
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

    <!-- ── Main Content ──────────────────────────────────────────────── -->
    <main class="main-content">

      <header class="header">
        <div class="location">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          <?= htmlspecialchars($user['lokasi'] ?? 'Jakarta, Indonesia') ?>
        </div>
        <div class="header-actions">
          <button class="icon-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span class="notification-badge">3</span>
          </button>
          <button class="icon-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
          </button>
        </div>
      </header>

      <h1 class="page-title">Pengaturan</h1>
      <p class="page-subtitle">Kelola akun dan preferensi Anda</p>

      <?php if ($alert_message): ?>
        <div class="alert alert-<?= $alert_type ?>">
          <?php if ($alert_type === 'success'): ?>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12" />
            </svg>
          <?php else: ?>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
          <?php endif; ?>
          <?= htmlspecialchars($alert_message) ?>
        </div>
      <?php endif; ?>

      <!-- ── Tab Navigation ────────────────────────────────────────── -->
      <div class="settings-tabs">
        <a href="#section-umum" class="tab-btn <?= $active_tab === 'umum' ? 'active' : '' ?>" data-tab="umum">Umum</a>
        <a href="#section-notifikasi" class="tab-btn <?= $active_tab === 'notifikasi' ? 'active' : '' ?>"
          data-tab="notifikasi">Notifikasi</a>
        <a href="#section-privasi" class="tab-btn <?= $active_tab === 'privasi' ? 'active' : '' ?>"
          data-tab="privasi">Privasi</a>
        <a href="#section-keamanan" class="tab-btn <?= $active_tab === 'keamanan' ? 'active' : '' ?>"
          data-tab="keamanan">Keamanan</a>
      </div>

      <!-- ══════════════════════════════════════════════════════════
             TAB: UMUM
             ══════════════════════════════════════════════════════════ -->
      <div id="section-umum" class="tab-content <?= $active_tab === 'umum' ? 'active' : '' ?>">

        <!-- Informasi Akun — terintegrasi dengan profil_pelamar.php -->
        <div class="settings-section">
          <div class="section-header">
            <h2 class="section-title">Informasi Akun</h2>
            <p class="section-description">
              Perbarui nama, nomor telepon, dan lokasi Anda. Perubahan di sini juga akan tercermin di halaman profil.
            </p>
          </div>

          <form method="POST" action="pengaturan_pelamar.php?tab=umum">
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($user['nama'] ?? '') ?>"
                placeholder="Nama lengkap Anda" required />
            </div>

            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled />
              <p class="form-helper">Email tidak dapat diubah</p>
            </div>

            <div class="form-group">
              <label class="form-label">Nomor Telepon</label>
              <input type="tel" name="telepon" class="form-input"
                value="<?= htmlspecialchars($user['telepon'] ?? '') ?>" placeholder="Contoh: 08123456789" />
            </div>

            <div class="form-group">
              <label class="form-label">Lokasi</label>
              <input type="text" name="lokasi" class="form-input" value="<?= htmlspecialchars($user['lokasi'] ?? '') ?>"
                placeholder="Kota, Provinsi" />
              <p class="form-helper">
                Ingin mengubah detail profil lebih lanjut?
                <a href="profil_pelamar.php" class="profil-link-note">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="m12 5 7 7-7 7" />
                  </svg>
                  Buka Halaman Profil
                </a>
              </p>
            </div>

            <div class="button-group">
              <a href="profil_pelamar.php" class="btn btn-secondary"
                style="text-decoration:none; display:inline-flex; align-items:center;">Lihat Profil Lengkap</a>
              <button type="submit" name="simpan_akun" class="btn btn-primary">Simpan Perubahan</button>
            </div>
          </form>
        </div>

      </div><!-- /tab umum -->

      <!-- ══════════════════════════════════════════════════════════
             TAB: NOTIFIKASI
             ══════════════════════════════════════════════════════════ -->
      <div id="section-notifikasi" class="tab-content <?= $active_tab === 'notifikasi' ? 'active' : '' ?>">

        <div class="settings-section">
          <div class="section-header">
            <h2 class="section-title">Notifikasi</h2>
            <p class="section-description">Atur preferensi notifikasi Anda</p>
          </div>

          <div class="toggle-group">
            <div class="toggle-info">
              <div class="toggle-label">Notifikasi Email</div>
              <div class="toggle-description">Terima notifikasi melalui email</div>
            </div>
            <div class="toggle-switch active">
              <div class="toggle-slider"></div>
            </div>
          </div>

          <div class="toggle-group">
            <div class="toggle-info">
              <div class="toggle-label">Lowongan Baru</div>
              <div class="toggle-description">Dapatkan info lowongan yang sesuai dengan profil Anda</div>
            </div>
            <div class="toggle-switch active">
              <div class="toggle-slider"></div>
            </div>
          </div>

          <div class="toggle-group">
            <div class="toggle-info">
              <div class="toggle-label">Update Lamaran</div>
              <div class="toggle-description">Notifikasi tentang status lamaran Anda</div>
            </div>
            <div class="toggle-switch active">
              <div class="toggle-slider"></div>
            </div>
          </div>

          <div class="toggle-group">
            <div class="toggle-info">
              <div class="toggle-label">Tips Karir</div>
              <div class="toggle-description">Terima tips dan saran untuk pengembangan karir</div>
            </div>
            <div class="toggle-switch">
              <div class="toggle-slider"></div>
            </div>
          </div>

          <div class="toggle-group">
            <div class="toggle-info">
              <div class="toggle-label">Newsletter</div>
              <div class="toggle-description">Dapatkan newsletter mingguan dari Lokerin</div>
            </div>
            <div class="toggle-switch">
              <div class="toggle-slider"></div>
            </div>
          </div>
        </div>

      </div><!-- /tab notifikasi -->

      <!-- ══════════════════════════════════════════════════════════
             TAB: PRIVASI
             ══════════════════════════════════════════════════════════ -->
      <div id="section-privasi" class="tab-content <?= $active_tab === 'privasi' ? 'active' : '' ?>">

        <div class="settings-section">
          <div class="section-header">
            <h2 class="section-title">Privasi</h2>
            <p class="section-description">Kontrol siapa yang dapat melihat profil Anda</p>
          </div>

          <div class="toggle-group">
            <div class="toggle-info">
              <div class="toggle-label">Profil Publik</div>
              <div class="toggle-description">Buat profil Anda terlihat oleh recruiter</div>
            </div>
            <div class="toggle-switch active">
              <div class="toggle-slider"></div>
            </div>
          </div>

          <div class="toggle-group">
            <div class="toggle-info">
              <div class="toggle-label">Tampilkan Status</div>
              <div class="toggle-description">Tampilkan status "Mencari Pekerjaan" di profil</div>
            </div>
            <div class="toggle-switch active">
              <div class="toggle-slider"></div>
            </div>
          </div>

          <div class="toggle-group">
            <div class="toggle-info">
              <div class="toggle-label">Sembunyikan dari Perusahaan</div>
              <div class="toggle-description">Sembunyikan profil dari perusahaan saat ini</div>
            </div>
            <div class="toggle-switch">
              <div class="toggle-slider"></div>
            </div>
          </div>
        </div>

      </div><!-- /tab privasi -->

      <!-- ══════════════════════════════════════════════════════════
             TAB: KEAMANAN
             ══════════════════════════════════════════════════════════ -->
      <div id="section-keamanan" class="tab-content <?= $active_tab === 'keamanan' ? 'active' : '' ?>">

        <!-- Ganti Password — terintegrasi DB -->
        <div id="section-ganti-password" class="settings-section">
          <div class="section-header">
            <h2 class="section-title">Keamanan</h2>
            <p class="section-description">Jaga keamanan akun Anda</p>
          </div>

          <form method="POST" action="pengaturan_pelamar.php?tab=keamanan">
            <div class="form-group">
              <label class="form-label">Password Lama</label>
              <input type="password" name="password_lama" class="form-input" placeholder="Masukkan password lama"
                required />
            </div>

            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <input type="password" name="password_baru" class="form-input"
                placeholder="Masukkan password baru (min. 6 karakter)" required />
            </div>

            <div class="form-group">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="password_konfirm" class="form-input" placeholder="Ulangi password baru"
                required />
            </div>

            <div class="toggle-group">
              <div class="toggle-info">
                <div class="toggle-label">Autentikasi Dua Faktor</div>
                <div class="toggle-description">Tambah lapisan keamanan ekstra untuk akun Anda</div>
              </div>
              <div class="toggle-switch">
                <div class="toggle-slider"></div>
              </div>
            </div>

            <div class="button-group">
              <button type="reset" class="btn btn-secondary">Batal</button>
              <button type="submit" name="ganti_password" class="btn btn-primary">Update Password</button>
            </div>
          </form>
        </div>

        <!-- Hapus Akun — terintegrasi DB -->
        <div id="section-hapus-akun" class="settings-section danger-zone">
          <div class="section-header">
            <h2 class="section-title">Zona Bahaya</h2>
            <p class="section-description">Tindakan ini tidak dapat dibatalkan</p>
          </div>

          <div class="form-group">
            <label class="form-label">Hapus Akun</label>
            <p class="form-helper">
              Menghapus akun akan menghilangkan semua data Anda secara permanen,
              termasuk seluruh lamaran yang pernah Anda kirim.
            </p>
          </div>

          <form method="POST" action="pengaturan_pelamar.php?tab=keamanan" onsubmit="return konfirmasiHapus()">
            <div class="confirm-input-wrapper">
              <label class="confirm-label">
                Ketik <strong>HAPUS</strong> untuk mengkonfirmasi penghapusan akun:
              </label>
              <input type="text" name="konfirm_hapus" class="confirm-input" placeholder="HAPUS" autocomplete="off" />
            </div>

            <div class="button-group" style="border-top: none; padding-top: 16px;">
              <button type="submit" name="hapus_akun" class="btn btn-danger">Hapus Akun</button>
            </div>
          </form>
        </div>

      </div><!-- /tab keamanan -->

    </main>
  </div>

  <!-- Avatar modal -->
  <div id="avatarModal" class="avatar-modal">
    <img id="avatarModalImg" src="" alt="Foto Profil">
  </div>

  <script>
    /* ── Tab switching ─────────────────────────────────────────────────── */
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const tab = this.dataset.tab;

        // Update active tab button
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Show matching content, hide others
        document.querySelectorAll('.tab-content').forEach(sec => sec.classList.remove('active'));
        document.getElementById('section-' + tab)?.classList.add('active');

        // Update URL without reload so refresh stays on same tab
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);

        // Smooth scroll to top of main content
        document.querySelector('.main-content').scrollTo({ top: 0, behavior: 'smooth' });
      });
    });

    /* ── Toggle switches ───────────────────────────────────────────────── */
    document.querySelectorAll('.toggle-switch').forEach(toggle => {
      toggle.addEventListener('click', function () {
        this.classList.toggle('active');
      });
    });

    /* ── Konfirmasi hapus akun ─────────────────────────────────────────── */
    function konfirmasiHapus() {
      const val = document.querySelector('input[name="konfirm_hapus"]').value;
      if (val !== 'HAPUS') {
        alert('Silakan ketik HAPUS (huruf kapital semua) untuk mengkonfirmasi.');
        return false;
      }
      return confirm('Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan.');
    }

    /* ── Avatar modal ──────────────────────────────────────────────────── */
    const avatarEl = document.querySelector('.sidebar-avatar img');
    const modal = document.getElementById('avatarModal');
    const modalImg = document.getElementById('avatarModalImg');

    if (avatarEl) {
      avatarEl.addEventListener('click', () => {
        modal.style.display = 'flex';
        modalImg.src = avatarEl.src;
      });
      modal.addEventListener('click', () => { modal.style.display = 'none'; });
      document.addEventListener('keydown', e => {
        if (e.key === 'Escape') modal.style.display = 'none';
      });
    }
  </script>
</body>

</html>