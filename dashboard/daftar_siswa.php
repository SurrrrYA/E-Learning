<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'guru') {
    header('Location: ../index.php');
    exit;
}

$kelas = $_SESSION['kelas'];

// Hapus akun siswa
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $hapus = mysqli_query($koneksi, "DELETE FROM users WHERE id = $id AND role = 'siswa'");
    $_SESSION['success'] = $hapus ? "✅ Akun siswa berhasil dihapus." : "❌ Gagal menghapus akun siswa.";
    header("Location: daftar_siswa.php");
    exit;
}

// Ambil data siswa berdasarkan kelas guru
$query = mysqli_query($koneksi, "SELECT id, username, nama, kelas FROM users WHERE role='siswa' AND kelas='$kelas' ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa Kelas <?= htmlspecialchars($kelas) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">


<div class="w-full bg-white rounded-2xl shadow-md p-6 sm:p-8 mt-4">

    <h2 class="text-2xl font-bold text-blue-700 text-center mb-6">
        📋 Daftar Siswa Kelas <?= htmlspecialchars($kelas) ?>
    </h2>

    <!-- Tombol Aksi -->
    <div class="flex flex-col sm:flex-row justify-between gap-3 mb-6">
        <a href="../dashboard/guru.php"
           class="w-full sm:w-auto bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-4 py-2 rounded-lg text-center transition">
           ← Kembali ke Dashboard
        </a>
        <a href="buat_akun_siswa.php"
           class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-center transition">
           ➕ Tambahkan Akun Siswa
        </a>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg mb-4 text-center">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-4 text-center">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Daftar Siswa -->
    <?php if (mysqli_num_rows($query) > 0): ?>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-sm sm:text-base">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="py-2 px-3 text-left">#</th>
                        <th class="py-2 px-3 text-left">Username</th>
                        <th class="py-2 px-3 text-left">Nama</th>
                        <th class="py-2 px-3 text-left">Kelas</th>
                        <th class="py-2 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3"><?= $no++ ?></td>
                            <td class="py-2 px-3"><?= htmlspecialchars($row['username']) ?></td>
                            <td class="py-2 px-3"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="py-2 px-3"><?= htmlspecialchars($row['kelas']) ?></td>
                            <td class="py-2 px-3 text-center flex flex-col sm:flex-row justify-center gap-2">
                                <a href="../Akun/reset_password_siswa.php?id=<?= $row['id'] ?>"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-xs sm:text-sm transition">
                                   🔐 Reset
                                </a>
                                <a href="?hapus=<?= $row['id'] ?>"
                                   onclick="return confirm('Yakin ingin menghapus akun siswa <?= addslashes($row['nama']) ?>?')"
                                   class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-xs sm:text-sm transition">
                                   🗑️ Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center bg-blue-50 border border-blue-200 text-blue-700 py-3 rounded-lg">
            Belum ada siswa di kelas <?= htmlspecialchars($kelas) ?>.
        </div>
    <?php endif; ?>
</div>

</body>
</html>
