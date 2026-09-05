<?php
session_start();
include('../config/koneksi.php');
if ($_SESSION['role'] != 'guru') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT file_path FROM tugas WHERE id='$id'"));
if ($data && !empty($data['file_path'])) {
    $path = "../uploads/tugas/" . $data['file_path'];
    if (file_exists($path)) {
        unlink($path);
    }
}

mysqli_query($koneksi, "DELETE FROM tugas WHERE id='$id'");
echo "<script>alert('Tugas berhasil dihapus');location.href='buat_tugas.php';</script>";
