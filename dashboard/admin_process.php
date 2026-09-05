<?php
session_start();
include("../config/koneksi.php");

/* =======================
   CEK LOGIN ADMIN
======================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

/* =======================
   HELPER FLASH
======================= */
function flash($msg) {
    $_SESSION['flash'] = $msg;
    header("Location: admin.php");
    exit;
}

/* =======================
   TAMBAH USER
======================= */
if (isset($_POST['tambah_user'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $kelas    = trim($_POST['kelas']);
    $role     = $_POST['role'];

    if ($nama === '' || $username === '' || $kelas === '') {
        flash("❌ Semua field wajib diisi!");
    }

    if (!in_array($role, ['guru','siswa'])) {
        flash("❌ Role tidak valid!");
    }

    // password default
    $password = password_hash("123456", PASSWORD_DEFAULT);

    // cek username
    $cek = mysqli_query($koneksi,
        "SELECT id FROM users WHERE username='".mysqli_real_escape_string($koneksi,$username)."'"
    );

    if (mysqli_num_rows($cek) > 0) {
        flash("❌ Username sudah digunakan!");
    }

    mysqli_query($koneksi,"
        INSERT INTO users (nama, username, password, role, kelas)
        VALUES (
            '".mysqli_real_escape_string($koneksi,$nama)."',
            '".mysqli_real_escape_string($koneksi,$username)."',
            '$password',
            '$role',
            '".mysqli_real_escape_string($koneksi,$kelas)."'
        )
    ");

    flash("✅ Akun $role berhasil ditambahkan (password default: 123456)");
}

/* =======================
   UPDATE USER
======================= */
if (isset($_POST['update_user'])) {
    $id       = intval($_POST['id']);
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $kelas    = trim($_POST['kelas']);

    if ($nama === '' || $username === '') {
        flash("❌ Nama dan Username tidak boleh kosong!");
    }

    // cek username ganda
    $cek = mysqli_query($koneksi,"
        SELECT id FROM users 
        WHERE username='".mysqli_real_escape_string($koneksi,$username)."'
        AND id!='$id'
    ");

    if (mysqli_num_rows($cek) > 0) {
        flash("❌ Username sudah dipakai akun lain!");
    }

    mysqli_query($koneksi,"
        UPDATE users SET
            nama='".mysqli_real_escape_string($koneksi,$nama)."',
            username='".mysqli_real_escape_string($koneksi,$username)."',
            kelas='".mysqli_real_escape_string($koneksi,$kelas)."'
        WHERE id='$id'
    ");

    flash("✅ Data akun berhasil diperbarui.");
}

/* =======================
   RESET PASSWORD
======================= */
if (isset($_POST['reset'])) {
    $id = intval($_POST['id']);
    $new_pass = password_hash("123456", PASSWORD_DEFAULT);

    mysqli_query($koneksi,"
        UPDATE users SET password='$new_pass' WHERE id='$id'
    ");

    flash("✅ Password berhasil direset ke 123456");
}

/* =======================
   HAPUS USER
======================= */
if (isset($_POST['hapus'])) {
    $id = intval($_POST['id']);

    $cek = mysqli_query($koneksi,"SELECT role, kelas FROM users WHERE id='$id'");
    $user = mysqli_fetch_assoc($cek);

    if (!$user) {
        flash("❌ Data tidak ditemukan!");
    }

    if ($user['role'] === 'admin') {
        flash("❌ Akun admin tidak bisa dihapus!");
    }

    // jika guru → hapus semua data kelas
    if ($user['role'] === 'guru') {
        $kelas = $user['kelas'];

        mysqli_query($koneksi,"DELETE FROM materi WHERE kelas='$kelas'");
        mysqli_query($koneksi,"DELETE FROM tugas WHERE kelas='$kelas'");

        $siswa = mysqli_query($koneksi,"
            SELECT id FROM users WHERE role='siswa' AND kelas='$kelas'
        ");

        while ($s = mysqli_fetch_assoc($siswa)) {
            mysqli_query($koneksi,"DELETE FROM jawaban_tugas WHERE user_id='{$s['id']}'");
            mysqli_query($koneksi,"DELETE FROM users WHERE id='{$s['id']}'");
        }
    }

    mysqli_query($koneksi,"DELETE FROM users WHERE id='$id'");
    flash("✅ Akun berhasil dihapus.");
}

/* =======================
   KENAIKAN KELAS
======================= */
if (isset($_POST['naik_kelas'])) {
    $asal   = trim($_POST['kelas_asal']);
    $tujuan = trim($_POST['kelas_tujuan']);

    if ($asal === '' || $tujuan === '') {
        flash("❌ Kelas asal & tujuan wajib diisi!");
    }

    if ($asal === $tujuan) {
        flash("❌ Kelas asal & tujuan tidak boleh sama!");
    }

    mysqli_query($koneksi,"
        UPDATE users SET kelas='$tujuan'
        WHERE role='siswa' AND kelas='$asal'
    ");

    $jumlah = mysqli_affected_rows($koneksi);
    flash("✅ $jumlah siswa berhasil naik kelas.");
}

header("Location: admin.php");
exit;
