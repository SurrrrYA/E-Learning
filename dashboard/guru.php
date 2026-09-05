<?php
session_start();
include('../config/koneksi.php');

// Pastikan login
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php?error=notlogin");
    exit;
}

// Timeout (30 menit)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=session_expired");
    exit;
}
$_SESSION['last_activity'] = time();

if ($_SESSION['role'] !== 'guru') {
    header("Location: ../index.php?error=unauthorized");
    exit;
}

$user_id = $_SESSION['user_id'];
$kelas   = $_SESSION['kelas'];

// Ambil data guru
$stmt = $koneksi->prepare("SELECT username FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$username = $data['username'] ?? "Guru";

// Statistik
$jml_materi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM materi WHERE kelas='$kelas'"))['jml'] ?? 0;
$tugas_aktif = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM tugas WHERE kelas='$kelas' AND deadline >= NOW()"))['jml'] ?? 0;
$tugas_dibuat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM tugas WHERE kelas='$kelas'"))['jml'] ?? 0;

// Jumlah siswa
$qSiswa = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM users WHERE role='siswa' AND kelas='$kelas'");
$rowSiswa = mysqli_fetch_assoc($qSiswa);
$jumlah_siswa = $rowSiswa['jml'] ?? 0;

// Materi terbaru
$qMateri = mysqli_query($koneksi, "
    SELECT judul, uploaded_at 
    FROM materi 
    WHERE kelas='$kelas'
    ORDER BY uploaded_at DESC
    LIMIT 1
");
$rowMateri = mysqli_fetch_assoc($qMateri);
$materi_terbaru = $rowMateri 
    ? $rowMateri['judul'] . " (" . date("d M Y H:i", strtotime($rowMateri['uploaded_at'])) . ")"
    : "Belum ada materi diupload";

// Progres tugas
$progresResult = mysqli_query($koneksi, "
    SELECT t.id, t.judul, t.deadline,
        (SELECT COUNT(*) FROM users u WHERE u.role='siswa' AND u.kelas='$kelas') AS total_siswa,
        (SELECT COUNT(*) FROM jawaban_tugas jt WHERE jt.tugas_id=t.id) AS sudah_kumpul
    FROM tugas t
    WHERE t.kelas='$kelas'
    ORDER BY t.deadline ASC
    LIMIT 3
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Guru</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { background:#f8f9fa; font-family:'Segoe UI',sans-serif }
.card-stat { transition:.3s }
.card-stat:hover { transform:translateY(-4px); box-shadow:0 4px 15px rgba(0,0,0,.1) }
.progress { height:18px }
.info-card { background:#e0f2fe; border-left:5px solid #0284c7 }
.menu-card a { font-size:.95rem }
</style>
</head>

<body>

<!-- FULL WIDTH (TANPA RUANG KIRI KANAN) -->
<div class="container-fluid px-0">
<div class="px-3 px-md-4 py-3">

<!-- HEADER -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm sticky-top">
    <div>
        <h2 class="mb-1">
            Guru Kelas <?= htmlspecialchars($kelas) ?> 👨‍🏫
        </h2>
        <p class="text-muted mb-0">
            Selamat datang kembali,
            <strong><?= htmlspecialchars($username) ?></strong>
        </p>
    </div>
    <a href="../auth/logout.php" class="btn btn-danger mt-2 mt-sm-0">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- INFO -->
<div class="alert info-card d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-info-circle-fill mt-1 text-primary"></i>
    <div>
        <strong>Jumlah siswa:</strong> <?= $jumlah_siswa ?> orang<br>
        <strong>Materi terbaru:</strong> <?= htmlspecialchars($materi_terbaru) ?>
    </div>
</div>

<!-- STATISTIK -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card card-stat text-center p-3">
            <i class="bi bi-folder fs-2 text-warning"></i>
            <h6 class="mt-2 mb-0"><?= $jml_materi ?> Materi</h6>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card card-stat text-center p-3">
            <i class="bi bi-upload fs-2 text-success"></i>
            <h6 class="mt-2 mb-0"><?= $tugas_dibuat ?> Tugas Dibuat</h6>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card card-stat text-center p-3">
            <i class="bi bi-journal-text fs-2 text-primary"></i>
            <h6 class="mt-2 mb-0"><?= $tugas_aktif ?> Tugas Aktif</h6>
        </div>
    </div>
</div>

<!-- PROGRES -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-graph-up"></i> Progres Tugas Terbaru
    </div>
    <div class="list-group list-group-flush">
        <?php if(mysqli_num_rows($progresResult)==0): ?>
            <div class="list-group-item text-center text-muted py-3">
                Belum ada tugas
            </div>
        <?php else: while($p=mysqli_fetch_assoc($progresResult)):
            $total=$p['total_siswa'];
            $sudah=$p['sudah_kumpul'];
            $persen=$total?round(($sudah/$total)*100):0;
        ?>
        <div class="list-group-item small">
            <div class="d-flex justify-content-between flex-wrap">
                <strong><?= htmlspecialchars($p['judul']) ?></strong>
                <span class="text-muted">
                    Deadline: <?= date("d M Y H:i",strtotime($p['deadline'])) ?>
                </span>
            </div>
            <div class="progress mt-2">
                <div class="progress-bar <?= $persen<50?'bg-danger':($persen<80?'bg-warning':'bg-success') ?>"
                     style="width:<?= $persen ?>%">
                    <?= $persen ?>%
                </div>
            </div>
            <small>
                <?= $sudah ?> / <?= $total ?> siswa sudah kumpul
            </small>
        </div>
        <?php endwhile; endif; ?>
    </div>
</div>

<!-- MENU GURU (ICON UTUH) -->
<div class="card shadow-sm menu-card mb-5">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-list-task"></i> Menu Guru
    </div>
    <div class="list-group list-group-flush">
        <a href="../materi/upload.php" class="list-group-item list-group-item-action">
            <i class="bi bi-folder-fill text-warning me-2"></i> Kelola Materi
        </a>
        <a href="../tugas/buat_tugas.php" class="list-group-item list-group-item-action">
            <i class="bi bi-pencil-square text-primary me-2"></i> Kelola Tugas
        </a>
        <a href="../tugas/lihat_tugas_siswa.php" class="list-group-item list-group-item-action">
            <i class="bi bi-clipboard-data text-success me-2"></i> Manajemen Jawaban Siswa
        </a>
        <a href="../mapel/tambah_mapel.php" class="list-group-item list-group-item-action">
            <i class="bi bi-book-half text-info me-2"></i> Kelola Mata Pelajaran
        </a>
        <a href="../dashboard/daftar_siswa.php" class="list-group-item list-group-item-action">
            <i class="bi bi-list-ul text-secondary me-2"></i> Manajemen Akun Siswa
        </a>
        <a href="../Akun/ganti_password_guru.php" class="list-group-item list-group-item-action">
            <i class="bi bi-lock-fill text-danger me-2"></i> Ganti Password
        </a>
    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
