<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] !== 'guru') {
    header('Location: ../index.php');
    exit;
}

$kelas = $_SESSION['kelas'];

/* MAPEL */
$mapel = mysqli_query($koneksi, "SELECT * FROM mapel");

/* ================= UPLOAD TUGAS ================= */
if (isset($_POST['buat'])) {

    $judul     = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $mapel_id  = $_POST['mapel_id'];
    $deadline  = str_replace('T', ' ', $_POST['deadline']) . ':00';

    $file_clean = null;

    if (!empty($_FILES['file']['name'])) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allow = ['pdf','doc','docx','jpg','jpeg','png','zip'];

        if (in_array($ext, $allow)) {
            $dir = "../uploads/tugas/";
            if (!is_dir($dir)) mkdir($dir,0777,true);

            $file_clean = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $dir.$file_clean);
        }
    }

    mysqli_query($koneksi,"
        INSERT INTO tugas (judul,deskripsi,mapel_id,kelas,deadline,file_path,uploaded_at)
        VALUES ('$judul','$deskripsi','$mapel_id','$kelas','$deadline',".($file_clean?"'$file_clean'":"NULL").",NOW())
    ");

    header("Location: buat_tugas.php");
    exit;
}

/* ================= STATISTIK ================= */
$total_tugas = mysqli_fetch_assoc(mysqli_query($koneksi,"
    SELECT COUNT(*) jml FROM tugas WHERE kelas='$kelas'
"))['jml'];

$total_file = mysqli_fetch_assoc(mysqli_query($koneksi,"
    SELECT COUNT(*) jml FROM tugas 
    WHERE kelas='$kelas' 
    AND file_path IS NOT NULL 
    AND file_path!=''
"))['jml'];

$total_no_file = $total_tugas - $total_file;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buat Tugas</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f3f4f6}
.card{border-radius:14px;transition:.3s}
.card:hover{transform:translateY(-4px);box-shadow:0 8px 25px rgba(0,0,0,.12)}
.tugas-card h6{font-size:.95rem}
</style>
</head>
<body>

<div class="container-fluid px-4 px-md-5 py-4">

<h2 class="text-center text-primary mb-4">📝 Buat Tugas</h2>
<a href="../dashboard/guru.php" class="btn btn-secondary mb-3">← Kembali ke Dashboard</a>

<!-- STAT -->
<div class="row g-3 mb-4 text-center">
    <div class="col-md-4"><div class="card p-3"><h6>Total Tugas</h6><h4><?= $total_tugas ?></h4></div></div>
    <div class="col-md-4"><div class="card p-3"><h6>Dengan File</h6><h4><?= $total_file ?></h4></div></div>
    <div class="col-md-4"><div class="card p-3"><h6>Tanpa File</h6><h4><?= $total_no_file ?></h4></div></div>
</div>

<!-- FORM -->
<div class="card p-4 mb-4">
<form method="post" enctype="multipart/form-data">
<div class="row g-3">
    <div class="col-md-4">
        <input name="judul" class="form-control" placeholder="Judul Tugas" required>
    </div>
    <div class="col-md-4">
        <select name="mapel_id" class="form-select" required>
            <option value="">-- Pilih Mapel --</option>
            <?php mysqli_data_seek($mapel,0); while($m=mysqli_fetch_assoc($mapel)): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-4">
        <input type="datetime-local" name="deadline" class="form-control" required>
    </div>
    <div class="col-md-6">
        <input type="file" name="file" class="form-control">
    </div>
    <div class="col-md-6">
        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi Tugas"></textarea>
    </div>
    <div class="col-12">
        <button name="buat" class="btn btn-primary">📌 Buat Tugas</button>
    </div>
</div>
</form>
</div>

<!-- FILTER -->
<div class="row g-2 mb-4">
    <div class="col-md-4">
        <select id="filterMapel" class="form-select">
            <option value="">Semua Mapel</option>
            <?php mysqli_data_seek($mapel,0); while($m=mysqli_fetch_assoc($mapel)): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-md-6">
        <input id="searchJudul" class="form-control" placeholder="Cari judul tugas...">
    </div>
</div>

<div id="tugasContainer"></div>

</div>

<script>
function loadTugas(){
    const mapel  = document.getElementById('filterMapel').value;
    const search = document.getElementById('searchJudul').value;

    fetch(`tugas_ajax.php?mapel=${mapel}&search=${encodeURIComponent(search)}`)
        .then(res=>res.text())
        .then(html=>{
            document.getElementById('tugasContainer').innerHTML = html;
        });
}

document.addEventListener('DOMContentLoaded', loadTugas);
document.getElementById('filterMapel').addEventListener('change', loadTugas);
document.getElementById('searchJudul').addEventListener('keyup', loadTugas);
</script>

</body>
</html>
