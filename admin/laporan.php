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

// FILTER DATA
if (isset($_POST['filter'])) {
    $tanggal_awal  = mysqli_real_escape_string($conn, $_POST['tanggal_awal']);
    $tanggal_akhir = mysqli_real_escape_string($conn, $_POST['tanggal_akhir']);
    $where = " WHERE tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' ";
}

// ============================
// QUERY DATA PENJUALAN
// ============================
$query = mysqli_query($conn, "
    SELECT *
    FROM penjualan
    $where
    ORDER BY id_penjualan DESC
");

// VALIDASI QUERY
if (!$query) {
    die("Query Error : " . mysqli_error($conn));
}

// ============================
// TOTAL PENJUALAN
// ============================
$total_penjualan = 0;
$total_penjualan_query = mysqli_query($conn, "
    SELECT SUM(total_harga) AS total_penjualan
    FROM penjualan
    $where
");

if ($total_penjualan_query) {
    $data_penjualan = mysqli_fetch_assoc($total_penjualan_query);
    $total_penjualan = $data_penjualan['total_penjualan'] ?? 0;
}

// ============================
// TOTAL KEUNTUNGAN
// ============================
$total_keuntungan = 0;
$total_keuntungan_query = mysqli_query($conn, "
    SELECT SUM(keuntungan) AS total_keuntungan
    FROM penjualan
    $where
");

if ($total_keuntungan_query) {
    $data_keuntungan = mysqli_fetch_assoc($total_keuntungan_query);
    $total_keuntungan = $data_keuntungan['total_keuntungan'] ?? 0;
}

// ============================
// TOTAL TRANSAKSI
// ============================
$total_transaksi = 0;
$total_transaksi_query = mysqli_query($conn, "
    SELECT COUNT(id_penjualan) AS total_transaksi
    FROM penjualan
    $where
");

if ($total_transaksi_query) {
    $data_transaksi = mysqli_fetch_assoc($total_transaksi_query);
    $total_transaksi = $data_transaksi['total_transaksi'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* =========================
           CONTENT
        ========================= */
        .content {
            padding: 25px;
            margin-top: 75px; /* Jarak aman dari fixed navbar */
        }

        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }

        .stat-card {
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .table tbody tr:hover {
            background: #fff7f0;
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
            font-size: 26px;
            color: white;
        }

        .bg-orange { background: linear-gradient(135deg, #ff7b00, #ff5200); }
        .bg-green { background: linear-gradient(135deg, #198754, #20c997); }
        .bg-blue { background: linear-gradient(135deg, #296bf9, #142b76); }

        /* =========================
           MEDIA PRINT (CETAK)
        ========================= */
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
        }
    </style>
</head>

<body>

<!-- NAVBAR & OFFCANVAS -->
<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
    
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasNavbarLabel">
          <i class="bi bi-shop"></i> MITRA AZAM
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
              <li><a class="dropdown-item" href="barang.php"><i class="bi bi-list-ul me-2"></i> Semua Barang</a></li>
              <li><a class="dropdown-item" href="tambah_barang.php"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a></li>
              <li><a class="dropdown-item" href="barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Barang Masuk</a></li>
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

<!-- CONTENT -->
<div class="content">

    <!-- HEADER -->
    <div class="card mb-4 bg-white">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-1">LAPORAN PENJUALAN</h2>
                <p class="text-muted mb-0">Sistem Informasi Toko Bangunan Mitra Azam</p>
            </div>
            <div class="fw-bold">
                <i class="bi bi-person-circle text-primary"></i>
                <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
            </div>
        </div>
    </div>

    <!-- STATISTIK -->
    <div class="row mb-4">
        <!-- TOTAL PENJUALAN -->
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Penjualan</span>
                        <h3 class="fw-bold mb-0">Rp <?= number_format($total_penjualan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box bg-blue">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOTAL KEUNTUNGAN -->
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Keuntungan</span>
                        <h3 class="fw-bold mb-0">Rp <?= number_format($total_keuntungan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box bg-green">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOTAL TRANSAKSI -->
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Transaksi</span>
                        <h3 class="fw-bold mb-0"><?= $total_transaksi; ?></h3>
                    </div>
                    <div class="icon-box bg-orange">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER -->
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

    <!-- TABEL LAPORAN -->
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="padding: 15px;">No</th>
                        <th>Tanggal</th>
                        <th>Total Harga</th>
                        <th>Bayar</th>
                        <th>Kembalian</th>
                        <th>Keuntungan</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($query) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while($d = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $no++; ?></td>
                            <td class="text-center"><?= date('d-m-Y H:i', strtotime($d['tanggal'])); ?></td>
                            <td class="text-end px-4">Rp <?= number_format($d['total_harga'], 0, ',', '.'); ?></td>
                            <td class="text-end px-4">Rp <?= number_format($d['bayar'], 0, ',', '.'); ?></td>
                            <td class="text-end px-4">Rp <?= number_format($d['kembali'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <span class="badge bg-success px-3 py-2 fs-6">
                                    Rp <?= number_format($d['keuntungan'], 0, ',', '.'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-danger py-4 fw-bold">
                            <i class="bi bi-exclamation-circle me-2"></i> Data laporan tidak ditemukan atau kosong.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- BOOTSTRAP BUNDLE JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>