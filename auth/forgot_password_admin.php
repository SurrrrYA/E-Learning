<?php
session_start();
include("../config/koneksi.php");

// load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Load kredensial dari file .env di root project
$env = parse_ini_file(__DIR__ . '/../.env');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));

    // cek email admin
    $stmt = $koneksi->prepare("SELECT * FROM users WHERE LOWER(email)=? AND role='admin' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin) {
        $token   = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // hapus token lama
        $koneksi->query("DELETE FROM password_resets WHERE user_id='{$admin['id']}'");

        // simpan token baru
        $stmt2 = $koneksi->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)");
        $stmt2->bind_param("iss", $admin['id'], $token, $expires);
        $stmt2->execute();

        // link reset
        $resetLink = "http://localhost/E-Learning/auth/reset_password.php?token=$token";

        // kirim email via Gmail SMTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $env['SMTP_EMAIL'];    // Mengambil dari .env
            $mail->Password   = $env['SMTP_PASSWORD']; // Mengambil dari .env
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($env['SMTP_EMAIL'], 'E-Learning');
            $mail->addAddress($admin['email'], $admin['nama']);

            $mail->isHTML(true);
            $mail->Subject = "Reset Password Admin E-Learning";
            $mail->Body    = "Halo Admin,<br><br>Klik link berikut untuk reset password Anda:<br>
                              <a href='$resetLink'>$resetLink</a><br><br>Link ini berlaku 1 jam.";
            $mail->AltBody = "Halo Admin,\n\nKlik link berikut untuk reset password Anda:\n$resetLink\n\nLink ini berlaku 1 jam.";

            $mail->send();
            $msg = "✅ Link reset password sudah dikirim ke email.";
        } catch (Exception $e) {
            $msg = "❌ Email gagal dikirim. Error: {$mail->ErrorInfo}";
        }
    } else {
        $msg = "❌ Email tidak ditemukan atau bukan admin.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password Admin</title>
</head>
<body>
    <h2>Lupa Password (Admin)</h2>
    <?php if (!empty($msg)) echo "<p>$msg</p>"; ?>
    <form method="POST">
        <label>Email Admin: <input type="email" name="email" required></label>
        <button type="submit">Kirim Link Reset</button>
    </form>
</body>
</html>