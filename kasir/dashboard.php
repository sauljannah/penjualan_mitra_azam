<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK KONEKSI DATABASE
// ======================================
if (!$conn) {
    die("Koneksi database gagal : " . mysqli_connect_error());
}

// ======================================
// CEK LOGIN
// ======================================
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// CEK LEVEL KASIR
// ======================================
if ($_SESSION['level'] != "kasir") {
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// TOTAL BARANG
// ======================================
$query_barang = mysqli_query($conn, "SELECT COUNT(*) AS total FROM barang");
$data_barang = mysqli_fetch_assoc($query_barang);
$total_barang = $data_barang['total'] ?? 0;

// ======================================
// TOTAL TRANSAKSI
// ======================================
$query_penjualan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM penjualan");
$data_penjualan = mysqli_fetch_assoc($query_penjualan);
$total_penjualan = $data_penjualan['total'] ?? 0;

// ======================================
// TOTAL PENDAPATAN
// ======================================
$query_pendapatan = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM penjualan");
$data_pendapatan = mysqli_fetch_assoc($query_pendapatan);
$total_pendapatan = $data_pendapatan['total'] ?? 0;

// ======================================
// PENDAPATAN HARI INI
// ======================================
$query_hari_ini = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM penjualan WHERE DATE(tanggal)=CURDATE()");
$data_hari_ini = mysqli_fetch_assoc($query_hari_ini);
$pendapatan_hari_ini = $data_hari_ini['total'] ?? 0;

// ======================================
// HUTANG JATUH TEMPO HARI INI
// ======================================
$query_jatuh_tempo = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND DATE(jatuh_tempo)=CURDATE()
");

$data_jatuh_tempo = mysqli_fetch_assoc($query_jatuh_tempo);
$total_jatuh_tempo = $data_jatuh_tempo['total'];


// ======================================
// HUTANG AKAN JATUH TEMPO (3 HARI LAGI)
// ======================================
$query_mendekati = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND jatuh_tempo BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 DAY)
AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
");

$data_mendekati = mysqli_fetch_assoc($query_mendekati);
$total_mendekati = $data_mendekati['total'];


// ======================================
// HUTANG SUDAH TERLEWAT JATUH TEMPO
// ======================================
$query_terlambat = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND jatuh_tempo < CURDATE()
");

$data_terlambat = mysqli_fetch_assoc($query_terlambat);
$total_terlambat = $data_terlambat['total'];



