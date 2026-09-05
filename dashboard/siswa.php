<?php
session_start();
include('../config/koneksi.php');

// Pastikan login siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$kelas   = $_SESSION['kelas'];

// Ambil nama siswa
$qNama = mysqli_query($koneksi, "SELECT nama FROM users WHERE id='$user_id'");
$data  = mysqli_fetch_assoc($qNama);
$nama  = $data['nama'] ?? 'Siswa';

// Hitung jumlah data
$jml_materi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM materi WHERE kelas='$kelas'"))['jml'] ?? 0;
$total_tugas = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM tugas WHERE kelas='$kelas'"))['jml'] ?? 0;
$tugas_aktif = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM tugas WHERE kelas='$kelas' AND deadline >= NOW()"))['jml'] ?? 0;
$tugas_dikerjakan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT jt.tugas_id) AS jml 
    FROM jawaban_tugas jt
    JOIN tugas t ON jt.tugas_id = t.id
    WHERE jt.user_id='$user_id' AND t.kelas='$kelas'
"))['jml'] ?? 0;
$tugas_dinilai = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT jt.tugas_id) AS jml 
    FROM jawaban_tugas jt
    JOIN tugas t ON jt.tugas_id = t.id
    WHERE jt.user_id='$user_id' AND jt.skor IS NOT NULL AND t.kelas='$kelas'
"))['jml'] ?? 0;

// Deadline terdekat
$qDeadline = mysqli_query($koneksi, "
    SELECT judul, deadline 
    FROM tugas 
    WHERE kelas='$kelas' AND deadline >= NOW()
    ORDER BY deadline ASC
    LIMIT 1
");
$deadlineRow = mysqli_fetch_assoc($qDeadline);
$deadline_terdekat = $deadlineRow 
    ? $deadlineRow['judul'] . " - " . date("d M Y H:i", strtotime($deadlineRow['deadline']))
    : "Tidak ada deadline";

// Progress
$persen_dikerjakan = $total_tugas > 0 ? round(($tugas_dikerjakan / $total_tugas) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Siswa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- penting untuk responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color:#f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card-stat { transition:0.3s; border-radius:12px; }
        .card-stat:hover { transform:translateY(-3px); box-shadow:0 4px 12px rgba(0,0,0,0.1); }
        .progress { height:20px; }
        @media (max-width: 576px) {
            h2 { font-size:1.4rem; }
            .card-stat i { font-size:1.5rem; }
            .card-stat h5 { font-size:1rem; }
            .alert { font-size:0.9rem; padding:10px; }
            .progress { height:15px; }
            .list-group-item { font-size:0.9rem; }
            .btn { font-size:0.9rem; padding:6px 10px; }
        }
    </style>
</head>
<body>
<div class="container-fluid py-3">


    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm sticky-top">
        <div>
            <h2 class="mb-1">Halo, <?= htmlspecialchars($nama) ?> 👋</h2>
            <p class="text-muted mb-0">Selamat belajar di Kelas <strong><?= htmlspecialchars($kelas) ?></strong> 📚</p>
        </div>
        <a href="../auth/logout.php" class="btn btn-danger mt-2 mt-sm-0">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

    <!-- Deadline -->
    <div class="alert alert-info d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="bi bi-megaphone-fill mt-1"></i>
        <div><strong>Deadline Tugas Terdekat:</strong> <?= htmlspecialchars($deadline_terdekat) ?></div>
    </div>

    <!-- Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-stat text-center p-3">
                <i class="bi bi-folder fs-2 text-warning"></i>
                <h5 class="mt-2 mb-0"><?= $jml_materi ?><br><small>Materi</small></h5>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat text-center p-3">
                <i class="bi bi-journal-text fs-2 text-primary"></i>
                <h5 class="mt-2 mb-0"><?= $tugas_aktif ?><br><small>Tugas Aktif</small></h5>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat text-center p-3">
                <i class="bi bi-upload fs-2 text-success"></i>
                <h5 class="mt-2 mb-0"><?= $tugas_dikerjakan ?><br><small>Dikerjakan</small></h5>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat text-center p-3">
                <i class="bi bi-check2-circle fs-2 text-info"></i>
                <h5 class="mt-2 mb-0"><?= $tugas_dinilai ?><br><small>Dinilai</small></h5>
            </div>
        </div>
    </div>

    <!-- Progress -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <i class="bi bi-bar-chart-line"></i> Progress Pengerjaan
        </div>
        <div class="card-body">
            <p class="mb-2">Sudah dikerjakan: <strong><?= $tugas_dikerjakan ?>/<?= $total_tugas ?></strong></p>
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: <?= $persen_dikerjakan ?>%;" 
                     aria-valuenow="<?= $persen_dikerjakan ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= $persen_dikerjakan ?>%
                </div>
            </div>
        </div>
    </div>

    <!-- Menu -->
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-list-task"></i> Menu Siswa</h6>
            <button class="btn btn-light btn-sm d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#menuList">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div id="menuList" class="list-group list-group-flush collapse show">
            <a href="../materi/daftar_materi.php" class="list-group-item list-group-item-action">
                <i class="bi bi-folder-fill text-warning"></i> Lihat Materi
            </a>
            <a href="../tugas/daftar_tugas.php" class="list-group-item list-group-item-action">
                <i class="bi bi-pencil-square text-primary"></i> Lihat Tugas
            </a>
            <a href="../Akun/ganti_password_siswa.php" class="list-group-item list-group-item-action">
                <i class="bi bi-lock-fill text-danger"></i> Ganti Password
            </a>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
