<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'guru') {
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
    <title>Ganti Password Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            font-size: 1.2rem;
            color: #6c757d;
            transition: color 0.3s ease;
        }
        .toggle-password.active {
            color: #0d6efd; /* biru saat aktif */
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm p-4 col-md-6 mx-auto">
            <h3 class="mb-4">🔒 Ganti Password Guru</h3>

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
                    <span class="toggle-password" toggle="#password_lama" title="Tampilkan/Sembunyikan Password">👁</span>
                </div>
                <div class="mb-3 password-wrapper">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <input type="password" name="password_baru" id="password_baru" class="form-control" required minlength="6" />
                    <span class="toggle-password" toggle="#password_baru" title="Tampilkan/Sembunyikan Password">👁</span>
                </div>
                <div class="mb-3 password-wrapper">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" required minlength="6" />
                    <span class="toggle-password" toggle="#konfirmasi_password" title="Tampilkan/Sembunyikan Password">👁</span>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Simpan Password</button>
                <a href="../dashboard/guru.php" class="btn btn-secondary ms-2">Kembali</a>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = document.querySelector(this.getAttribute('toggle'));
                if (input.type === 'password') {
                    input.type = 'text';
                    this.textContent = '🙈';
                    this.classList.add('active');
                } else {
                    input.type = 'password';
                    this.textContent = '👁';
                    this.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
