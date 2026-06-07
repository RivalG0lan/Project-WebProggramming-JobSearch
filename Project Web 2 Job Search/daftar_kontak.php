<?php
/**
 * API: Daftar Kontak Chat
 * Method: GET
 * Returns: JSON array of contacts with latest message preview
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Belum login']);
    exit;
}

include 'config/koneksi.php';

$id_user = (int) $_SESSION['id_user'];

// Ambil semua orang yang pernah berkirim pesan dengan user ini
// Beserta pesan terakhir dan jumlah unread
$sql = "
    SELECT
        u.id_user,
        u.nama,
        u.foto_profil,
        u.role,
        u.bidang_keahlian,
        last_msg.isi_pesan AS pesan_terakhir,
        last_msg.tanggal_kirim AS waktu_terakhir,
        last_msg.id_pengirim AS pengirim_terakhir,
        COALESCE(unread_count.total, 0) AS unread
    FROM users u
    INNER JOIN (
        SELECT
            CASE WHEN id_pengirim = ? THEN id_penerima ELSE id_pengirim END AS contact_id,
            MAX(id_pesan) AS last_id
        FROM pesan
        WHERE id_pengirim = ? OR id_penerima = ?
        GROUP BY contact_id
    ) contacts ON u.id_user = contacts.contact_id
    INNER JOIN pesan last_msg ON last_msg.id_pesan = contacts.last_id
    LEFT JOIN (
        SELECT id_pengirim, COUNT(*) AS total
        FROM pesan
        WHERE id_penerima = ? AND status = 'unread'
        GROUP BY id_pengirim
    ) unread_count ON unread_count.id_pengirim = u.id_user
    ORDER BY last_msg.tanggal_kirim DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id_user, $id_user, $id_user, $id_user);
$stmt->execute();
$result = $stmt->get_result();

$contacts = [];
while ($row = $result->fetch_assoc()) {
    $nama = $row['nama'] ?? 'User';
    $initials = strtoupper(substr($nama, 0, 1)) . strtoupper(substr($nama, 1, 1));
    if (strlen($nama) <= 1) $initials = strtoupper(substr($nama, 0, 1));

    $contacts[] = [
        'id_user'           => (int)$row['id_user'],
        'nama'              => htmlspecialchars($nama),
        'initials'          => $initials,
        'foto_profil'       => $row['foto_profil'] ?? null,
        'role'              => $row['role'],
        'bidang_keahlian'   => $row['bidang_keahlian'] ?? '',
        'pesan_terakhir'    => htmlspecialchars(mb_strimwidth($row['pesan_terakhir'], 0, 60, '...')),
        'waktu_terakhir'    => $row['waktu_terakhir'],
        'waktu_relatif'     => waktu_relatif($row['waktu_terakhir']),
        'unread'            => (int)$row['unread'],
        'pengirim_terakhir' => (int)$row['pengirim_terakhir']
    ];
}
$stmt->close();

echo json_encode(['success' => true, 'contacts' => $contacts]);

/**
 * Mengubah timestamp menjadi waktu relatif (contoh: "5 menit lalu")
 */
function waktu_relatif($timestamp) {
    $now  = new DateTime();
    $time = new DateTime($timestamp);
    $diff = $now->diff($time);

    if ($diff->days === 0) {
        if ($diff->h === 0 && $diff->i === 0) return 'Baru saja';
        if ($diff->h === 0) return $diff->i . ' menit lalu';
        return $diff->h . ' jam lalu';
    }
    if ($diff->days === 1) return 'Kemarin';
    if ($diff->days < 7) return $diff->days . ' hari lalu';
    return $time->format('d M Y');
}
