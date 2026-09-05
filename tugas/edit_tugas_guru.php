<?php
session_start();
include('../config/koneksi.php');
if ($_SESSION['role'] != 'guru') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'];
$mapel = mysqli_query($koneksi, "SELECT * FROM mapel");
$tugas = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tugas WHERE id='$id'"));

if (!$tugas) {
    echo "Tugas tidak ditemukan.";
    exit;
}

if (isset($_POST['update'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $mapel_id = $_POST['mapel_id'];

    // Perbaiki format datetime agar sesuai MySQL (YYYY-MM-DD HH:MM:SS)
    $deadline_raw = $_POST['deadline'];  // format dari input: "YYYY-MM-DDTHH:MM"
    $deadline = str_replace('T', ' ', $deadline_raw) . ':00';

    $file_path = $tugas['file_path'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];
        $file_clean = time() . "_" . preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $file);

        if (!is_dir("../uploads/tugas")) {
            mkdir("../uploads/tugas", 0777, true);
        }

        move_uploaded_file($tmp, "../uploads/tugas/" . $file_clean);
        $file_path = $file_clean;
    }

    $update_query = "UPDATE tugas SET 
                     judul='$judul', 
                     deskripsi='$deskripsi', 
                     mapel_id='$mapel_id', 
                     deadline='$deadline', 
                     file_path='$file_path' 
                     WHERE id='$id'";
    mysqli_query($koneksi, $update_query);

    echo "<script>alert('Tugas berhasil diperbarui');location.href='buat_tugas.php';</script>";
    exit;
}

// Format deadline untuk input datetime-local
$deadline_value = date('Y-m-d\TH:i', strtotime($tugas['deadline']));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Tugas | ONClass</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f2f4f7;
        }
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
        .btn-primary:hover {
            background-color: #43a047;
            border-color: #43a047;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h3 class="text-center mb-4">✏️ Edit Tugas</h3>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul Tugas</label>
                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($tugas['judul']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                <select name="mapel_id" class="form-select" required>
                    <?php while ($row = mysqli_fetch_assoc($mapel)) :
                        $selected = $row['id'] == $tugas['mapel_id'] ? 'selected' : '';
                        echo "<option value='".$row['id']."' $selected>".$row['nama_mapel']."</option>";
                    endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($tugas['deskripsi']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="deadline" class="form-label">Batas Waktu</label>
                <input type="datetime-local" name="deadline" class="form-control" value="<?= $deadline_value ?>" required>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">Ganti File (Opsional)</label>
                <input type="file" name="file" class="form-control">
                <?php if ($tugas['file_path']) : ?>
                    <small class="text-muted">File saat ini: <a href="../uploads/tugas/<?= $tugas['file_path'] ?>" target="_blank"><?= htmlspecialchars($tugas['file_path']) ?></a></small>
                <?php endif; ?>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" name="update" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="buat_tugas.php" class="btn btn-secondary">⬅️ Kembali</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
