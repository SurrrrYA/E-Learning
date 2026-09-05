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

// Hapus file dari server jika ada
$file_path = "../uploads/materi/" . $materi['file_path'];
if (file_exists($file_path)) {
    unlink($file_path);
}

// Hapus data dari database
mysqli_query($koneksi, "DELETE FROM materi WHERE id = '$id'");

echo "<script>alert('Materi berhasil dihapus');location.href='upload.php';</script>";
