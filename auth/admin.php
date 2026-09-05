<?php
session_start();
include("../config/koneksi.php");

// Pastikan sudah login
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php?error=notlogin");
    exit;
}

// Cek session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=session_expired");
    exit;
}
$_SESSION['last_activity'] = time();

// Pastikan role admin
if ($_SESSION['role'] !== 'admin') {
    echo "❌ Akses ditolak. Halaman ini khusus Admin.";
    exit;
}

// Tambah guru
if (isset($_POST['tambah_guru'])) {
    $username = trim($_POST['username']);
    $kelas    = trim($_POST['kelas']);
    $password = password_hash("123456", PASSWORD_DEFAULT);

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $msg_tambah = "Username sudah digunakan!";
    } else {
        mysqli_query($koneksi, "INSERT INTO users (username, password, role, kelas) VALUES ('$username','$password','guru','$kelas')");
        $msg_tambah = "Akun guru berhasil ditambahkan. Password default: 123456";
    }
}

// Reset password
if (isset($_POST['reset'])) {
    $id = intval($_POST['id']);
    $new_pass = password_hash("123456", PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE users SET password='$new_pass' WHERE id='$id'");
    $msg_reset = "Password user ID $id berhasil direset ke '123456'";
}

// Hapus akun & semua data terkait
if (isset($_POST['hapus'])) {
    $id = intval($_POST['id']);
    $cekUser = mysqli_query($koneksi, "SELECT role, kelas FROM users WHERE id='$id' LIMIT 1");
    $dataUser = mysqli_fetch_assoc($cekUser);

    if ($dataUser && $dataUser['role'] != 'admin') {
        $kelasUser = $dataUser['kelas'];

        if ($dataUser['role'] == 'guru') {
            // --- Hapus semua data di kelas guru ini ---
            mysqli_query($koneksi, "DELETE FROM materi WHERE kelas='$kelasUser'");
            mysqli_query($koneksi, "DELETE FROM tugas WHERE kelas='$kelasUser'");

            // Cari semua siswa di kelas itu
            $siswa = mysqli_query($koneksi, "SELECT id FROM users WHERE role='siswa' AND kelas='$kelasUser'");
            while ($s = mysqli_fetch_assoc($siswa)) {
                $sid = $s['id'];
                mysqli_query($koneksi, "DELETE FROM jawaban_tugas WHERE user_id='$sid'");
                mysqli_query($koneksi, "DELETE FROM password_resets WHERE user_id='$sid'");
                mysqli_query($koneksi, "DELETE FROM users WHERE id='$sid'");
            }

            // Hapus reset token guru & guru itu sendiri
            mysqli_query($koneksi, "DELETE FROM password_resets WHERE user_id='$id'");
            mysqli_query($koneksi, "DELETE FROM users WHERE id='$id'");

            $msg_hapus = "✅ Semua data guru & kelas '$kelasUser' berhasil dihapus (guru, siswa, tugas, materi, dan jawaban).";
        } else {
            // Jika siswa biasa
            mysqli_query($koneksi, "DELETE FROM jawaban_tugas WHERE user_id='$id'");
            mysqli_query($koneksi, "DELETE FROM password_resets WHERE user_id='$id'");
            mysqli_query($koneksi, "DELETE FROM users WHERE id='$id'");
            $msg_hapus = "✅ Akun siswa ID $id berhasil dihapus.";
        }
    } else {
        $msg_hapus = "Tidak bisa menghapus akun admin!";
    }
}

// Ambil kelas unik
$kelasResult = mysqli_query($koneksi, "SELECT DISTINCT kelas FROM users WHERE kelas IS NOT NULL ORDER BY kelas");
$kelasList = [];
while ($k = mysqli_fetch_assoc($kelasResult)) {
    $kelasList[] = $k['kelas'];
}

// Filter kelas
$filterKelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$sql = "SELECT * FROM users";
if ($filterKelas) {
    $filterKelas = mysqli_real_escape_string($koneksi, $filterKelas);
    $sql .= " WHERE kelas='$filterKelas'";
}
$sql .= " ORDER BY role, username";
$result = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {background-color:#f4f6f8;}
.top-bar {display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;}
.logout-btn {background:#e74c3c;color:white;padding:8px 15px;border-radius:5px;text-decoration:none;}
.logout-btn:hover {background:#c0392b;}
.card-header {background:#2563eb;color:white;font-weight:bold;}
.btn-reset {background:#2196F3;color:white;border:none;padding:5px 10px;border-radius:4px;}
.btn-reset:hover {background:#0b7dda;}
.btn-hapus {background:#e74c3c;color:white;border:none;padding:5px 10px;border-radius:4px;}
.btn-hapus:hover {background:#c0392b;}
.message {padding:10px;border-radius:5px;text-align:center;margin-bottom:15px;}
.message-success {background:#d1fae5;color:#065f46;}
.message-error {background:#fee2e2;color:#b91c1c;}
.table-responsive {overflow-x:auto;}
@media(max-width:576px){
    .top-bar h1 {font-size:1.2rem;}
    .btn-reset, .btn-hapus {padding:3px 6px;font-size:0.8rem;}
    .form-control, .form-select {font-size:0.9rem;}
}
</style>
</head>
<body>
<div class="container py-4">

    <div class="top-bar">
        <h1>Dashboard Admin</h1>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if(!empty($msg_tambah)) : ?>
        <div class="message <?= strpos($msg_tambah,'berhasil')!==false ? 'message-success':'message-error' ?>"><?= $msg_tambah ?></div>
    <?php endif; ?>
    <?php if(!empty($msg_reset)) : ?>
        <div class="message message-success"><?= $msg_reset ?></div>
    <?php endif; ?>
    <?php if(!empty($msg_hapus)) : ?>
        <div class="message message-success"><?= $msg_hapus ?></div>
    <?php endif; ?>

    <!-- Form Tambah Guru -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">Tambah Akun Guru</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="col-md-5">
                    <input type="text" name="kelas" class="form-control" placeholder="Kelas (misal 1a)" required>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" name="tambah_guru" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Kelas -->
    <form method="GET" class="mb-3 text-center">
        <label>Pilih Kelas: 
            <select name="kelas" onchange="this.form.submit()" class="form-select d-inline-block w-auto">
                <option value="">-- Semua --</option>
                <?php foreach($kelasList as $k): ?>
                    <option value="<?= htmlspecialchars($k) ?>" <?= ($filterKelas==$k?'selected':'') ?>><?= htmlspecialchars($k) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <!-- Tabel akun -->
    <div class="card shadow-sm p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td>
                            <?php if($row['role'] != 'admin'): ?>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="reset" class="btn-reset btn-sm">Reset Password</button>
                                </form>
                                <form method="POST" style="display:inline" onsubmit="return confirm('⚠️ Hapus akun ini dan SEMUA data kelas terkait?')">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="hapus" class="btn-hapus btn-sm">Hapus</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted fst-italic">(Admin)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
