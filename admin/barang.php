<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/load_theme.php';

/** @var mysqli $conn */

// =====================================
// PROTEKSI LOGIN
// =====================================
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

// =====================================
// PENCARIAN + FILTER + SORTING
// =====================================
$cari   = $_GET['cari'] ?? '';
$sort   = $_GET['sort'] ?? 'id_barang';
$order  = $_GET['order'] ?? 'DESC';
$filter = $_GET['filter'] ?? '';

$allowed_sort = [
    'id_barang',
    'kode_barang',
    'nama_barang',
    'harga_beli',
    'harga_jual',
    'stok'
];

if(!in_array($sort,$allowed_sort)){
    $sort = 'id_barang';
}

if($order != 'ASC' && $order != 'DESC'){
    $order = 'DESC';
}

$query = "SELECT * FROM barang WHERE 1=1";

if($cari != ''){
    $cari = mysqli_real_escape_string($conn, $cari);
    $query .= " AND (nama_barang LIKE '%$cari%' OR kode_barang LIKE '%$cari%')";
}

/* FILTER DASHBOARD */
if($filter == 'habis'){
    $query .= " AND stok <= 0";
}

if($filter == 'menipis'){
    $query .= " AND stok > 0 AND stok <= stok_minimum";
}

$query .= " ORDER BY $sort $order";
$data = mysqli_query($conn, $query);

if(!$data){
    die('Query Error : ' . mysqli_error($conn));
}

// =====================================
// TOTAL BARANG
// =====================================
$total_barang = 0;
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang");
if ($q_total) {
    $d_total = mysqli_fetch_assoc($q_total);
    $total_barang = $d_total['total'];
}

// =====================================
// STOK MENIPIS
// =====================================
$stok_menipis = 0;
$q_stok = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang WHERE stok > 0 AND stok <= stok_minimum");
if ($q_stok) {
    $d_stok = mysqli_fetch_assoc($q_stok);
    $stok_menipis = $d_stok['total'];
}

// =====================================
// STOK HABIS
// =====================================
$q_habis = mysqli_query($conn, "SELECT * FROM barang WHERE stok <= 0");
$total_habis = mysqli_num_rows($q_habis);

