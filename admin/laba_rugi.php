<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ============================
// PROTEKSI LOGIN
// ============================
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil tema dari session (default light)
$current_tema = $_SESSION['tema'] ?? 'light';

// ======================================
// INTEGRASI DARK MODE DARI DATABASE
// ======================================
$queryGlobalSetting = mysqli_query($conn, "SELECT tema FROM setting LIMIT 1");
if ($queryGlobalSetting) {
    $globalSetting = mysqli_fetch_assoc($queryGlobalSetting);
    $tema_sistem = $globalSetting['tema'] ?? 'light';

    // Sinkronisasi database ke session (agar perubahan di setting langsung terlihat)
    if ($tema_sistem !== $current_tema) {
        $_SESSION['tema'] = $tema_sistem;
        $current_tema = $tema_sistem;
    }
}

// ============================
// FILTER TANGGAL & STATUS LUNAS
// ============================
$tanggal_awal  = "";
$tanggal_akhir = "";

$kolom_status = "penjualan.status_pembayaran"; 
$where_detail  = " WHERE $kolom_status = 'Lunas' "; 
$where_pengeluaran = " WHERE 1=1 ";

if (isset($_POST['filter'])) {
    $tanggal_awal  = mysqli_real_escape_string($conn, $_POST['tanggal_awal']);
    $tanggal_akhir = mysqli_real_escape_string($conn, $_POST['tanggal_akhir']);
    
    $where_detail .= " AND penjualan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' ";
    $where_pengeluaran .= " AND tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' ";
}

