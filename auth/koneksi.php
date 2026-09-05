<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "elearning";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Samakan timezone PHP & MySQL
date_default_timezone_set("Asia/Jakarta");
mysqli_query($koneksi, "SET time_zone = '+07:00'");
?>
