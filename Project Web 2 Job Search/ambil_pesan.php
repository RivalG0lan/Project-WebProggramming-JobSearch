<?php
/**
 * API: Ambil Pesan (Riwayat Chat)
 * Method: GET
 * Params: id_lawan (ID lawan bicara), after (optional, id_pesan terakhir utk polling)
 * Returns: JSON array of messages
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Belum login']);
    exit;
}

include 'config/koneksi.php';

$id_user  = (int) $_SESSION['id_user'];
$id_lawan = (int) ($_GET['id_lawan'] ?? 0);
$after    = (int) ($_GET['after'] ?? 0);

if ($id_lawan <= 0) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
    exit;
}

// Ambil riwayat percakapan antara user dan lawan
if ($after > 0) {
    // Polling: hanya pesan baru setelah id tertentu
    $stmt = $conn->prepare("
        SELECT id_pesan, id_pengirim, id_penerima, isi_pesan, tanggal_kirim, status
        FROM pesan
        WHERE id_pesan > ?
          AND (
            (id_pengirim = ? AND id_penerima = ?)
            OR (id_pengirim = ? AND id_penerima = ?)
          )
        ORDER BY tanggal_kirim ASC
    ");
    $stmt->bind_param("iiiii", $after, $id_user, $id_lawan, $id_lawan, $id_user);
} else {
    // Load awal: ambil 50 pesan terakhir
    $stmt = $conn->prepare("
        SELECT id_pesan, id_pengirim, id_penerima, isi_pesan, tanggal_kirim, status
        FROM pesan
        WHERE (id_pengirim = ? AND id_penerima = ?)
           OR (id_pengirim = ? AND id_penerima = ?)
        ORDER BY tanggal_kirim ASC
        LIMIT 50
    ");
    $stmt->bind_param("iiii", $id_user, $id_lawan, $id_lawan, $id_user);
}

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id_pesan'      => (int)$row['id_pesan'],
        'id_pengirim'   => (int)$row['id_pengirim'],
        'id_penerima'   => (int)$row['id_penerima'],
        'isi_pesan'     => htmlspecialchars($row['isi_pesan']),
        'tanggal_kirim' => $row['tanggal_kirim'],
        'status'        => $row['status'],
        'is_mine'       => ((int)$row['id_pengirim'] === $id_user)
    ];
}
$stmt->close();

// Tandai pesan dari lawan sebagai 'read'
$update = $conn->prepare("
    UPDATE pesan SET status = 'read'
    WHERE id_pengirim = ? AND id_penerima = ? AND status = 'unread'
");
$update->bind_param("ii", $id_lawan, $id_user);
$update->execute();
$update->close();

echo json_encode(['success' => true, 'messages' => $messages]);
