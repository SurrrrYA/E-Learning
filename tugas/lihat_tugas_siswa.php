<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] !== 'guru') {
    header('Location: ../index.php');
    exit;
}

$kelas = $_SESSION['kelas'];

// Ambil daftar mapel
$mapelQuery = "
    SELECT DISTINCT mapel.id, mapel.nama_mapel
    FROM mapel
    JOIN tugas ON mapel.id = tugas.mapel_id
    WHERE tugas.kelas = '$kelas'
    ORDER BY mapel.nama_mapel ASC
";
$mapelResult = mysqli_query($koneksi, $mapelQuery);

$selectedMapel = isset($_GET['mapel']) ? $_GET['mapel'] : '';

// Ambil daftar tugas
$tugasQuery = "
    SELECT tugas.id AS tugas_id, tugas.judul, tugas.deadline, mapel.nama_mapel
    FROM tugas
    JOIN mapel ON tugas.mapel_id = mapel.id
    WHERE tugas.kelas = '$kelas'
";
if (!empty($selectedMapel)) {
    $selectedMapelEsc = mysqli_real_escape_string($koneksi, $selectedMapel);
    $tugasQuery .= " AND mapel.nama_mapel = '$selectedMapelEsc'";
}
$tugasQuery .= " ORDER BY mapel.nama_mapel ASC, tugas.judul ASC";

$tugasResult = mysqli_query($koneksi, $tugasQuery);

// Ambil semua siswa di kelas
$siswaQuery = "SELECT id, nama FROM users WHERE role='siswa' AND kelas='$kelas' ORDER BY nama ASC";
$siswaResult = mysqli_query($koneksi, $siswaQuery);
$siswaList = [];
while ($row = mysqli_fetch_assoc($siswaResult)) {
    $siswaList[$row['id']] = $row['nama'];
}

// Ambil semua jawaban
$jawabanQuery = "
    SELECT jawaban_tugas.*, users.nama, tugas.id AS tugas_id, tugas.deadline, mapel.nama_mapel, tugas.judul
    FROM jawaban_tugas
    JOIN users ON jawaban_tugas.user_id = users.id
    JOIN tugas ON jawaban_tugas.tugas_id = tugas.id
    JOIN mapel ON tugas.mapel_id = mapel.id
    WHERE users.kelas = '$kelas'
";
$jawabanResult = mysqli_query($koneksi, $jawabanQuery);

$jawabanList = [];
while ($row = mysqli_fetch_assoc($jawabanResult)) {
    $jawabanList[$row['tugas_id']][$row['user_id']] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Jawaban Tugas Siswa</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ====== PERUBAHAN HANYA DI SINI ====== */
body{
    font-family: Arial, sans-serif;
    background:#f3f4f6;
    margin:0;
    padding:0;
}

.container{
    max-width:100%;
    margin:0;
    background:#fff;
    padding:20px;
    border-radius:0;
    box-shadow:none;
}
/* ====== END ====== */

h2{text-align:center;color:#1e40af;margin-bottom:20px}
form{text-align:center;margin-bottom:20px}
select{padding:10px 14px;border-radius:8px}

.card-tugas{
    margin-bottom:20px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.06);
    overflow:hidden;
}
.card-header{
    background:#2563eb;
    color:#fff;
    cursor:pointer;
    padding:15px;
    font-weight:bold;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.card-body{padding:15px;display:none;background:#f9fafb}
.toggle-icon{font-size:20px}

.status-belum{color:#6b7280;font-weight:bold}
.status-tepat{color:#16a34a;font-weight:bold}
.status-terlambat{color:#dc2626;font-weight:bold}

.progress{height:20px;border-radius:10px;margin:10px 0}
.progress-bar{font-weight:bold}

.table-wrapper{overflow-x:auto}
table{width:100%;min-width:600px}
th{background:#2563eb;color:#fff}
th,td{padding:12px;border-bottom:1px solid #e5e7eb}
tr:hover{background:#f1f5f9}

a.btn{
    padding:6px 12px;
    background:#2563eb;
    color:#fff;
    border-radius:6px;
    font-size:.9rem;
    text-decoration:none;
}
a.btn:hover{background:#1e40af}
</style>
</head>

<body>
<div class="container">

<h2>Daftar Jawaban Tugas Siswa</h2>

<form method="GET">
    <label>Filter Mapel:</label>
    <select name="mapel" onchange="this.form.submit()">
        <option value="">Semua Mapel</option>
        <?php mysqli_data_seek($mapelResult,0); while($m=mysqli_fetch_assoc($mapelResult)): ?>
            <option value="<?= htmlspecialchars($m['nama_mapel']) ?>" <?= $selectedMapel==$m['nama_mapel']?'selected':'' ?>>
                <?= htmlspecialchars($m['nama_mapel']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<a href="../dashboard/guru.php" class="btn mb-3">⬅️ Kembali ke Dashboard</a>

<?php while($tugas=mysqli_fetch_assoc($tugasResult)):
    $tugasId=$tugas['tugas_id'];
    $jawabanTugas=$jawabanList[$tugasId]??[];
    $total=count($siswaList);
    $sudah=count($jawabanTugas);
    $belum=$total-$sudah;
    $persen=$total?round($sudah/$total*100):0;
?>
<div class="card-tugas">
    <div class="card-header" onclick="toggleDetail(this)">
        📌 <?= htmlspecialchars($tugas['judul']) ?> (<?= htmlspecialchars($tugas['nama_mapel']) ?>)
        <span class="toggle-icon">+</span>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <span>Total siswa: <?= $total ?></span>
            <span>Sudah kumpul: <?= $sudah ?></span>
            <span>Belum kumpul: <?= $belum ?></span>
        </div>

        <div class="progress">
            <div class="progress-bar bg-<?= $persen<50?'danger':($persen<80?'warning':'success') ?>" style="width:<?= $persen ?>%">
                <?= $persen ?>%
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach($siswaList as $siswaId => $namaSiswa):
    if(isset($jawabanTugas[$siswaId])){
        $row = $jawabanTugas[$siswaId];

        $uploaded = new DateTime($row['uploaded_at'], new DateTimeZone('Asia/Jakarta'));
        $deadline = new DateTime($row['deadline'], new DateTimeZone('Asia/Jakarta'));

        if($uploaded <= $deadline){
            $statusText = "Tepat waktu";
            $statusClass = "status-tepat";
        } else {
            $diff = $uploaded->diff($deadline);
            $jam = $diff->days * 24 + $diff->h;
            $statusText = "Terlambat {$jam} jam {$diff->i} menit";
            $statusClass = "status-terlambat";
        }

        $nilai = ($row['skor'] !== null) ? htmlspecialchars($row['skor']) : '-';
        $aksi = "<a href='detail_jawaban.php?id={$row['id']}' class='btn'>Lihat Jawaban</a>";
    } else {
        $statusText = "Belum mengumpulkan";
        $statusClass = "status-belum";
        $nilai = "-";
        $aksi = "-";
    }
?>
<tr>
    <td><?= htmlspecialchars($namaSiswa) ?></td>
    <td><?= $nilai ?></td>
    <td class="<?= $statusClass ?>"><?= $statusText ?></td>
    <td><?= $aksi ?></td>
</tr>
<?php endforeach; ?>
</tbody>

            </table>
        </div>
    </div>
</div>
<?php endwhile; ?>

</div>

<script>
function toggleDetail(el){
    const body=el.nextElementSibling;
    const icon=el.querySelector('.toggle-icon');
    body.style.display=body.style.display==="block"?"none":"block";
    icon.textContent=icon.textContent==="+"?"−":"+";
}
</script>

</body>
</html>
