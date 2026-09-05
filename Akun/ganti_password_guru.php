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
            $success = "Password Anda berhasil diganti.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ganti Password Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
        body {
            background-color: #f3f4f6;
            font-family: "Inter", sans-serif;
        }
        .card {
            border: none;
            border-radius: 1rem;
            padding: 2rem;
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.75rem;
        }
        .form-control {
            border-right: none;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
            border-radius: 0.5rem;
        }

        @media (max-width: 576px) {
            .card {
                margin: 0.5rem;
                padding: 1.25rem;
                border-radius: 0.75rem;
            }
            h3 {
                font-size: 1.2rem;
                margin-bottom: 1rem;
            }
            .form-control {
                font-size: 0.95rem;
                padding: 0.55rem 0.75rem;
            }
            .input-group-text i {
                font-size: 1.1rem;
            }
            button, a.btn {
                width: 100%;
                margin-bottom: 10px;
                font-size: 0.95rem;
                padding: 0.55rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card shadow-sm col-md-6 mx-auto">
            <h3>Ganti Password Guru</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?><br />
                    <strong>Password baru Anda:</strong> 
                    <code><?= htmlspecialchars($display_password) ?></code>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label for="password_lama" class="form-label">Password Lama</label>
                    <div class="input-group">
                        <input type="password" name="password_lama" id="password_lama" class="form-control" required />
                        <span class="input-group-text toggle-password" data-target="#password_lama">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="password_baru" id="password_baru" class="form-control" required minlength="6" />
                        <span class="input-group-text toggle-password" data-target="#password_baru">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" required minlength="6" />
                        <span class="input-group-text toggle-password" data-target="#konfirmasi_password">
                            <i class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="submit" class="btn btn-primary">Simpan Password</button>
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
