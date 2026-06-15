# LokerIn - Job Portal Platform

**Mata Kuliah Web Programming | D3 Teknik Informatika USU | Angkatan 2024**

Kelompok 3

---

## Anggota Tim

| NIM | Nama |
|---|---|
| 241712009 | Maulia Revani Putri |
| 241712015 | Auzan Taris |
| 241712026 | Khalif Al Malik Yales |
| 241712033 | Annisa Jannati 'Adnin |
| 241712040 | Nadya Putri Anggina Siregar |
| 241712043 | Rivaldo Nainggolan |

---

## Tentang Proyek

LokerIn adalah platform portal kerja berbasis web yang menghubungkan pelamar kerja dengan perusahaan secara langsung. Sistem ini dirancang untuk menyederhanakan proses rekrutmen dari sisi pelamar maupun perusahaan, dengan fitur manajemen lamaran, analisis profil kandidat, dan sistem pesan real-time antar pengguna.

---

## Fitur Utama

**Untuk Pelamar**

- Registrasi dan autentikasi akun pelamar
- Pencarian dan filter lowongan kerja berdasarkan kata kunci dan lokasi
- Halaman detail lowongan dengan tombol lamar langsung
- Dasbor lamaran pribadi dengan pelacakan status (Dikirim, Review, Interview, Accepted, Ditolak)
- Manajemen profil lengkap mencakup foto, CV (PDF), bio, pendidikan, pengalaman, dan skill
- Career Roadmap berdasarkan bidang keahlian yang dipilih pengguna
- Skill Gap Analyzer yang membandingkan skill pengguna dengan kebutuhan industri
- Sistem pesan (chat) real-time dengan perusahaan
- Halaman pengaturan akun termasuk ganti password dan hapus akun

**Untuk Perusahaan**

- Registrasi dan autentikasi akun perusahaan
- Dasbor perusahaan dengan ringkasan statistik rekrutmen
- Formulir posting lowongan multi-langkah (Info Dasar, Deskripsi, Skills)
- Pengelolaan lowongan aktif dengan opsi edit dan hapus
- Halaman kandidat dengan filter berdasarkan lowongan dan status lamaran
- Pembaruan status lamaran langsung dari dasbor kandidat
- Halaman profil perusahaan yang dapat diisi lengkap (logo, deskripsi, industri, dll.)
- Halaman Analytics rekrutmen mencakup grafik tren lamaran, distribusi status, dan performa per lowongan
- Employer Score berdasarkan kelengkapan profil perusahaan
- Sistem pesan real-time dengan kandidat

---

## Teknologi yang Digunakan

| Lapisan | Teknologi |
|---|---|
| Backend | PHP 8.2 (Native) |
| Database | MySQL / MariaDB 10.4 |
| Server | Apache (XAMPP / LAMP Stack) |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Styling | Custom CSS dengan CSS Variables |
| Grafik | Chart.js (via CDN) |
| Peta / Maps | Tidak digunakan |
| Upload File | PHP native file handling |

---

## Struktur Database

Database bernama `portal_kerja` terdiri dari empat tabel utama.

**Tabel `users`**
Menyimpan seluruh data pengguna baik pelamar maupun perusahaan dalam satu tabel dengan kolom `role` sebagai pembeda. Kolom pelamar mencakup pendidikan, pengalaman, skill, CV, dan bidang keahlian. Kolom perusahaan mencakup deskripsi, website, alamat, industri, ukuran, dan tahun berdiri.

**Tabel `lowongan`**
Menyimpan data lowongan kerja yang diposting oleh perusahaan, mencakup judul, kategori, lokasi, gaji, deskripsi, skill yang dibutuhkan, dan status aktif/tutup.

**Tabel `lamaran`**
Menyimpan relasi antara pelamar dan lowongan, dengan kolom status yang berisi salah satu nilai: `dikirim`, `review`, `interview`, `accepted`, atau `rejected`.

**Tabel `pesan`**
Menyimpan riwayat pesan antar pengguna dengan kolom pengirim, penerima, isi pesan, waktu kirim, dan status baca.

---

## Instalasi dan Konfigurasi

**Prasyarat**

- XAMPP (atau server LAMP/LEMP setara) dengan PHP 8.x dan MariaDB/MySQL
- Web browser modern

**Langkah Instalasi**

1. Clone atau salin folder proyek ke direktori `htdocs` pada instalasi XAMPP:

