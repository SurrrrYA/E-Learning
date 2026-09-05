<?php
session_start();
include('../config/koneksi.php');

if (!isset($_SESSION['kelas'])) {
    exit("<p class='text-danger text-center'>Session tidak valid.</p>");
}

$kelas  = mysqli_real_escape_string($koneksi, $_SESSION['kelas']);
$mapel  = $_GET['mapel']  ?? '';
$search = $_GET['search'] ?? '';

/* QUERY DASAR */
$sql = "
SELECT 
    tugas.*,
    mapel.nama_mapel,
    mapel.id AS mapel_id
FROM tugas
JOIN mapel ON tugas.mapel_id = mapel.id
WHERE tugas.kelas = '$kelas'
";

/* FILTER MAPEL */
if ($mapel !== '') {
    $sql .= " AND mapel.id='".mysqli_real_escape_string($koneksi,$mapel)."'";
}

/* SEARCH JUDUL */
if ($search !== '') {
    $sql .= " AND tugas.judul LIKE '%".mysqli_real_escape_string($koneksi,$search)."%'";
}

/* URUT */
$sql .= " ORDER BY mapel.nama_mapel ASC, tugas.uploaded_at DESC";

$q = mysqli_query($koneksi, $sql);

if (!$q || mysqli_num_rows($q) === 0) {
    exit("<p class='text-muted text-center'>Belum ada tugas.</p>");
}

$current_mapel = null;

while ($t = mysqli_fetch_assoc($q)) {

    /* ===== HEADER MAPEL ===== */
    if ($current_mapel !== $t['mapel_id']) {

        if ($current_mapel !== null) {
            echo "</div></div>";
        }

        echo "
        <div class='mt-4'>
            <div class='d-flex align-items-center mb-2'>
                <h5 class='mb-0 text-primary fw-semibold'>
                    📘 ".htmlspecialchars($t['nama_mapel'])."
                </h5>
                <hr class='flex-grow-1 ms-3'>
            </div>

            <div class='row g-3'>
        ";

        $current_mapel = $t['mapel_id'];
    }

    $is_new = (time() - strtotime($t['uploaded_at'])) < 604800;

    echo "
    <div class='col-12 col-md-6 col-lg-4'>
        <div class='card tugas-card h-100 shadow-sm'>
            <div class='card-body d-flex flex-column'>

                <h6 class='fw-bold mb-1'>
                    ".htmlspecialchars($t['judul'])."
                    ".($is_new ? "<span class='badge bg-success ms-2'>Baru</span>" : "")."
                    ".(!empty($t['file_path'])
                        ? "<span class='badge bg-primary ms-1'>Dengan File</span>"
                        : "<span class='badge bg-secondary ms-1'>Tanpa File</span>"
                    )."
                </h6>

                <p class='text-muted small mb-2 flex-grow-1'>
                    ".(
                        $t['deskripsi']
                        ? nl2br(htmlspecialchars($t['deskripsi']))
                        : "<i>Tidak ada deskripsi</i>"
                    )."
                </p>

                <small class='text-muted mb-2'>
                    ⏰ Deadline: <strong>".date('d M Y H:i', strtotime($t['deadline']))."</strong>
                </small>";

    if (!empty($t['file_path'])) {
        echo "
        <a href='../uploads/tugas/".urlencode($t['file_path'])."' 
           target='_blank'
           class='btn btn-outline-primary btn-sm mt-auto'>
           📄 Lihat File
        </a>";
    }

    echo "
            </div>

            <div class='card-footer bg-white d-flex justify-content-between align-items-center'>
                <small class='text-muted'>
                    Dibuat: ".date('d M Y', strtotime($t['uploaded_at']))."
                </small>
                <div>
                    <a href='edit_tugas_guru.php?id=".$t['id']."' 
                       class='btn btn-sm btn-warning me-1'>✏</a>
                    <a href='hapus_tugas_guru.php?id=".$t['id']."' 
                       onclick=\"return confirm('Yakin hapus tugas ini?')\"
                       class='btn btn-sm btn-danger'>🗑</a>
                </div>
            </div>

        </div>
    </div>";
}

echo "</div></div>";
