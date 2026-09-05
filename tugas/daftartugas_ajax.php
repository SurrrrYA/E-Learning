<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'siswa') exit;

date_default_timezone_set('Asia/Jakarta');

$kelas = $_SESSION['kelas'];
$user_id = $_SESSION['user_id'];
$filter_mapel = $_GET['mapel'] ?? '';

$sql = "
    SELECT tugas.*, mapel.nama_mapel,
           jawaban_tugas.id AS jawaban_id,
           jawaban_tugas.skor AS nilai
    FROM tugas
    JOIN mapel ON tugas.mapel_id = mapel.id
    LEFT JOIN jawaban_tugas 
        ON tugas.id = jawaban_tugas.tugas_id AND jawaban_tugas.user_id = '$user_id'
    WHERE tugas.kelas = '$kelas'
";

if ($filter_mapel && is_numeric($filter_mapel)) {
    $sql .= " AND mapel.id = '$filter_mapel'";
}

$sql .= " ORDER BY mapel.nama_mapel, tugas.deadline ASC";
$q = mysqli_query($koneksi, $sql);

$tugas = [];
$total = $dikerjakan = $belum = $aktif = 0;
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

while ($r = mysqli_fetch_assoc($q)) {
    $total++;
    $deadline = new DateTime($r['deadline']);

    if ($r['jawaban_id']) {
        $dikerjakan++;
    } elseif ($now < $deadline) {
        $aktif++;
    } else {
        $belum++;
    }

    $tugas[$r['nama_mapel']][] = $r;
}

/* Ringkasan */
echo "
<div class='grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 text-center'>
    <div class='bg-blue-100 p-4 rounded'><b>$total</b><br>Total</div>
    <div class='bg-green-100 p-4 rounded'><b>$dikerjakan</b><br>Dikerjakan</div>
    <div class='bg-yellow-100 p-4 rounded'><b>$aktif</b><br>Aktif</div>
    <div class='bg-red-100 p-4 rounded'><b>$belum</b><br>Belum</div>
</div>";

if (empty($tugas)) {
    echo "<p class='text-center text-gray-500'>Tidak ada tugas.</p>";
    exit;
}

foreach ($tugas as $mapel => $list) {
    echo "<h3 class='text-2xl font-semibold text-blue-600 mb-6 border-b pb-2'>"
         . htmlspecialchars($mapel) . "</h3>";

    echo "<div class='grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10'>";
    foreach ($list as $t) {
        $deadline = new DateTime($t['deadline']);
        $late = $now > $deadline;
        $diff = $now->diff($deadline);
        $jam = $diff->days * 24 + $diff->h;

        echo "<div class='bg-white shadow rounded p-5 flex flex-col'>";
        echo "<h4 class='font-bold mb-2'>" . htmlspecialchars($t['judul']) . "</h4>";
        echo "<p class='text-gray-600 flex-grow mb-3'>"
             . nl2br(htmlspecialchars($t['deskripsi']) ?: '<i>Tidak ada deskripsi</i>')
             . "</p>";

        echo "<p class='text-sm mb-1'><b>Deadline:</b> {$deadline->format('d M Y H:i')}</p>";

        if ($t['jawaban_id']) {
            echo "<span class='text-green-700 text-sm mb-2'>✅ Sudah dikerjakan</span>";
        } elseif ($late) {
            echo "<span class='text-red-700 text-sm mb-2'>❌ Lewat deadline</span>";
        } else {
            echo "<span class='text-yellow-700 text-sm mb-2'>⏳ $jam jam lagi</span>";
        }

        if ($t['jawaban_id']) {
            echo "<a href='edit_hapus_siswa.php?id={$t['jawaban_id']}'
                  class='mt-auto bg-indigo-600 text-white text-center py-2 rounded'>
                  ✏️ Lihat / Edit
                  </a>";
        } else {
            echo "<a href='kirim_tugas.php?id={$t['id']}'
                  class='mt-auto bg-blue-600 text-white text-center py-2 rounded'>
                  📤 Kerjakan
                  </a>";
        }

        echo "</div>";
    }
    echo "</div>";
}
