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
// CEK LEVEL
// ======================================
if ($_SESSION['level'] != "kasir") {
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// PENCARIAN
// ======================================
$cari = "";
$query = "SELECT * FROM penjualan ORDER BY id_penjualan DESC";

if (isset($_GET['cari']) && !empty($_GET['cari'])) {
    $cari = mysqli_real_escape_string($conn, trim($_GET['cari']));
    $query = "SELECT * FROM penjualan WHERE DATE(tanggal) = '$cari' ORDER BY id_penjualan DESC";
}

// ======================================
// AMBIL DATA TRANSAKSI
// ======================================
$data = mysqli_query($conn, $query);
if (!$data) {
    die("Query Error : " . mysqli_error($conn));
}

// ======================================
// TOTAL TRANSAKSI
// ======================================
$total_transaksi = 0;
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM penjualan");
if ($total_query) {
    $row = mysqli_fetch_assoc($total_query);
    $total_transaksi = $row['total'];
}

// ======================================
// TOTAL PENDAPATAN
// ======================================
$total_pendapatan = 0;
$pendapatan_query = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM penjualan");
if ($pendapatan_query) {
    $row_pendapatan = mysqli_fetch_assoc($pendapatan_query);
    $total_pendapatan = $row_pendapatan['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi</title>

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
        .card{ border:none; border-radius:24px; overflow:hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .header-card{ background:linear-gradient(135deg, #2563eb, #3b82f6); color:white; }

        .stat-card{ border-radius:22px; color:white; padding:20px; transition:0.3s; border: none; }
        .stat-card:hover{ transform:translateY(-5px); }
        .bg-blue{ background:linear-gradient(135deg, #3b82f6, #2563eb); }
        .bg-green{ background:linear-gradient(135deg, #10b981, #059669); }
        .icon-stat{ font-size:40px; opacity:0.7; }

        /* ======================================
        TABLE & FORM
        ====================================== */
        .table thead{ background:#eff6ff; }
        .table thead th{ color:#1e3a8a; border:none; font-weight:600; padding: 15px 10px; }
        .form-control{ border-radius:14px; border:1px solid #dbeafe; padding:12px; }
        .form-control:focus{ border-color:#2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
        
        .input-group-text-custom {
            background-color: #f8fafc;
            border: 1px solid #dbeafe;
            border-right: none;
            border-radius: 14px 0 0 14px;
            color: #3b82f6;
            padding-left: 15px;
            padding-right: 15px;
        }
        .form-control-search {
            border-left: none;
            border-radius: 0 14px 14px 0 !important;
        }
        .btn{ border-radius:12px; font-weight:500; }
        .btn-search-custom { border-radius: 12px; padding-left: 20px; padding-right: 20px; }
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
        <a href="dashboard.php">
            <i class="bi bi-house-door-fill"></i> Dashboard
        </a>
        <a href="transaksi.php">
            <i class="bi bi-cart-fill"></i> Transaksi
        </a>
        <a href="data_hutang.php" class="active">
            <i class="bi bi-people-fill"></i> Data Hutang Customer
        </a>
        <a href="riwayat_transaksi.php" class="active">
            <i class="bi bi-clock-history"></i> Riwayat Transaksi
        </a>
        <hr class="text-white-50 my-3">
        <a href="../auth/logout.php">
            <i class="bi bi-box-arrow-right text-danger"></i> <span class="text-white">Logout</span>
        </a>
    </div>
  </div>
</div>

<div class="content">

    <div class="card header-card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Riwayat Transaksi</h2>
                    <p class="mb-0 opacity-75">Sistem Kasir Toko Mitra Azam</p>
                </div>
                <div class="bg-white bg-opacity-25 py-2 px-3 rounded-4 border border-white border-opacity-25">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['nama']); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <div class="col-md-6">
            <div class="stat-card bg-blue shadow">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold"><?= $total_transaksi; ?></h2>
                        <p class="mb-0 opacity-75">Total Transaksi</p>
                    </div>
                    <i class="bi bi-cart-check icon-stat"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card bg-green shadow">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h2>
                        <p class="mb-0 opacity-75">Total Pendapatan</p>
                    </div>
                    <i class="bi bi-cash-stack icon-stat"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-6 col-md-12">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-table text-primary"></i> Data Transaksi
                    </h5>
                </div>
                <div class="col-lg-6 col-md-12">
                    <form method="GET" action="">
                        <div class="d-flex gap-2 justify-content-lg-end">
                            <div class="input-group" style="max-width: 340px;">
                                <span class="input-group-text input-group-text-custom">
                                    <i class="bi bi-calendar-event"></i>
                                </span>
                                <input type="date" name="cari" class="form-control form-control-search" value="<?= htmlspecialchars($cari); ?>">
                            </div>
                            <button class="btn btn-primary btn-search-custom d-flex align-items-center gap-2 shadow-sm" type="submit">
                                <i class="bi bi-search"></i> <span>Filter</span>
                            </button>
                            <?php if(!empty($cari)): ?>
                                <a href="riwayat_transaksi.php" class="btn btn-light border d-flex align-items-center gap-1">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead>
                        <tr class="table-light">
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Metode</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Informasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($data) > 0): ?>
                        <?php $no = 1; ?>

                        <?php while($d = mysqli_fetch_assoc($data)): ?>
                        <tr>

                            <td><?= $no++; ?></td>

                            <td><?= date('d-m-Y H:i', strtotime($d['tanggal'])); ?></td>

                            <td><?= !empty($d['nama_customer']) ? $d['nama_customer'] : '-'; ?></td>

                            <td>
                                <?php
                                if($d['metode_pembayaran']=="Tunai"){
                                    echo "<span class='badge bg-success'>Tunai</span>";
                                }elseif($d['metode_pembayaran']=="Transfer"){
                                    echo "<span class='badge bg-primary'>Transfer</span>";
                                }elseif($d['metode_pembayaran']=="QRIS"){
                                    echo "<span class='badge bg-warning text-dark'>QRIS</span>";
                                }else{
                                    echo "<span class='badge bg-danger'>Hutang</span>";
                                }
                                ?>
                            </td>

                            <td>
                                Rp <?= number_format($d['total_harga'],0,',','.'); ?>
                            </td>

                            <td>
                                <?php
                                if($d['status_pembayaran']=="Lunas"){
                                    echo "<span class='badge bg-success'>Lunas</span>";
                                }else{
                                    echo "<span class='badge bg-danger'>Belum Lunas</span>";
                                }
                                ?>
                            </td>

                            <td>
                                <?php
                                if($d['metode_pembayaran']=="Transfer" || $d['metode_pembayaran']=="QRIS"){

                                    if(!empty($d['bukti_pembayaran'])){
                                        echo "<span class='badge bg-success'>Ada Bukti</span>";
                                    }else{
                                        echo "<span class='badge bg-danger'>Tidak Ada Bukti</span>";
                                    }

                                }elseif($d['metode_pembayaran']=="Hutang"){

                                    echo "Jatuh Tempo : ".$d['jatuh_tempo'];

                                }else{

                                    echo "-";
                                }
                                ?>
                            </td>

                            <td class="text-center">

                                <a href="struk.php?id=<?= $d['id_penjualan']; ?>"
                                target="_blank"
                                class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-receipt"></i> Struk
                                </a>

                                <?php if(
                                    !empty($d['bukti_pembayaran']) &&
                                    file_exists("../uploads/bukti_pembayaran/".$d['bukti_pembayaran'])
                                ): ?>

                                    <button
                                        class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal<?= $d['id_penjualan']; ?>">
                                        <i class="bi bi-image"></i> Bukti
                                    </button>

                                <?php endif; ?>

                            </td>

                        </tr>

                        <?php if(
                            !empty($d['bukti_pembayaran']) &&
                            file_exists("../uploads/bukti_pembayaran/".$d['bukti_pembayaran'])
                        ): ?>

                        <div class="modal fade" id="modal<?= $d['id_penjualan']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Bukti Pembayaran</h5>

                                        <button type="button"
                                                class="btn-close"
                                                data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body text-center">
                                        <img src="../uploads/bukti_pembayaran/<?= $d['bukti_pembayaran']; ?>"
                                            class="img-fluid rounded shadow">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <?php endif; ?>

                        <?php endwhile; ?>

                    <?php else: ?>

                    <tr>
                        <td colspan="8" class="text-center text-danger py-5">
                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                            Data riwayat transaksi tidak ditemukan.
                        </td>
                    </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>