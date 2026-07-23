<?php
session_start();
// ============================
// SET TIMEZONE WIT
// ============================
date_default_timezone_set('Asia/Jayapura');
require_once '../config/koneksi.php';
/** @var mysqli $conn */
mysqli_query($conn, "SET time_zone = '" . date('P') . "'");
// ============================
// PROTEKSI LOGIN
// ============================
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}
// ============================
// SINKRONISASI TEMA DARI DATABASE
// ============================
if (isset($_SESSION['id_user'])) {
    $id_user_aktif = $_SESSION['id_user'];
    $query_setting = mysqli_query($conn, "SELECT tema FROM users WHERE id_user = '$id_user_aktif'");
    if ($query_setting && mysqli_num_rows($query_setting) > 0) {
        $data_setting = mysqli_fetch_assoc($query_setting);
        if (!empty($data_setting['tema'])) {
            $_SESSION['tema'] = $data_setting['tema'];
        }
    }
}
$current_tema = $_SESSION['tema'] ?? 'light';
// ============================
// FILTER & QUERY
// ============================
$periode = $_POST['periode'] ?? 'semua';
$tanggal_hari = $_POST['tanggal_hari'] ?? '';
$bulan = $_POST['bulan'] ?? '';
$tahun_bulan = $_POST['tahun_bulan'] ?? date('Y');
$tanggal_awal = $_POST['tanggal_awal'] ?? '';
$tanggal_akhir = $_POST['tanggal_akhir'] ?? '';
$status_bayar = $_POST['status_bayar'] ?? 'semua';
$conditions = [];

// Tambahkan filter status pembayaran
if ($status_bayar != 'semua') {
    $conditions[] = "p.status_pembayaran = '$status_bayar'";
}

if (isset($_POST['filter'])) {
    if ($periode == 'harian' && !empty($tanggal_hari)) {
        $conditions[] = "DATE(p.tanggal) = '$tanggal_hari'";
    } elseif ($periode == 'mingguan' && !empty($tanggal_awal) && !empty($tanggal_akhir)) {
        $conditions[] = "p.tanggal BETWEEN '$tanggal_awal 00:00:00' AND '$tanggal_akhir 23:59:59'";
    } elseif ($periode == 'bulanan' && !empty($bulan)) {
        $conditions[] = "MONTH(p.tanggal) = '$bulan' AND YEAR(p.tanggal) = '$tahun_bulan'";
    }
}

$where = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

// ============================
// QUERY DATA PENJUALAN
// ============================
$query = mysqli_query($conn, "
    SELECT p.*,
           DATE_ADD(p.tanggal, INTERVAL 7 HOUR) AS tanggal_wit,
           u.nama AS nama_kasir
    FROM penjualan p
    LEFT JOIN users u ON p.id_user = u.id_user
    $where
    ORDER BY p.id_penjualan DESC
");
if (!$query) {
    die("Query Error : " . mysqli_error($conn));
}

// Total Summary Berdasarkan Filter yang Aktif
$total_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM penjualan p $where"))['total'] ?? 0;
$total_keuntungan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(keuntungan) AS total FROM penjualan p $where"))['total'] ?? 0;
$total_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id_penjualan) AS total FROM penjualan p $where"))['total'] ?? 0;

// ====================== PERBAIKAN: KOSONGKAN TOTAL SAAT BELUM LUNAS ======================
if ($status_bayar === 'Belum Lunas') {
    $total_penjualan = 0;
    $total_keuntungan = 0;
}
// =======================================================================================

