<?php
session_start();

// ============================
// SET TIMEZONE WIT
// ============================
date_default_timezone_set('Asia/Jayapura');

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// =====================================
// PROTEKSI LOGIN
// =====================================
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

// =====================================
// AMBIL ID
// =====================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: data_hutang.php");
    exit;
}

$id_penjualan = mysqli_real_escape_string($conn, $_GET['id']);

// Cek data hutang
$cek = mysqli_query($conn, "
    SELECT * FROM penjualan 
    WHERE id_penjualan = '$id_penjualan' 
    AND metode_pembayaran = 'Hutang' 
    AND status_pembayaran = 'Belum Lunas'
");

if (mysqli_num_rows($cek) == 0) {
    $status = "error";
    $pesan = "Data hutang tidak ditemukan atau sudah lunas!";
} else {
    $data = mysqli_fetch_assoc($cek);
    
    // Proses pelunasan
    $update = mysqli_query($conn, "
        UPDATE penjualan SET 
            status_pembayaran = 'Lunas',
            tanggal_pelunasan = NOW()
        WHERE id_penjualan = '$id_penjualan'
    ");

    if ($update) {
        $status = "success";
        $pesan = "Hutang berhasil dilunasi!";
    } else {
        $status = "error";
        $pesan = "Gagal melunasi hutang: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pelunasan Hutang</title>

    <!-- Script Inisialisasi Tema (Mencegah Layar Putih Berkedip) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc, #e0f2fe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ======================================
           DARK MODE STYLING OVERRIDES
           ====================================== */
        [data-bs-theme="dark"] body { 
            background: linear-gradient(135deg, #0f172a, #1e293b) !important; 
            color: #f8fafc; 
        }
        [data-bs-theme="dark"] .result-card { 
            background: #1e293b !important; 
            color: #f8fafc !important; 
            border: 1px solid #334155 !important; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4) !important; 
        }
        [data-bs-theme="dark"] .result-card p.lead {
            color: #94a3b8 !important;
        }

        .result-card {
            max-width: 520px;
            width: 100%;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            background: white;
        }
        .icon-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 45px;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="result-card p-5 text-center">
        
        <?php if($status == 'success'): ?>
            <div class="icon-circle bg-success text-white mb-4">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h3 class="text-success fw-bold">Berhasil!</h3>
            <p class="lead"><?= htmlspecialchars($pesan) ?></p>
            <div class="mt-4">
                <a href="data_hutang.php" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-arrow-left"></i> Kembali ke Data Hutang
                </a>
            </div>
        <?php else: ?>
            <div class="icon-circle bg-danger text-white mb-4">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <h3 class="text-danger fw-bold">Gagal</h3>
            <p class="lead"><?= htmlspecialchars($pesan) ?></p>
            <div class="mt-4">
                <a href="data_hutang.php" class="btn btn-danger btn-lg px-5">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>