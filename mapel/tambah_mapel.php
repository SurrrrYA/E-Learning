<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] !== 'guru') {
    header('Location: ../index.php');
    exit;
}

// Tambah Mapel
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);
    mysqli_query($koneksi, "INSERT INTO mapel (nama_mapel) VALUES ('$nama')");
    echo "<script>alert('Mata Pelajaran berhasil ditambahkan');location.href='tambah_mapel.php';</script>";
}

// Hapus Mapel
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM mapel WHERE id = $id");
    echo "<script>alert('Mapel berhasil dihapus');location.href='tambah_mapel.php';</script>";
}

// Update Mapel
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);
    mysqli_query($koneksi, "UPDATE mapel SET nama_mapel = '$nama' WHERE id = $id");
    echo "<script>alert('Mapel berhasil diperbarui');location.href='tambah_mapel.php';</script>";
}

// Ambil data semua mapel
$data_mapel = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY nama_mapel ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Mata Pelajaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center p-4 sm:p-6">

    <!-- Form Tambah Mapel -->
    <div class="w-full max-w-md bg-white p-6 sm:p-8 rounded-2xl shadow-lg mt-6">
        <h2 class="text-xl sm:text-2xl font-bold text-center text-blue-700 mb-5">➕ Tambah Mata Pelajaran</h2>

        <form method="post" class="space-y-4">
            <div>
                <label for="nama_mapel" class="block text-gray-700 font-medium mb-1 text-sm sm:text-base">Nama Mata Pelajaran:</label>
                <input type="text" name="nama_mapel" id="nama_mapel" required
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm sm:text-base">
            </div>

            <button type="submit" name="tambah"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-lg text-sm sm:text-base transition">
                Tambah Mapel
            </button>
        </form>
    </div>

    <!-- Daftar Mapel -->
    <div class="w-full max-w-2xl bg-white p-5 sm:p-8 rounded-2xl shadow-lg mt-6">
        <h2 class="text-xl sm:text-2xl font-bold text-center text-blue-700 mb-4">📚 Daftar Mata Pelajaran</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm sm:text-base border border-gray-200 rounded-lg">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="p-2 text-left">No</th>
                        <th class="p-2 text-left">Nama Mapel</th>
                        <th class="p-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($data_mapel)) : ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2"><?= $no++ ?></td>
                        <td class="p-2">
                            <?php if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) : ?>
                                <form method="post" class="flex flex-col sm:flex-row gap-2">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="text" name="nama_mapel" value="<?= htmlspecialchars($row['nama_mapel']) ?>"
                                           class="flex-1 border p-2 rounded-lg text-sm sm:text-base focus:ring-2 focus:ring-blue-400" required>
                                    <button type="submit" name="update"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm sm:text-base transition">
                                        💾 Simpan
                                    </button>
                                </form>
                            <?php else : ?>
                                <?= htmlspecialchars($row['nama_mapel']) ?>
                            <?php endif; ?>
                        </td>
                        <td class="p-2 text-center">
                            <?php if (!isset($_GET['edit']) || $_GET['edit'] != $row['id']) : ?>
                                <a href="?edit=<?= $row['id'] ?>" 
                                   class="inline-block text-blue-600 hover:underline mr-2"> Edit</a>
                                <a href="?hapus=<?= $row['id'] ?>" 
                                   onclick="return confirm('Yakin ingin menghapus mapel ini?')" 
                                   class="inline-block text-red-600 hover:underline"> Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($data_mapel) === 0) : ?>
                    <tr>
                        <td colspan="3" class="p-4 text-center text-gray-500 italic">Belum ada mata pelajaran</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tombol kembali -->
    <a href="../dashboard/guru.php" 
       class="w-full max-w-md mt-8 bg-gray-200 hover:bg-gray-300 text-gray-700 text-center py-2.5 rounded-lg text-sm sm:text-base transition">
        ⬅️ Kembali ke Dashboard
    </a>

</body>
</html>
