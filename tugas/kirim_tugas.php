<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit;
}

$id_tugas = $_GET['id'];
$user_id = $_SESSION['user_id'];

if (isset($_POST['kirim'])) {
    $jawaban_teks = mysqli_real_escape_string($koneksi, $_POST['jawaban_teks']);
    $file_path = '';

    // Upload file
    if ($_FILES['file']['error'] == 0) {
        $original_name = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed)) {
            echo "<script>alert('Hanya file PDF, DOC, DOCX, JPG, JPEG, PNG yang diizinkan');history.back();</script>";
            exit;
        }

        $file_clean = str_replace([' ', '(', ')'], '_', $original_name);
        $file_clean = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $file_clean);
        $upload_path = "../uploads/tugas/" . $file_clean;

        if (!is_dir("../uploads/tugas")) {
            mkdir("../uploads/tugas", 0777, true);
        }

        if (move_uploaded_file($tmp, $upload_path)) {
            $file_path = $file_clean;
        } else {
            echo "<script>alert('Gagal mengupload file');history.back();</script>";
            exit;
        }
    }

    // Simpan ke database
    $cek = mysqli_query($koneksi, "SELECT * FROM jawaban_tugas WHERE user_id='$user_id' AND tugas_id='$id_tugas'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "
            UPDATE jawaban_tugas 
            SET jawaban_teks='$jawaban_teks', file_path='$file_path', uploaded_at=NOW(), status='sudah' 
            WHERE user_id='$user_id' AND tugas_id='$id_tugas'
        ");
    } else {
        mysqli_query($koneksi, "
            INSERT INTO jawaban_tugas (user_id, tugas_id, jawaban_teks, file_path, status) 
            VALUES ('$user_id', '$id_tugas', '$jawaban_teks', '$file_path', 'sudah')
        ");
    }

    echo "<script>alert('Tugas berhasil dikirim');location.href='daftar_tugas.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Jawaban Tugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col justify-center items-center p-4 sm:p-6">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <h2 class="text-xl sm:text-2xl font-bold text-center text-blue-600 mb-6">📤 Kirim Jawaban Tugas</h2>

        <form method="post" enctype="multipart/form-data" class="space-y-5">
            <!-- Textarea jawaban -->
            <div>
                <label for="jawaban_teks" class="block font-medium text-gray-700 mb-2 text-sm sm:text-base">
                    Tulis Jawaban (opsional):
                </label>
                <textarea name="jawaban_teks" id="jawaban_teks" rows="5" placeholder="Tulis jawaban Anda di sini..."
                    class="w-full text-sm sm:text-base p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
            </div>

            <!-- Upload file -->
            <div>
                <label for="file" class="block font-medium text-gray-700 mb-2 text-sm sm:text-base">
                    Upload File (PDF, DOC, JPG, DLL):
                </label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <label for="file" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg cursor-pointer font-semibold text-sm sm:text-base transition">
                        Pilih File
                    </label>
                    <span id="file-name" class="text-gray-500 italic text-xs sm:text-sm">Belum ada file</span>
                </div>
                <input type="file" name="file" id="file" class="hidden" onchange="updateFileName()">
            </div>

            <!-- Tombol aksi -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4">
                <a href="daftar_tugas.php" 
                   class="w-full sm:w-auto text-center bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2.5 rounded-lg text-sm sm:text-base transition">
                    ⬅️ Kembali
                </a>

                <button type="submit" name="kirim"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm sm:text-base transition">
                    Kirim Jawaban
                </button>
            </div>
        </form>
    </div>

    <script>
        function updateFileName() {
            const input = document.getElementById("file");
            const fileNameDisplay = document.getElementById("file-name");
            fileNameDisplay.textContent = input.files.length > 0 ? input.files[0].name : "Belum ada file";
        }
    </script>

</body>
</html>
