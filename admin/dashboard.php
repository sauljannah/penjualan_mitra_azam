<?php
session_start();

// ============================
// SET TIMEZONE WIT (WAKTU INDONESIA TIMUR)
// ============================
date_default_timezone_set('Asia/Jayapura');

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// INTEGRASI DARK MODE DARI DATABASE
// ======================================
$queryGlobalSetting = mysqli_query($conn, "SELECT tema FROM setting LIMIT 1");
$globalSetting = mysqli_fetch_assoc($queryGlobalSetting);
$tema_sistem = $globalSetting['tema'] ?? 'light';

// ======================================
// TOTAL BARANG
// ======================================
$q_barang = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM barang"
);
$d_barang = mysqli_fetch_assoc($q_barang);
$total_barang = $d_barang['total'] ?? 0;

// ======================================
// STOK MENIPIS
// ======================================
$q_stok = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM barang
     WHERE stok > 0
     AND stok <= stok_minimum"
);
$d_stok = mysqli_fetch_assoc($q_stok);
$stok_menipis = $d_stok['total'] ?? 0;

// ======================================
// STOK HABIS
// ======================================
$q_habis = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM barang
     WHERE stok <= 0"
);
$d_habis = mysqli_fetch_assoc($q_habis);
$stok_habis = $d_habis['total'] ?? 0;

// ======================================
// TOTAL TRANSAKSI
// ======================================
$q_transaksi = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM penjualan"
);
$d_transaksi = mysqli_fetch_assoc($q_transaksi);
$total_transaksi = $d_transaksi['total'] ?? 0;

// ======================================
// PENDAPATAN & KEUNTUNGAN HARI INI (FIXED)
// ======================================
$q_hari_ini = mysqli_query(
    $conn,
    "SELECT
    SUM(total_harga) AS total,
    SUM(keuntungan) AS keuntungan
    FROM penjualan
    WHERE DATE(tanggal) = CURDATE() 
    AND status_pembayaran = 'Lunas'"
);
$d_hari_ini = mysqli_fetch_assoc($q_hari_ini);
$pendapatan_hari_ini = $d_hari_ini['total'] ?? 0;
$keuntungan_hari_ini = $d_hari_ini['keuntungan'] ?? 0;

// ======================================
// PENDAPATAN & KEUNTUNGAN BULAN INI (FIXED - BUG REMOVED)
// ======================================
$q_bulan = mysqli_query(
    $conn,
    "SELECT
    SUM(total_harga) AS total,
    SUM(keuntungan) AS keuntungan
    FROM penjualan
    WHERE MONTH(tanggal) = MONTH(CURDATE())
    AND YEAR(tanggal) = YEAR(CURDATE())
    AND status_pembayaran = 'Lunas'"
);
$d_bulan = mysqli_fetch_assoc($q_bulan);
$pendapatan_bulan = $d_bulan['total'] ?? 0;
$keuntungan_bulan = $d_bulan['keuntungan'] ?? 0;

// ======================================
// PENDAPATAN & KEUNTUNGAN TAHUN INI (FIXED)
// ======================================
$q_tahun = mysqli_query(
    $conn,
    "SELECT
        SUM(total_harga) AS total,
        SUM(keuntungan) AS keuntungan
     FROM penjualan
     WHERE YEAR(tanggal) = YEAR(CURDATE())
     AND status_pembayaran = 'Lunas'"
);
$d_tahun = mysqli_fetch_assoc($q_tahun);
$pendapatan_tahun = $d_tahun['total'] ?? 0;
$keuntungan_tahun = $d_tahun['keuntungan'] ?? 0;

// ======================================
// TOTAL ESTIMASI LABA POTENSIAL (LUNAS + HUTANG)
// ======================================
$q_potensial = mysqli_query(
    $conn,
    "SELECT SUM(keuntungan) AS total_potensial
     FROM penjualan"
);
$d_potensial = mysqli_fetch_assoc($q_potensial);
$laba_potensial = $d_potensial['total_potensial'] ?? 0;

// ======================================
// TRANSAKSI TERBARU
// ======================================
$transaksi = mysqli_query(
    $conn,
    "SELECT *
    FROM penjualan
    ORDER BY id_penjualan DESC
    LIMIT 5"
);

