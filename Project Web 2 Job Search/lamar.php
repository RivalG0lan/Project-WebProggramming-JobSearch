<?php

session_start();

include 'config/koneksi.php';

if(!isset($_SESSION['id_user'])){

    header("Location: login_pelamar.php");

}

$id_pelamar = $_SESSION['id_user'];

$id_lowongan = $_GET['id'];

$cek = mysqli_query($conn,

"SELECT * FROM lamaran

WHERE id_pelamar='$id_pelamar'

AND id_lowongan='$id_lowongan'");

if(mysqli_num_rows($cek) > 0){

    echo "

    <script>

    alert('Kamu sudah melamar lowongan ini');

    window.location='cari_lowongan.php';

    </script>

    ";

}else{

    $query = mysqli_query($conn,

    "INSERT INTO lamaran
    (
    id_lowongan,
    id_pelamar,
    status
    )

    VALUES
    (
    '$id_lowongan',
    '$id_pelamar',
    'dikirim'
    )");

    if($query){

        header("Location: lamaran_saya.php");

    }else{

        echo "Gagal melamar";
    }
}
?>