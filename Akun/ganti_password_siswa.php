<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$display_password = '';

if (isset($_POST['submit'])) {
    $password_lama = mysqli_real_escape_string($koneksi, $_POST['password_lama']);
    $password_baru = mysqli_real_escape_string($koneksi, $_POST['password_baru']);
    $konfirmasi_password = mysqli_real_escape_string($koneksi, $_POST['konfirmasi_password']);

    $query = mysqli_query($koneksi, "SELECT password FROM users WHERE id='$user_id'");
    $data = mysqli_fetch_assoc($query);

    if (!$data || !password_verify($password_lama, $data['password'])) {
        $error = "Password lama tidak sesuai.";
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = "Password baru dan konfirmasi tidak cocok.";
    } elseif (password_verify($password_baru, $data['password'])) {
        $error = "Password baru tidak boleh sama dengan password lama.";
    } elseif (strlen($password_baru) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } else {
        $password_baru_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($koneksi, "UPDATE users SET password='$password_baru_hash' WHERE id='$user_id'");

        if ($update) {
            $success = "Password lama Anda berhasil diganti menjadi password baru.";
            $display_password = $password_baru;
        } else {
            $error = "Gagal memperbarui password, silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Ganti Password Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 2.5rem;
        }
        .toggle-password {
            cursor: pointer;
            position: absolute;
            top: 70%;
            right: 0.75rem;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: #444;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm p-4 col-md-6 mx-auto">
            <h3 class="mb-4">🔒 Ganti Password </h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?><br />
                    <strong>Password baru Anda adalah:</strong> <code><?= htmlspecialchars($display_password) ?></code>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3 password-wrapper">
                    <label for="password_lama" class="form-label">Password Lama</label>
                    <input type="password" name="password_lama" id="password_lama" class="form-control" required />
                    <i class="bi bi-eye-slash toggle-password" toggle="#password_lama" title="Tampilkan/Sembunyikan Password"></i>
                </div>

                <div class="mb-3 password-wrapper">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <input type="password" name="password_baru" id="password_baru" class="form-control" required minlength="6" />
                    <i class="bi bi-eye-slash toggle-password" toggle="#password_baru" title="Tampilkan/Sembunyikan Password"></i>
                </div>

                <div class="mb-3 password-wrapper">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" required minlength="6" />
                    <i class="bi bi-eye-slash toggle-password" toggle="#konfirmasi_password" title="Tampilkan/Sembunyikan Password"></i>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Simpan Password</button>
                <a href="../dashboard/siswa.php" class="btn btn-secondary ms-2">Kembali</a>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const input = document.querySelector(this.getAttribute('toggle'));
                const isHidden = input.type === "password";
                input.type = isHidden ? "text" : "password";
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        });
    </script>
</body>
</html>
