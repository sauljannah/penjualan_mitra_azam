<?php

session_start();
require_once 'config/koneksi.php';

/*
|--------------------------------------------------------------------------
| REDIRECT LOGIN
|--------------------------------------------------------------------------
*/
if(isset($_SESSION['level'])){
    if($_SESSION['level'] == 'admin'){
        header("Location: admin/dashboard.php");
        exit;
    }
    if($_SESSION['level'] == 'kasir'){
        header("Location: kasir/dashboard.php");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| PROFIL TOKO
|--------------------------------------------------------------------------
*/
$nama_toko   = "TOKO MITRA AZAM";
$jenis_usaha = "Toko Retail";
$alamat      = "-";
$telepon     = "-";
$email       = "-";
$deskripsi   = "Sistem Informasi Penjualan";
$logo        = "logo.png";

$qProfil = mysqli_query($conn, "SELECT * FROM profil_toko LIMIT 1");

if($qProfil && mysqli_num_rows($qProfil) > 0){
    $profil = mysqli_fetch_assoc($qProfil);
    $nama_toko   = $profil['nama_toko'];
    $jenis_usaha = $profil['jenis_usaha'];
    $alamat      = $profil['alamat'];
    $telepon     = $profil['telepon'];
    $email       = $profil['email'];
    $deskripsi   = $profil['deskripsi'];
    $logo        = $profil['logo'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Penjualan - <?= $nama_toko; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *{
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html {
            scroll-behavior: smooth;
        }
        
        /* BACKGROUND GRADASI BIRU HITAM */
        body {
            background: #0f172a;
            background-image: 
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.25), transparent 40%),
                radial-gradient(circle at bottom right, rgba(30, 64, 175, 0.2), transparent 40%);
            overflow-x: hidden;
            color: #ffffff; /* Memastikan semua teks bawaan berwarna putih */
        }

        /* NAVBAR */
        .navbar {
            background: rgba(15, 23, 42, 0.85) !important;
            backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 18px 0;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 20px;
            letter-spacing: -0.5px;
            color: #ffffff !important;
        }

        /* HERO SECTION */
        .hero {
            min-height: 85vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 60px 0;
            /* Membawa kembali gradasi linear biru-hitam premium */
            background: linear-gradient(135deg, #0f172a, #1e293b, #2563eb) !important;
        }
        .hero h1 {
            font-size: 56px;
            font-weight: 800;
            letter-spacing: -1.5px;
            line-height: 1.15;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p {
            font-size: 18px;
            color: #e2e8f0; /* Berwarna putih abu terang agar kontras */
            font-weight: 400;
            max-width: 90%;
        }
        
        /* LOGO CONTAINER */
        .logo-wrapper {
            position: relative;
            display: inline-block;
        }
        .logo-box {
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            animation: float 6s ease-in-out infinite;
        }
        .logo-box img {
            width: 300px;
            height: auto;
            object-fit: contain;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        /* BUTTON PREMIUM */
        .btn-gate {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 38px;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: #fff !important;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .btn-gate:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.5);
            opacity: 0.95;
        }

        /* CARDS & PANELS */
        .panel-title {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #ffffff;
        }
        .card-access {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            background: rgba(30, 41, 59, 0.45); /* Menggunakan basis warna biru gelap transparan */
            backdrop-filter: blur(15px);
            padding: 35px;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-access:hover {
            transform: translateY(-8px);
            border-color: rgba(96, 165, 250, 0.5);
            background: rgba(30, 41, 59, 0.7);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        .card-access i {
            font-size: 40px;
            display: inline-block;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-access h4 {
            font-weight: 600;
            font-size: 20px;
            margin-bottom: 12px;
            color: #ffffff;
        }
        .card-access p {
            color: #cbd5e1; /* Teks deskripsi card putih abu-abu terang */
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        /* INFO BOX */
        .info-panel {
            background: rgba(30, 41, 59, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
        }

        /* FOOTER */
        .footer {
            background: #0b0f19;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 60px 0 40px 0;
            margin-top: 100px;
        }
        .footer h5 {
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #ffffff;
        }
        .footer p {
            font-size: 14px;
            color: #94a3b8;
        }

        @media(max-width:991px){
            .hero { text-align: center; padding-top: 40px; }
            .hero h1 { font-size: 40px; }
            .hero p { max-width: 100%; margin-right: auto; margin-left: auto; }
            .logo-box { margin-top: 50px; width: 200px; height: 200px; border-radius: 30px; }
            .logo-box img { width: 120px; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/logo/logo.png" class="navbar-logo me-2" width="40" alt="logo">
                <?= $nama_toko; ?>
            </a>
            <a href="auth/login.php" class="btn btn-outline-light btn-sm px-4 py-2 rounded-3 fw-medium">
                <i class="bi bi-box-arrow-in-right me-1"></i> Gateway Login
            </a>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="badge px-3 py-2 mb-3 rounded-pill fw-semibold text-uppercase tracking-wider" style="background: rgba(59,130,246,0.2); color: #93c5fd; border: 1px solid rgba(59,130,246,0.3);">
                        Internal Enterprise System
                    </span>
                    <h1>Sistem Informasi <br><span class="gradient-text">Manajemen Penjualan</span></h1>
                    <p class="my-4">
                        Platform integrasi data operasional untuk pencatatan transaksi kasir, pelacakan real-time inventaris barang, dan pengelolaan laporan keuangan berkala pada <?= $nama_toko; ?>.
                    </p>
                    <div class="pt-2">
                        <a href="auth/login.php" class="btn-gate">
                            <span>Masuk Sistem</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="logo-wrapper">
                        <div class="logo-box">
                            <img src="assets/logo/logo.png" alt="Application Logo">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="panel-title mb-2">Hak Akses & Otorisasi</h2>
                <p class="text-light small opacity-75">Pembagian hak kerja sistem berdasarkan jabatan fungsional pengguna</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-5">
                    <div class="card-access">
                        <i class="bi bi-shield-lock"></i>
                        <h4>Administrator</h4>
                        <p>Memiliki otoritas penuh terhadap konfigurasi sistem, manajemen enkripsi data pengguna (kasir), rekapitulasi laporan laba rugi, serta kontrol master data barang.</p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card-access">
                        <i class="bi bi-terminal-dash"></i>
                        <h4>Kasir Operasional</h4>
                        <p>Akses khusus ruang lingkup transaksi penjualan ritel langsung, manajemen keranjang belanja, pencatatan metode pembayaran multi-opsi, dan pencetakan struk fisik konsumen.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="info-panel">
                <div class="row g-4">
                    <div class="col-lg-6 border-end border-secondary border-opacity-20">
                        <h4 class="fw-bold text-white mb-3" style="font-size: 18px;">Informasi Instansi</h4>
                        <div class="text-light opacity-75 small">
                            <p class="mb-2 d-flex align-items-center"><i class="bi bi-geo-alt-fill text-primary me-2"></i> <?= $alamat; ?></p>
                            <p class="mb-2 d-flex align-items-center"><i class="bi bi-telephone-fill text-primary me-2"></i> <?= $telepon; ?></p>
                            <p class="mb-0 d-flex align-items-center"><i class="bi bi-envelope-fill text-primary me-2"></i> <?= $email; ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6 ps-lg-4">
                        <h4 class="fw-bold text-white mb-3" style="font-size: 18px;">Konsolidasi Pembayaran</h4>
                        <div class="row text-light opacity-75 small row-gap-2">
                            <div class="col-6"><i class="bi bi-check2-circle text-success me-2"></i>Tunai (Cash Ledger)</div>
                            <div class="col-6"><i class="bi bi-check2-circle text-success me-2"></i>Giro / Transfer Bank</div>
                            <div class="col-6"><i class="bi bi-check2-circle text-success me-2"></i>QRIS Settlement</div>
                            <div class="col-6"><i class="bi bi-check2-circle text-success me-2"></i>Piutang Dagang</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container text-center">
            <img src="assets/logo/<?= $logo; ?>" width="60" class="mb-3 opacity-75" alt="Footer Logo">
            <h5><?= $nama_toko; ?></h5>
            <p class="mb-0 mt-2 small text-light opacity-50">© <?= date('Y'); ?> Enterprise System. Dikembangkan untuk efisiensi bisnis retail.</p>
        </div>
    </footer>

</body>
</html>