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

        /* ========================================================
           SIDEBAR IMPLEMENTASI TEMA BIRU ELEGAN & STRUKTUR DROPDOWN
           ======================================================== */
        .offcanvas {
            background: linear-gradient(180deg, #0d6efd, #0a46a6) !important; /* Tema Warna Biru Elegan */
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
        
        /* Navigasi Utama Menu */
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
        
        /* Style Submenu Collapse Kontainer (Persis seperti background abu-abu pada gambar Anda) */
        .submenu-container {
            background-color: #f1f3f5; /* Latar belakang item drop-down abu-abu muda */
            border-radius: 10px;
            margin: 5px 0 10px 0;
            padding: 6px 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
        }
        .submenu-link {
            display: flex;
            align-items: center;
            padding: 10px 20px 10px 40px;
            color: #333333; /* Font gelap agar terbaca jelas di background abu-abu */
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
        
        /* Rotasi Panah Saat Dropdown Terbuka */
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
<body>

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
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
        </div>
        
        <!-- DATA HUTANG -->
<div class="mb-1">

<a href="data_hutang.php"
class="menu-item-link">

<span>

<i class="bi bi-credit-card menu-icon"></i>

Data Hutang Customer

</span>

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

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between flex-wrap align-items-center">
            <div>
                <h3 class="mb-1 fw-bold">Riwayat Stok Barang Masuk</h3>
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

    <a href="stok_barang_masuk.php" class="btn btn-warning mb-3 fw-semibold">
        <i class="bi bi-plus-circle me-2"></i> Tambah Stok Barang Masuk
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