?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="<?= $current_tema ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Toko Bangunan Mitra Azam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
   
    <style>
        :root { --primary: #0d6efd; }
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
            transition: background 0.3s, color 0.3s;
        }
        /* DARK MODE */
        [data-bs-theme="dark"] body { background: #0f172a; color: #e2e8f0; }
        [data-bs-theme="dark"] .card, [data-bs-theme="dark"] .stat-card {
            background: #1e293b !important; border-color: #334155 !important; color: #f1f5f9;
        }
        [data-bs-theme="dark"] .table { color: #e2e8f0 !important; }
        [data-bs-theme="dark"] .table thead.table-dark { background: #1e293b !important; color: #94a3b8; }
        [data-bs-theme="dark"] .table tbody tr:hover { background: #334155 !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        [data-bs-theme="dark"] .navbar { background: #1e293b !important; }
        [data-bs-theme="dark"] .offcanvas { background: linear-gradient(180deg, #1e40af, #1e3a8a) !important; }
        [data-bs-theme="dark"] .submenu-container { background-color: #334155 !important; }
        [data-bs-theme="dark"] .submenu-link { color: #e2e8f0 !important; }
        [data-bs-theme="dark"] .submenu-link:hover { background-color: rgba(255,255,255,0.1) !important; }
        .content { padding: 25px; margin-top: 75px; }
        .stat-card { transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        /* SIDEBAR THEME */
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
            width: 55px;
            height: 55px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            border: 2px solid rgba(255,255,255,.5);
        }
        .profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
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
        .menu-item-link[aria-expanded="true"] i.arrow-icon {
            transform: rotate(180deg);
        }
        .menu-item-link i.arrow-icon {
            transition: transform 0.2s;
            font-size: 12px;
        }
        .icon-box {
            width: 60px; height: 60px; border-radius: 16px;
            display: flex; justify-content: center; align-items: center;
            font-size: 26px; color: white;
        }
        .btn { border-radius: 12px; padding: 10px 20px; font-weight: 600; }
        .form-control, .form-select { border-radius: 12px; padding: 10px; }
        /* ======================================================== */
        /* AESTHETIC & MODERN PRINT STYLING (SUPER CLEAN & PREMIUM) */
        /* ======================================================== */
        .print-header { display: none; }
        @media print {
            @page { size: A4 landscape; margin: 12mm; }
            .navbar, .btn, form, .offcanvas, .filter-section, .stat-card, .card.mb-4.bg-white, .sidebar-header-custom { display: none !important; }
            .content { margin-top: 0 !important; padding: 0 !important; width: 100% !important; }
            .print-header { display: block !important; width: 100%; }
            body { background: #ffffff !important; color: #1e293b !important; font-size: 11px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-print-color-adjust: exact; }
           
            .print-top-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 3px double #0f172a;
                padding-bottom: 12px;
                margin-bottom: 15px;
            }
            .print-brand {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .print-brand-icon {
                width: 48px;
                height: 48px;
                background: #0f172a;
                color: #ffffff;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
            }
            .print-brand-text h2 {
                margin: 0;
                font-size: 20px;
                font-weight: 800;
                color: #0f172a;
                letter-spacing: 0.5px;
            }
            .print-brand-text h5 {
                margin: 0;
                font-size: 12px;
                font-weight: 700;
                color: #d97706;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .print-brand-text p {
                margin: 2px 0 0 0;
                font-size: 10px;
                color: #64748b;
            }
           
            .print-title-box {
                text-align: right;
            }
            .print-title-box h3 {
                margin: 0;
                font-size: 18px;
                font-weight: 800;
                color: #0f172a;
                text-transform: uppercase;
            }
            .print-periode-badge {
                display: inline-block;
                background: #f1f5f9;
                color: #0f172a;
                border: 1px solid #cbd5e1;
                padding: 4px 10px;
                border-radius: 6px;
                font-size: 10px;
                font-weight: 600;
                margin-top: 4px;
            }
            .print-summary-row {
                display: flex;
                gap: 12px;
                margin-bottom: 15px;
            }
            .print-summary-box {
                flex: 1;
                border: 1px solid #e2e8f0;
                padding: 10px 14px;
                border-radius: 8px;
                background: #f8fafc;
                box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            }
            .print-summary-box span {
                font-size: 9px;
                color: #64748b;
                text-transform: uppercase;
                display: block;
                font-weight: 700;
                letter-spacing: 0.5px;
            }
            .print-summary-box h4 {
                margin: 4px 0 0 0;
                font-size: 14px;
                font-weight: 800;
                color: #0f172a;
            }
            .card { border: none !important; box-shadow: none !important; }
            .table-responsive { overflow: visible !important; }
            .table { border-collapse: separate !important; border-spacing: 0; width: 100% !important; margin-bottom: 15px !important; }
            .table th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                border: none !important;
                padding: 8px 10px !important;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .table th:first-child { border-top-left-radius: 6px; border-bottom-left-radius: 6px; }
            .table th:last-child { border-top-right-radius: 6px; border-bottom-right-radius: 6px; }
           
            .table td {
                border-bottom: 1px solid #e2e8f0 !important;
                border-top: none !important;
                color: #334155 !important;
                padding: 8px 10px !important;
                font-size: 11px;
            }
            .table tbody tr:nth-child(even) {
                background-color: #f8fafc !important;
            }
            .badge {
                padding: 3px 8px !important;
                font-size: 9px !important;
                font-weight: 600 !important;
                border-radius: 4px !important;
            }
            .bg-success { background-color: #10b981 !important; color: #fff !important; }
            .bg-warning { background-color: #f59e0b !important; color: #fff !important; }
            .print-footer-section {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-top: 25px;
                page-break-inside: avoid;
            }
            .print-notes {
                border: 1px solid #e2e8f0;
                padding: 10px 14px;
                border-radius: 8px;
                width: 50%;
                background: #f8fafc;
            }
            .print-notes h6 { margin: 0 0 4px 0; font-size: 11px; font-weight: 700; color: #0f172a; }
            .print-notes ul { margin: 0; padding-left: 14px; font-size: 10px; color: #475569; }
            .print-notes li { margin-bottom: 2px; }
           
            .print-signature {
                text-align: center;
                width: 30%;
                font-size: 11px;
                color: #0f172a;
            }
            .print-signature p { margin: 0 0 50px 0; line-height: 1.4; }
            .print-signature .sign-name { font-weight: 700; text-decoration: underline; font-size: 12px; color: #0f172a; }
        }
    </style>
</head>
<body>
<nav class="navbar bg-body-tertiary fixed-top shadow-sm d-print-none">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
   
    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-flex align-items-center gap-2 me-3" id="themeToggleBtn" type="button">
        <i class="bi" id="themeIcon"></i>
        <span class="small fw-semibold d-none d-md-inline" id="themeText"></span>
    </button>
  </div>
</nav>
<div class="offcanvas offcanvas-start d-print-none" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
  <div class="sidebar-header-custom d-flex justify-content-between align-items-center">
    <span class="fs-5 fw-bold text-white d-flex align-items-center gap-2">
        <i class="bi bi-shop"></i> MITRA AZAM
    </span>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="profile-section d-flex align-items-center gap-3">
    <div class="profile-img">
      <?php if (!empty($_SESSION['foto']) && file_exists("../assets/admin/" . $_SESSION['foto'])): ?>
        <img src="../assets/admin/<?= htmlspecialchars($_SESSION['foto']); ?>" class="user-avatar" alt="Profil">
      <?php else: ?>
        <div class="user-avatar-default">
            <i class="bi bi-person text-white"></i>
        </div>
      <?php endif; ?>
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
            <a href="data_hutang.php" class="menu-item-link">
                <span><i class="bi bi-credit-card menu-icon"></i> Data Hutang Customer</span>
            </a>
        </div>
       
        <div class="mb-1">
            <button class="menu-item-link" type="button" data-bs-toggle="collapse" data-bs-target="#menuLaporan" aria-expanded="true">
                <span><i class="bi bi-file-earmark-text menu-icon"></i> Laporan</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse show" id="menuLaporan">
                <div class="submenu-container">
                    <a href="laporan.php" class="submenu-link active"><i class="bi bi-file-earmark-spreadsheet"></i> Ringkasan Laporan</a>
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
    <!-- AESTHETIC PRINT HEADER / KOP LAPORAN -->
    <div class="print-header">
        <div class="print-top-container">
            <div class="print-brand">
                <div class="print-brand-icon">
                    <i class="bi bi-shop"></i>
                </div>
                <div class="print-brand-text">
                    <h2>MITRA AZAM</h2>
                    <h5>Toko Bangunan</h5>
                    <p>Jl. Hj.Falaq Desa Luhu Dusun Limboro Kecamatan Huamual &bull; Lengkap & Terpercaya</p>
                </div>
            </div>
            <div class="print-title-box">
                <h3>Laporan Penjualan</h3>
                <div class="print-periode-badge">
                    <i class="bi bi-calendar3 me-1"></i> Periode :
                    <?php
                        $nama_bulan_print = [1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"];
                        if($periode == 'harian' && !empty($tanggal_hari)) echo date('d M Y', strtotime($tanggal_hari));
                        elseif($periode == 'mingguan' && !empty($tanggal_awal) && !empty($tanggal_akhir)) echo date('d M Y', strtotime($tanggal_awal)) . ' s/d ' . date('d M Y', strtotime($tanggal_akhir));
                        elseif($periode == 'bulanan' && !empty($bulan)) echo $nama_bulan_print[$bulan] . ' ' . $tahun_bulan;
                        else echo 'Semua Periode (Keseluruhan)';
                    ?>
                </div>
            </div>
        </div>
        <!-- Aesthetic Summary Cards on Print -->
        <div class="print-summary-row">
            <div class="print-summary-box">
                <span>Total Transaksi</span>
                <h4><?= number_format($total_transaksi, 0, ',', '.'); ?> Transaksi</h4>
            </div>
            <div class="print-summary-box">
                <span>Total Penjualan (Bruto)</span>
                <h4>Rp <?= number_format($total_penjualan, 0, ',', '.'); ?></h4>
            </div>
            <div class="print-summary-box">
                <span>Total Keuntungan Bersih</span>
                <h4>Rp <?= number_format($total_keuntungan, 0, ',', '.'); ?></h4>
            </div>
        </div>
    </div>
    <div class="card mb-4 bg-white d-print-none">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-1">LAPORAN PENJUALAN</h2>
                <p class="text-muted mb-0">Sistem Informasi Penjualan Mitra Azam</p>
            </div>
            <div class="fw-bold">
                <i class="bi bi-person-circle text-primary"></i>
                <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
            </div>
        </div>
    </div>
    <div class="row mb-4 d-print-none">
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Akumulasi Total Penjualan</span>
                        <h3 class="fw-bold mb-0 text-primary">Rp <?= number_format($total_penjualan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box bg-blue bg-primary">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Keuntungan</span>
                        <h3 class="fw-bold mb-0 text-success">Rp <?= number_format($total_keuntungan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box bg-success">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Transaksi</span>
                        <h3 class="fw-bold mb-0 text-warning"><?= $total_transaksi; ?></h3>
                    </div>
                    <div class="icon-box bg-warning">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Filter Section -->
    <div class="card mb-4 filter-section d-print-none">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-funnel me-2"></i>Filter Periode Laporan</h5>
            <form method="POST" id="formFilter">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-bold text-muted">Pilih Periode</label>
                        <select name="periode" id="periode" class="form-select" onchange="toggleFilterInput()" required>
                            <option value="semua" <?= $periode == 'semua' ? 'selected' : ''; ?>>Semua Data</option>
                            <option value="harian" <?= $periode == 'harian' ? 'selected' : ''; ?>>Harian</option>
                            <option value="mingguan" <?= $periode == 'mingguan' ? 'selected' : ''; ?>>Mingguan</option>
                            <option value="bulanan" <?= $periode == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small fw-bold text-muted">Status</label>
                        <select name="status_bayar" class="form-select">
                            <option value="semua">Semua Status</option>
                            <option value="Lunas" <?= $status_bayar == 'Lunas' ? 'selected' : ''; ?>>Lunas</option>
                            <option value="Belum Lunas" <?= $status_bayar == 'Belum Lunas' ? 'selected' : ''; ?>>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-3 filter-input" id="input-harian" style="display: none;">
                        <label class="form-label small fw-bold text-muted">Pilih Tanggal</label>
                        <input type="date" name="tanggal_hari" class="form-control" value="<?= htmlspecialchars($tanggal_hari); ?>">
                    </div>
                    <div class="col-md-5 mb-3 filter-input" id="input-mingguan" style="display: none;">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Tanggal Awal</label>
                                <input type="date" name="tanggal_awal" class="form-control" value="<?= htmlspecialchars($tanggal_awal); ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Tanggal Akhir</label>
                                <input type="date" name="tanggal_akhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 mb-3 filter-input" id="input-bulanan" style="display: none;">
                        <div class="row">
                            <div class="col-7">
                                <label class="form-label small fw-bold text-muted">Pilih Bulan</label>
                                <select name="bulan" class="form-select">
                                    <option value="">-- Pilih Bulan --</option>
                                    <?php
                                    $nama_bulan = [1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"];
                                    foreach ($nama_bulan as $num => $name) {
                                        $selected = ($bulan == $num) ? 'selected' : '';
                                        echo "<option value='$num' $selected>$name</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-bold text-muted">Tahun</label>
                                <input type="number" name="tahun_bulan" class="form-control" value="<?= htmlspecialchars($tahun_bulan); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 d-flex gap-2">
                        <button type="submit" name="filter" class="btn btn-primary flex-fill">
                            <i class="bi bi-search me-2"></i>Filter
                        </button>
                        <button type="button" onclick="window.print()" class="btn btn-success flex-fill">
                            <i class="bi bi-printer me-2"></i>Print
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Tabel -->
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="padding: 10px;">No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Nama Kasir</th>
                        <th>Status</th>
                        <th>Total Nilai Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while($d = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++; ?></td>
                                <td class="text-center fw-semibold text-secondary">TRX-<?= str_pad($d['id_penjualan'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="text-center"><?= date('d-m-Y H:i', strtotime($d['tanggal_wit'])); ?></td>
                                <td class="text-center">
                                    <?= !empty($d['nama_kasir']) ? htmlspecialchars($d['nama_kasir']) : '<span class="text-muted">ID Kasir: ' . htmlspecialchars($d['id_user'] ?? 'Kosong') . '</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= ($d['status_pembayaran'] ?? '') == 'Lunas' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                        <?= htmlspecialchars($d['status_pembayaran'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="text-end px-4 fw-bold">Rp <?= number_format($d['total_harga'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-danger py-4 fw-bold">
                                <i class="bi bi-exclamation-circle me-2"></i> Data laporan pada periode tersebut tidak ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Aesthetic Footer Section (Catatan & Tanda Tangan) -->
    <div class="print-header">
        <div class="print-footer-section">
            <div class="print-notes">
                <h6><i class="bi bi-info-circle me-1"></i> Catatan Laporan:</h6>
                <ul>
                    <li>Dokumen ini dicetak secara otomatis melalui sistem rekapitulasi Toko Bangunan Mitra Azam.</li>
                    <li>Semua data transaksi di atas valid sesuai dengan sistem keuangan yang tercatat.</li>
                </ul>
            </div>
            <div class="print-signature">
                <p>Huamual, <?= date('d F Y'); ?><br>Mengetahui,<br><strong>Pimpinan / Pemilik</strong></p>
                <div class="sign-name">MITRA AZAM</div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleFilterInput() {
    var periode = document.getElementById('periode').value;
    document.querySelectorAll('.filter-input').forEach(function(el) {
        el.style.display = 'none';
        el.querySelectorAll('input, select').forEach(ins => ins.removeAttribute('required'));
    });
    if (periode === 'harian') {
        var div = document.getElementById('input-harian');
        div.style.display = 'block';
        div.querySelector('input').setAttribute('required', 'required');
    } else if (periode === 'mingguan') {
        var div = document.getElementById('input-mingguan');
        div.style.display = 'block';
        div.querySelectorAll('input').forEach(ins => ins.setAttribute('required', 'required'));
    } else if (periode === 'bulanan') {
        var div = document.getElementById('input-bulanan');
        div.style.display = 'block';
        div.querySelector('select').setAttribute('required', 'required');
    }
}
function updateThemeButtonUI(theme) {
    const iconEl = document.getElementById('themeIcon');
    const textEl = document.getElementById('themeText');
    if (theme === 'dark') {
        iconEl.className = 'bi bi-moon-stars-fill text-warning';
        if (textEl) textEl.innerText = 'Dark Mode';
    } else {
        iconEl.className = 'bi bi-sun-fill text-warning';
        if (textEl) textEl.innerText = 'Light Mode';
    }
}
document.addEventListener("DOMContentLoaded", function() {
    toggleFilterInput();
    let currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
    updateThemeButtonUI(currentTheme);
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            let activeTheme = document.documentElement.getAttribute('data-bs-theme');
            let newTheme = (activeTheme === 'dark') ? 'light' : 'dark';
           
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            updateThemeButtonUI(newTheme);
            fetch('../admin/update_tema.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'tema=' + newTheme
            }).catch(error => console.error('Gagal menyimpan tema:', error));
        });
    }
});
</script>
</body>
</html>