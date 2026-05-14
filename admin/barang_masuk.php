<?php
session_start();
require_once '../config/koneksi.php';

/* =========================
   FIX ERROR $conn
========================= */
if (!isset($conn) || !$conn) {
    die("❌ Koneksi database gagal. Pastikan file config/koneksi.php benar dan variabel \$conn tersedia.");
}

/* =========================
   CEK LOGIN
========================= */
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   DATA BARANG MASUK
========================= */
$query = mysqli_query($conn, "
SELECT bm.*, b.nama_barang, b.kode_barang
FROM barang_masuk bm
JOIN barang b ON bm.id_barang = b.id_barang
ORDER BY bm.id_masuk DESC
");

if (!$query) {
    die("❌ Query gagal: " . mysqli_error($conn));
}

/* =========================
   TOTAL BARANG MASUK
========================= */
$q_total = mysqli_query($conn, "
SELECT COALESCE(SUM(jumlah),0) AS total
FROM barang_masuk
");

$d_total = mysqli_fetch_assoc($q_total);
$total_masuk = $d_total['total'] ?? 0;

/* =========================
   TOTAL TRANSAKSI
========================= */
$q_count = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM barang_masuk
");

$d_count = mysqli_fetch_assoc($q_count);
$total_transaksi = $d_count['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Masuk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ===== GLOBAL ===== */
        body{
            background:#f4f6fb;
            font-family:'Segoe UI',sans-serif;
            margin: 0;
        }

        /* ===== CONTENT ===== */
        .content{
            padding:30px;
            margin-top: 75px; /* Jarak aman dari fixed navbar */
        }

        /* ===== CARD ===== */
        .card{
            border:none;
            border-radius:18px;
            box-shadow:0 8px 25px rgba(0,0,0,0.05);
        }

        /* ===== HEADER ===== */
        .card-header{
            background:linear-gradient(135deg, #296bf9, #142b76) !important;
            color:white !important;
            font-weight:600;
            border-top-left-radius: 18px !important;
            border-top-right-radius: 18px !important;
        }

        /* ===== TABLE ===== */
        .table tbody tr:hover{
            background:#fff7f0;
        }

        .badge{
            padding:7px 10px;
        }

        /* ===== BUTTON ===== */
        .btn-warning{
            background:linear-gradient(135deg, #296bf9, #142b76);
            border:none;
            color:white;
        }

        .btn-warning:hover{
            opacity:0.9;
            color:white;
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
            <a class="nav-link dropdown-toggle active fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-box-seam me-2 text-primary"></i> Data Barang
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="barang.php"><i class="bi bi-list-ul me-2"></i> Semua Barang</a></li>
              <li><a class="dropdown-item" href="tambah_barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Tambah Stok Masuk</a></li>
              <li><a class="dropdown-item" href="tambah_barang.php"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a></li>
              <li><a class="dropdown-item active" href="barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Barang Masuk</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-file-earmark-text me-2 text-primary"></i> Laporan
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="laporan.php"><i class="bi bi-file-earmark-ruled me-2"></i> Ringkasan Laporan</a></li>
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

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between flex-wrap align-items-center">
            <div>
                <h3 class="mb-1 fw-bold">Riwayat Barang Masuk</h3>
                <small class="text-muted">Manajemen stok gudang</small>
            </div>
            <div class="fw-bold">
                <i class="bi bi-person-circle text-primary"></i>
                <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3><?= $total_transaksi; ?></h3>
                    <p class="mb-0">Total Transaksi</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3><?= $total_masuk; ?></h3>
                    <p class="mb-0">Total Barang Masuk</p>
                </div>
            </div>
        </div>
    </div>

    <a href="tambah_barang_masuk.php" class="btn btn-warning mb-3 fw-semibold">
        <i class="bi bi-plus-circle me-2"></i> Tambah Barang Masuk
    </a>

    <div class="card">
        <div class="card-header p-3">
            <i class="bi bi-list-stars me-2"></i> Data Barang Masuk
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-warning text-center">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Harga Beli</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($query) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while($d = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center"><?= date('d-m-Y', strtotime($d['tanggal'])); ?></td>
                            <td class="text-center"><code><?= $d['kode_barang']; ?></code></td>
                            <td><?= $d['nama_barang']; ?></td>
                            <td class="text-center"><span class="badge bg-primary fs-6"><?= $d['jumlah']; ?></span></td>
                            <td class="text-end">Rp <?= number_format($d['harga_beli'],0,',','.'); ?></td>
                            <td><?= !empty($d['keterangan']) ? $d['keterangan'] : '-'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4 fw-bold">
                            <i class="bi bi-exclamation-circle me-2"></i> Tidak ada data barang masuk.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>