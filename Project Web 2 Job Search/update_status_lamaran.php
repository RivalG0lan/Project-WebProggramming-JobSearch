<?php

session_start();
include 'config/koneksi.php';

if(!isset($_SESSION['id_user'])){
    header("Location: login_perusahaan.php");
    exit;
}

if($_SESSION['role'] != 'perusahaan'){
    header("Location: login_perusahaan.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: kandidat.php");
    exit;
}

$id_perusahaan = $_SESSION['id_user'];
$id_lamaran    = (int)$_POST['id_lamaran'];
$status_baru   = $_POST['status'];
$redirect      = isset($_POST['redirect']) ? $_POST['redirect'] : 'kandidat.php';

// Validasi status yang diizinkan
$allowed = ['dikirim','review','interview','accepted','rejected'];
if(!in_array($status_baru, $allowed)){
    header("Location: kandidat.php?error=invalid_status");
    exit;
}

// Pastikan lamaran ini milik lowongan yang dimiliki perusahaan ini (keamanan)
$cek = mysqli_query($conn,
    "SELECT lmr.id_lamaran
     FROM lamaran lmr
     INNER JOIN lowongan l ON lmr.id_lowongan = l.id_lowongan
     WHERE lmr.id_lamaran='$id_lamaran'
     AND l.id_perusahaan='$id_perusahaan'
     LIMIT 1"
);

if(mysqli_num_rows($cek) === 0){
    // Lamaran tidak ditemukan atau bukan milik perusahaan ini
    header("Location: kandidat.php?error=forbidden");
    exit;
}

// Lakukan update
$status_esc = mysqli_real_escape_string($conn, $status_baru);
$query = mysqli_query($conn,
    "UPDATE lamaran SET status='$status_esc'
     WHERE id_lamaran='$id_lamaran'"
);

if($query){
    // Redirect balik ke halaman sebelumnya dengan pesan sukses
    // Tambahkan parameter updated=1 ke URL redirect
    $sep = (strpos($redirect, '?') !== false) ? '&' : '?';
    header("Location: " . $redirect . $sep . "updated=1");
} else {
    header("Location: kandidat.php?error=db");
}
exit;
?>
