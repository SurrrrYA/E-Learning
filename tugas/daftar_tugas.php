<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$kelas = $_SESSION['kelas'];
$user_id = $_SESSION['user_id'];

// Ambil daftar mapel
$mapel_result = mysqli_query($koneksi, "
    SELECT DISTINCT mapel.id, mapel.nama_mapel
    FROM tugas
    JOIN mapel ON tugas.mapel_id = mapel.id
    WHERE tugas.kelas = '$kelas'
    ORDER BY mapel.nama_mapel
");

$mapel_list = [];
while ($row = mysqli_fetch_assoc($mapel_result)) {
    $mapel_list[$row['id']] = $row['nama_mapel'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tugas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen py-6">

<div class="w-full bg-white rounded-xl shadow-md p-6">

    <h2 class="text-3xl font-extrabold text-center text-blue-700 mb-8">
        📝 Daftar Tugas
    </h2>

    <!-- FILTER -->
    <div class="mb-8 text-center">
        <label class="block mb-2 text-lg text-gray-700">Filter Mata Pelajaran</label>
        <select id="mapel"
                class="w-full sm:w-1/2 px-4 py-2 rounded border border-gray-300">
            <option value="">Semua Mapel</option>
            <?php foreach ($mapel_list as $id => $nama): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- HASIL AJAX -->
    <div id="tugas-container"></div>

    <div class="mt-10 text-center">
        <a href="../dashboard/siswa.php"
           class="inline-block bg-gray-700 hover:bg-gray-800 text-white font-semibold px-6 py-3 rounded-lg shadow">
            ⬅️ Kembali ke Dashboard
        </a>
    </div>
</div>

<script>
const selectMapel = document.getElementById('mapel');
const container = document.getElementById('tugas-container');

loadTugas('');

selectMapel.addEventListener('change', () => {
    loadTugas(selectMapel.value);
});

function loadTugas(mapel) {
    container.innerHTML = '<p class="text-center text-gray-400">Memuat tugas...</p>';

    fetch('daftartugas_ajax.php?mapel=' + mapel)
        .then(res => res.text())
        .then(html => container.innerHTML = html)
        .catch(() => container.innerHTML =
            '<p class="text-center text-red-500">Gagal memuat data</p>'
        );
}
</script>

</body>
</html>
