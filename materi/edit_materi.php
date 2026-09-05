<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'guru') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'];
$materi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM materi WHERE id = '$id'"));

if (!$materi) {
    echo "Materi tidak ditemukan.";
    exit;
}

$mapel = mysqli_query($koneksi, "SELECT * FROM mapel");

if (isset($_POST['update'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $mapel_id = $_POST['mapel_id'];

    if ($_FILES['file']['error'] === 0) {
        $file = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];
        $file_clean = time() . "_" . preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $file);

        move_uploaded_file($tmp, "../uploads/materi/" . $file_clean);

        if (!empty($materi['file_path']) && file_exists("../uploads/materi/" . $materi['file_path'])) {
            unlink("../uploads/materi/" . $materi['file_path']);
        }

        mysqli_query($koneksi, "UPDATE materi SET judul='$judul', deskripsi='$deskripsi', mapel_id='$mapel_id', file_path='$file_clean' WHERE id='$id'");
    } else {
        mysqli_query($koneksi, "UPDATE materi SET judul='$judul', deskripsi='$deskripsi', mapel_id='$mapel_id' WHERE id='$id'");
    }

    echo "<script>alert('Materi berhasil diperbarui');location.href='upload.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Materi | ONClass</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom right, #e3f2fd, #ffffff);
            font-family: 'Segoe UI', sans-serif;
        }
        .form-container {
            max-width: 700px;
            margin: 60px auto;
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.08);
        }
        .form-title {
            font-weight: 700;
            font-size: 1.75rem;
            color: #1565c0;
        }
        label {
            font-weight: 500;
            margin-bottom: 6px;
        }
        .btn-primary {
            background-color: #1976d2;
            border-color: #1976d2;
        }
        .btn-primary:hover {
            background-color: #0d47a1;
        }
        .file-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <div class="text-center mb-4">
            <div class="form-title">✏️ Edit Materi</div>
            <p class="text-muted">Perbarui informasi materi pembelajaran dengan mudah</p>
        </div>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">📘 Judul Materi</label>
                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($materi['judul']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">📝 Deskripsi / Link</label>
                <textarea name="deskripsi" class="form-control" rows="4" placeholder="Tambahkan deskripsi materi atau link eksternal"><?= htmlspecialchars($materi['deskripsi']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="mapel_id" class="form-label">📚 Mata Pelajaran</label>
                <select name="mapel_id" class="form-select" required>
                    <?php while ($row = mysqli_fetch_assoc($mapel)) :
                        $selected = ($row['id'] == $materi['mapel_id']) ? 'selected' : '';
                        echo "<option value='".$row['id']."' $selected>".$row['nama_mapel']."</option>";
                    endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">📎 Ganti File Materi (opsional)</label>
                <input type="file" name="file" class="form-control">
                <?php if ($materi['file_path']) : ?>
                    <div class="file-info mt-1">
                        File saat ini: <a href="../uploads/materi/<?= $materi['file_path'] ?>" target="_blank"><?= $materi['file_path'] ?></a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" name="update" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="upload.php" class="btn btn-outline-secondary">⬅️ Kembali</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
