<?php
session_start();
include('../config/koneksi.php');
if ($_SESSION['role'] != 'guru') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'];

// Ambil data jawaban tugas
$query = mysqli_query($koneksi, "SELECT * FROM jawaban_tugas WHERE id='$id'");
$row = mysqli_fetch_assoc($query);

if (!$row) {
    echo "Data tidak ditemukan.";
    exit;
}

if (isset($_POST['nilai'])) {
    $skor = $_POST['skor'];
    $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar']);

    mysqli_query($koneksi, "UPDATE jawaban_tugas SET skor='$skor', komentar='$komentar' WHERE id='$id'");
    echo "<script>alert('Tugas berhasil dinilai');location.href='lihat_tugas_siswa.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beri Nilai Tugas | ONClass</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #eef2f7;
        }
        .nilai-form {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
        }
        .btn-primary {
            background-color: #00796B;
            border-color: #00796B;
        }
        .btn-primary:hover {
            background-color: #004D40;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="nilai-form">
        <h4 class="mb-4 text-center">📝 Penilaian Tugas Siswa</h4>
        <form method="post">
            <div class="mb-3">
                <label for="skor" class="form-label">Skor (0-100)</label>
                <input type="number" name="skor" class="form-control" value="<?= htmlspecialchars($row['skor']) ?>" min="0" max="100" required>
            </div>
            <div class="mb-3">
                <label for="komentar" class="form-label">Komentar</label>
                <textarea name="komentar" class="form-control" rows="5"><?= htmlspecialchars($row['komentar']) ?></textarea>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" name="nilai" class="btn btn-primary">💾 Simpan Nilai</button>
                <a href="lihat_tugas_siswa.php" class="btn btn-secondary">⬅️ Kembali</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
