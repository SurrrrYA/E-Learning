<?php
include("../config/koneksi.php");

if (!isset($_GET['token'])) {
    die("❌ Token tidak ditemukan.");
}

$token = $_GET['token'];

// ambil token dari DB
$stmt = $koneksi->prepare("SELECT * FROM password_resets WHERE token=? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$reset = $result->fetch_assoc();

if (!$reset) {
    die("❌ Token tidak ditemukan.");
}

// cek expire manual pakai PHP
$currentTime = time();
$expireTime  = strtotime($reset['expires_at']);

if ($expireTime < $currentTime) {
    die("❌ Token sudah kadaluarsa.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        echo "❌ Password minimal 6 karakter.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // update password admin
        $stmt2 = $koneksi->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt2->bind_param("si", $hashed, $reset['user_id']);
        $stmt2->execute();

        // hapus token
        $koneksi->query("DELETE FROM password_resets WHERE user_id='{$reset['user_id']}'");

        echo "✅ Password berhasil direset. Silakan <a href='../index.php'>login</a>.";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password Admin</h2>
    <form method="POST">
        <label>Password Baru: <input type="password" name="password" required></label>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
