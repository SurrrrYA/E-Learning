<?php
session_start();
include("../config/koneksi.php");

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Aman: gunakan prepared statement
    $stmt = $koneksi->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data && password_verify($password, $data['password'])) {
        // Regenerasi session ID untuk mencegah session fixation
        session_regenerate_id(true);

        // Simpan data user ke session
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['role']    = $data['role'];
        $_SESSION['kelas']   = $data['kelas'];

        // Set session timeout (30 menit)
        $_SESSION['last_activity'] = time();
        $_SESSION['expire_time']   = 1800; // 1800 detik = 30 menit

        // Arahkan ke dashboard sesuai role
        if ($data['role'] == 'admin') {
            header('Location: ../dashboard/admin.php');
        } elseif ($data['role'] == 'guru') {
            header('Location: ../dashboard/guru.php');
        } elseif ($data['role'] == 'siswa') {
            header('Location: ../dashboard/siswa.php');
        } else {
            header("Location: ../index.php?error=2");
        }
        exit;
    } else {
        // Username / password salah
        header("Location: ../index.php?error=1");
        exit;
    }
}
?>
