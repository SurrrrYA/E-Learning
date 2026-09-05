<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'siswa') exit;

$kelas = $_SESSION['kelas'];
$mapel = $_GET['mapel'] ?? '';

$sql = "
    SELECT m.*, mp.nama_mapel
    FROM materi m
    JOIN mapel mp ON m.mapel_id = mp.id
    WHERE m.kelas = '$kelas'
";

if ($mapel !== '' && is_numeric($mapel)) {
    $sql .= " AND mp.id = '$mapel'";
}

$sql .= " ORDER BY mp.nama_mapel ASC, m.uploaded_at DESC";

$q = mysqli_query($koneksi, $sql);

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[$r['nama_mapel']][] = $r;
}

if (empty($data)) {
    echo '<p class="text-center text-gray-500">Tidak ada materi.</p>';
    exit;
}

foreach ($data as $mapel => $list) {
    echo "<h3 class='text-xl font-semibold text-blue-600 mb-4 border-b pb-2'>"
         . htmlspecialchars($mapel) . "</h3>";

    echo "<div class='grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8'>";
    foreach ($list as $m) {
        echo "<div class='bg-white rounded-lg shadow p-4 flex flex-col'>";
        echo "<h4 class='font-bold mb-2'>" . htmlspecialchars($m['judul']) . "</h4>";
        echo "<p class='text-gray-600 text-sm flex-grow mb-3'>"
             . nl2br(htmlspecialchars($m['deskripsi']) ?: '<i>Tidak ada deskripsi</i>')
             . "</p>";

        if (!empty($m['file_path'])) {
            echo "<a target='_blank' href='../uploads/materi/"
                 . urlencode($m['file_path'])
                 . "' class='bg-blue-600 text-white text-center py-2 rounded'>📥 Download</a>";
        } else {
            echo "<span class='text-gray-400 italic text-sm'>Tidak ada file</span>";
        }

        echo "<span class='text-xs text-gray-400 mt-2'>"
             . date("d M Y, H:i", strtotime($m['uploaded_at'])) . "</span>";
        echo "</div>";
    }
    echo "</div>";
}