// ======================================
// TRANSAKSI TERBARU
// ======================================
$query_transaksi = mysqli_query($conn, "SELECT * FROM penjualan ORDER BY id_penjualan DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *{ font-family:'Poppins',sans-serif; }
        body{ background:#f1f5f9; overflow-x:hidden; padding-top: 70px; }

        /* ======================================
        SIDEBAR MODEREN (OFFCANVAS)
        ====================================== */
        .offcanvas { 
            background: linear-gradient(180deg, #2563eb, #1e3a8a) !important; 
            color: #ffffff; 
            width: 290px !important; 
            border-right: none; 
        }
        .sidebar-header-custom { padding: 25px 20px 10px 20px; }
        .logo{ font-size:24px; font-weight:700; color: white; display: flex; align-items: center; gap: 10px; }
        
        /* BOX PROFIL DI DALAM SIDEBAR */
        .sidebar-profile {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 16px;
            padding: 15px;
            margin: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .profile-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.6);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .profile-info h6 { margin: 0; font-size: 14px; font-weight: 600; color: white; }
        .profile-info span { font-size: 12px; color: rgba(255, 255, 255, 0.75); display: flex; align-items: center; gap: 5px; }

        .sidebar-nav-container { padding: 5px 15px 20px 15px; }
        .sidebar-nav-container a {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            padding: 14px 18px;
            margin-bottom: 10px;
            border-radius: 14px;
            transition: 0.2s ease;
            font-weight: 500;
        }
        .sidebar-nav-container a:hover, .sidebar-nav-container a.active {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            transform: translateX(4px);
        }
        .sidebar-nav-container i { font-size: 18px; margin-right: 12px; }

        /* ======================================
        CONTENT & CARDS
        ====================================== */
        .content{ padding:20px 30px; }
        .welcome{ font-weight:700; color:#0f172a; }
        
        .topbar{
            background:white; border-radius:24px; padding:25px; margin-bottom:30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }


        .card-dashboard{ border:none; border-radius:24px; overflow:hidden; transition:0.3s; }
        .card-dashboard:hover{ transform:translateY(-6px); box-shadow: 0 12px 25px rgba(0,0,0,0.12); }

        .bg-red{
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color:white;
        }

        .bg-yellow{
            background: linear-gradient(135deg, #facc15, #eab308);
            color:#1e293b;
        }

        .bg-purple{
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color:white;
        }

        .bg-blue{ background: linear-gradient(135deg, #3b82f6, #2563eb); color:white; }
        .bg-green{ background: linear-gradient(135deg, #10b981, #059669); color:white; }
        .bg-orange{ background: linear-gradient(135deg, #f59e0b, #d97706); color:white; }
        .icon-card{ font-size:50px; opacity:0.7; }

        .table-card{ border:none; border-radius:24px; overflow:hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
        .table thead{ background:#0f172a; color:white; }
    </style>
</head>
<body>

<nav class="navbar bg-white fixed-top shadow-sm" style="height: 65px;">
  <div class="container-fluid px-4 d-flex align-items-center justify-content-start gap-3">
    <button class="btn btn-primary d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarKasir">
      <i class="bi bi-list fs-5"></i>
    </button>
    <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2 m-0 p-0" href="dashboard.php">
      <i class="bi bi-shop"></i> MITRA AZAM
    </a>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarKasir">
  <div class="sidebar-header-custom d-flex justify-content-between align-items-center">
    <div class="logo">
        <i class="bi bi-shop"></i> MITRA AZAM
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="sidebar-profile">
      <div class="profile-avatar">
          <i class="bi bi-person-fill"></i>
      </div>
      <div class="profile-info">
          <h6><?= htmlspecialchars($_SESSION['nama'] ?? 'Kasir Utama'); ?></h6>
          <span><i class="bi bi-circle-fill text-success" style="font-size: 7px;"></i> <?= htmlspecialchars(ucfirst($_SESSION['level'] ?? 'Kasir')); ?></span>
      </div>
  </div>

  <div class="offcanvas-body p-0">
    <div class="sidebar-nav-container">
        <a href="dashboard.php" class="active">
            <i class="bi bi-house-door-fill"></i> Dashboard
        </a>
        <a href="transaksi.php">
            <i class="bi bi-cart-fill"></i> Transaksi
        </a>
        <a href="data_hutang.php" class="active">
            <i class="bi bi-people-fill"></i> Data Hutang Customer
        </a>
        <a href="riwayat_transaksi.php">
            <i class="bi bi-clock-history"></i> Riwayat Transaksi
        </a>
        <hr class="text-white-50 my-3">
        <a href="../auth/logout.php" class="text-danger-emphasis">
            <i class="bi bi-box-arrow-right text-danger"></i> <span class="text-white">Logout</span>
        </a>
    </div>
  </div>
</div>

<div class="content">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="welcome mb-1">Dashboard Kasir</h2>
            <p class="text-muted mb-0">Sistem Penjualan Toko Mitra Azam</p>
        </div>
        <div>
            <h5 class="m-0 bg-light py-2 px-3 rounded-4 border">
                <i class="bi bi-person-circle text-primary me-2"></i>
                <?= htmlspecialchars($_SESSION['nama']); ?>
            </h5>
        </div>
    </div>

    <!-- CARD NOTIFIKASI HUTANG -->
<div class="row g-4">

    <!-- Mendekati Jatuh Tempo -->
    <div class="col-md-4">
<a href="data_hutang.php?filter=mendekati"
class="text-decoration-none">            <div class="card card-dashboard bg-yellow shadow">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <h1 class="fw-bold">
                                <?= $total_mendekati; ?>
                            </h1>

                            <p class="mb-0">
                                Mendekati Jatuh Tempo
                            </p>
                        </div>

                        <i class="bi bi-alarm-fill icon-card"></i>

                    </div>
                </div>
            </div>
        </a>
    </div>


    <!-- Sudah Terlambat -->
    <div class="col-md-4">
<a href="data_hutang.php?filter=terlambat"
class="text-decoration-none">            <div class="card card-dashboard bg-red shadow">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <h1 class="fw-bold">
                                <?= $total_terlambat; ?>
                            </h1>

                            <p class="mb-0">
                                Lewat Jatuh Tempo
                            </p>
                        </div>

                        <i class="bi bi-exclamation-triangle-fill icon-card"></i>

                    </div>
                </div>
            </div>
        </a>
    </div>


    <!-- Jatuh Tempo Hari Ini -->
    <div class="col-md-4">
<a href="data_hutang.php?filter=hariini"
class="text-decoration-none">            <div class="card card-dashboard bg-purple shadow">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <h1 class="fw-bold">
                                <?= $total_jatuh_tempo; ?>
                            </h1>

                            <p class="mb-0">
                                Jatuh Tempo Hari Ini
                            </p>
                        </div>

                        <i class="bi bi-calendar-check-fill icon-card"></i>

                    </div>
                </div>
            </div>
        </a>
    </div>

</div>

    

        <!-- INFORMASI TOKO -->
<div class="row g-4 mt-2">

    <div class="col-md-4">
        <div class="card card-dashboard bg-blue shadow">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h1 class="fw-bold">
                            <?= $total_barang; ?>
                        </h1>

                        <p class="mb-0 opacity-75">
                            Total Barang
                        </p>
                    </div>

                    <i class="bi bi-box-seam icon-card"></i>

                </div>

            </div>
        </div>
    </div>


    <div class="col-md-4">
        <div class="card card-dashboard bg-green shadow">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h1 class="fw-bold">
                            <?= $total_penjualan; ?>
                        </h1>

                        <p class="mb-0 opacity-75">
                            Total Transaksi
                        </p>
                    </div>

                    <i class="bi bi-cart-check icon-card"></i>

                </div>

            </div>
        </div>
    </div>


    <div class="col-md-4">
        <div class="card card-dashboard bg-orange shadow">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h3 class="fw-bold m-0">
                            Rp <?= number_format($pendapatan_hari_ini,0,',','.'); ?>
                        </h3>

                        <p class="mb-0 opacity-75 mt-2">
                            Pendapatan Hari Ini
                        </p>
                    </div>

                    <i class="bi bi-cash-stack icon-card"></i>

                </div>

            </div>
        </div>
    </div>

</div>

        <div 


    <div class="card table-card mt-5">
        <div class="card-header bg-dark text-white p-3 d-flex align-items-center gap-2">
            <i class="bi bi-clock-history"></i>
            <h5 class="mb-0">Transaksi Terbaru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Kembalian</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    while($d = mysqli_fetch_assoc($query_transaksi)){
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($d['tanggal'])); ?></td>
                        <td class="fw-semibold text-primary">Rp <?= number_format($d['total_harga'], 0, ',', '.'); ?></td>
                        <td>Rp <?= number_format($d['bayar'], 0, ',', '.'); ?></td>
                        <td class="text-success">Rp <?= number_format($d['kembali'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>