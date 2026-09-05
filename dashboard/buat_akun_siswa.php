<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] !== 'guru') {
    header('Location: ../index.php');
    exit;
}

$kelas = $_SESSION['kelas'];
$info_akun = '';

if (isset($_POST['buat_akun'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    if (!preg_match('/^[0-9]{10,12}$/', $nis)) {
        echo "<script>alert('NIS harus terdiri dari 10 hingga 12 digit angka');</script>";
    } else {
        $username = $nis;
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('NISN ini sudah digunakan sebagai akun.');</script>";
        } else {
            $insert = mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role, kelas)
                                              VALUES ('$nama', '$username', '$password_hash', 'siswa', '$kelas')");
            if ($insert) {
                $info_akun = "
                    <div class='mt-4 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg text-sm text-green-800'>
                        <p class='font-semibold'>✅ Akun siswa berhasil dibuat:</p>
                        <div class='mt-2'>
                            <p>Nama: <b>$nama</b></p>
                            <p>Username (NISN): <b>$username</b></p>
                            <p>Password: <b>$password</b></p>
                            <small class='text-gray-600'><i>Harap catat dan berikan ke siswa.</i></small>
                        </div>
                    </div>";
            } else {
                echo "<script>alert('Gagal membuat akun');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">
    <h2 class="text-2xl font-bold text-blue-700 text-center mb-6">➕ Buat Akun Siswa</h2>

    <form method="post" class="space-y-4">
        <div>
            <label for="nama" class="block text-gray-700 font-medium mb-1">Nama Lengkap:</label>
            <input type="text" name="nama" id="nama" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <div>
            <label for="nis" class="block text-gray-700 font-medium mb-1">NISN (10–12 digit):</label>
            <input type="text" name="nis" id="nis" maxlength="12" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <div>
            <label for="password" class="block text-gray-700 font-medium mb-1">Password:</label>
            <div class="relative">
                <input type="password" name="password" id="password" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <button type="button" id="togglePassword" class="absolute right-3 top-2.5 text-gray-500">
                    <!-- ikon mata terbuka -->
                    <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>

                    <!-- ikon mata tertutup -->
                    <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.98 8.223A10.477 10.477 0 0 0 2.458 12c1.274 4.057 5.065 7 9.542 7 1.78 0 3.47-.41 4.958-1.14M9.88 9.88a3 3 0 1 0 4.24 4.24M6.1 6.1l11.8 11.8" />
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" name="buat_akun"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg transition">
            ✅ Buat Akun
        </button>
    </form>

    <?= $info_akun ?>

    <a href="daftar_siswa.php"
       class="block text-center mt-6 text-blue-600 hover:text-blue-800 font-medium">
       ⬅️ Kembali ke Daftar Siswa
    </a>
</div>

<script>
const toggleBtn = document.getElementById('togglePassword');
const input = document.getElementById('password');
const eyeOpen = document.getElementById('eyeOpen');
const eyeClosed = document.getElementById('eyeClosed');

toggleBtn.addEventListener('click', () => {
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    eyeOpen.classList.toggle('hidden', isHidden);
    eyeClosed.classList.toggle('hidden', !isHidden);
});
</script>

</body>
</html>
