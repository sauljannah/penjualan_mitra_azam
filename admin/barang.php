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
    </style>
</head>
<body class="<?= ($tema_sistem ?? 'light') == 'dark' ? 'dark-theme' : ''; ?>">

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
              <li><a class="dropdown-item" href="barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Barang Masuk</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-file-earmark-text me-2 text-primary"></i> Laporan
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="laporan.php"><i class="bi bi-file-earmark-ruled me-2"></i> Ringkasan Laporan</a></li>
              <li><a class="dropdown-item" href="laba_rugi.php"><i class="bi bi-cash-stack me-2"></i> Laba Ruigi</a></li>
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
                            <th><a href="?sort=stok&order=<?= ($sort == 'stok' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>&filter=<?= urlencode($filter); ?>" class="text-decoration-none text-dark">Stok</a></th>
                            <th>Status</th>
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
                            ?>
                            <tr class="<?= $class_row; ?>">
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($d['kode_barang']); ?></td>
                                <td><?= htmlspecialchars($d['nama_barang']); ?></td>
                                <td>Rp <?= number_format($d['harga_beli'],0,',','.'); ?></td>
                                <td>Rp <?= number_format($d['harga_jual'],0,',','.'); ?></td>
                                <td class="text-center"><?= $d['stok']; ?></td>
                                <td class="text-center">
                                    <?php if($d['stok'] <= 0): ?>
                                        <span class="badge bg-danger">Stok Habis</span>
                                    <?php elseif($d['stok'] <= $d['stok_minimum']): ?>
                                        <span class="badge bg-warning text-dark">Menipis</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aman</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="edit_barang.php?id=<?= urlencode($d['id_barang']); ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                    <a href="hapus_barang.php?id=<?= urlencode($d['id_barang']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus data?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-danger">Data Tidak Ditemukan</td>
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
    const isDark = "<?= isset($tema_sistem) ? $tema_sistem : 'light'; ?>" === 'dark';
    if (isDark) {
        document.body.classList.add('dark-theme');
    }
</script>
</body>
</html>