<?php
include("config/koneksi.php");

$username = 'gurukelas1'; // Ganti sesuai user yang kamu masukkan

$query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    echo "Username: " . $data['username'] . "<br>";
    echo "Password Hash: " . $data['password'] . "<br>";
    echo "Role: " . $data['role'] . "<br>";
    echo "Kelas: " . $data['kelas'] . "<br>";
} else {
    echo "User tidak ditemukan.";
}
?>
