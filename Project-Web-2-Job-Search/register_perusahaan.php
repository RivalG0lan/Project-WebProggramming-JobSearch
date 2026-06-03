<?php

include 'config/koneksi.php';

if(isset($_POST['register'])){

    $nama = $_POST['nama'];

    $email = $_POST['email'];

    $password =
    md5($_POST['password']);

    $query = mysqli_query($conn,

    "INSERT INTO users
    (nama,email,password,role)

    VALUES

    ('$nama',
    '$email',
    '$password',
    'perusahaan')
    ");

    if($query){

        echo "Register berhasil";

    } else {

        echo "Register gagal";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Diri - Lokerin</title>
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

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* Left Side - Form */
        .left-side {
            background: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-container {
            max-width: 400px;
            margin: 0 auto;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 40px;
        }

        .logo-img {
            width: 27px;
            height: 27px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 32px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 0;
            margin-bottom: 32px;
            border-bottom: 2px solid #f3f4f6;
        }

        .tab {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            background: none;
            border: none;
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            position: relative;
            transition: color 0.2s;
        }

        .tab.active {
            color: #0d9488;
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #0d9488;
        }

        .tab-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e0f2f1;
            color: #0d9488;
            font-size: 12px;
            margin-right: 8px;
        }

        .tab.active .tab-icon {
            background: #0d9488;
            color: white;
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            color: #374151;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            transition: all 0.2s;
            background: white;
        }

        input:focus {
            outline: none;
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        input::placeholder {
            color: #9ca3af;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            flex: 1;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-primary {
            background: #14b8a6;
            color: white;
        }

        .btn-primary:hover {
            background: #0d9488;
        }

        /* Footer Link */
        .footer-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #6b7280;
        }

        .footer-link a {
            color: #0d9488;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }

        /* Right Side - Hero */
        .right-side {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);
            padding: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .right-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 500px;
        }

        .hero-content h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 16px;
            color: rgba(255,255,255,0.9);
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
            }

            .right-side {
                display: none;
            }

            .left-side {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Side - Form -->
        <div class="left-side">
            <div class="form-container">
                <div class="logo">
                    <img class="logo-img" src="assets/logo_lokerin.png" alt="L">
                    <span class="logo-text">LokerIn</span>
                </div>

                <h1>Buat Akun Baru</h1>
                <p class="subtitle">Bergabunglah dengan Lokerin dan temukan peluang karir terbaik</p>

                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab active">
                        <span class="tab-icon">1</span>
                        Pilih Tipe
                    </button>
                    <button class="tab active">
                        <span class="tab-icon">2</span>
                        Data Diri
                    </button>
                </div>

                <!-- Registration Form -->
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Perusahaan</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <input type="text" name="nama" placeholder="PT Contoh Indonesia" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                            <input type="email" name="email" placeholder="email@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" name="confirm_password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="btn-group">
                        <a href="tipe_akun.html" class="btn btn-secondary">Kembali</a>
                        <button
                        type="submit"
                        name="register"
                        class="btn btn-primary">

                            Daftar

                            <svg style="display: inline; margin-left: 4px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>

                        </button>
                    </div>
                </form>

                <div class="footer-link">
                    Sudah punya akun? <a href="login_pelamar.php">Masuk</a>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero -->
        <div class="right-side">
            <div class="hero-content">
                <h2>Bergabung dengan 100K+ Pengguna</h2>
                <p>Temukan peluang karir terbaik atau kandidat ideal dengan teknologi AI matching kami.</p>
            </div>
        </div>
    </div>
</body>
</html>