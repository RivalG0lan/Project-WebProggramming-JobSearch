<?php

session_start();

include 'config/koneksi.php';

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password_input = $_POST['password'];

    // Gunakan prepared statement untuk keamanan dari SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='perusahaan'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $password_db = $data['password'];
        $is_valid = false;

        // Cek dengan password_verify (untuk akun baru) atau md5 (untuk akun lama)
        if (password_verify($password_input, $password_db)) {
            $is_valid = true;
        } elseif (md5($password_input) === $password_db) {
            $is_valid = true;
        }

        if ($is_valid) {
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['email'] = $data['email'];

            header("Location: dashboard_perusahaan.php");
            exit;
        } else {
            echo "Email atau password salah";
        }
    } else {
        echo "Email atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun - Lokerin</title>
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

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* Left Side - Hero */
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
            left: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
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
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        /* Right Side - Form */
        .left-side {
            background: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-container {
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
            text-decoration: none;
            color: inherit;
        }

        .logo-img {
            width: 27px;
            height: 27px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 32px;
        }

        /* User Type Toggle */
        .user-type-toggle {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
        }

        .type-btn {
            flex: 1;
            padding: 12px 24px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
            color: #6b7280;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .type-btn:hover {
            border-color: #0d9488;
            color: #0d9488;
        }

        .type-btn.active {
            border-color: #0d9488;
            background: #f0fdfa;
            color: #0d9488;
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
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        input {
            width: 100%;
            padding: 13px 16px 13px 48px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
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

        /* Forgot Password */
        .forgot-password {
            text-align: right;
            margin-top: -12px;
            margin-bottom: 24px;
        }

        .forgot-password a {
            color: #0d9488;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 14px 24px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: #0d9488;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-login:hover {
            background: #0f766e;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
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
            font-weight: 600;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            color: #9ca3af;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 16px;
        }

        /* Social Login */
        .social-login {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            text-decoration: none;
        }

        .social-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
            }

            .left-side {
                display: none;
            }

            .right-side {
                padding: 40px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Left Side - Hero -->
        <div class="right-side">
            <div class="hero-content">
                <h2>Selamat Datang Kembali!</h2>
                <p>Masuk ke akun Anda untuk melanjutkan perjalanan karir atau menemukan kandidat terbaik.</p>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="left-side">
            <div class="form-container">
                <a href="index.html" class="logo">
                    <svg class="back-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>

                    <img class="logo-img" src="assets/logo_lokerin.png" alt="L">
                    <span class="logo-text">LokerIn</span>
                </a>

                <h1>Masuk ke Akun</h1>
                <p class="subtitle">Masukkan email dan password untuk melanjutkan</p>

                <!-- User Type Toggle -->
                <div class="user-type-toggle">
                    <a href="login_pelamar.php" class="type-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Pelamar
                    </a>

                    <a href="login_perusahaan.php" class="type-btn active">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                        Perusahaan
                    </a>
                </div>

                <!-- Login Form -->
                <form method="POST">
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                            <input type="email" name="email" placeholder="email@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="forgot-password">
                        <a href="">Lupa password?</a>
                    </div>

                    <button type="submit" name="login" class="btn-login">

                        Masuk

                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M5 12h14"></path>
                            <path d="m12 5 7 7-7 7"></path>
                        </svg>

                    </button>
                </form>

                <div class="divider">
                    <span>Atau masuk dengan</span>
                </div>

                <div class="social-login">
                    <a href="" class="social-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                fill="#4285F4" />
                            <path
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                fill="#34A853" />
                            <path
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                fill="#FBBC05" />
                            <path
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                fill="#EA4335" />
                        </svg>
                        Google
                    </a>
                    <a href="" class="social-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#1877F2">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        Facebook
                    </a>
                </div>

                <div class="footer-link">
                    Belum punya akun? <a href="tipe_akun.html">Daftar Gratis</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle user type
        document.querySelectorAll('.type-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>

</html>