// ======================================
// GRAFIK PENJUALAN (FIXED)
// ======================================
$grafik = mysqli_query(
    $conn,
    "SELECT
    DATE(tanggal) AS tgl,
    SUM(total_harga) AS total
    FROM penjualan
    WHERE status_pembayaran = 'Lunas'
    GROUP BY DATE(tanggal)
    ORDER BY DATE(tanggal) ASC
    LIMIT 7"
);

$label_grafik = [];
$data_grafik  = [];

while($g = mysqli_fetch_assoc($grafik)){
    $label_grafik[] = date(
        'd M',
        strtotime($g['tgl'])
    );
    $data_grafik[] = (int)$g['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            transition: background 0.3s, color 0.3s;
        }

        /* LIGHT MODE (DEFAULT) */
        body{
            background:#f4f7fb;
            font-family:'Segoe UI',sans-serif;
            color:#2d3436;
            padding-top: 70px;
        }
        .topbar, .dashboard-card, .chart-box, .income-card, .transaction-box { background: white; border: 1px solid #eef2f7; color: #2d3436; }
        .table tbody tr { background: white; color: #2d3436; }
        .table thead th { background: #fff8f1; color: #ff7b00; }

        /* DARK MODE STYLING */
        body.dark-theme { background: #0f172a; color: #ffffff; }
        body.dark-theme .topbar, 
        body.dark-theme .dashboard-card, 
        body.dark-theme .chart-box, 
        body.dark-theme .income-card, 
        body.dark-theme .transaction-box { background: #1e293b; border-color: #334155; color: #ffffff; }
        body.dark-theme .topbar p, 
        body.dark-theme .dashboard-card h6, 
        body.dark-theme .income-card small,
        body.dark-theme .text-muted { color: #cbd5e1 !important; }
        body.dark-theme .table tbody tr { background: #1e293b; color: #ffffff; box-shadow: 0 3px 12px rgba(0,0,0,0.2); }
        body.dark-theme .table thead th { background: #334155; color: #ff7b00; }
        body.dark-theme .table td { color: #ffffff; }

        .content{ padding:25px; }

        .topbar{
            border-radius:25px;
            padding:22px 28px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:25px;
            box-shadow:0 6px 18px rgba(0,0,0,0.04);
        }

        .topbar h2{ font-size:30px; font-weight:700; }
        .topbar p{ color:#7f8c8d; margin-top:4px; }

        .profile-admin{
            background:#fff3e8;
            color:#ff7b00;
            padding:10px 18px;
            border-radius:14px;
            font-weight:600;
        }

        .dashboard-card{
            border-radius:25px;
            padding:24px;
            box-shadow:0 6px 18px rgba(0,0,0,0.04);
            transition:0.3s;
            overflow:hidden;
            position:relative;
            text-decoration:none;
            display:block;
            cursor:pointer;
        }

        .dashboard-card:hover{
            transform:translateY(-5px);
            box-shadow:0 10px 25px rgba(0,0,0,0.1);
            color:inherit;
        }

        .card-flex{ display:flex; justify-content:space-between; align-items:center; }
        .dashboard-card h6{ color:#7f8c8d; font-size:14px; margin-bottom:10px; }
        .dashboard-card h3{ font-size:30px; font-weight:700; }

        .icon-box{
            width:65px;
            height:65px;
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
        }

        .orange{ background:#fff3e8; color:#ff7b00; }
        .blue{ background:#eaf2ff; color:#0d6efd; }
        .red{ background:#ffeaea; color:#dc3545; }
        .yellow{ background:#fff8e5; color:#f39c12; }
        .green{ background:#e9fff2; color:#198754; }
        .purple{ background:#f3ecff; color:#6f42c1; }
        .gold{ background:#fff8e1; color:#f39c12; }

        .chart-box{ border-radius:28px; padding:28px; box-shadow:0 6px 18px rgba(0,0,0,0.04); height:100%; }
        .chart-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .chart-header h4{ font-weight:700; margin-bottom:5px; }
        .chart-badge{ background:#fff3e8; color:#ff7b00; padding:8px 16px; border-radius:30px; font-size:13px; font-weight:600; }
        .chart-container{ position:relative; height:350px; }

        .income-card{ border-radius:25px; padding:24px; box-shadow:0 6px 18px rgba(0,0,0,0.04); margin-bottom:20px; transition:0.3s; }
        .income-card:hover{ transform:translateY(-4px); }
        .income-card small{ color:#7f8c8d; }
        .income-card h4{ margin-top:10px; font-size:28px; font-weight:700; }
        .income-footer{ margin-top:18px; padding-top:18px; border-top:1px solid #eef2f7; display:flex; justify-content:space-between; }
        body.dark-theme .income-footer { border-top-color: #334155; }
        .income-tag{ padding:8px 14px; border-radius:30px; font-size:12px; font-weight:600; }
        .tag-orange{ background:#fff3e8; color:#ff7b00; }
        .tag-blue{ background:#eaf2ff; color:#0d6efd; }

        .transaction-box{ margin-top:25px; border-radius:28px; padding:28px; box-shadow:0 6px 18px rgba(0,0,0,0.04); }
        .transaction-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
        .transaction-header h4{ font-weight:700; }
        .transaction-count{ background:#fff3e8; color:#ff7b00; padding:9px 16px; border-radius:30px; font-size:13px; font-weight:600; }
        .table{ border-collapse:separate; border-spacing:0 12px; background: transparent; }
        .table td{ border:none; padding:18px 15px; vertical-align:middle; }
        .status{ padding:8px 14px; border-radius:30px; font-size:12px; font-weight:700; }
        .status.lunas{ background:#e9fff2; color:#198754; }
        .status.hutang{ background:#ffeaea; color:#dc3545; }

        @media(max-width:992px){
            .topbar{ flex-direction:column; align-items:flex-start; gap:15px; }
        }

        .offcanvas {
            background: linear-gradient(180deg, #0d6efd, #0a46a6) !important;
            color: #ffffff;
            width: 290px !important;
            border-right: none;
        }
        .sidebar-header-custom {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        .profile-section {
            padding: 15px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            margin: 10px 15px;
        }
        .profile-img {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            color: white;
        }
        .profile-info h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: white;
        }
        .profile-info span {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.75);
        }
        
        .sidebar-nav-container {
            padding: 10px 15px;
        }
        .menu-item-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }
        .menu-item-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }
        .menu-item-link i.menu-icon {
            font-size: 18px;
            margin-right: 12px;
        }
        
        .submenu-container {
            background-color: #f1f3f5;
            border-radius: 10px;
            margin: 5px 0 10px 0;
            padding: 6px 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
        }
        .submenu-link {
            display: flex;
            align-items: center;
            padding: 10px 20px 10px 40px;
            color: #333333;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .submenu-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: #0d6efd;
        }
        .submenu-link.active {
            color: #0d6efd;
            font-weight: 600;
            background-color: rgba(13, 110, 253, 0.08);
        }
        .submenu-link i {
            font-size: 16px;
            margin-right: 12px;
            color: #555;
        }
        .submenu-link.text-danger i {
            color: #dc3545;
        }
        
        .menu-item-link[aria-expanded="true"] i.arrow-icon {
            transform: rotate(180deg);
        }
        .menu-item-link i.arrow-icon {
            transition: transform 0.2s;
            font-size: 12px;
        }

        @media print {
            .navbar, .btn, form, .navbar-toggler, .offcanvas, .filter-section {
                display: none !important;
            }
            .content {
                margin-top: 0 !important;
                padding: 0 !important;
            }
            body {
                background: white;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
</head>

<body class="<?= $tema_sistem == 'dark' ? 'dark-theme' : ''; ?>">

<nav class="navbar fixed-top shadow-sm <?= $tema_sistem == 'dark' ? 'navbar-dark bg-dark' : 'bg-body-tertiary'; ?>">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
  
  <div class="sidebar-header-custom d-flex justify-content-between align-items-center">
    <span class="fs-5 fw-bold text-white d-flex align-items-center gap-2">
        <i class="bi bi-shop"></i> MITRA AZAM
    </span>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="profile-section d-flex align-items-center gap-3">
    <div class="profile-img">
        <i class="bi bi-person-fill"></i>
    </div>
    <div class="profile-info">
        <h6><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></h6>
        <span>
            <i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i> 
            <?= htmlspecialchars(ucfirst($_SESSION['level'] ?? 'Kasir')); ?>
        </span>
    </div>
  </div>

  <div class="offcanvas-body p-0">
    <div class="sidebar-nav-container">
        
        <div class="mb-1">
            <a href="dashboard.php" class="menu-item-link">
                <span><i class="bi bi-speedometer2 menu-icon"></i> Dashboard</span>
            </a>
        </div>
        
        <div class="mb-1">
            <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuBarang" aria-expanded="false">
                <span><i class="bi bi-box-seam menu-icon"></i> Data Barang</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse" id="menuBarang">
                <div class="submenu-container">
                    <a href="barang.php" class="submenu-link"><i class="bi bi-list-ul"></i> Semua Barang</a>
                    <a href="tambah_barang.php" class="submenu-link"><i class="bi bi-plus-circle"></i> Tambah Barang</a>
                    <a href="stok_barang_masuk.php" class="submenu-link"><i class="bi bi-journal-arrow-down"></i> Stok Barang Masuk</a>
                    <a href="riwayat_barang_masuk.php" class="submenu-link"><i class="bi bi-download"></i> Riwayat Barang Masuk</a>
                </div>
            </div>
        </div>
        
        <div class="mb-1">
            <button class="menu-item-link" type="button" data-bs-toggle="collapse" data-bs-target="#menuLaporan" aria-expanded="true">
                <span><i class="bi bi-file-earmark-text menu-icon"></i> Laporan</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse show" id="menuLaporan">
                <div class="submenu-container">
                    <a href="laporan.php" class="submenu-link active"><i class="bi bi-file-earmark-spreadsheet"></i> Ringkasan Laporan</a>
                    <a href="laba_rugi.php" class="submenu-link"><i class="bi bi-cash-coin"></i> Laba Rugi</a>
                </div>
            </div>
        </div>
        
        <div class="mb-1">
            <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuSetting" aria-expanded="false">
                <span><i class="bi bi-gear menu-icon"></i> Setting</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse" id="menuSetting">
                <div class="submenu-container">
                    <a href="setting.php" class="submenu-link"><i class="bi bi-sliders"></i> Pengaturan Umum</a>
                    
                    <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="../admin/manajemen_user.php" class="submenu-link"><i class="bi bi-people"></i> Manajemen User</a>
                    <?php endif; ?>
                    
                    <hr class="my-1 text-muted">
                    <a href="../auth/logout.php" class="submenu-link text-danger fw-semibold">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </a>
                </div>
            </div>
        </div>

    </div>
  </div>
</div>

<div class="content">

    <div class="topbar">
        <div>
            <h2>Dashboard Admin</h2>
            <p>Sistem Informasi Toko Bangunan Mitra Azam</p>
        </div>
        <div class="profile-admin">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- 4 Kartu Pertama -->
        <div class="col-md-6 col-lg-4">
            <a href="barang.php" class="dashboard-card">
                <div class="card-flex">
                    <div>
                        <h6>Total Barang</h6>
                        <h3><?= $total_barang; ?></h3>
                    </div>
                    <div class="icon-box orange"><i class="bi bi-box-seam"></i></div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="laporan.php" class="dashboard-card">
                <div class="card-flex">
                    <div>
                        <h6>Total Transaksi</h6>
                        <h3><?= $total_transaksi; ?></h3>
                    </div>
                    <div class="icon-box blue"><i class="bi bi-receipt"></i></div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="barang.php?filter=habis" class="dashboard-card">
                <div class="card-flex">
                    <div>
                        <h6>Stok Habis</h6>
                        <h3><?= $stok_habis; ?></h3>
                    </div>
                    <div class="icon-box red"><i class="bi bi-x-circle"></i></div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="barang.php?filter=menipis" class="dashboard-card">
                <div class="card-flex">
                    <div>
                        <h6>Stok Menipis</h6>
                        <h3><?= $stok_menipis; ?></h3>
                    </div>
                    <div class="icon-box yellow"><i class="bi bi-exclamation-circle"></i></div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="dashboard-card">
                <div class="card-flex">
                    <div>
                        <h6>Keuntungan Hari Ini</h6>
                        <h3>Rp <?= number_format($keuntungan_hari_ini,0,',','.'); ?></h3>
                    </div>
                    <div class="icon-box green"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="dashboard-card">
                <div class="card-flex">
                    <div>
                        <h6>Keuntungan Bulan</h6>
                        <h3>Rp <?= number_format($keuntungan_bulan,0,',','.'); ?></h3>
                    </div>
                    <div class="icon-box purple"><i class="bi bi-graph-up"></i></div>
                </div>
            </div>
        </div>

        <!-- Keuntungan Tahunan -->
        <div class="col-md-6 col-lg-4">
            <div class="dashboard-card">
                <div class="card-flex">
                    <div>
                        <h6>Keuntungan Tahunan</h6>
                        <h3>Rp <?= number_format($keuntungan_tahun,0,',','.'); ?></h3>
                    </div>
                    <div class="icon-box gold"><i class="bi bi-calendar3"></i></div>
                </div>
            </div>
        </div>

        <!-- Estimasi Laba Potensial -->
        <div class="col-md-6 col-lg-4">
            <div class="dashboard-card">
                <div class="card-flex">
                    <div>
                        <h6>Estimasi Laba Potensial</h6>
                        <h3>Rp <?= number_format($laba_potensial, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box purple"><i class="bi bi-wallet2"></i></div>
                </div>
                <small class="text-muted">Total laba jika semua hutang lunas</small>
            </div>
        </div>

    <div class="row mt-4 g-4">
        <div class="col-lg-8">
            <div class="chart-box">
                <div class="chart-header">
                    <div>
                        <h4><i class="bi bi-bar-chart-line-fill text-warning"></i> Grafik Penjualan</h4>
                        <small class="text-muted">Statistik penjualan 7 hari terakhir</small>
                    </div>
                    <div class="chart-badge">Penjualan Aktif</div>
                </div>
                <div class="chart-container">
                    <canvas id="grafikPenjualan"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="income-card">
                <small>Pendapatan Hari Ini</small>
                <h4>Rp <?= number_format($pendapatan_hari_ini,0,',','.'); ?></h4>
                <div class="income-footer">
                    <div>
                        <small>Keuntungan</small>
                        <div class="fw-bold">Rp <?= number_format($keuntungan_hari_ini,0,',','.'); ?></div>
                    </div>
                    <div><span class="income-tag tag-orange">Hari Ini</span></div>
                </div>
            </div>

            <div class="income-card">
                <small>Pendapatan Bulan Ini</small>
                <h4>Rp <?= number_format($pendapatan_bulan,0,',','.'); ?></h4>
                <div class="income-footer">
                    <div>
                        <small>Keuntungan</small>
                        <div class="fw-bold">Rp <?= number_format($keuntungan_bulan,0,',','.'); ?></div>
                    </div>
                    <div><span class="income-tag tag-blue">Bulanan</span></div>
                </div>
            </div>

            <div class="income-card">
                <small>Pendapatan Tahun Ini</small>
                <h4>Rp <?= number_format($pendapatan_tahun,0,',','.'); ?></h4>
                <div class="income-footer">
                    <div>
                        <small>Keuntungan</small>
                        <div class="fw-bold">Rp <?= number_format($keuntungan_tahun,0,',','.'); ?></div>
                    </div>
                    <div><span class="income-tag tag-orange">Tahunan</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="transaction-box">
        <div class="transaction-header">
            <div>
                <h4><i class="bi bi-clock-history text-warning"></i> Transaksi Terbaru</h4>
                <small class="text-muted">Data transaksi terbaru toko</small>
            </div>
            <div class="transaction-count"><?= $total_transaksi; ?> Transaksi</div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Bayar</th>
                        <th>Kembalian</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                while($t = mysqli_fetch_assoc($transaksi)):
                    // Logika: Kita prioritaskan status dari database.
                    // Jika database menulis 'Lunas', maka tampilkan Lunas.
                    // Jika database menulis 'Hutang' atau lainnya, maka tampilkan Belum Lunas.
                    $is_lunas = (isset($t['status_pembayaran']) && $t['status_pembayaran'] == 'Lunas');
                    
                    $status_badge = $is_lunas ? 'lunas' : 'hutang';
                    $status_text  = $is_lunas ? 'Lunas' : 'Belum Lunas';
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td>
                        <div class="fw-bold"><?= date('d M Y', strtotime($t['tanggal'])); ?></div>
                        <small class="text-muted"><?= date('H:i', strtotime($t['tanggal'])); ?> WIT</small>
                    </td>
                    <td class="fw-bold text-success">Rp <?= number_format($t['total_harga'],0,',','.'); ?></td>
                    <td>Rp <?= number_format($t['bayar'],0,',','.'); ?></td>
                    <td>Rp <?= number_format(($t['bayar'] - $t['total_harga']),0,',','.'); ?></td>
                    <td><span class="status <?= $status_badge; ?>"><?= $status_text; ?></span></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Integrasi Grafik Chart.js
    const ctx = document.getElementById('grafikPenjualan').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($label_grafik); ?>,
            datasets: [{
                label: 'Omset Penjualan (Rp)',
                data: <?= json_encode($data_grafik); ?>,
                borderColor: '#ff7b00',
                backgroundColor: 'rgba(255, 123, 0, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
</body>
</html>