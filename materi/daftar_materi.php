<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit;
}

$kelas = $_SESSION['kelas'];

// Ambil mapel
$mapel_query = mysqli_query($koneksi, "
    SELECT DISTINCT mp.id, mp.nama_mapel
    FROM materi m
    JOIN mapel mp ON m.mapel_id = mp.id
    WHERE m.kelas = '$kelas'
    ORDER BY mp.nama_mapel ASC
");

$mapel_list = [];
while ($m = mysqli_fetch_assoc($mapel_query)) {
    $mapel_list[$m['id']] = $m['nama_mapel'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Materi Pelajaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen py-6">

<div class="w-full bg-white rounded-xl shadow-md p-4 sm:p-6 md:p-8">
    <h2 class="text-2xl sm:text-3xl font-extrabold text-center text-blue-700 mb-6">
        📚 Daftar Materi Pelajaran
    </h2>

    <!-- DROPDOWN -->
    <div class="text-center mb-6">
        <label class="block mb-2 text-gray-700">Filter Mata Pelajaran</label>
        <select id="mapel"
                class="w-full sm:w-1/2 px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Mapel</option>
            <?php foreach ($mapel_list as $id => $nama): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($nama) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- HASIL MATERI -->
    <div id="materi-container">
        <!-- AJAX result -->
    </div>

    <div class="mt-8 text-center">
        <a href="../dashboard/siswa.php"
           class="inline-block bg-gray-700 hover:bg-gray-800 text-white font-semibold px-6 py-3 rounded-lg">
            ⬅️ Kembali ke Dashboard
        </a>
    </div>
</div>

<script>
const selectMapel = document.getElementById('mapel');
const container = document.getElementById('materi-container');

// load awal
loadMateri('');

selectMapel.addEventListener('change', () => {
    loadMateri(selectMapel.value);
});

function loadMateri(mapel) {
    container.innerHTML = '<p class="text-center text-gray-400">Memuat materi...</p>';

    fetch('daftarmateri_ajax.php?mapel=' + mapel)
        .then(res => res.text())
        .then(html => container.innerHTML = html)
        .catch(() => container.innerHTML =
            '<p class="text-center text-red-500">Gagal memuat data</p>'
        );
}
</script>

</body>
</html>
