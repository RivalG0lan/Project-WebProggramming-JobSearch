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

$id_user = $_SESSION['id_user'];
$pesan_sukses = '';
$pesan_error = '';

// ─── HANDLE POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // ── Hapus Foto ─────────────────────────────────────────────────────────
  if (isset($_POST['hapus_foto'])) {
    $q = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id_user='$id_user'");
    $data = mysqli_fetch_assoc($q);

    if (!empty($data['foto_profil'])) {
      $file = 'uploads/foto_profil/' . $data['foto_profil'];
      if (file_exists($file))
        unlink($file);
      mysqli_query($conn, "UPDATE users SET foto_profil=NULL WHERE id_user='$id_user'");
    }

    header("Location: profil_pelamar.php?hapus_foto=ok");
    exit;
  }

  // ── Upload Foto Profil ─────────────────────────────────────────────────
  if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
    $allowed_img = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $ftype = $_FILES['foto_profil']['type'];
    $fsize = $_FILES['foto_profil']['size'];

    if (!in_array($ftype, $allowed_img)) {
      $pesan_error = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.';
    } elseif ($fsize > 2 * 1024 * 1024) {
      $pesan_error = 'Ukuran foto maksimal 2 MB.';
    } else {
      // 1. Ambil foto lama SEBELUM apapun
      $q_old = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id_user='$id_user'");
      $old = mysqli_fetch_assoc($q_old);

      $ext = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
      $nama_file = 'foto_' . $id_user . '_' . time() . '.' . $ext;
      $tujuan = 'uploads/foto_profil/' . $nama_file;

      if (!is_dir('uploads/foto_profil'))
        mkdir('uploads/foto_profil', 0755, true);

      // 2. Pindahkan file baru
      if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $tujuan)) {
        // 3. Hapus file lama dari folder
        if (!empty($old['foto_profil'])) {
          $old_file = 'uploads/foto_profil/' . $old['foto_profil'];
          if (file_exists($old_file))
            unlink($old_file);
        }

        // 4. Simpan nama file baru ke DB
        $nama_esc = mysqli_real_escape_string($conn, $nama_file);
        mysqli_query($conn, "UPDATE users SET foto_profil='$nama_esc' WHERE id_user='$id_user'");

        header("Location: profil_pelamar.php?foto=ok");
        exit;
      } else {
        $pesan_error = 'Gagal mengupload foto. Coba lagi.';
      }
    }
  }

  // ── Hapus CV ───────────────────────────────────────────────────────────
  if (isset($_POST['hapus_cv'])) {
    $q = mysqli_query($conn, "SELECT cv_path FROM users WHERE id_user='$id_user'");
    $data = mysqli_fetch_assoc($q);

    if (!empty($data['cv_path'])) {
      $file = 'uploads/cv/' . $data['cv_path'];
      if (file_exists($file))
        unlink($file);
      mysqli_query($conn, "UPDATE users SET cv_path=NULL WHERE id_user='$id_user'");
    }

    header("Location: profil_pelamar.php?hapus_cv=ok");
    exit;
  }

  // ── Upload CV ──────────────────────────────────────────────────────────
  if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
    $allowed_cv = ['application/pdf'];
    $ftype = $_FILES['cv_file']['type'];
    $fsize = $_FILES['cv_file']['size'];

    if (!in_array($ftype, $allowed_cv)) {
      $pesan_error = 'Format CV harus PDF.';
    } elseif ($fsize > 5 * 1024 * 1024) {
      $pesan_error = 'Ukuran CV maksimal 5 MB.';
    } else {
      // 1. Ambil CV lama SEBELUM apapun
      $q_old = mysqli_query($conn, "SELECT cv_path FROM users WHERE id_user='$id_user'");
      $old = mysqli_fetch_assoc($q_old);

      $nama_cv = 'cv_' . $id_user . '_' . time() . '.pdf';
      $tujuan = 'uploads/cv/' . $nama_cv;

      if (!is_dir('uploads/cv'))
        mkdir('uploads/cv', 0755, true);

      // 2. Pindahkan file baru
      if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $tujuan)) {
        // 3. Hapus file lama dari folder
        if (!empty($old['cv_path'])) {
          $old_file = 'uploads/cv/' . $old['cv_path'];
          if (file_exists($old_file))
            unlink($old_file);
        }

        // 4. Simpan nama file baru ke DB
        $nama_esc = mysqli_real_escape_string($conn, $nama_cv);
        mysqli_query($conn, "UPDATE users SET cv_path='$nama_esc' WHERE id_user='$id_user'");

        header("Location: profil_pelamar.php?cv=ok");
        exit;
      } else {
        $pesan_error = 'Gagal mengupload CV. Coba lagi.';
      }
    }
  }

  // ── Simpan data profil teks ────────────────────────────────────────────
  if (isset($_POST['simpan_profil'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $telepon = mysqli_real_escape_string($conn, trim($_POST['telepon']));
    $lokasi = mysqli_real_escape_string($conn, trim($_POST['lokasi']));
    $bio = mysqli_real_escape_string($conn, trim($_POST['bio']));
    $pendidikan = mysqli_real_escape_string($conn, trim($_POST['pendidikan']));
    $pengalaman = mysqli_real_escape_string($conn, trim($_POST['pengalaman']));
    $skills = mysqli_real_escape_string($conn, trim($_POST['skills']));
    $linkedin = mysqli_real_escape_string($conn, trim($_POST['linkedin']));
    $portfolio = mysqli_real_escape_string($conn, trim($_POST['portfolio']));
    $tgl_lahir = mysqli_real_escape_string($conn, trim($_POST['tanggal_lahir']));

    $q = mysqli_query($conn, "
      UPDATE users SET
        nama          = '$nama',
        telepon       = '$telepon',
        lokasi        = '$lokasi',
        bio           = '$bio',
        pendidikan    = '$pendidikan',
        pengalaman    = '$pengalaman',
        skills        = '$skills',
        linkedin      = '$linkedin',
        portfolio     = '$portfolio',
        tanggal_lahir = " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . "
      WHERE id_user = '$id_user'
    ");

    if ($q) {
      $_SESSION['nama'] = $nama;
      header("Location: profil_pelamar.php?simpan=ok");
      exit;
    } else {
      $pesan_error = 'Gagal menyimpan profil. Coba lagi.';
    }
  }
}

// ─── Ambil data user ───────────────────────────────────────────────────────
$result = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($result);

// Hitung kelengkapan profil
$fields_cek = ['nama', 'telepon', 'lokasi', 'bio', 'pendidikan', 'pengalaman', 'skills', 'foto_profil', 'cv_path'];
$isi = 0;
foreach ($fields_cek as $f) {
  if (!empty($user[$f]))
    $isi++;
}
$kelengkapan = round(($isi / count($fields_cek)) * 100);

// Statistik lamaran
$stat_q = mysqli_query($conn, "SELECT status, COUNT(*) as jml FROM lamaran WHERE id_pelamar='$id_user' GROUP BY status");
$stats = ['dikirim' => 0, 'review' => 0, 'interview' => 0, 'accepted' => 0, 'rejected' => 0];
while ($r = mysqli_fetch_assoc($stat_q))
  $stats[$r['status']] = $r['jml'];
$total_lamaran = array_sum($stats);

// Flash messages
if (isset($_GET['simpan']) && $_GET['simpan'] === 'ok')
  $pesan_sukses = 'Profil berhasil disimpan!';
if (isset($_GET['foto']) && $_GET['foto'] === 'ok')
  $pesan_sukses = 'Foto profil berhasil diupload!';
if (isset($_GET['cv']) && $_GET['cv'] === 'ok')
  $pesan_sukses = 'CV berhasil diupload!';
if (isset($_GET['hapus_foto']) && $_GET['hapus_foto'] === 'ok')
  $pesan_sukses = 'Foto profil berhasil dihapus!';
if (isset($_GET['hapus_cv']) && $_GET['hapus_cv'] === 'ok')
  $pesan_sukses = 'CV berhasil dihapus!';

// Initials avatar
$initials = strtoupper(substr($user['nama'] ?? 'U', 0, 2));
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya - LokerIn</title>
  <link rel="icon" type="image/png" href="assets/icon_head_lokerin.png">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f9fafb;
      color: #111827;
      line-height: 1.6
    }

    .layout {
      display: flex;
      min-height: 100vh
    }

    /* ─── Sidebar ─────────────────────────────────────── */
    .sidebar {
      width: 260px;
      background: #fff;
      border-right: 1px solid #e5e7eb;
      padding: 24px 16px;
      display: flex;
      flex-direction: column;
      position: fixed;
      height: 100vh;
      overflow-y: auto
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 24px;
      padding: 0 8px
    }

    .logo-img {
      width: 27px;
      height: 27px;
      object-fit: contain
    }

    .logo-text {
      font-size: 18px;
      font-weight: 700;
      color: #111827
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
      border-radius: 50%
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
      min-width: 0
    }

    .user-name {
      font-size: 14px;
      font-weight: 600;
      color: #111827;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis
    }

    .user-email {
      font-size: 12px;
      color: #6b7280;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis
    }

    .career-score {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 12px;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      margin-bottom: 24px
    }

    .career-score-label {
      font-size: 12px;
      color: #6b7280
    }

    .career-score-value {
      font-size: 14px;
      font-weight: 600;
      color: #0d9488
    }

    nav {
      flex: 1
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
      transition: all .2s
    }

    .nav-item:hover {
      background: #f9fafb;
      color: #111827
    }

    .nav-item.active {
      background: #e0f2f1;
      color: #0d9488
    }

    .nav-item-logout {
      color: #ef4444
    }

    .nav-item-logout:hover {
      background: #fef2f2
    }

    .nav-divider {
      height: 1px;
      background: #e5e7eb;
      margin: 16px 0
    }

    /* ─── Main ────────────────────────────────────────── */
    .main-content {
      flex: 1;
      margin-left: 260px;
      padding: 32px
    }

    .page-header {
      margin-bottom: 32px
    }

    .page-title {
      font-size: 28px;
      font-weight: 700;
      color: #111827
    }

    .page-subtitle {
      font-size: 15px;
      color: #6b7280;
      margin-top: 4px
    }

    /* ─── Alert ───────────────────────────────────────── */
    .alert {
      padding: 14px 18px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0
    }

    .alert-error {
      background: #fef2f2;
      color: #991b1b;
      border: 1px solid #fecaca
    }

    /* ─── Grid Layout ─────────────────────────────────── */
    .profil-grid {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 24px;
      align-items: start
    }

    /* ─── Card ────────────────────────────────────────── */
    .card {
      background: #fff;
      border-radius: 16px;
      border: 1px solid #e5e7eb;
      overflow: hidden
    }

    .card-header {
      padding: 20px 24px;
      border-bottom: 1px solid #f3f4f6
    }

    .card-title {
      font-size: 16px;
      font-weight: 700;
      color: #111827;
      display: flex;
      align-items: center;
      gap: 8px
    }

    .card-body {
      padding: 24px
    }

    /* ─── Avatar Card ─────────────────────────────────── */
    .avatar-section {
      text-align: center;
      padding: 32px 24px 24px
    }

    .big-avatar {
      width: 100px;
      height: 100px;
      background: #0d9488;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700;
      font-size: 32px;
      margin: 0 auto 16px;
      overflow: hidden;
      position: relative;
      border: 4px solid #e0f2f1;
      cursor: pointer;
      transition: transform .2s;
    }

    .big-avatar:hover {
      transform: scale(1.05);
    }

    .big-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .avatar-name {
      font-size: 20px;
      font-weight: 700;
      color: #111827;
      margin-bottom: 4px
    }

    .avatar-email {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 16px
    }

    .upload-foto-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f0fdfa;
      color: #0d9488;
      border: 1px solid #99f6e4;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all .2s
    }

    .upload-foto-btn:hover {
      background: #ccfbf1
    }

    .upload-foto-input {
      display: none
    }

    /* ─── Kelengkapan ─────────────────────────────────── */
    .kelengkapan-section {
      padding: 0 24px 24px
    }

    .kelengkapan-label {
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 8px;
      display: flex;
      justify-content: space-between
    }

    .progress-bar {
      height: 8px;
      background: #f3f4f6;
      border-radius: 4px;
      overflow: hidden
    }

    .progress-fill {
      height: 100%;
      border-radius: 4px;
      background: linear-gradient(90deg, #0d9488, #14b8a6);
      transition: width .6s
    }

    /* ─── Stat Boxes ──────────────────────────────────── */
    .stat-boxes {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      padding: 0 24px 24px
    }

    .stat-box {
      background: #f9fafb;
      border-radius: 10px;
      padding: 12px;
      text-align: center
    }

    .stat-num {
      font-size: 22px;
      font-weight: 700;
      color: #0d9488
    }

    .stat-lbl {
      font-size: 12px;
      color: #6b7280;
      margin-top: 2px
    }

    /* ─── CV Section ──────────────────────────────────── */
    .cv-section {
      padding: 0 24px 24px
    }

    .cv-card {
      background: #f9fafb;
      border: 1px dashed #d1d5db;
      border-radius: 12px;
      padding: 16px;
      text-align: center
    }

    .cv-card.has-cv {
      border-style: solid;
      border-color: #99f6e4;
      background: #f0fdfa
    }

    .cv-icon {
      font-size: 32px;
      margin-bottom: 8px
    }

    .cv-name {
      font-size: 13px;
      font-weight: 600;
      color: #111827;
      margin-bottom: 4px;
      word-break: break-all
    }

    .cv-meta {
      font-size: 12px;
      color: #6b7280;
      margin-bottom: 12px
    }

    .cv-actions {
      display: flex;
      gap: 8px;
      justify-content: center;
      flex-wrap: wrap
    }

    .btn-cv-view {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #0d9488;
      color: #fff;
      padding: 7px 14px;
      border-radius: 7px;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      transition: background .2s
    }

    .btn-cv-view:hover {
      background: #0f766e
    }

    .btn-cv-upload {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #f3f4f6;
      color: #374151;
      padding: 7px 14px;
      border-radius: 7px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: background .2s
    }

    .btn-cv-upload:hover {
      background: #e5e7eb
    }

    .cv-upload-input {
      display: none
    }

    .cv-hint {
      font-size: 11px;
      color: #9ca3af;
      margin-top: 8px
    }

    .btn-hapus-file {
      background: #ef4444;
      color: white;
      border: none;
      padding: 7px 14px;
      border-radius: 7px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: .2s;
    }

    .btn-hapus-file:hover {
      background: #dc2626;
    }

    /* ─── Form ────────────────────────────────────────── */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px
    }

    .form-group.full {
      grid-column: 1/-1
    }

    label {
      font-size: 13px;
      font-weight: 600;
      color: #374151
    }

    input[type=text],
    input[type=email],
    input[type=tel],
    input[type=date],
    input[type=url],
    textarea,
    select {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 14px;
      color: #111827;
      background: #fff;
      transition: all .2s;
      font-family: inherit
    }

    input:focus,
    textarea:focus,
    select:focus {
      outline: none;
      border-color: #0d9488;
      box-shadow: 0 0 0 3px rgba(13, 148, 136, .1)
    }

    textarea {
      resize: vertical;
      min-height: 90px
    }

    .input-hint {
      font-size: 11px;
      color: #9ca3af
    }

    /* ─── Skills Tag Input ────────────────────────────── */
    .skills-input-wrapper {
      position: relative
    }

    .skills-tags-preview {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 8px
    }

    .skill-tag {
      background: #e0f2f1;
      color: #0d9488;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600
    }

    /* ─── Submit ──────────────────────────────────────── */
    .form-footer {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 8px;
      padding-top: 20px;
      border-top: 1px solid #f3f4f6
    }

    .btn-simpan {
      background: #0d9488;
      color: #fff;
      border: none;
      padding: 12px 28px;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background .2s
    }

    .btn-simpan:hover {
      background: #0f766e
    }

    .btn-reset {
      background: #f3f4f6;
      color: #6b7280;
      border: none;
      padding: 12px 24px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: background .2s
    }

    .btn-reset:hover {
      background: #e5e7eb
    }

    /* ─── Sections inside form ────────────────────────── */
    .form-section {
      margin-bottom: 28px
    }

    .form-section-title {
      font-size: 14px;
      font-weight: 700;
      color: #0d9488;
      margin-bottom: 16px;
      padding-bottom: 8px;
      border-bottom: 2px solid #e0f2f1;
      display: flex;
      align-items: center;
      gap: 8px
    }

    @media(max-width:1100px) {
      .profil-grid {
        grid-template-columns: 1fr
      }
    }

    @media(max-width:768px) {
      .sidebar {
        display: none
      }

      .main-content {
        margin-left: 0;
        padding: 16px
      }

      .form-grid {
        grid-template-columns: 1fr
      }
    }
  </style>
