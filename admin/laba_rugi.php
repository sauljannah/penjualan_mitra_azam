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

// ============================
// FILTER TANGGAL
// ============================
$tanggal_awal  = "";
$tanggal_akhir = "";
$where         = "";

if (isset($_POST['filter'])) {
    $tanggal_awal  = mysqli_real_escape_string($conn, $_POST['tanggal_awal']);
    $tanggal_akhir = mysqli_real_escape_string($conn, $_POST['tanggal_akhir']);
    $where = " WHERE tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' ";
}

// ============================
// TOTAL PENJUALAN
// ============================
$total_penjualan = 0;
$query_penjualan = mysqli_query($conn, "
    SELECT SUM(total_harga) AS total_penjualan
    FROM penjualan
    $where
");

if (!$query_penjualan) {
    die("Query Penjualan Error : " . mysqli_error($conn));
}

$data_penjualan = mysqli_fetch_assoc($query_penjualan);
$total_penjualan = $data_penjualan['total_penjualan'] ?? 0;

// ============================
// TOTAL KEUNTUNGAN
// ============================
$total_keuntungan = 0;
$query_keuntungan = mysqli_query($conn, "
    SELECT SUM(keuntungan) AS total_keuntungan
    FROM penjualan
    $where
");

if (!$query_keuntungan) {
    die("Query Keuntungan Error : " . mysqli_error($conn));
}

$data_keuntungan = mysqli_fetch_assoc($query_keuntungan);
$total_keuntungan = $data_keuntungan['total_keuntungan'] ?? 0;

// ============================
// TOTAL MODAL
// ============================
$total_modal = 0;
$where_detail = "";
if (isset($_POST['filter'])) {
    $where_detail = " WHERE penjualan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' ";
}

$query_modal = mysqli_query($conn, "
    SELECT SUM(detail_penjualan.jumlah * barang.harga_beli) AS total_modal
    FROM detail_penjualan
    JOIN barang ON detail_penjualan.id_barang = barang.id_barang
    JOIN penjualan ON detail_penjualan.id_penjualan = penjualan.id_penjualan
    $where_detail
");

if (!$query_modal) {
    die("Query Modal Error : " . mysqli_error($conn));
}

$data_modal = mysqli_fetch_assoc($query_modal);
$total_modal = $data_modal['total_modal'] ?? 0;

// ============================
// HITUNG LABA / RUGI
// ============================
$laba_bersih = $total_penjualan - $total_modal;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
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

        /* Desain Box Ikon Bulat */
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

        /* Warna Background Gradasi Card */
        .bg-blue { background: linear-gradient(135deg, #296bf9, #142b76); }
        .bg-red { background: linear-gradient(135deg, #dc3545, #911623); }
        .bg-green { background: linear-gradient(135deg, #198754, #105936); }
        .bg-orange { background: linear-gradient(135deg, #ffc107, #d39e00); }

        @media print {
            .navbar, .btn, form, .navbar-toggler, .offcanvas {
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
            .icon-box {
                color: #000 !important;
                background: none !important;
                border: 1px solid #ccc !important;
            }
        }
    </style>
</head>

<body>

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop-window me-2"></i> MITRA AZAM
    </a>
    
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasNavbarLabel">
          <i class="bi bi-shop-window me-2"></i> MITRA AZAM
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      
      <div class="offcanvas-body">
        <ul class="navbar-nav justify-content-start flex-grow-1 pe-3">
          
          <li class="nav-item mb-2">
            <a class="nav-link fw-semibold" href="dashboard.php">
              <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
            </a>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-box-seam me-2 text-primary"></i> Data Barang
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="barang.php">Semua Barang</a></li>
              <li><a class="dropdown-item" href="tambah_barang.php">Tambah Barang</a></li>
              <li><a class="dropdown-item" href="barang_masuk.php">Barang Masuk</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle active fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-file-earmark-text me-2 text-primary"></i> Laporan
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item active" href="laporan.php"><i class="bi bi-file-earmark-ruled me-2"></i> Ringkasan Laporan</a></li>
              <li><a class="dropdown-item" href="laba_rugi.php"><i class="bi bi-cash-stack me-2"></i> Laba Rugi</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-gear me-2 text-primary"></i> Setting
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="setting.php"><i class="bi bi-sliders me-2"></i> Pengaturan Umum</a></li>
              <li><a class="dropdown-item" href="manajemen_user.php"><i class="bi bi-people me-2"></i> Manajemen User</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger fw-bold" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </li>

        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="content">

    <div class="card mb-4 bg-white">
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
                        <span class="opacity-75 fw-semibold d-block mb-1">Total Modal</span>
                        <h3 class="fw-bold mb-0">Rp <?= number_format($total_modal, 0, ',', '.'); ?></h3>
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
                        <td class="px-4 text-secondary">Total Pendapatan (Penjualan)</td>
                        <td class="text-end px-4 fw-bold text-primary">Rp <?= number_format($total_penjualan, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td class="px-4 text-secondary">Total Pengeluaran HPP (Modal Barang)</td>
                        <td class="text-end px-4 fw-bold text-danger">Rp <?= number_format($total_modal, 0, ',', '.'); ?></td>
                    </tr>
                    <tr class="table-light border-top border-dark">
                        <td class="px-4 fw-bold text-dark">
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
</body>
</html>