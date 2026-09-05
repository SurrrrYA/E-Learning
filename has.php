<?php
// Password asli yang ingin di-hash
$password = "Admin123SDnegeri2pbr"; // Ganti dengan password yang ingin kamu gunakan

// Hash password menggunakan bcrypt
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Tampilkan hash yang dihasilkan
echo "Hash dari password: " . $hashed_password;
?>
