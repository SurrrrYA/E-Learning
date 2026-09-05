<?php
session_start();
include('../config/koneksi.php');

if (!isset($_SESSION['kelas'])) {
    exit("<p class='text-danger text-center'>Session tidak valid.</p>");
}

$kelas  = mysqli_real_escape_string($koneksi, $_SESSION['kelas']);
$mapel  = $_GET['mapel']  ?? '';
$search = $_GET['search'] ?? '';

$sql = "
SELECT materi.*, mapel.nama_mapel, mapel.id AS mapel_id
FROM materi
JOIN mapel ON materi.mapel_id = mapel.id
WHERE materi.kelas = '$kelas'
";

if ($mapel !== '') {
    $sql .= " AND mapel.id = '".mysqli_real_escape_string($koneksi,$mapel)."'";
}

if ($search !== '') {
    $sql .= " AND materi.judul LIKE '%".mysqli_real_escape_string($koneksi,$search)."%'";
}

$sql .= " ORDER BY mapel.nama_mapel ASC, materi.uploaded_at DESC";

$q = mysqli_query($koneksi, $sql);

if (!$q || mysqli_num_rows($q) === 0) {
    exit("<p class='text-muted text-center mt-4'>Tidak ada materi.</p>");
}

$current_mapel = null;

while ($m = mysqli_fetch_assoc($q)) {

    if ($current_mapel !== $m['mapel_id']) {

        if ($current_mapel !== null) {
            echo "</div></div>";
        }

        echo "
        <div class='mt-4'>
            <div class='d-flex align-items-center mb-2'>
                <h5 class='mb-0 text-primary fw-semibold'>
                    📘 ".htmlspecialchars($m['nama_mapel'])."
                </h5>
                <hr class='flex-grow-1 ms-3'>
            </div>

            <div class='row g-3'>
        ";

        $current_mapel = $m['mapel_id'];
    }

    $is_new = (time() - strtotime($m['uploaded_at'])) < 604800;

    echo "
    <div class='col-12 col-md-6 col-lg-4'>
        <div class='card materi-card h-100 shadow-sm'>
            <div class='card-body d-flex flex-column'>

                <h6 class='fw-bold mb-1'>
                    ".htmlspecialchars($m['judul'])."
                    ".($is_new ? "<span class='badge bg-success ms-2'>Baru</span>" : "")."
                    ".(!empty($m['file_path'])
                        ? "<span class='badge bg-primary ms-1'>Dengan File</span>"
                        : "<span class='badge bg-secondary ms-1'>Tanpa File</span>"
                    )."
                </h6>

                <p class='text-muted small flex-grow-1'>
                    ".(
                        $m['deskripsi']
                        ? nl2br(htmlspecialchars($m['deskripsi']))
                        : "<i>Tidak ada deskripsi</i>"
                    )."
                </p>";

    if (!empty($m['file_path'])) {
        echo "
        <a href='../uploads/materi/".urlencode($m['file_path'])."' 
           target='_blank'
           class='btn btn-outline-primary btn-sm mt-auto'>
           📄 Lihat File
        </a>";
    }

    echo "
            </div>

            <div class='card-footer bg-white d-flex justify-content-between align-items-center'>
                <small class='text-muted'>
                    ".date('d M Y H:i', strtotime($m['uploaded_at']))."
                </small>
                <div>
                    <a href='edit_materi.php?id=".$m['id']."' class='btn btn-sm btn-warning me-1'>✏</a>
                    <a href='hapus_materi.php?id=".$m['id']."' 
                       onclick=\"return confirm('Yakin hapus materi ini?')\"
                       class='btn btn-sm btn-danger'>🗑</a>
                </div>
            </div>

        </div>
    </div>";
}

echo "</div></div>";
