<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] != 'guru') {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';
$display_password = '';

if (isset($_POST['submit'])) {
    $username_siswa = mysqli_real_escape_string($koneksi, $_POST['username_siswa']);
    $password_baru = mysqli_real_escape_string($koneksi, $_POST['password_baru']);
    $konfirmasi_password = mysqli_real_escape_string($koneksi, $_POST['konfirmasi_password']);

    if (empty($username_siswa) || empty($password_baru) || empty($konfirmasi_password)) {
        $error = "Semua field harus diisi.";
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = "Password baru dan konfirmasi tidak cocok.";
    } elseif (strlen($password_baru) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } else {
        $query = mysqli_query($koneksi, "SELECT id FROM users WHERE username='$username_siswa' AND role='siswa'");
        if (mysqli_num_rows($query) == 0) {
            $error = "Username siswa tidak ditemukan.";
        } else {
            $data = mysqli_fetch_assoc($query);
            $user_id = $data['id'];

            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $update = mysqli_query($koneksi, "UPDATE users SET password='$password_hash' WHERE id='$user_id'");

            if ($update) {
                $success = "Password siswa <strong>$username_siswa</strong> berhasil direset.";
                $display_password = $password_baru;
            } else {
                $error = "Gagal reset password, silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Reset Password Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background: #f3f4f6;
            font-family: "Inter", sans-serif;
        }
        .card {
            border: none;
            border-radius: 1rem;
        }
        h3 {
            font-weight: 700;
            color: #0d6efd;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 500;
            color: #374151;
        }
        .input-group-text {
            background: transparent;
            border-left: none;
            cursor: pointer;
        }
        .input-group input {
            border-right: none;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
            border-radius: 0.5rem;
        }
        @media (max-width: 576px) {
            .card {
                margin: 1rem;
                padding: 1rem !important;
            }
            h3 {
                font-size: 1.3rem;
            }
            button, a.btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card shadow-sm p-4 col-md-6 mx-auto">
            <h3>Reset Password Siswa</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    <?= $success ?><br />
                    <strong>Password baru siswa adalah:</strong> 
                    <code><?= htmlspecialchars($display_password) ?></code>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label for="username_siswa" class="form-label">Username Siswa</label>
                    <input type="text" name="username_siswa" id="username_siswa" class="form-control" placeholder="Masukkan username siswa" required />
                </div>

                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="password_baru" id="password_baru" class="form-control" placeholder="Minimal 6 karakter" required minlength="6" />
                        <span class="input-group-text toggle-password" data-target="#password_baru">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" placeholder="Ulangi password baru" required minlength="6" />
                        <span class="input-group-text toggle-password" data-target="#konfirmasi_password">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="submit" class="btn btn-primary">Reset Password</button>
                    <a href="../dashboard/guru.php" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = document.querySelector(this.dataset.target);
                const icon = this.querySelector('i');
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('bi-eye', isPassword);
                icon.classList.toggle('bi-eye-slash', !isPassword);
            });
        });
    </script>
</body>
</html>
