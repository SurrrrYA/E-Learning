<?php
session_start();
include('../config/koneksi.php');

if ($_SESSION['role'] !== 'guru') {
    header('Location: ../index.php');
    exit;
}

if (!isset($_GET['id'])) {
    die('ID jawaban tidak ditemukan.');
}

$id = intval($_GET['id']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skor = floatval($_POST['skor']);

    if ($skor < 0 || $skor > 100) {
        $message = "Nilai harus antara 0 sampai 100.";
    } else {
        $update = mysqli_query($koneksi, "UPDATE jawaban_tugas SET skor = $skor WHERE id = $id");
        if ($update) {
            $message = "✅ Nilai berhasil disimpan.";
        } else {
            $message = "⚠️ Gagal menyimpan nilai.";
        }
    }
}

$query = mysqli_query($koneksi, "
    SELECT jt.*, u.nama, t.judul 
    FROM jawaban_tugas jt 
    JOIN users u ON jt.user_id = u.id 
    JOIN tugas t ON jt.tugas_id = t.id 
    WHERE jt.id = $id
");

if (mysqli_num_rows($query) === 0) {
    die('Jawaban tidak ditemukan.');
}

$row = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail Jawaban - <?= htmlspecialchars($row['judul']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: #eef2ff;
            margin: 0;
            padding: 40px 20px;
            color: #1e293b;
        }
        .container {
            max-width: 720px;
            background: #fff;
            margin: auto;
            padding: 30px 35px;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.15);
            transition: box-shadow 0.3s ease;
        }
        .container:hover {
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
        }
        h2 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 15px;
            color: #4f46e5;
            letter-spacing: 0.03em;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            font-weight: 600;
            color: #4f46e5;
            text-decoration: none;
            border: 2px solid transparent;
            padding: 8px 14px;
            border-radius: 8px;
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.6);
            text-decoration: none;
        }
        p strong {
            display: inline-block;
            min-width: 120px;
            color: #4338ca;
            font-weight: 600;
        }
        .jawaban-teks {
            background: #f3f4f6;
            padding: 15px 18px;
            border-radius: 10px;
            font-size: 1rem;
            line-height: 1.5;
            white-space: pre-wrap;
            color: #334155;
            box-shadow: inset 0 0 8px rgba(79, 70, 229, 0.1);
            margin-bottom: 25px;
        }
        .file-link {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background: #6366f1;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            transition: background-color 0.3s ease;
        }
        .file-link:hover {
            background: #4338ca;
            box-shadow: 0 6px 16px rgba(67, 56, 202, 0.6);
        }
        form label {
            font-weight: 600;
            display: block;
            margin: 18px 0 8px 0;
            color: #3730a3;
        }
        input[type=number] {
            width: 100%;
            padding: 12px 14px;
            font-size: 1rem;
            border: 2px solid #c7d2fe;
            border-radius: 10px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            color: #1e293b;
        }
        input[type=number]:focus {
            border-color: #4f46e5;
            outline: none;
        }
        button {
            margin-top: 30px;
            background-color: #4f46e5;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
        }
        button:hover {
            background-color: #4338ca;
            box-shadow: 0 8px 24px rgba(67, 56, 202, 0.7);
        }
        .message {
            margin-top: 25px;
            padding: 15px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            background-color: #d1fae5;
            color: #065f46;
            box-shadow: 0 4px 15px rgba(5, 111, 75, 0.2);
            text-align: center;
            user-select: none;
        }
        @media (max-width: 480px) {
            body {
                padding: 20px 10px;
            }
            .container {
                padding: 25px 20px;
            }
            h2 {
                font-size: 1.6rem;
            }
            button {
                font-size: 1rem;
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="lihat_tugas_siswa.php" class="back-link">⬅️ Kembali ke Daftar Jawaban</a>

    <h2>Detail Jawaban Tugas: <?= htmlspecialchars($row['judul']) ?></h2>
    <p><strong>Nama Siswa:</strong> <?= htmlspecialchars($row['nama']) ?></p>

    <p><strong>Jawaban Teks:</strong></p>
    <div class="jawaban-teks"><?= htmlspecialchars($row['jawaban_teks']) ?: '<i>Jawaban kosong</i>' ?></div>

    <?php if (!empty($row['file_path'])): ?>
        <p><strong>File Jawaban:</strong></p>
        <a href="../uploads/tugas/<?= urlencode($row['file_path']) ?>" target="_blank" class="file-link">📄 Lihat / Download File</a>
    <?php else: ?>
        <p><strong>File Jawaban:</strong> <i>Tidak ada file</i></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="skor">Nilai (0-100):</label>
        <input type="number" id="skor" name="skor" value="<?= htmlspecialchars($row['skor'] ?? '') ?>" min="0" max="100" required>

        <button type="submit">Simpan Nilai</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</div>

</body>
</html>
