<?php

session_start();

include 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {

    header("Location: login_perusahaan.php");

}

if ($_SESSION['role'] != 'perusahaan') {

    header("Location: login_perusahaan.php");

}

$id_lowongan = $_GET['id'];

$ambil = mysqli_query(
    $conn,

    "SELECT * FROM lowongan
WHERE id_lowongan='$id_lowongan'"
);

$data = mysqli_fetch_assoc($ambil);

if (isset($_POST['post_lowongan'])) {

    $judul =
        $_POST['judul'];

    $kategori =
        $_POST['kategori'];

    $lokasi =
        $_POST['lokasi'];

    $gaji =
        $_POST['gaji'];

    $deskripsi =
        $_POST['deskripsi'];

    $query = mysqli_query(
        $conn,

        "UPDATE lowongan SET

    judul='$judul',
    kategori='$kategori',
    lokasi='$lokasi',
    gaji='$gaji',
    deskripsi='$deskripsi'

    WHERE id_lowongan='$id_lowongan'
    "
    );

    if ($query) {

        header("Location: kelola_lowongan.php");

    } else {

        echo 'Gagal update';
    }
}




?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Edit Lowongan - Lokerin</title>
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

        .company-info {
            flex: 1;
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

        .greeting {
            font-size: 16px;
            color: #6b7280;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
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
        }

        .btn-post:hover {
            background: #0f766e;
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
            transition: all 0.2s;
        }

        .icon-btn:hover {
            background: #f9fafb;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 32px;
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

        /* Step Progress */
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 32px;
            background: white;
            padding: 24px 32px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            position: relative;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
            z-index: 1;
        }

        .step-item.active .step-number {
            background: #0d9488;
            color: white;
        }

        .step-item.completed .step-number {
            background: #0d9488;
            color: white;
        }

        .step-item.inactive .step-number {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .step-label {
            font-size: 14px;
            font-weight: 500;
        }

        .step-item.active .step-label {
            color: #111827;
        }

        .step-item.completed .step-label {
            color: #111827;
        }

        .step-item.inactive .step-label {
            color: #9ca3af;
        }

        /* Content Area */
        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        /* Form Section */
        .form-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }

        .required {
            color: #ef4444;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            transition: all 0.2s;
            background: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: #0d9488;
            background: white;
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            transition: all 0.2s;
            background: #f9fafb;
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #0d9488;
            background: white;
        }

        .char-count {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .input-with-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .input-with-icon .form-input {
            padding-left: 44px;
        }

        /* Type Buttons */
        .type-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .type-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .type-btn:hover {
            border-color: #0d9488;
            color: #0d9488;
        }

        .type-btn.active {
            background: #0d9488;
            color: white;
            border-color: #0d9488;
        }

        /* Select Dropdown */
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s;
        }

        .form-select:focus {
            outline: none;
            border-color: #0d9488;
            background: white;
        }

        /* Checkbox */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-group label {
            font-size: 14px;
            color: #6b7280;
            cursor: pointer;
        }

        /* Dynamic Input */
        .dynamic-input-group {
            margin-bottom: 16px;
        }

        .input-with-add {
            display: flex;
            gap: 12px;
        }

        .btn-add {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: #0d9488;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .btn-add:hover {
            background: #0f766e;
        }

        .added-items {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .added-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f0fdfa;
            border: 1px solid #ccfbf1;
            border-radius: 8px;
            font-size: 13px;
            color: #0d9488;
        }

        .remove-item {
            cursor: pointer;
            color: #0d9488;
            font-weight: 600;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-secondary {
            padding: 12px 24px;
            border-radius: 8px;
            border: 1px solid #0d9488;
            background: white;
            color: #0d9488;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #f0fdfa;
        }

        .btn-primary {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            background: #0d9488;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #0f766e;
        }

        /* Sidebar Score */
        .score-sidebar {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            height: fit-content;
            position: sticky;
            top: 24px;
        }

        .score-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .score-icon {
            color: #f59e0b;
        }

        .score-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .score-display {
            text-align: center;
            margin-bottom: 20px;
        }

        .score-number {
            font-size: 56px;
            font-weight: 700;
            color: #ef4444;
            line-height: 1;
        }

        .score-total {
            font-size: 24px;
            color: #9ca3af;
        }

        .score-bar {
            height: 8px;
            background: #f3f4f6;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .score-fill {
            height: 100%;
            background: #ef4444;
            width: 0%;
            border-radius: 4px;
            transition: width 0.3s;
        }

        .score-message {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .checklist {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #6b7280;
        }

        .checklist-item svg {
            flex-shrink: 0;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }

            .score-sidebar {
                position: relative;
                top: 0;
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

            .form-row {
                grid-template-columns: 1fr;
            }

            .step-progress {
                flex-direction: column;
                gap: 16px;
            }

            .step-item::after {
                display: none;
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
                <div class="company-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                </div>
                <div class="company-info">
                    <div class="company-name">

                        <?= $_SESSION['nama']; ?>

                    </div>
                    <div class="company-industry">

                        <?= $_SESSION['email']; ?>

                    </div>
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
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>

                <a href="posting_lowongan.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="12" r="8"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                    </svg>
                    Posting Lowongan
                </a>

                <a href="kelola_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Kelola Lowongan
                </a>

                <a href="kandidat.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Kandidat
                </a>

                <a href="profil_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Profil Perusahaan
                </a>

                <a href="analytics.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    Analytics
                </a>

                <a href="pesan_perusahaan.html" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Pesan
                </a>

                <div class="nav-divider"></div>

                <a href="pengaturan_perusahaan.html" class="nav-item">
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
                <div class="greeting">

                    Selamat datang,
                    <?= $_SESSION['nama']; ?>

                </div>

                <div class="header-actions">
                    <button class="btn-post">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                        Post Lowongan
                    </button>

                    <button class="icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </button>

                    <button class="icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Edit Lowongan Baru</h1>
                <p class="page-subtitle">Perbarui informasi lowongan pekerjaan</p>
            </div>

            <!-- Step Progress -->
            <div class="step-progress">
                <div class="step-item active" id="step1">
                    <div class="step-number">1</div>
                    <div class="step-label">Info Dasar</div>
                </div>
                <div class="step-item inactive" id="step2">
                    <div class="step-number">2</div>
                    <div class="step-label">Deskripsi</div>
                </div>
                <div class="step-item inactive" id="step3">
                    <div class="step-number">3</div>
                    <div class="step-label">Skills & Benefit</div>
                </div>
            </div>

            <!-- Content -->
            <div class="content-wrapper">
                <!-- Form Section -->
                <div class="form-section">

                    <form method="POST">
                        <!-- Step 1: Info Dasar -->
                        <div id="form-step1">
                            <h2 class="section-title">Informasi Dasar</h2>

                            <div class="form-group">
                                <label class="form-label">Judul Posisi <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                    <input type="text" name="judul" value="<?= $data['judul']; ?>" class="form-input"
                                        placeholder="e.g. Senior Frontend Developer">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Departemen</label>
                                    <input type="text" name="kategori" value="<?= $data['kategori']; ?>"
                                        class="form-input" placeholder="e.g. Engineering">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Lokasi <span class="required">*</span></label>
                                    <div class="input-with-icon">
                                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <input type="text" name="lokasi" value="<?= $data['lokasi']; ?>"
                                            class="form-input" placeholder="e.g. Jakarta">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tipe Pekerjaan <span class="required">*</span></label>
                                    <div class="type-buttons">
                                        <button type="button" class="type-btn active">Full-time</button>
                                        <button type="button" class="type-btn">Part-time</button>
                                        <button type="button" class="type-btn">Remote</button>
                                        <button type="button" class="type-btn">Hybrid</button>
                                        <button type="button" class="type-btn">Contract</button>
                                        <button type="button" class="type-btn">Internship</button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Level <span class="required">*</span></label>
                                    <select class="form-select">
                                        <option>Mid</option>
                                        <option>Entry</option>
                                        <option>Senior</option>
                                        <option>Lead</option>
                                        <option>Manager</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Range Gaji</label>
                                <div class="form-row">
                                    <div class="input-with-icon">
                                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="1" x2="12" y2="23"></line>
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                        </svg>
                                        <input type="text" name="gaji" value="<?= $data['gaji']; ?>" class="form-input"
                                            placeholder="Min (e.g. 20000000)">
                                    </div>
                                    <div class="input-with-icon">
                                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="1" x2="12" y2="23"></line>
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                        </svg>
                                        <input type="text" class="form-input" placeholder="Max (e.g. 35000000)">
                                    </div>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" id="hide-salary">
                                    <label for="hide-salary">Sembunyikan range gaji dari pelamar</label>
                                </div>
                            </div>

                            <div class="form-actions">
                                <div></div>
                                <button type="button" class="btn-primary" onclick="nextStep(2)">
                                    Selanjutnya
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Deskripsi -->
                        <div id="form-step2" style="display: none;">
                            <h2 class="section-title">Deskripsi & Requirements</h2>

                            <div class="form-group">
                                <label class="form-label">Deskripsi Pekerjaan <span class="required">*</span></label>
                                <textarea name="deskripsi" class="form-textarea"><?= $data['deskripsi']; ?></textarea>
                                <div class="char-count">0/500 karakter</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Requirements / Kualifikasi</label>
                                <div class="input-with-add">
                                    <input type="text" class="form-input" id="requirement-input"
                                        placeholder="e.g. Minimal 3 tahun pengalaman...">
                                    <button type="button" class="btn-add" onclick="addRequirement()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                    </button>
                                </div>
                                <div class="added-items" id="requirements-list"></div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-secondary" onclick="prevStep(1)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <line x1="19" y1="12" x2="5" y2="12"></line>
                                        <polyline points="12 19 5 12 12 5"></polyline>
                                    </svg>
                                    Sebelumnya
                                </button>
                                <button type="button" class="btn-primary" onclick="nextStep(3)">
                                    Selanjutnya
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Skills & Benefit -->
                        <div id="form-step3" style="display: none;">
                            <h2 class="section-title">Skills & Benefits</h2>

                            <div class="form-group">
                                <label class="form-label">Skills yang Dibutuhkan</label>
                                <div class="input-with-add">
                                    <input type="text" class="form-input" id="skill-input"
                                        placeholder="e.g. React, TypeScript...">
                                    <button type="button" class="btn-add" onclick="addSkill()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                    </button>
                                </div>
                                <div class="added-items" id="skills-list"></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Benefits & Fasilitas</label>
                                <div class="input-with-add">
                                    <input type="text" class="form-input" id="benefit-input"
                                        placeholder="e.g. Health Insurance, Remote Work...">
                                    <button type="button" class="btn-add" onclick="addBenefit()">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                    </button>
                                </div>
                                <div class="added-items" id="benefits-list"></div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-secondary" onclick="prevStep(2)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <line x1="19" y1="12" x2="5" y2="12"></line>
                                        <polyline points="12 19 5 12 12 5"></polyline>
                                    </svg>
                                    Sebelumnya
                                </button>
                                <button type="submit" name="post_lowongan" class="btn-primary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Update Lowongan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Score Sidebar -->
                <div class="score-sidebar">
                    <div class="score-header">
                        <svg class="score-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                            </polygon>
                        </svg>
                        <span class="score-title">Job Attractiveness Score</span>
                    </div>

                    <div class="score-display">
                        <span class="score-number" id="score-value">0</span>
                        <span class="score-total">/100</span>
                    </div>

                    <div class="score-bar">
                        <div class="score-fill" id="score-bar"></div>
                    </div>

                    <p class="score-message">Lengkapi informasi untuk hasil lebih baik</p>

                    <div class="checklist">
                        <div class="checklist-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke="#e5e7eb"></circle>
                            </svg>
                            Judul posisi
                        </div>
                        <div class="checklist-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke="#e5e7eb"></circle>
                            </svg>
                            Deskripsi lengkap
                        </div>
                        <div class="checklist-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke="#e5e7eb"></circle>
                            </svg>
                            Range gaji terlihat
                        </div>
                        <div class="checklist-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke="#e5e7eb"></circle>
                            </svg>
                            Min. 3 skills
                        </div>
                        <div class="checklist-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke="#e5e7eb"></circle>
                            </svg>
                            Min. 2 benefits
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let currentStep = 1;
        let requirements = [];
        let skills = [];
        let benefits = [];

        // Step Navigation
        function nextStep(step) {
            document.getElementById('form-step' + currentStep).style.display = 'none';
            document.getElementById('form-step' + step).style.display = 'block';

            document.getElementById('step' + currentStep).classList.remove('active');
            document.getElementById('step' + currentStep).classList.add('completed');

            document.getElementById('step' + step).classList.remove('inactive');
            document.getElementById('step' + step).classList.add('active');

            currentStep = step;
        }

        function prevStep(step) {
            document.getElementById('form-step' + currentStep).style.display = 'none';
            document.getElementById('form-step' + step).style.display = 'block';

            document.getElementById('step' + currentStep).classList.remove('active');
            document.getElementById('step' + currentStep).classList.add('inactive');

            document.getElementById('step' + step).classList.remove('completed');
            document.getElementById('step' + step).classList.add('active');

            currentStep = step;
        }

        // Type Buttons
        document.querySelectorAll('.type-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                this.classList.toggle('active');
            });
        });

        // Add Requirement
        function addRequirement() {
            const input = document.getElementById('requirement-input');
            const value = input.value.trim();

            if (value) {
                requirements.push(value);
                updateList('requirements-list', requirements, 'requirement');
                input.value = '';
            }
        }

        // Add Skill
        function addSkill() {
            const input = document.getElementById('skill-input');
            const value = input.value.trim();

            if (value) {
                skills.push(value);
                updateList('skills-list', skills, 'skill');
                input.value = '';
                updateScore();
            }
        }

        // Add Benefit
        function addBenefit() {
            const input = document.getElementById('benefit-input');
            const value = input.value.trim();

            if (value) {
                benefits.push(value);
                updateList('benefits-list', benefits, 'benefit');
                input.value = '';
                updateScore();
            }
        }

        // Update List
        function updateList(listId, array, type) {
            const list = document.getElementById(listId);
            list.innerHTML = '';

            array.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'added-item';
                div.innerHTML = `
                    ${item}
                    <span class="remove-item" onclick="removeItem('${type}', ${index})">×</span>
                `;
                list.appendChild(div);
            });
        }

        // Remove Item
        function removeItem(type, index) {
            if (type === 'requirement') {
                requirements.splice(index, 1);
                updateList('requirements-list', requirements, 'requirement');
            } else if (type === 'skill') {
                skills.splice(index, 1);
                updateList('skills-list', skills, 'skill');
                updateScore();
            } else if (type === 'benefit') {
                benefits.splice(index, 1);
                updateList('benefits-list', benefits, 'benefit');
                updateScore();
            }
        }

        // Update Score
        function updateScore() {
            let score = 0;

            // Check skills (min 3)
            if (skills.length >= 3) score += 20;

            // Check benefits (min 2)
            if (benefits.length >= 2) score += 20;

            document.getElementById('score-value').textContent = score;
            document.getElementById('score-bar').style.width = score + '%';

            // Update color based on score
            const scoreBar = document.getElementById('score-bar');
            if (score >= 60) {
                scoreBar.style.background = '#0d9488';
            } else if (score >= 30) {
                scoreBar.style.background = '#f59e0b';
            } else {
                scoreBar.style.background = '#ef4444';
            }
        }

        // Character Count
        document.querySelector('.form-textarea').addEventListener('input', function () {
            const count = this.value.length;
            this.parentElement.querySelector('.char-count').textContent = count + '/500 karakter';
        });

        // Enter key for adding items
        document.getElementById('requirement-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addRequirement();
            }
        });

        document.getElementById('skill-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSkill();
            }
        });

        document.getElementById('benefit-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBenefit();
            }
        });
    </script>
</body>

</html>