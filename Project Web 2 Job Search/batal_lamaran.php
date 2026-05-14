<?php

session_start();

include 'config/koneksi.php';

if(!isset($_SESSION['id_user'])){

    header("Location: login_pelamar.php");

}

$id_pelamar = $_SESSION['id_user'];

$id_lamaran = $_GET['id'];

$query = mysqli_query($conn,

"DELETE FROM lamaran

WHERE id_lamaran='$id_lamaran'

AND id_pelamar='$id_pelamar'");

if($query){

    echo "

    <script>

    alert('Lamaran berhasil dibatalkan');

    window.location='lamaran_saya.php';

    </script>

    ";

}else{

    echo "Gagal membatalkan lamaran";
}
?>