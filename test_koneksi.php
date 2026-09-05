<?php
include("config/koneksi.php");

if ($koneksi) {
    echo "Koneksi berhasil ke database!";
} else {
    echo "Koneksi gagal.";
}
?>