</head>

<body>
  <div class="layout">

    <!-- ─── Sidebar ──────────────────────────────────────────────────────────── -->
    <aside class="sidebar">
      <div class="logo">
        <img class="logo-img" src="assets/logo_lokerin.png" alt="L">
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
            <rect x="3" y="3" width="7" height="7" />
            <rect x="14" y="3" width="7" height="7" />
            <rect x="14" y="14" width="7" height="7" />
            <rect x="3" y="14" width="7" height="7" />
          </svg>
          Dashboard
        </a>
        <a href="cari_lowongan.php" class="nav-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
          Cari Lowongan
        </a>
        <a href="lamaran_saya.php" class="nav-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
          Lamaran Saya
        </a>
        <a href="profil_pelamar.php" class="nav-item active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          Profil
        </a>
        <a href="career_roadmap.php" class="nav-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 3v18h18" />
            <path d="m19 9-5 5-4-4-3 3" />
          </svg>
          Career Roadmap
        </a>
        <a href="skill_gap_analyzer.php" class="nav-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3" />
            <path d="M12 1v6m0 6v6m-9-9h6m6 0h6" />
          </svg>
          Skill Gap Analyzer
        </a>
        <a href="pesan_pelamar.php" class="nav-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
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
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
          Keluar
        </a>
      </nav>
    </aside>

    <!-- ─── Main Content ───────────────────────────────────────────────────────── -->
    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Profil Saya</h1>
        <p class="page-subtitle">Lengkapi profil agar lebih mudah ditemukan perusahaan</p>
      </div>

      <?php if ($pesan_sukses): ?>
        <div class="alert alert-success">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          <?= htmlspecialchars($pesan_sukses) ?>
        </div>
      <?php endif; ?>
      <?php if ($pesan_error): ?>
        <div class="alert alert-error">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          <?= htmlspecialchars($pesan_error) ?>
        </div>
      <?php endif; ?>

      <div class="profil-grid">

        <!-- ── Kolom Kiri: Avatar + Stats + CV ──────────────────────────────── -->
        <div>

          <!-- Avatar Card -->
          <div class="card" style="margin-bottom:16px">
            <div class="avatar-section">
              <div class="big-avatar">
                <?php if (!empty($user['foto_profil']) && file_exists('uploads/foto_profil/' . $user['foto_profil'])): ?>
                  <img src="uploads/foto_profil/<?= htmlspecialchars($user['foto_profil']) ?>" alt="foto profil">
                <?php else: ?>
                  <?= $initials ?>
                <?php endif; ?>
              </div>
              <div class="avatar-name"><?= htmlspecialchars($user['nama'] ?? '-') ?></div>
              <div class="avatar-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>

              <!-- Form Upload Foto -->
              <form method="POST" enctype="multipart/form-data">
                <label class="upload-foto-btn" for="foto_profil_input">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                  </svg>
                  Ganti Foto
                </label>
                <input class="upload-foto-input" id="foto_profil_input" type="file" name="foto_profil"
                  accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()">
              </form>

              <!-- form hapus poto -->
              <?php if (!empty($user['foto_profil']) && file_exists('uploads/foto_profil/' . $user['foto_profil'])): ?>
                <form method="POST" style="margin-top:8px;">
                  <button type="submit" name="hapus_foto" class="btn-hapus-file"
                    onclick="return confirm('Yakin hapus foto profil?')">
                    Hapus Foto
                  </button>
                </form>
              <?php endif; ?>
            </div>

            <!-- Kelengkapan Profil -->
            <div class="kelengkapan-section">
              <div class="kelengkapan-label">
                <span>Kelengkapan Profil</span>
                <span style="font-weight:700;color:#0d9488"><?= $kelengkapan ?>%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $kelengkapan ?>%"></div>
              </div>
              <?php if ($kelengkapan < 100): ?>
                <p style="font-size:12px;color:#6b7280;margin-top:8px">Lengkapi profil untuk meningkatkan peluang diterima
                </p>
              <?php else: ?>
                <p style="font-size:12px;color:#0d9488;margin-top:8px;font-weight:600">✓ Profil sudah lengkap!</p>
              <?php endif; ?>
            </div>

            <!-- Stat Boxes -->
            <div class="stat-boxes">
              <div class="stat-box">
                <div class="stat-num"><?= $total_lamaran ?></div>
                <div class="stat-lbl">Total Lamaran</div>
              </div>
              <div class="stat-box">
                <div class="stat-num"><?= $stats['accepted'] ?></div>
                <div class="stat-lbl">Diterima</div>
              </div>
              <div class="stat-box">
                <div class="stat-num"><?= $stats['interview'] ?></div>
                <div class="stat-lbl">Interview</div>
              </div>
              <div class="stat-box">
                <div class="stat-num"><?= $stats['review'] ?></div>
                <div class="stat-lbl">Direview</div>
              </div>
            </div>
          </div>

          <!-- CV Card -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                  <polyline points="10 9 9 9 8 9" />
                </svg>
                Curriculum Vitae
              </div>
            </div>
            <div class="card-body" style="padding-top:16px">
              <div class="cv-card <?= !empty($user['cv_path']) ? 'has-cv' : '' ?>">
                <div class="cv-icon"><?= !empty($user['cv_path']) ? '📄' : '📁' ?></div>
                <?php if (!empty($user['cv_path']) && file_exists('uploads/cv/' . $user['cv_path'])): ?>
                  <div class="cv-name"><?= htmlspecialchars($user['cv_path']) ?></div>
                  <div class="cv-meta">CV tersimpan</div>
                  <div class="cv-actions">
                    <a href="uploads/cv/<?= htmlspecialchars($user['cv_path']) ?>" target="_blank" class="btn-cv-view">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      Lihat CV
                    </a>
                    <form method="POST" enctype="multipart/form-data" style="display:inline">
                      <label class="btn-cv-upload" for="cv_file_input">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                          <polyline points="17 8 12 3 7 8" />
                          <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        Ganti CV
                      </label>
                      <input class="cv-upload-input" id="cv_file_input" type="file" name="cv_file"
                        accept="application/pdf" onchange="this.form.submit()">
                    </form>
                  </div>
                  <form method="POST" style="display:inline">
                    <button type="submit" name="hapus_cv" class="btn-hapus-file"
                      onclick="return confirm('Yakin hapus CV?')">
                      Hapus CV
                    </button>
                  </form>
                <?php else: ?>
                  <div class="cv-meta" style="margin-bottom:12px">Belum ada CV yang diupload</div>
                  <form method="POST" enctype="multipart/form-data">
                    <label class="btn-cv-upload" for="cv_file_new" style="background:#0d9488;color:#fff;padding:9px 18px">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                      </svg>
                      Upload CV (PDF)
                    </label>
                    <input class="cv-upload-input" id="cv_file_new" type="file" name="cv_file" accept="application/pdf"
                      onchange="this.form.submit()">
                  </form>
                <?php endif; ?>
                <div class="cv-hint">Format PDF, maks. 5 MB</div>
              </div>
            </div>
          </div>

        </div><!-- end kolom kiri -->

        <!-- ── Kolom Kanan: Form Edit Profil ─────────────────────────────────── -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
              </svg>
              Edit Profil Lengkap
            </div>
          </div>
          <div class="card-body">
            <form method="POST">
              <!-- Informasi Dasar -->
              <div class="form-section">
                <div class="form-section-title">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  Informasi Dasar
                </div>
                <div class="form-grid">
                  <div class="form-group">
                    <label>Nama Lengkap <span style="color:#ef4444">*</span></label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama'] ?? '') ?>"
                      placeholder="Nama lengkap Anda" required>
                  </div>
                  <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir"
                      value="<?= htmlspecialchars($user['tanggal_lahir'] ?? '') ?>">
                  </div>
                  <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="tel" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>"
                      placeholder="08xxxxxxxxxx">
                  </div>
                  <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" name="lokasi" value="<?= htmlspecialchars($user['lokasi'] ?? '') ?>"
                      placeholder="Kota, Provinsi">
                  </div>
                  <div class="form-group full">
                    <label>Tentang Saya (Bio)</label>
                    <textarea name="bio"
                      placeholder="Ceritakan tentang diri Anda, pengalaman, dan tujuan karir..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>

              <!-- Pendidikan & Pengalaman -->
              <div class="form-section">
                <div class="form-section-title">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                    <path d="M6 12v5c3 3 9 3 12 0v-5" />
                  </svg>
                  Pendidikan & Pengalaman
                </div>
                <div class="form-grid">
                  <div class="form-group full">
                    <label>Pendidikan Terakhir</label>
                    <input type="text" name="pendidikan" value="<?= htmlspecialchars($user['pendidikan'] ?? '') ?>"
                      placeholder="Contoh: S1 Teknik Informatika - Universitas XYZ (2024)">
                  </div>
                  <div class="form-group full">
                    <label>Pengalaman Kerja</label>
                    <textarea name="pengalaman"
                      placeholder="Ceritakan pengalaman kerja atau magang yang pernah Anda lakukan..."><?= htmlspecialchars($user['pengalaman'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>

              <!-- Skills -->
              <div class="form-section">
                <div class="form-section-title">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon
                      points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                  </svg>
                  Skills
                </div>
                <div class="form-group">
                  <label>Skills (pisahkan dengan koma)</label>
                  <input type="text" name="skills" id="skills_input"
                    value="<?= htmlspecialchars($user['skills'] ?? '') ?>"
                    placeholder="Contoh: PHP, MySQL, HTML, CSS, JavaScript">
                  <span class="input-hint">Tulis skill Anda, pisahkan dengan tanda koma</span>
                  <div class="skills-tags-preview" id="skills_preview"></div>
                </div>
              </div>

              <!-- Link & Portfolio -->
              <div class="form-section">
                <div class="form-section-title">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                  </svg>
                  Link & Portfolio
                </div>
                <div class="form-grid">
                  <div class="form-group">
                    <label>LinkedIn</label>
                    <input type="url" name="linkedin" value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>"
                      placeholder="https://linkedin.com/in/username">
                  </div>
                  <div class="form-group">
                    <label>Portfolio / GitHub</label>
                    <input type="url" name="portfolio" value="<?= htmlspecialchars($user['portfolio'] ?? '') ?>"
                      placeholder="https://github.com/username">
                  </div>
                </div>
              </div>

              <div class="form-footer">
                <button type="reset" class="btn-reset">Reset</button>
                <button type="submit" name="simpan_profil" class="btn-simpan">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                  </svg>
                  Simpan Profil
                </button>
              </div>
            </form>
          </div>
        </div><!-- end kolom kanan -->

      </div><!-- end profil-grid -->
    </main>
  </div>

  <script>
    // Skills tag preview
    function updateSkillTags() {
      const val = document.getElementById('skills_input').value;
      const prev = document.getElementById('skills_preview');
      prev.innerHTML = '';
      val.split(',').forEach(s => {
        s = s.trim();
        if (s) {
          const span = document.createElement('span');
          span.className = 'skill-tag';
          span.textContent = s;
          prev.appendChild(span);
        }
      });
    }
    document.getElementById('skills_input').addEventListener('input', updateSkillTags);
    updateSkillTags(); // run on load
  </script>

  <!-- lihat foto profil -->
  <div id="avatarModal" class="avatar-modal">
    <img id="avatarModalImg" src="" alt="Foto Profil">
  </div>
  <script>
    const avatars = document.querySelectorAll(
      ".sidebar-avatar img, .big-avatar img"
    );

    const modal = document.getElementById("avatarModal");
    const modalImg = document.getElementById("avatarModalImg");

    avatars.forEach(avatar => {

      avatar.addEventListener("click", () => {

        modal.style.display = "flex";
        modalImg.src = avatar.src;

      });

    });

    modal.addEventListener("click", () => {

      modal.style.display = "none";

    });

    document.addEventListener("keydown", (e) => {

      if (e.key === "Escape") {
        modal.style.display = "none";
      }

    });
  </script>
</body>

</html>