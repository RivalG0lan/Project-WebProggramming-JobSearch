<?php

$id_perusahaan = (int)$_SESSION['id_user'];

$q_user = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE id_user='$id_perusahaan'"
);

$user_perusahaan = mysqli_fetch_assoc($q_user);

$fields_cek_perusahaan = [
    'nama',
    'email',
    'telepon',
    'lokasi',
    'bio',
    'foto_profil'
];

$isi_perusahaan = 0;

foreach ($fields_cek_perusahaan as $field) {
    if (!empty($user_perusahaan[$field])) {
        $isi_perusahaan++;
    }
}

$employer_score = round(
    ($isi_perusahaan / count($fields_cek_perusahaan)) * 100
);