// =====================================
// CEK KODE BARANG GANDA
// =====================================
$peringatan_kode = "";
$q_kode = mysqli_query($conn, "
    SELECT
        kode_barang,
        GROUP_CONCAT(DISTINCT nama_barang) AS nama_barang,
        COUNT(DISTINCT nama_barang) AS total_nama
    FROM barang
    GROUP BY kode_barang
    HAVING total_nama > 1
");

if(mysqli_num_rows($q_kode) > 0){
    $peringatan_kode .= "
    <div class='alert alert-danger'>
        <h6>
            <i class='bi bi-exclamation-triangle-fill'></i>
            Kode Barang Duplikat Tidak Valid
        </h6>
    ";
    while($kode = mysqli_fetch_assoc($q_kode)){
        $peringatan_kode .= "
        <div class='mt-2'>
            <b>Kode :</b> ".$kode['kode_barang']." <br>
            <b>Digunakan oleh :</b> ".$kode['nama_barang']."
        </div>
        ";
    }
    $peringatan_kode .= "</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body{
            background:#f4f6f9;
            overflow-x:hidden;
            font-family:Arial,sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        /* TEMA GELAP (DARK MODE) */
        body.dark-theme { 
            background: #0f172a; 
            color: #ffffff; 
        }
        body.dark-theme .card { 
            background: #1e293b; 
            color: #ffffff; 
            box-shadow: 0 5px 15px rgba(255,255,255,0.03);
        }
        body.dark-theme .card-body { color: #ffffff; }
        body.dark-theme .table { color: #ffffff; }
        body.dark-theme .table-bordered { border-color: #334155; }
        body.dark-theme .table > :not(caption) > * > * { background-color: #1e293b; color: #fff; }
        body.dark-theme .table-hover tbody tr:hover { background: #334155 !important; }
        body.dark-theme .table-hover tbody tr:hover > * { background: #334155 !important; color: #fff; }
        body.dark-theme .text-muted { color: #cbd5e1 !important; }
        
        /* Navbar & Offcanvas Dark Mode */
        body.dark-theme .navbar,
        body.dark-theme .offcanvas { 
            background-color: #1e293b !important; 
            color: #ffffff !important;
            border-color: #334155 !important;
        }
        body.dark-theme .navbar-brand,
        body.dark-theme .nav-link,
        body.dark-theme .offcanvas-title { 
            color: #ffffff !important; 
        }
        body.dark-theme .navbar-toggler-icon {
            filter: invert(1);
        }
        body.dark-theme .dropdown-menu { 
            background: #0f172a; 
            border: 1px solid #334155; 
        }
        body.dark-theme .dropdown-item { color: #ffffff; }
        body.dark-theme .dropdown-item:hover { background: #334155; }
        body.dark-theme .dropdown-divider { border-color: #334155; }

        /* Penyesuaian konten agar tidak tertimpa Navbar Fixed-Top */
        .content{
            padding: 25px;
            margin-top: 75px; 
        }

        .card{
            border:none;
            border-radius:20px;
            box-shadow:0 5px 15px rgba(0,0,0,0.05);
            transition:.3s;
            cursor:pointer;
        }

        .card:hover{
            transform:translateY(-5px);
            box-shadow:0 12px 25px rgba(0,0,0,.15);
        }

        .btn{
            border-radius:10px;
        }

        .table tbody tr:hover{
            background:#f1f1f1;
        }

        .badge{
            padding:8px 12px;
        }

        .search-box{
            border-radius:10px;
        }

        .alert{
            border:none;
            border-radius:15px;
        }

        .kode-valid{
            background:#e8fff1 !important;
        }

        .kode-tidak-valid{
            background:#ffe5e5 !important;
        }

        /* KACA CALCULATOR */
        .kaca-calculator {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px;
            font-size: 0.9rem;
        }
        body.dark-theme .kaca-calculator {
            background: #1e293b;
        }
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
        .profile-img{
            width:55px;
            height:55px;
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
        .menu-item-link[aria-expanded="true"] i.arrow-icon {
            transform: rotate(180deg);
        }
        .menu-item-link i.arrow-icon {
            transition: transform 0.2s;
            font-size: 12px;
        }
    </style>
</head>
<body class="<?= ($tema_sistem ?? 'light') == 'dark' ? 'dark-theme' : ''; ?>">

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
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3>Data Barang</h3>
                <p class="mb-0 text-muted">Sistem Informasi Toko Bangunan</p>
            </div>
            <div>
                <h5>
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
                </h5>
            </div>
        </div>
    </div>

    <?= $peringatan_kode; ?>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <a href="barang.php" class="text-decoration-none">
                <div class="card bg-primary text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2><?= $total_barang; ?></h2>
                            <p class="mb-0">Total Barang</p>
                        </div>
                        <i class="bi bi-box-seam" style="font-size:50px;"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 mb-3">
            <a href="barang.php?filter=menipis" class="text-decoration-none">
                <div class="card bg-warning text-dark">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2><?= $stok_menipis; ?></h2>
                            <p class="mb-0">Stok Menipis</p>
                        </div>
                        <i class="bi bi-exclamation-circle" style="font-size:50px;"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 mb-3">
            <a href="barang.php?filter=habis" class="text-decoration-none">
                <div class="card bg-danger text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2><?= $total_habis; ?></h2>
                            <p class="mb-0">Stok Habis</p>
                        </div>
                        <i class="bi bi-x-circle" style="font-size:50px;"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <?php if($filter == 'habis'): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i> Menampilkan barang dengan stok habis
        </div>
    <?php endif; ?>

    <?php if($filter == 'menipis'): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-circle-fill"></i> Menampilkan barang dengan stok menipis
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box"></i> Data Barang</h5>
            <a href="tambah_barang.php" class="btn btn-warning btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Barang
            </a>
        </div>
        <div class="card-body">

            <form method="GET" class="mb-4">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="cari" class="form-control search-box" placeholder="Cari barang..." value="<?= htmlspecialchars($cari); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="sort" class="form-select">
                            <option value="id_barang" <?= ($sort == 'id_barang') ? 'selected' : ''; ?>>Urutkan Berdasarkan</option>
                            <option value="kode_barang" <?= ($sort == 'kode_barang') ? 'selected' : ''; ?>>Kode Barang</option>
                            <option value="nama_barang" <?= ($sort == 'nama_barang') ? 'selected' : ''; ?>>Nama Barang</option>
                            <option value="harga_beli" <?= ($sort == 'harga_beli') ? 'selected' : ''; ?>>Harga Beli</option>
                            <option value="harga_jual" <?= ($sort == 'harga_jual') ? 'selected' : ''; ?>>Harga Jual</option>
                            <option value="stok" <?= ($sort == 'stok') ? 'selected' : ''; ?>>Stok</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="order" class="form-select">
                            <option value="ASC" <?= ($order == 'ASC') ? 'selected' : ''; ?>>A-Z / Terkecil</option>
                            <option value="DESC" <?= ($order == 'DESC') ? 'selected' : ''; ?>>Z-A / Terbesar</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bi bi-filter"></i> Terapkan
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-warning text-center">
                        <tr>
                            <th>No</th>
                            <th><a href="?sort=kode_barang&order=<?= ($sort == 'kode_barang' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>&filter=<?= urlencode($filter); ?>" class="text-decoration-none text-dark">Kode</a></th>
                            <th><a href="?sort=nama_barang&order=<?= ($sort == 'nama_barang' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>&filter=<?= urlencode($filter); ?>" class="text-decoration-none text-dark">Nama Barang</a></th>
                            <th><a href="?sort=harga_beli&order=<?= ($sort == 'harga_beli' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>&filter=<?= urlencode($filter); ?>" class="text-decoration-none text-dark">Harga Beli</a></th>
                            <th><a href="?sort=harga_jual&order=<?= ($sort == 'harga_jual' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>&filter=<?= urlencode($filter); ?>" class="text-decoration-none text-dark">Harga Jual</a></th>
                            <th>Detail Keuntungan</th>
                            <th><a href="?sort=stok&order=<?= ($sort == 'stok' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>&filter=<?= urlencode($filter); ?>" class="text-decoration-none text-dark">Stok</a></th>
                            <th>Status</th>
                            <th width="260">Kebutuhan Kaca</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>    
                    <tbody>
                    <?php if(mysqli_num_rows($data) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while($d = mysqli_fetch_assoc($data)): ?>
                            <?php
                            $kode_barang = mysqli_real_escape_string($conn, $d['kode_barang']);
                            $cek_valid = mysqli_query($conn, "SELECT COUNT(DISTINCT nama_barang) AS total FROM barang WHERE kode_barang = '$kode_barang'");
                            $hasil_valid = mysqli_fetch_assoc($cek_valid);
                            
                            $class_row = ($hasil_valid['total'] > 1) ? "kode-tidak-valid" : "kode-valid";

                            $harga_beli = $d['harga_beli'];
                            $harga_jual = $d['harga_jual'];
                            $stok = $d['stok'];
                            
                            $persen_untung = 0;
                            $total_nominal_untung = 0;

                            if ($harga_beli > 0) {
                                $keuntungan_per_unit = $harga_jual - $harga_beli;
                                $persen_untung = ($keuntungan_per_unit / $harga_beli) * 100;
                                if ($stok > 0) {
                                    $total_nominal_untung = $keuntungan_per_unit * $stok;
                                }
                            }
                            ?>
                            <tr class="<?= $class_row; ?>">
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($d['kode_barang']); ?></td>
                                <td><?= htmlspecialchars($d['nama_barang']); ?></td>
                                <td>Rp <?= number_format($harga_beli,0,',','.'); ?></td>
                                <td>Rp <?= number_format($harga_jual,0,',','.'); ?></td>
                                <td>
                                    <div class="fw-bold text-success">
                                        <?= $persen_untung > 0 ? '+' . number_format($persen_untung, 1, ',', '.') . '%' : number_format($persen_untung, 1, ',', '.') . '%'; ?>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Total Untung: <span class="fw-semibold text-primary">Rp <?= number_format($total_nominal_untung, 0, ',', '.'); ?></span>
                                    </small>
                                </td>
                                <td class="text-center"><?= $stok; ?></td>
                                <td class="text-center">
                                    <?php if($stok <= 0): ?>
                                        <span class="badge bg-danger">Stok Habis</span>
                                    <?php elseif($stok <= $d['stok_minimum']): ?>
                                        <span class="badge bg-warning text-dark">Menipis</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aman</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- ================== BAGIAN BARANG KACA (Diperbaiki) ================== -->
                                <td>
                                    <?php if(strtolower($d['jenis_penjualan'] ?? '') == 'kaca'): ?>
                                        <div class="kaca-calculator">
                                            <small class="text-muted d-block mb-1">
                                                Standar: <?= $d['panjang_standar'] ?? '-' ?> × <?= $d['lebar_standar'] ?? '-' ?> cm
                                            </small>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="number" step="0.01" class="form-control form-control-sm panjang" placeholder="Pjg (cm)" onkeyup="hitungKaca(this)">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" step="0.01" class="form-control form-control-sm lebar" placeholder="Lbr (cm)" onkeyup="hitungKaca(this)">
                                                </div>
                                            </div>
                                            <div class="mt-2 text-center">
                                                <strong class="result text-primary">0.00 m²</strong>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <!-- ==================================================== -->

                                <td class="text-center">
                                    <a href="edit_barang.php?id=<?= urlencode($d['id_barang']); ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                    <a href="hapus_barang.php?id=<?= urlencode($d['id_barang']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus data?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-danger">Data Tidak Ditemukan</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function hitungKaca(el) {
        const row = el.closest('tr');
        const panjang = parseFloat(row.querySelector('.panjang').value) || 0;
        const lebar   = parseFloat(row.querySelector('.lebar').value) || 0;
        const luas    = (panjang * lebar / 10000).toFixed(2);  // konversi cm² ke m²
        row.querySelector('.result').textContent = luas + ' m²';
    }

    const isDark = "<?= isset($tema_sistem) ? $tema_sistem : 'light'; ?>" === 'dark';
    if (isDark) {
        document.body.classList.add('dark-theme');
    }
</script>
</body>
</html>