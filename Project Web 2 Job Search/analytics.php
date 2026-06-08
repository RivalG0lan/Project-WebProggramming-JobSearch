<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login_perusahaan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Lokerin</title>
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

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 24px 32px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .header-left h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .header-left p {
            font-size: 14px;
            color: #6b7280;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .date-filter {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
        }

        .btn-export {
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

        .btn-export:hover {
            background: #0f766e;
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.blue {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-icon.green {
            background: #f0fdf4;
            color: #22c55e;
        }

        .stat-icon.purple {
            background: #faf5ff;
            color: #a855f7;
        }

        .stat-icon.orange {
            background: #fff7ed;
            color: #f59e0b;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .stat-trend.up {
            background: #dcfce7;
            color: #16a34a;
        }

        .stat-trend.down {
            background: #fee2e2;
            color: #dc2626;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .stat-subtext {
            font-size: 12px;
            color: #9ca3af;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .card-tabs {
            display: flex;
            gap: 8px;
        }

        .tab-btn {
            padding: 6px 12px;
            background: transparent;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            background: #f9fafb;
        }

        .tab-btn.active {
            background: #0d9488;
            color: white;
            border-color: #0d9488;
        }

        .chart-container {
            height: 300px;
            background: linear-gradient(to bottom, #f9fafb 0%, #ffffff 100%);
            border-radius: 8px;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            padding: 20px;
            gap: 8px;
        }

        .chart-bar {
            flex: 1;
            background: #0d9488;
            border-radius: 4px 4px 0 0;
            position: relative;
            transition: all 0.3s;
        }

        .chart-bar:hover {
            opacity: 0.8;
        }

        .chart-bar:nth-child(1) {
            height: 45%;
            background: #14b8a6;
        }

        .chart-bar:nth-child(2) {
            height: 60%;
            background: #0d9488;
        }

        .chart-bar:nth-child(3) {
            height: 75%;
            background: #14b8a6;
        }

        .chart-bar:nth-child(4) {
            height: 55%;
            background: #0d9488;
        }

        .chart-bar:nth-child(5) {
            height: 85%;
            background: #14b8a6;
        }

        .chart-bar:nth-child(6) {
            height: 70%;
            background: #0d9488;
        }

        .chart-bar:nth-child(7) {
            height: 90%;
            background: #14b8a6;
        }

        .progress-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .progress-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .progress-label {
            font-size: 13px;
            color: #374151;
            font-weight: 500;
        }

        .progress-value {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .progress-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s;
        }

        .progress-fill.blue {
            background: #3b82f6;
        }

        .progress-fill.green {
            background: #22c55e;
        }

        .progress-fill.purple {
            background: #a855f7;
        }

        .progress-fill.orange {
            background: #f59e0b;
        }

        .progress-fill.red {
            background: #ef4444;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #f9fafb;
        }

        .job-title-cell {
            font-weight: 600;
            color: #111827;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge.warning {
            background: #fef3c7;
            color: #d97706;
        }

        .badge.info {
            background: #dbeafe;
            color: #2563eb;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .metric-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        .metric-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .metric-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .metric-icon-circle.teal {
            background: #e0f2f1;
            color: #0d9488;
        }

        .metric-icon-circle.blue {
            background: #eff6ff;
            color: #3b82f6;
        }

        .metric-icon-circle.indigo {
            background: #eef2ff;
            color: #6366f1;
        }

        .metric-info {
            flex: 1;
        }

        .metric-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .metric-value-large {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        .metric-details {
            display: flex;
            gap: 16px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }

        .metric-detail {
            flex: 1;
        }

        .metric-detail-label {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .metric-detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        @media (max-width: 1400px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1200px) {
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }

            .metrics-grid {
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

            .stats-overview {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
            }

            .date-filter {
                flex: 1;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="logo">
                <img class="logo-img" src="assets/logo_lokerin.png" alt="L">
                <span class="logo-text">LokerIn</span>
            </div>

            <div class="company-profile">
                <div class="company-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                </div>
                <div class="company-info">
                    <div class="company-name">
                        <?= htmlspecialchars($_SESSION['nama']) ?>
                    </div>
                    <div class="company-industry">
                        <?= htmlspecialchars($_SESSION['email']) ?>
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

                <a href="posting_lowongan.php" class="nav-item">
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

                <a href="kandidat.php" class="nav-item">
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

                <a href="analytics.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    Analytics
                </a>

                <a href="pesan_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Pesan
                </a>

                <div class="nav-divider"></div>

                <a href="pengaturan_perusahaan.php" class="nav-item">
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

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <h1>Analytics</h1>
                    <p>Pantau performa rekrutmen perusahaan Anda</p>
                </div>

                <div class="header-actions">
                    <div class="date-filter">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        30 Hari Terakhir
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                    <button class="btn-export">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Export Report
                    </button>
                </div>
            </header>

            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon blue">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <div class="stat-trend up">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                            12.5%
                        </div>
                    </div>
                    <div class="stat-label">Total Views</div>
                    <div class="stat-value">45,231</div>
                    <div class="stat-subtext">+5,432 dari bulan lalu</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                        </div>
                        <div class="stat-trend up">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                            8.2%
                        </div>
                    </div>
                    <div class="stat-label">Total Aplikasi</div>
                    <div class="stat-value">347</div>
                    <div class="stat-subtext">+28 dari bulan lalu</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon purple">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="stat-trend down">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            2.1%
                        </div>
                    </div>
                    <div class="stat-label">Conversion Rate</div>
                    <div class="stat-value">7.6%</div>
                    <div class="stat-subtext">-0.8% dari bulan lalu</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon orange">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="stat-trend up">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                            15.3%
                        </div>
                    </div>
                    <div class="stat-label">Avg. Time to Hire</div>
                    <div class="stat-value">18 hari</div>
                    <div class="stat-subtext">3 hari lebih