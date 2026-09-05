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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd, #f8f9fa);
        }

        /* CONTAINER LOGIN */
        .login-container {
            max-width: 520px;              /* LEBIH GEDE */
            padding: 55px 45px;
            border-radius: 18px;
            background-color: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        /* JUDUL */
        .login-container h3 {
            color: #0d6efd;
            font-weight: 800;
            font-size: 28px;
        }

        .login-container p {
            font-size: 16px;
        }

        /* INPUT */
        .form-control {
            border-radius: 30px;
            padding: 14px 22px;
            font-size: 16px;
        }

        /* BUTTON */
        .btn-login {
            border-radius: 30px;
            padding: 14px;
            font-size: 17px;
            font-weight: bold;
            background-color: #0d6efd;
            border: none;
        }

        .btn-login:hover {
            background-color: #0b5ed7;
        }

        /* PASSWORD */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
            color: #6c757d;
        }

        .toggle-password:hover {
            color: #0d6efd;
        }

        /* RESPONSIVE HP */
        @media (max-width: 576px) {
            .login-container {
                max-width: 95%;
                padding: 40px 25px;
            }

            .login-container h3 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center min-vh-100">

<div class="login-container">

    <h3 class="text-center">SDN 2 Pabuaran</h3>
    <p class="text-center text-muted mb-4">
        Selamat datang di Sistem E-Learning
    </p>

    <!-- NOTIFIKASI ERROR -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center py-2 rounded-3">
            <?php
                if ($_GET['error'] == 1) {
                    echo "Username atau password salah!";
                } elseif ($_GET['error'] == "role") {
                    echo "Role tidak dikenali, hubungi admin.";
                }
            ?>
        </div>
    <?php endif; ?>

    <!-- FORM LOGIN -->
    <form action="auth/login.php" method="POST" onsubmit="return validasiForm();">

        <div class="mb-3">
            <input type="text" name="username" id="username"
                   class="form-control"
                   placeholder="Username">
        </div>

        <div class="mb-4 password-wrapper">
            <input type="password" name="password" id="password"
                   class="form-control"
                   placeholder="Password">
            <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
        </div>

        <button type="submit" name="login" class="btn btn-login w-100">
            🔐 Login
        </button>

    </form>

</div>

<script>
    // Toggle password
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const isHidden = password.type === 'password';
        password.type = isHidden ? 'text' : 'password';
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });

    // Validasi
    function validasiForm() {
        if (username.value.trim() === "") {
            alert("Username wajib diisi!");
            username.focus();
            return false;
        }
        if (password.value.trim() === "") {
            alert("Password wajib diisi!");
            password.focus();
            return false;
        }
        return true;
    }
</script>

</body>
</html>