```
C:\xampp\htdocs\lokerin\
```

2. Jalankan Apache dan MySQL melalui XAMPP Control Panel.

3. Buka phpMyAdmin di `http://localhost/phpmyadmin`, buat database baru bernama `portal_kerja`.

4. Import file SQL yang disediakan (berisi struktur tabel dan data awal) ke dalam database tersebut.

5. Pastikan konfigurasi koneksi pada `config/koneksi.php` sesuai dengan lingkungan lokal:

```php
$conn = mysqli_connect("localhost", "root", "", "portal_kerja");
```

6. Buat folder untuk penyimpanan file upload jika belum ada:

```
lokerin/uploads/foto_profil/
lokerin/uploads/foto_perusahaan/
lokerin/uploads/cv/
```

7. Akses aplikasi melalui browser di `http://localhost/lokerin/`.

---

## Akun Demo

Data awal yang tersedia setelah import SQL:

| Role | Email | Password |
|---|---|---|
| Pelamar | dnda@gmail.com | (sesuai hash di database) |
| Perusahaan | rvnn@gmail.com | (sesuai hash di database) |

Password default menggunakan hash MD5. Untuk kebutuhan pengujian, lakukan registrasi akun baru melalui halaman `/tipe_akun.html`.

---

## Struktur Direktori

```
lokerin/
    config/
        koneksi.php
    uploads/
        foto_profil/
        foto_perusahaan/
        cv/
    assets/
        logo_lokerin.png
        icon_head_lokerin.png
    index.html
    tipe_akun.html
    login_pelamar.php
    login_perusahaan.php
    register_pelamar.php
    register_perusahaan.php
    logout.php
    dashboard_pelamar.php
    dashboard_perusahaan.php
    cari_lowongan.php
    detail_lowongan.php
    lamar.php
    batal_lamaran.php
    lamaran_saya.php
    profil_pelamar.php
    profil_perusahaan.php
    pengaturan_pelamar.php
    pengaturan_perusahaan.php
    career_roadmap.php
    skill_gap_analyzer.php
    pesan_pelamar.php
    pesan_perusahaan.php
    ambil_pesan.php
    kirim_pesan.php
    daftar_kontak.php
    posting_lowongan.php
    kelola_lowongan.php
    edit_lowongan.php
    kandidat.php
    update_status_lamaran.php
    analytics.php
    employer_score.php
```

---

## Alur Penggunaan

**Alur Pelamar**

```
Registrasi -> Login -> Lengkapi Profil & Upload CV
    -> Cari Lowongan -> Lihat Detail -> Lamar
    -> Pantau Status di "Lamaran Saya"
    -> Chat dengan Perusahaan
    -> Lihat Career Roadmap & Skill Gap Analyzer
```

**Alur Perusahaan**

```
Registrasi -> Login -> Lengkapi Profil Perusahaan
    -> Posting Lowongan -> Kelola Lowongan
    -> Review Kandidat di Halaman "Kandidat"
    -> Update Status Lamaran (Review / Interview / Accepted / Ditolak)
    -> Chat dengan Pelamar
    -> Pantau Analytics Rekrutmen
```

---

## Catatan Pengembangan

- Sistem autentikasi menggunakan session PHP. Password baru dienkripsi dengan `password_hash()` (bcrypt). Password lama pada data awal menggunakan MD5 dan tetap didukung saat login melalui pengecekan ganda.
- Sistem pesan menggunakan polling berbasis `setInterval` setiap 2 detik untuk simulasi real-time tanpa WebSocket.
- Career Roadmap dan Skill Gap Analyzer sepenuhnya berbasis data yang tersimpan di kolom `bidang_keahlian` dan `skills` pada tabel `users`, tanpa dependensi API eksternal.
- Upload file foto profil dibatasi 2 MB (JPG/PNG/WEBP) dan CV dibatasi 5 MB (PDF). File lama dihapus otomatis saat file baru diupload.
- Employer Score dihitung dari persentase kelengkapan kolom profil perusahaan (nama, email, telepon, lokasi, bio, foto).

---

## Lisensi

Proyek ini dibuat untuk keperluan akademik dalam rangka tugas mata kuliah Pemrograman Web 1, Program Studi D3 Teknik Informatika, Universitas Sumatera Utara. Tidak ditujukan untuk penggunaan komersial.
