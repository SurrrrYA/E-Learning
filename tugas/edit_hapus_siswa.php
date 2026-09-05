<?php
session_start();
include('../config/koneksi.php');

// Cegah akses selain siswa
if ($_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$id_jawaban = $_GET['id'] ?? 0;

// Ambil data jawaban
$query = mysqli_query($koneksi, "
    SELECT jawaban_tugas.*, tugas.judul AS tugas_judul 
    FROM jawaban_tugas 
    JOIN tugas ON jawaban_tugas.tugas_id = tugas.id
    WHERE jawaban_tugas.id = '$id_jawaban' AND jawaban_tugas.user_id = '$user_id'
");
$row = mysqli_fetch_assoc($query);

if (!$row) {
    echo "<script>alert('Tugas tidak ditemukan');location.href='daftar_tugas.php';</script>";
    exit;
}

// Hapus jawaban
if (isset($_POST['hapus'])) {
    if (!empty($row['file_path']) && file_exists("../uploads/tugas/" . $row['file_path'])) {
        unlink("../uploads/tugas/" . $row['file_path']);
    }
    mysqli_query($koneksi, "DELETE FROM jawaban_tugas WHERE id='$id_jawaban' AND user_id='$user_id'");
    echo "<script>alert('Jawaban berhasil dihapus');location.href='daftar_tugas.php';</script>";
    exit;
}

// Simpan perubahan
if (isset($_POST['simpan'])) {
    $jawaban_teks = mysqli_real_escape_string($koneksi, $_POST['jawaban_teks']);
    $file_clean = $row['file_path'];

    // Upload baru
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $original_name = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed)) {
            echo "<script>alert('Hanya file PDF, DOC, DOCX, JPG, JPEG, PNG yang diizinkan');history.back();</script>";
            exit;
        }

        $file_clean = time() . "_" . preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $original_name);
        $upload_path = "../uploads/tugas/" . $file_clean;

        if (!move_uploaded_file($tmp, $upload_path)) {
            echo "<script>alert('Gagal mengupload file');history.back();</script>";
            exit;
        }

        // Hapus file lama
        if (!empty($row['file_path']) && file_exists("../uploads/tugas/" . $row['file_path'])) {
            unlink("../uploads/tugas/" . $row['file_path']);
        }
    }

    mysqli_query($koneksi, "
        UPDATE jawaban_tugas 
        SET jawaban_teks='$jawaban_teks', file_path='$file_clean', uploaded_at=NOW() 
        WHERE id='$id_jawaban' AND user_id='$user_id'
    ");

    echo "<script>alert('Tugas berhasil diperbarui');location.href='daftar_tugas.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jawaban Tugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center p-4 sm:p-6">

<div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-5 sm:p-8 mt-6">
    <h2 class="text-lg sm:text-2xl font-bold text-center text-blue-700 mb-6">
        ✏️ Edit Jawaban Tugas<br>
        <span class="block text-gray-600 text-sm sm:text-base mt-1"><?= htmlspecialchars($row['tugas_judul']) ?></span>
    </h2>

    <form method="post" enctype="multipart/form-data" class="space-y-5">
        <!-- Jawaban teks -->
        <div>
            <label for="jawaban_teks" class="block font-medium text-gray-700 mb-2 text-sm sm:text-base">
                Jawaban Teks:
            </label>
            <textarea name="jawaban_teks" id="jawaban_teks" rows="5" 
                placeholder="Tulis jawaban Anda di sini..."
                class="w-full text-sm sm:text-base p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"><?= htmlspecialchars($row['jawaban_teks']) ?></textarea>
        </div>

        <!-- Upload file -->
        <div>
            <label for="file" class="block font-medium text-gray-700 mb-2 text-sm sm:text-base">
                Upload File Jawaban (opsional):
            </label>

            <?php if ($row['file_path']) { ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3 text-sm">
                    <p class="text-gray-600 mb-1">📎 File yang sudah diunggah:</p>
                    <a href="../uploads/tugas/<?= htmlspecialchars($row['file_path']) ?>" 
                       target="_blank" class="text-blue-600 hover:underline break-all">
                       <?= htmlspecialchars($row['file_path']) ?>
                    </a>
                </div>
            <?php } ?>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <label for="file"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg cursor-pointer font-semibold text-sm sm:text-base transition">
                    Pilih File Baru
                </label>
                <span id="file-name" class="text-gray-500 italic text-xs sm:text-sm">Belum ada file dipilih</span>
            </div>
            <input type="file" name="file" id="file" class="hidden" onchange="updateFileName()">
        </div>

        <!-- Tombol aksi -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-4">
            <a href="daftar_tugas.php" 
               class="w-full sm:w-auto text-center bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2.5 rounded-lg text-sm sm:text-base transition">
               ⬅️ Kembali
            </a>

            <div class="flex w-full sm:w-auto justify-between sm:justify-end gap-3">
                <button type="submit" name="hapus" 
                        onclick="return confirm('Yakin ingin menghapus jawaban ini?')" 
                        class="w-1/2 sm:w-auto bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm sm:text-base transition">
                    🗑️ Hapus
                </button>

                <button type="submit" name="simpan"
                        class="w-1/2 sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm sm:text-base transition">
                    💾 Simpan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function updateFileName() {
    const input = document.getElementById("file");
    const fileNameDisplay = document.getElementById("file-name");
    fileNameDisplay.textContent = input.files.length > 0 ? input.files[0].name : "Belum ada file dipilih";
}
</script>

</body>
</html>
