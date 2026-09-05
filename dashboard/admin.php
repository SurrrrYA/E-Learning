<?php
session_start();
include("../config/koneksi.php");

/* =======================
   CEK LOGIN & ROLE
======================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

/* =======================
   DATA KELAS
======================= */
$kelasResult = mysqli_query($koneksi,
    "SELECT DISTINCT kelas FROM users WHERE kelas IS NOT NULL ORDER BY kelas"
);
$kelasList = [];
while ($k = mysqli_fetch_assoc($kelasResult)) {
    $kelasList[] = $k['kelas'];
}

/* =======================
   FILTER USER
======================= */
$filterKelas = $_GET['kelas'] ?? '';
$sql = "SELECT * FROM users";
if ($filterKelas) {
    $sql .= " WHERE kelas='".mysqli_real_escape_string($koneksi,$filterKelas)."'";
}
$sql .= " ORDER BY role, nama";
$result = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f4f6f8 }
.top-bar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px
}
.logout-btn {
    background:#e74c3c;
    color:#fff;
    padding:8px 15px;
    border-radius:5px;
    text-decoration:none
}
.card-header {
    background:#2563eb;
    color:#fff;
    font-weight:bold
}
.message {
    padding:10px;
    border-radius:5px;
    text-align:center;
    margin-bottom:15px
}
.message-success {
    background:#d1fae5;
    color:#065f46
}
</style>
</head>

<body>

<div class="container-fluid px-4 py-4">

<!-- ================= TOP BAR ================= -->
<div class="top-bar">
    <h1>Dashboard Admin</h1>
    <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

<!-- ================= FLASH MESSAGE ================= -->
<?php if (!empty($_SESSION['flash'])): ?>
<div class="message message-success">
    <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
</div>
<?php endif; ?>

<!-- ================= TAMBAH USER ================= -->
<div class="card mb-4 shadow-sm">
<div class="card-header">Tambah Pengguna</div>
<div class="card-body">
<form method="POST" action="admin_process.php" class="row g-3">

<div class="col-md-3">
<input name="nama" class="form-control" placeholder="Nama Lengkap" required>
</div>

<div class="col-md-3">
<input name="username" class="form-control" placeholder="Username / NISN" required>
</div>

<div class="col-md-2">
<select name="role" class="form-select" required>
<option value="">-- Role --</option>
<option value="guru">Guru</option>
<option value="siswa">Siswa</option>
</select>
</div>

<div class="col-md-2">
<input name="kelas" class="form-control" placeholder="Kelas" required>
</div>

<div class="col-md-2 d-grid">
<button name="tambah_user" class="btn btn-primary">Tambah</button>
</div>

</form>
</div>
</div>

<!-- ================= KENAIKAN KELAS ================= -->
<div class="card mb-4 shadow-sm">
<div class="card-header">Kenaikan Kelas</div>
<div class="card-body">
<form method="POST" action="admin_process.php" class="row g-3">

<div class="col-md-4">
<select name="kelas_asal" class="form-select" required>
<option value="">-- Kelas Asal --</option>
<?php foreach($kelasList as $k): ?>
<option value="<?= $k ?>"><?= $k ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-4">
<input name="kelas_tujuan" class="form-control" placeholder="Kelas Tujuan (contoh: 2A)" required>
</div>

<div class="col-md-4 d-grid">
<button name="naik_kelas" class="btn btn-primary"
onclick="return confirm('Naikkan semua siswa di kelas ini?')">
Naikkan Kelas
</button>
</div>

</form>
</div>
</div>

<!-- ================= FILTER ================= -->
<form method="GET" class="mb-3 text-center">
<select name="kelas" onchange="this.form.submit()" class="form-select w-auto d-inline">
<option value="">-- Semua Kelas --</option>
<?php foreach($kelasList as $k): ?>
<option value="<?= $k ?>" <?= $filterKelas==$k?'selected':'' ?>><?= $k ?></option>
<?php endforeach; ?>
</select>
</form>

<!-- ================= TABEL USER ================= -->
<div class="card shadow-sm">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">

<thead class="table-light">
<tr>
<th>No</th>
<th>Nama</th>
<th>Username</th>
<th>Role</th>
<th>Kelas</th>
<th width="230">Aksi</th>
</tr>
</thead>

<tbody>
<?php $no=1; while($row=mysqli_fetch_assoc($result)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['nama'] ?: '-') ?></td>
<td><?= htmlspecialchars($row['username']) ?></td>
<td><?= $row['role'] ?></td>
<td><?= $row['kelas'] ?></td>
<td>

<?php if($row['role'] !== 'admin'): ?>

<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#edit<?= $row['id'] ?>">Edit</button>

<form method="POST" action="admin_process.php" class="d-inline">
<input type="hidden" name="id" value="<?= $row['id'] ?>">
<button name="reset" class="btn btn-primary btn-sm">Reset</button>
</form>

<form method="POST" action="admin_process.php" class="d-inline"
onsubmit="return confirm('Hapus akun ini?')">
<input type="hidden" name="id" value="<?= $row['id'] ?>">
<button name="hapus" class="btn btn-danger btn-sm">Hapus</button>
</form>

<?php else: ?>
<em>Admin</em>
<?php endif; ?>

</td>
</tr>

<!-- ================= MODAL EDIT ================= -->
<div class="modal fade" id="edit<?= $row['id'] ?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST" action="admin_process.php">
<div class="modal-header">
<h5 class="modal-title">Edit Akun</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" name="id" value="<?= $row['id'] ?>">

<input name="nama" class="form-control mb-2"
value="<?= htmlspecialchars($row['nama']) ?>" placeholder="Nama Lengkap" required>

<input name="username" class="form-control mb-2"
value="<?= htmlspecialchars($row['username']) ?>" required>

<input name="kelas" class="form-control"
value="<?= htmlspecialchars($row['kelas']) ?>">
</div>

<div class="modal-footer">
<button name="update_user" class="btn btn-primary">Simpan</button>
</div>
</form>

</div>
</div>
</div>

<?php endwhile; ?>
</tbody>

</table>
</div>
</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
