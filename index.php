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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #2454b8;
    --primary-dark: #1c3f92;
    --ink: #1f2937;
    --muted: #6b7280;
    --border: #e5e7eb;
}

* { font-family: 'Inter', 'Segoe UI', sans-serif; }

body {
    margin: 0;
    min-height: 100vh;
    background: #f5f6f8;
    color: var(--ink);
}

.shell {
    min-height: 100vh;
    display: flex;
}

/* ===== PANEL KIRI (BRAND) ===== */
.brand-panel {
    flex: 1.1;
    background: linear-gradient(150deg, #274bb0 0%, #4c6ef5 55%, #7048e8 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 48px 52px;
}
.brand-panel::before {
    content: "";
    position: absolute;
    width: 280px; height: 280px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    top: -90px; right: -80px;
}
.brand-panel::after {
    content: "";
    position: absolute;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.07);
    bottom: -60px; left: -50px;
}

.brand-top { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; }
.brand-logo {
    width: 42px; height: 42px;
    border-radius: 11px;
    background: rgba(255,255,255,0.16);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800;
    font-size: 1rem;
}
.brand-top .name { font-weight: 700; font-size: 1.05rem; }

.brand-mid { position: relative; z-index: 1; }
.brand-mid h1 {
    font-size: 2.1rem;
    font-weight: 800;
    line-height: 1.25;
    margin-bottom: 14px;
    max-width: 420px;
}
.brand-mid p {
    font-size: 0.98rem;
    opacity: 0.88;
    max-width: 380px;
    line-height: 1.6;
}

.brand-stats {
    position: relative;
    z-index: 1;
    display: flex;
    gap: 28px;
}
.brand-stats div { font-size: 0.82rem; opacity: 0.85; }
.brand-stats strong { display: block; font-size: 1.3rem; font-weight: 800; opacity: 1; }

/* ===== PANEL KANAN (FORM) ===== */
.form-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    background: #fff;
}

.login-card { width: 100%; max-width: 360px; }

.login-card h2 {
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 4px;
}
.login-card .sub {
    color: var(--muted);
    font-size: 0.9rem;
    margin-bottom: 28px;
}

.form-label {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 6px;
}

.form-control {
    border-radius: 10px;
    border: 1.5px solid var(--border);
    padding: 12px 14px;
    font-size: 0.95rem;
}
.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(36, 84, 184, 0.12);
}

.password-wrapper { position: relative; }
.toggle-password {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--primary);
    font-size: 0.82rem;
    font-weight: 700;
    user-select: none;
}

.btn-login {
    border-radius: 10px;
    padding: 13px;
    font-size: 0.97rem;
    font-weight: 700;
    background: linear-gradient(120deg, #2454b8, #4c6ef5);
    border: none;
    color: #fff;
    width: 100%;
    margin-top: 8px;
    box-shadow: 0 6px 16px rgba(36, 84, 184, 0.25);
    transition: transform .12s, box-shadow .12s;
}
.btn-login:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(36, 84, 184, 0.32);
}

.alert-error {
    background: #fdecec;
    border: 1px solid #f4c6c6;
    color: #b3261e;
    border-radius: 9px;
    padding: 10px 14px;
    font-size: 0.85rem;
    text-align: center;
    margin-bottom: 20px;
}

@media (max-width: 900px) {
    .brand-panel { display: none; }
    .form-panel { padding: 60px 24px; }
}
</style>
</head>

<body>

<div class="shell">

    <!-- PANEL KIRI -->
    <div class="brand-panel">
        <div class="brand-top">
            <div class="brand-logo">SD</div>
            <div class="name">ONClass</div>
        </div>

        <div class="brand-mid">
            <h1>Belajar jadi lebih teratur untuk seluruh warga sekolah.</h1>
            <p>Sistem E-Learning SDN 2 Pabuaran — tempat guru membagikan materi, siswa mengumpulkan tugas, dan semuanya tercatat rapi di satu tempat.</p>
        </div>

        <div class="brand-stats">
            <div><strong>Materi</strong> Terpusat</div>
            <div><strong>Tugas</strong> Terpantau</div>
            <div><strong>Nilai</strong> Transparan</div>
        </div>
    </div>

    <!-- PANEL KANAN -->
    <div class="form-panel">
        <div class="login-card">

            <h2>Masuk</h2>
            <div class="sub">Gunakan akun yang diberikan sekolah</div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert-error">
                    <?php
                        if ($_GET['error'] == 1) {
                            echo "Username atau password salah.";
                        } elseif ($_GET['error'] == "role") {
                            echo "Role tidak dikenali, hubungi admin.";
                        } elseif ($_GET['error'] == "notlogin") {
                            echo "Silakan login terlebih dahulu.";
                        } elseif ($_GET['error'] == "session_expired") {
                            echo "Sesi Anda telah berakhir, silakan login kembali.";
                        } elseif ($_GET['error'] == "unauthorized") {
                            echo "Anda tidak memiliki akses ke halaman tersebut.";
                        }
                    ?>
                </div>
            <?php endif; ?>

            <form action="auth/login.php" method="POST" onsubmit="return validasiForm();">

                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" name="username" id="username"
                           class="form-control"
                           placeholder="Masukkan username" autocomplete="username">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password"
                               class="form-control"
                               placeholder="Masukkan password" autocomplete="current-password">
                        <span class="toggle-password" id="togglePassword">LIHAT</span>
                    </div>
                </div>

                <button type="submit" name="login" class="btn-login">Login</button>

            </form>

        </div>
    </div>

</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const isHidden = password.type === 'password';
        password.type = isHidden ? 'text' : 'password';
        this.textContent = isHidden ? 'SEMBUNYIKAN' : 'LIHAT';
    });

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