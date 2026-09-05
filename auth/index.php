<?php
session_start();

// Jika sudah login, arahkan sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard/admin.php");
        exit;
    } elseif ($_SESSION['role'] == 'guru') {
        header("Location: dashboard/guru.php");
        exit;
    } elseif ($_SESSION['role'] == 'siswa') {
        header("Location: dashboard/siswa.php");
        exit;
    } else {
        // role tidak dikenal, paksa logout
        session_destroy();
        header("Location: index.php?error=role");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ONClass SDN 2 Pabuaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 40px 30px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
        .login-container h3 { color: blue; font-weight: bold; }
        .login-container .form-control { border-radius: 25px; margin-bottom: 15px; }
        .login-container .btn {
            border-radius: 25px;
            padding: 12px;
            font-weight: bold;
            background-color: blue;
            border-color: blue;
            color: #ffffff;
        }
        .login-container .btn:hover {
            background-color: rgb(38, 22, 162);
            border-color: rgb(38, 22, 162);
        }
        .password-wrapper { position: relative; }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #6c757d;
            user-select: none;
        }
        .toggle-password:hover {
            color: #0d6efd;
        }
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center" style="height:100vh;">

    <div class="login-container">

        <h3 class="text-center">SDN 2 Pabuaran</h3>
        <p class="text-center text-muted mb-4">Selamat datang di sistem E-Learning kami</p>

        <!-- Notifikasi login gagal -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center py-2 px-3" role="alert" style="border-radius: 10px;">
                <?php 
                    if ($_GET['error'] == 1) echo "Username atau password salah!";
                    elseif ($_GET['error'] == "role") echo "Role tidak dikenali, silakan hubungi admin.";
                ?>
            </div>
        <?php endif; ?>

        <form action="auth/login.php" method="POST" onsubmit="return validasiForm();">
            <div class="mb-3">
                <input type="text" name="username" id="username" class="form-control" placeholder="Username">
            </div>
            <div class="mb-3 password-wrapper">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                <i class="bi bi-eye-slash toggle-password" id="togglePassword" title="Tampilkan/Sembunyikan Password"></i>
            </div>
            <button type="submit" name="login" class="btn w-100">Login</button>
        </form>
    </div>

    <script>
        // Ganti icon mata
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const isHidden = password.type === 'password';
            password.type = isHidden ? 'text' : 'password';
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        function validasiForm() {
            const username = document.getElementById("username");
            const password = document.getElementById("password");

            if (username.value.trim() === "") {
                alert("Silakan isi username Anda.");
                username.focus();
                return false;
            }

            if (password.value.trim() === "") {
                alert("Silakan isi password Anda.");
                password.focus();
                return false;
            }

            return true;
        }
    </script>

</body>
</html>