// ============================
// TOTAL PENJUALAN (Hanya yang Lunas)
// ============================
$total_penjualan = 0;
$query_penjualan = mysqli_query($conn, "
    SELECT SUM(detail_penjualan.jumlah * detail_penjualan.harga) AS total_penjualan
    FROM detail_penjualan
    JOIN penjualan ON detail_penjualan.id_penjualan = penjualan.id_penjualan
    $where_detail
");

if (!$query_penjualan) {
    die("<b>Query Penjualan Error:</b> " . mysqli_error($conn));
}

$data_penjualan = mysqli_fetch_assoc($query_penjualan);
$total_penjualan = $data_penjualan['total_penjualan'] ?? 0;

// ============================
// TOTAL MODAL (Hanya dari penjualan yang Lunas)
// ============================
$total_modal = 0;
$query_modal = mysqli_query($conn, "
    SELECT SUM(detail_penjualan.jumlah * barang.harga_beli) AS total_modal
    FROM detail_penjualan
    JOIN barang ON detail_penjualan.id_barang = barang.id_barang
    JOIN penjualan ON detail_penjualan.id_penjualan = penjualan.id_penjualan
    $where_detail
");

if (!$query_modal) {
    die("<b>Query Modal Error:</b> " . mysqli_error($conn));
}

$data_modal = mysqli_fetch_assoc($query_modal);
$total_modal = $data_modal['total_modal'] ?? 0;

// ============================
// TOTAL BIAYA OPERASIONAL DARI DATABASE
// ============================
function getBiayaByKategori($conn, $kategori, $where) {
    $q = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran $where AND kategori = '$kategori'");
    if ($q) {
        $d = mysqli_fetch_assoc($q);
        return $d['total'] ?? 0;
    }
    return 0;
}

$gaji_karyawan          = getBiayaByKategori($conn, 'Gaji Karyawan', $where_pengeluaran);
$listrik_air            = getBiayaByKategori($conn, 'Listrik & Air', $where_pengeluaran);
$transportasi           = getBiayaByKategori($conn, 'Transportasi', $where_pengeluaran);
$perawatan              = getBiayaByKategori($conn, 'Biaya Perawatan & Perbaikan', $where_pengeluaran);
$penyusutan             = getBiayaByKategori($conn, 'Penyusutan Peralatan', $where_pengeluaran);
$lain_lain              = getBiayaByKategori($conn, 'Biaya Lain-lain', $where_pengeluaran);

$total_biaya_operasional = $gaji_karyawan + $listrik_air + $transportasi + $perawatan + $penyusutan + $lain_lain;

// ============================
// HITUNG LABA / RUGI
// ============================
$laba_kotor = $total_penjualan - $total_modal;
$laba_bersih = $laba_kotor - $total_biaya_operasional;
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="<?= $current_tema ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0d6efd;
        }

        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
            transition: background 0.3s, color 0.3s;
        }

        /* ================= DARK MODE ================= */
        [data-bs-theme="dark"] body {
            background: #0f172a;
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .card {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9;
        }
        [data-bs-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }
        [data-bs-theme="dark"] .navbar {
            background: #1e293b !important;
        }
        [data-bs-theme="dark"] .offcanvas {
            background: linear-gradient(180deg, #1e40af, #1e3a8a) !important;
        }
        [data-bs-theme="dark"] .submenu-container {
            background-color: #334155 !important;
        }
        [data-bs-theme="dark"] .submenu-link {
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .table {
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .table-light {
            background: #1e293b !important;
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .form-control {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        .content {
            padding: 25px;
            margin-top: 75px;
        }

        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }

        .summary-card {
            transition: 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .form-control {
            border-radius: 12px;
            padding: 10px;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            color: white;
            background: rgba(255, 255, 255, 0.25);
        }

        .bg-blue { background: linear-gradient(135deg, #296bf9, #142b76); }
        .bg-red { background: linear-gradient(135deg, #dc3545, #911623); }
        .bg-green { background: linear-gradient(135deg, #198754, #105936); }
        .bg-orange { background: linear-gradient(135deg, #ffc107, #d39e00); }

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
        .profile-img{
            width:45px;
            height:45px;
            border-radius:50%;
            overflow:hidden;
            flex-shrink:0;
            display:flex;
            justify-content:center;
            align-items:center;
            background:#fff;
            border:2px solid rgba(255,255,255,.5);
        }
        .profile-img img{
            width:100%;
            height:100%;
            object-fit:cover;
            border-radius:50%;
            display:block;
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

        /* Elemen khusus cetak yang disembunyikan di layar normal */
        .print-only {
            display: none;
        }

        @media print {
            .navbar, .btn, form, .navbar-toggler, .offcanvas, .content > .card:nth-child(1), .content > .card:nth-child(2), .row.mb-4, .card:has(.table) {
                display: none !important;
            }
            .content {
                margin-top: 0 !important;
                padding: 0 !important;
            }
            body {
                background: white !important;
                color: #000 !important;
            }
            .print-only {
                display: block !important;
            }
        }
    </style>
</head>

<body>

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
    
    <!-- Tombol Toggle Tema -->
    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-flex align-items-center gap-2 me-3" id="themeToggleBtn" type="button">
        <i class="bi <?= $current_tema == 'dark' ? 'bi-moon-stars-fill text-warning' : 'bi-sun-fill text-warning'; ?>"></i>
        <span class="small fw-semibold d-none d-md-inline"><?= $current_tema == 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
    </button>
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
                    <a href="laporan.php" class="submenu-link"><i class="bi bi-file-earmark-spreadsheet"></i> Ringkasan Laporan</a>
                    
                    <!-- Submenu Laba Rugi yang diperluas -->
                    <button class="submenu-link w-100 text-start border-0 bg-transparent py-2 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#submenuLabaRugi" aria-expanded="true">
                        <span><i class="bi bi-cash-coin me-2"></i> Laba Rugi</span>
                        <i class="bi bi-chevron-down" style="font-size: 10px;"></i>
                    </button>
                    <div class="collapse show ps-3" id="submenuLabaRugi">
                        <a href="laba_rugi.php" class="submenu-link py-1"><i class="bi bi-table"></i>Laba Rugi</a>
                        <a href="tambah_biaya_operasional.php" class="submenu-link py-1 active"><i class="bi bi-plus-circle"></i> Tambah Biaya Operasional</a>
                    </div>
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
    <!-- TAMPILAN KHUSUS CETAK -->
    <div class="print-only">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
            <div>
                <h4 class="fw-bold mb-0" style="color: #0b2265;">MITRA AZAM</h4>
                <h6 class="fw-bold mb-1" style="color: #c0392b;">TOKO BANGUNAN</h6>
                <small class="text-muted">Jl. Hj.Falaq Desa Luhu Dusun Limboro Kecamatan Huamual</small>
            </div>
            <div class="text-center">
                <h4 class="fw-bold mb-1" style="color: #212529; font-size: 22px;">LAPORAN LABA RUGI</h4>
                <h6 class="fw-bold mb-2" style="color: #212529;">TOKO BANGUNAN “MITRA AZAM”</h6>
                <div class="badge text-white px-3 py-2 fw-semibold" style="background-color: #1e293b; border-radius: 20px; font-size: 13px;">
                    Periode : <?= !empty($tanggal_awal) && !empty($tanggal_akhir) ? date('d M Y', strtotime($tanggal_awal)) . ' – ' . date('d M Y', strtotime($tanggal_akhir)) : 'Semua Periode'; ?>
                </div>
            </div>
        </div>

        <div class="row gx-2 mb-2">
            <!-- Kolom Kiri: Pendapatan & HPP -->
            <div class="col-6">
                <table class="table table-bordered border-dark mb-2" style="font-size: 12px;">
                    <thead>
                        <tr class="text-white text-center" style="background-color: #198754 !important;-webkit-print-color-adjust: exact;">
                            <th colspan="2" class="py-1">PENDAPATAN</th>
                        </tr>
                        <tr class="table-light text-dark fw-bold">
                            <th>URAIAN</th>
                            <th class="text-end">JUMLAH (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Penjualan Barang</td>
                            <td class="text-end"><?= number_format($total_penjualan, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Retur Penjualan</td>
                            <td class="text-end">(0)</td>
                        </tr>
                        <tr class="fw-bold table-light">
                            <td>TOTAL PENDAPATAN BERSIH</td>
                            <td class="text-end"><?= number_format($total_penjualan, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>

                <table class="table table-bordered border-dark mb-0" style="font-size: 12px;">
                    <thead>
                        <tr class="text-white text-center" style="background-color: #198754 !important;-webkit-print-color-adjust: exact;">
                            <th colspan="2" class="py-1">HARGA POKOK PENJUALAN (HPP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pembelian / Modal Barang Terjual</td>
                            <td class="text-end"><?= number_format($total_modal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Retur Pembelian</td>
                            <td class="text-end">(0)</td>
                        </tr>
                        <tr>
                            <td>Barang Tersedia untuk Dijual</td>
                            <td class="text-end"><?= number_format($total_modal, 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Persediaan Akhir Barang</td>
                            <td class="text-end">(0)</td>
                        </tr>
                        <tr class="fw-bold table-light">
                            <td>TOTAL HPP</td>
                            <td class="text-end"><?= number_format($total_modal, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Kolom Kanan: Biaya Operasional Dari Database -->
            <div class="col-6">
                <table class="table table-bordered border-dark mb-0" style="font-size: 12px;">
                    <thead>
                        <tr class="text-white text-center" style="background-color: #1d4ed8 !important;-webkit-print-color-adjust: exact;">
                            <th colspan="2" class="py-1">BIAYA OPERASIONAL</th>
                        </tr>
                        <tr class="table-light text-dark fw-bold">
                            <th>URAIAN</th>
                            <th class="text-end">JUMLAH (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Gaji Karyawan</td><td class="text-end"><?= number_format($gaji_karyawan, 0, ',', '.'); ?></td></tr>
                        <tr><td>Listrik & Air</td><td class="text-end"><?= number_format($listrik_air, 0, ',', '.'); ?></td></tr>
                        <tr><td>Transportasi</td><td class="text-end"><?= number_format($transportasi, 0, ',', '.'); ?></td></tr>
                        <tr><td>Biaya Perawatan & Perbaikan</td><td class="text-end"><?= number_format($perawatan, 0, ',', '.'); ?></td></tr>
                        <tr><td>Penyusutan Peralatan</td><td class="text-end"><?= number_format($penyusutan, 0, ',', '.'); ?></td></tr>
                        <tr><td>Biaya Lain-lain</td><td class="text-end"><?= number_format($lain_lain, 0, ',', '.'); ?></td></tr>
                        <tr class="fw-bold table-light">
                            <td>TOTAL BIAYA OPERASIONAL</td>
                            <td class="text-end"><?= number_format($total_biaya_operasional, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row gx-2 mb-2" style="font-size: 12px;">
            <div class="col-6">
                <div class="border border-dark p-2 d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <strong>LABA KOTOR</strong><br>
                        <small class="text-muted" style="font-size: 10px;">(Total Pendapatan Bersih – Total HPP)</small>
                    </div>
                    <div class="fw-bold fs-6">
                        Rp <?= number_format($laba_kotor, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="border border-dark p-2 d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <strong>LABA BERSIH</strong><br>
                        <small class="text-muted" style="font-size: 10px;">(Laba Kotor – Total Biaya Operasional)</small>
                    </div>
                    <div class="fw-bold fs-6 text-success">
                        Rp <?= number_format($laba_bersih, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="border border-dark p-2 d-flex justify-content-between align-items-center" style="font-size: 11px;">
            <div style="width: 65%;">
                <strong>RINGKASAN</strong><br>
                <span>Pada periode tersebut, Toko Bangunan “MITRA AZAM” memperoleh <strong>Laba Bersih</strong> sebesar <strong>Rp <?= number_format($laba_bersih, 0, ',', '.'); ?></strong>.</span>
            </div>
            <div class="text-center" style="width: 30%;">
                <span>Ambon, <?= date('d M Y'); ?></span><br>
                <span>Pemilik Toko</span>
                <div style="height: 45px;"></div>
                <strong>(MITRA AZAM)</strong>
            </div>
        </div>
    </div>

    <!-- TAMPILAN WEB NORMAL -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-1">LAPORAN LABA RUGI</h2>
                <p class="text-muted mb-0">Sistem Penjualan Toko Mitra Azam</p>
            </div>
            <div class="fw-bold">
                <i class="bi bi-person-circle text-primary me-1"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-funnel me-2"></i>Filter Rentang Laporan</h5>
            <form method="POST">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="form-label small fw-bold text-muted">Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" class="form-control" value="<?= htmlspecialchars($tanggal_awal); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="form-label small fw-bold text-muted">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir); ?>" required>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" name="filter" class="btn btn-primary flex-fill"><i class="bi bi-search me-2"></i>Filter</button>
                        <button type="button" onclick="window.print()" class="btn btn-success flex-fill"><i class="bi bi-printer me-2"></i>Print</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card summary-card text-white bg-blue">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <span class="opacity-75 fw-semibold d-block mb-1">Total Penjualan</span>
                        <h3 class="fw-bold mb-0">Rp <?= number_format($total_penjualan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card summary-card text-white bg-red">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <span class="opacity-75 fw-semibold d-block mb-1">Total Modal & Operasional</span>
                        <h3 class="fw-bold mb-0">Rp <?= number_format($total_modal + $total_biaya_operasional, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card summary-card text-white <?= $laba_bersih >= 0 ? 'bg-green' : 'bg-orange text-dark'; ?>">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <span class="opacity-75 fw-semibold d-block mb-1">
                            <?= $laba_bersih >= 0 ? 'Laba Bersih' : 'Kerugian'; ?>
                        </span>
                        <h3 class="fw-bold mb-0">Rp <?= number_format($laba_bersih, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box" style="<?= $laba_bersih < 0 ? 'color: #000 !important;' : ''; ?>">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="70%" style="padding: 15px;">Komponen Keuangan</th>
                        <th class="text-end px-4">Nilai (Rupiah)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 text-secondary">Total Pendapatan (Penjualan Terbayar)</td>
                        <td class="text-end px-4 fw-bold text-primary">Rp <?= number_format($total_penjualan, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td class="px-4 text-secondary">Total Pengeluaran HPP (Modal Barang Terjual)</td>
                        <td class="text-end px-4 fw-bold text-danger">Rp <?= number_format($total_modal, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td class="px-4 text-secondary">Total Biaya Operasional</td>
                        <td class="text-end px-4 fw-bold text-danger">Rp <?= number_format($total_biaya_operasional, 0, ',', '.'); ?></td>
                    </tr>
                    <tr class="table-light border-top">
                        <td class="px-4 fw-bold">
                            <i class="bi bi-arrow-return-right me-2 text-muted"></i>
                            <?= $laba_bersih >= 0 ? 'Estimasi Laba Bersih' : 'Estimasi Kerugian'; ?>
                        </td>
                        <td class="text-end px-4 fw-bold <?= $laba_bersih >= 0 ? 'text-success' : 'text-danger'; ?>" style="font-size: 1.1rem;">
                            Rp <?= number_format($laba_bersih, 0, ',', '.'); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function syncThemeWithSession(theme) {
    fetch('update_theme.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'tema=' + theme
    }).catch(err => console.error('Gagal sinkronisasi tema:', err));
}

document.getElementById('themeToggleBtn').addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-bs-theme', newTheme);
    
    const icon = document.querySelector('#themeToggleBtn i');
    const text = document.querySelector('#themeToggleBtn span');

    if (newTheme === 'dark') {
        icon.className = "bi bi-moon-stars-fill text-warning";
        if(text) text.textContent = "Dark Mode";
    } else {
        icon.className = "bi bi-sun-fill text-warning";
        if(text) text.textContent = "Light Mode";
    }

    syncThemeWithSession(newTheme);
});
</script>
</body>
</html>