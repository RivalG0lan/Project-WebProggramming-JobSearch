<?php

session_start();

include 'config/koneksi.php';

if(!isset($_SESSION['id_user'])){

    header("Location: login_pelamar.php");

}

$id_lowongan = (int) $_GET['id'];

$query = mysqli_query($conn,

"SELECT * FROM lowongan

WHERE id_lowongan='$id_lowongan'");

$data = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Detail Lowongan</title>

<style>

body{

    font-family: Arial, sans-serif;
    background: #f3f4f6;
    padding: 30px;
}

.card{

    background: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 700px;
    margin: auto;
}

h1{

    margin-bottom: 10px;
}

.meta{

    color: #6b7280;
    margin-bottom: 20px;
}

.salary{

    color: #0d9488;
    font-weight: bold;
    margin-bottom: 20px;
}

.desc{

    line-height: 1.7;
    margin-bottom: 30px;
}

.btn{

    display: inline-block;
    padding: 12px 20px;
    background: #0d9488;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}

.btn:hover{

    background: #0f766e;
}

</style>
</head>

<body>

<div class="card">

<h1>

<?= $data['judul']; ?>

</h1>

<div class="meta">

📁 <?= $data['kategori']; ?>

<br><br>

📍 <?= $data['lokasi']; ?>

</div>

<div class="salary">

💰 Rp <?= $data['gaji']; ?>

</div>

<div class="desc">

<?= nl2br($data['deskripsi']); ?>

</div>

<a

href="lamar.php?id=<?= $data['id_lowongan']; ?>"

onclick="return confirm('Yakin ingin melamar pekerjaan ini?')"

class="btn">

Lamar Sekarang

</a>

</div>

</body>
</html>