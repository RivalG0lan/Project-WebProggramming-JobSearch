<?php
/**
 * API: Kirim Pesan
 * Method: POST
 * Params: id_penerima, isi_pesan
 * Returns: JSON { success: bool, message: string }
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Belum login']);
    exit;
}

include 'config/koneksi.php';

$id_pengirim = (int) $_SESSION['id_user'];
$id_penerima = (int) ($_POST['id_penerima'] ?? 0);
$isi_pesan   = trim($_POST['isi_pesan'] ?? '');

if ($id_penerima <= 0 || $isi_pesan === '') {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Pastikan penerima ada
$cek = $conn->prepare("SELECT id_user FROM users WHERE id_user = ?");
$cek->bind_param("i", $id_penerima);
$cek->execute();
$cek->store_result();
if ($cek->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Penerima tidak ditemukan']);
    exit;
}
$cek->close();

// Insert pesan
$stmt = $conn->prepare(
    "INSERT INTO pesan (id_pengirim, id_penerima, isi_pesan) VALUES (?, ?, ?)"
);
$stmt->bind_param("iis", $id_pengirim, $id_penerima, $isi_pesan);

if ($stmt->execute()) {
    echo json_encode([
        'success'   => true,
        'message'   => 'Pesan terkirim',
        'id_pesan'  => $stmt->insert_id,
        'tanggal'   => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan']);
}
$stmt->close();
