<?php
session_start();

// ============================
// SET TIMEZONE WIT (WAKTU INDONESIA TIMUR)
// ============================
date_default_timezone_set('Asia/Jayapura');

require_once '../config/koneksi.php';
require_once '../config/load_theme.php';

/** @var mysqli $conn */

// Mengatur koneksi database agar menggunakan timezone lokal PHP jika didukung server
mysqli_query($conn, "SET time_zone = '" . date('P') . "'");

// Ambil tema dari session (sinkron dengan halaman setting)
$current_tema = $_SESSION['tema'] ?? 'light';

/* =========================
   FIX ERROR $conn
========================= */
if (!isset($conn) || !$conn) {
    die("❌ Koneksi database gagal. Pastikan file config/koneksi.php benar dan variabel \$conn tersedia.");
}

// Proteksi Login
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Filter
$filter = $_GET['filter'] ?? '';
$cari = $_GET['cari'] ?? '';

// Query Data Hutang
$where = "
metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
";

/*
|--------------------------------------------------------------------------
| FILTER TANGGAL
|--------------------------------------------------------------------------
*/
if ($filter == 'hariini') {
    $where .= " AND DATE(jatuh_tempo)=CURDATE()";
} elseif ($filter == 'mendekati') {
    $where .= "
    AND jatuh_tempo BETWEEN DATE_ADD(CURDATE(),INTERVAL 1 DAY)
    AND DATE_ADD(CURDATE(),INTERVAL 3 DAY)
    ";
} elseif ($filter == 'terlambat') {
    $where .= "
    AND jatuh_tempo < CURDATE()
    ";
}

/*
|--------------------------------------------------------------------------
| FILTER NAMA CUSTOMER
|--------------------------------------------------------------------------
*/
if (!empty($cari)) {
    $cari = mysqli_real_escape_string($conn, $cari);
    $where .= "
    AND nama_customer LIKE '%$cari%'
    ";
}

/*
|--------------------------------------------------------------------------
| QUERY AKHIR
|--------------------------------------------------------------------------
*/
$sql = "
SELECT *
FROM penjualan
WHERE $where
ORDER BY jatuh_tempo ASC
";

$query_hutang = mysqli_query($conn, $sql);

if (!$query_hutang) {
    die("Query Error : " . mysqli_error($conn));
}

// Total hutang aktif
$q_total = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
");
$total_hutang = mysqli_fetch_assoc($q_total)['total'];

// Hari ini
$q_hariini = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND DATE(jatuh_tempo)=CURDATE()
");
$total_hariini = mysqli_fetch_assoc($q_hariini)['total'];

// Mendekati
$q_mendekati = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND jatuh_tempo BETWEEN DATE_ADD(CURDATE(),INTERVAL 1 DAY)
AND DATE_ADD(CURDATE(),INTERVAL 3 DAY)
");
$total_mendekati = mysqli_fetch_assoc($q_mendekati)['total'];

// Terlambat
$q_terlambat = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND jatuh_tempo < CURDATE()
");
$total_terlambat = mysqli_fetch_assoc($q_terlambat)['total'];
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="<?= $current_tema ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Hutang Customer</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ===== GLOBAL ===== */
        body {
            background: #f4f7fb;
            font-family: 'Segoe UI', sans-serif;
            padding-top: 80px;
            padding-bottom: 40px;
            color: #212529;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ===== TEMA GELAP (DARK MODE) ===== */
        body.dark-theme { 
            background: #0f172a !important; 
            color: #f8fafc !important; 
        }
        
        body.dark-theme .navbar,
        body.dark-theme .card,
        body.dark-theme .stat-card,
        body.dark-theme .card-header { 
            background: #1e293b !important; 
            border-color: #334155 !important; 
            color: #f8fafc !important; 
        }

        body.dark-theme .text-muted,
        body.dark-theme .stat-card h6 { 
            color: #94a3b8 !important; 
        }

        body.dark-theme .table {
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        body.dark-theme .table thead th {
            background-color: #1e293b !important;
            color: #60a5fa !important;
            border-color: #334155 !important;
        }

        body.dark-theme .table tbody tr {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        body.dark-theme .table tbody tr:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        body.dark-theme .table td, 
        body.dark-theme .table th {
            border-color: #334155 !important;
        }

        body.dark-theme .form-control {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        body.dark-theme .form-control:focus {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #2563eb !important;
        }

        /* ===== SIDEBAR (OFFCANVAS) ===== */
        .offcanvas {
            background: linear-gradient(180deg, #0d6efd, #0a46a6) !important;
            color: white;
            width: 290px !important;
            border-right: none;
        }

        .sidebar-header-custom {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,.15);
        }

        .profile-section {
            padding: 15px;
            margin: 10px 15px;
            border-radius: 12px;
            background: rgba(0,0,0,.1);
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
            color: white;
            margin: 0;
        }

        .profile-info span {
            color: rgba(255,255,255,.8);
            font-size: 12px;
        }

        .sidebar-nav-container {
            padding: 10px 15px;
        }

        .menu-item-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            background: transparent;
            border: none;
            width: 100%;
            font-size: 15px;
            font-weight: 500;
        }

        .menu-item-link:hover {
            background: rgba(255,255,255,.15);
            color: white;
        }

        .menu-icon {
            margin-right: 10px;
        }

        .submenu-container {
            background: #f1f3f5;
            padding: 6px 0;
            border-radius: 10px;
            margin-top: 5px;
        }

        body.dark-theme .submenu-container {
            background: #111827 !important;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            padding: 10px 20px 10px 30px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        body.dark-theme .submenu-link {
            color: #cbd5e1 !important;
        }

        .submenu-link:hover {
            color: #0d6efd;
            background: rgba(0,0,0,0.05);
        }

        body.dark-theme .submenu-link:hover {
            color: #60a5fa !important;
            background: rgba(255,255,255,0.05);
        }

        .arrow-icon {
            transition: .3s;
        }

        .menu-item-link[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }

        /* ===== CONTENT & COMPONENTS ===== */
        .content {
            padding: 25px;
        }

        .header-card {
            background: linear-gradient(135deg,#2563eb,#1d4ed8);
            color: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,.08);
        }

        .active-card {
            border: 3px solid #2563eb !important;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,.06);
            transition: .3s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,.15);
        }

        .stat-card h6 {
            color: #6c757d;
            margin-bottom: 10px;
        }

        .stat-card h2 {
            font-weight: bold;
            margin-bottom: 0;
        }

        .card {
            border: none;
            border-radius: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,.06);
        }

        .card-header {
            padding: 20px;
            background: #ffffff;
            border-top-left-radius: 25px !important;
            border-top-right-radius: 25px !important;
        }

        .table th {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .badge-terlambat {
            background: #dc3545;
            color: white;
            font-size: 11px;
            padding: 6px 10px;
        }

        .badge-belum {
            background: #ffc107;
            color: black;
            padding: 6px 12px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 12px;
        }
    </style>
</head>
<body class="<?= $current_tema == 'dark' ? 'dark-theme' : ''; ?>">

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
   
    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-flex align-items-center gap-2 me-3" id="themeToggleBtn">
        <i class="bi <?= $current_tema == 'dark' ? 'bi-moon-stars-fill text-warning' : 'bi-sun-fill text-warning'; ?>"></i>
        <span class="small fw-semibold d-none d-md-inline"><?= $current_tema == 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
    </button>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar">
    <div class="sidebar-header-custom d-flex justify-content-between align-items-center">
        <span class="fs-5 fw-bold text-white">
            <i class="bi bi-shop"></i> MITRA AZAM
        </span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="profile-section d-flex align-items-center gap-3">
        <div class="profile-img">
            <?php if (!empty($_SESSION['foto']) && file_exists("../assets/admin/" . $_SESSION['foto'])): ?>
                <img src="../assets/admin/<?= htmlspecialchars($_SESSION['foto']); ?>" class="user-avatar" alt="Profil">
            <?php else: ?>
                <div class="user-avatar-default">
                    <i class="bi bi-person text-dark fs-4"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h6><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></h6>
            <span>
                <i class="bi bi-circle-fill text-success me-1" style="font-size:8px;"></i>
                <?= ucfirst($_SESSION['level'] ?? 'Kasir'); ?>
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
                <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuBarang">
                    <span><i class="bi bi-box-seam menu-icon"></i> Data Barang</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </button>
                <div class="collapse" id="menuBarang">
                    <div class="submenu-container">
                        <a href="barang.php" class="submenu-link"><i class="bi bi-list-ul me-2"></i> Semua Barang</a>
                        <a href="tambah_barang.php" class="submenu-link"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a>
                        <a href="stok_barang_masuk.php" class="submenu-link"><i class="bi bi-journal-arrow-down me-2"></i> Stok Barang Masuk</a>
                        <a href="riwayat_barang_masuk.php" class="submenu-link"><i class="bi bi-download me-2"></i> Riwayat Barang Masuk</a>
                    </div>
                </div>
            </div>

            <!-- DATA HUTANG -->
            <div class="mb-1">
                <a href="data_hutang.php" class="menu-item-link">
                    <span><i class="bi bi-credit-card menu-icon"></i> Data Hutang Customer</span>
                </a>
            </div>

            <!-- LAPORAN -->
            <div class="mb-1">
                <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuLaporan">
                    <span><i class="bi bi-file-earmark-text menu-icon"></i> Laporan</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </button>
                <div class="collapse" id="menuLaporan">
                    <div class="submenu-container">
                        <a href="laporan.php" class="submenu-link"><i class="bi bi-file-earmark-spreadsheet me-2"></i> Ringkasan Laporan</a>
                        
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

            <!-- SETTING -->
            <div class="mb-1">
                <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuSetting">
                    <span><i class="bi bi-gear menu-icon"></i> Setting</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </button>
                <div class="collapse" id="menuSetting">
                    <div class="submenu-container">
                        <a href="setting.php" class="submenu-link"><i class="bi bi-sliders me-2"></i> Pengaturan Umum</a>
                        <?php if($_SESSION['level'] == 'admin'): ?>
                        <a href="../admin/manajemen_user.php" class="submenu-link"><i class="bi bi-people me-2"></i> Manajemen User</a>
                        <?php endif; ?>
                        <hr class="my-1 text-muted">
                        <a href="../auth/logout.php" class="submenu-link text-danger fw-semibold">
                            <i class="bi bi-box-arrow-left me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid content">
    <div class="header-card mb-4">
        <h2><i class="bi bi-credit-card"></i> Data Hutang Customer</h2>
        <p class="mb-0">Daftar piutang customer yang belum lunas.</p>
    </div>

    <div class="row g-4 mb-4">
        <!-- TOTAL HUTANG -->
        <div class="col-md-3">
            <a href="data_hutang.php?filter=&cari=<?= urlencode($cari); ?>" class="text-decoration-none">
                <div class="stat-card <?= empty($filter) ? 'active-card' : ''; ?>">
                    <h6>Total Hutang</h6>
                    <h2><?= $total_hutang ?></h2>
                </div>
            </a>
        </div>

        <!-- HARI INI -->
        <div class="col-md-3">
            <a href="data_hutang.php?filter=hariini&cari=<?= urlencode($cari); ?>" class="text-decoration-none">
                <div class="stat-card <?= $filter == 'hariini' ? 'active-card' : ''; ?>">
                    <h6>Hari Ini</h6>
                    <h2><?= $total_hariini ?></h2>
                </div>
            </a>
        </div>

        <!-- MENDEKATI -->
        <div class="col-md-3">
            <a href="data_hutang.php?filter=mendekati&cari=<?= urlencode($cari); ?>" class="text-decoration-none">
                <div class="stat-card <?= $filter == 'mendekati' ? 'active-card' : ''; ?>">
                    <h6>Mendekati</h6>
                    <h2><?= $total_mendekati ?></h2>
                </div>
            </a>
        </div>

        <!-- TERLAMBAT -->
        <div class="col-md-3">
            <a href="data_hutang.php?filter=terlambat&cari=<?= urlencode($cari); ?>" class="text-decoration-none">
                <div class="stat-card <?= $filter == 'terlambat' ? 'active-card' : ''; ?>">
                    <h6>Terlambat</h6>
                    <h2><?= $total_terlambat ?></h2>
                </div>
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <input type="text" name="cari" class="form-control" placeholder="Cari nama customer..." value="<?= htmlspecialchars($cari); ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="data_hutang.php" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DATA HUTANG -->
    <div class="card table-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2 text-primary"></i> Data Hutang
                </h5>
                <span class="badge bg-primary fs-6">
                    <?= mysqli_num_rows($query_hutang); ?> Data
                </span>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No Nota</th>
                        <th>Nama Customer</th>
                        <th>Tanggal Transaksi</th>
                        <th>Jatuh Tempo</th>
                        <th>Total Hutang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($query_hutang) < 1): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle fs-2 d-block mb-2"></i>
                            Tidak ada data hutang customer.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($row = mysqli_fetch_assoc($query_hutang)): ?>
                    <?php
                    $is_overdue = date('Y-m-d') > $row['jatuh_tempo'];
                    ?>
                    <tr>
                        <td>
                            <strong>#<?= $row['id_penjualan']; ?></strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['nama_customer']); ?>
                        </td>
                        <td>
                            <?= date('d M Y', strtotime($row['tanggal'])); ?>
                        </td>
                        <td>
                            <?= date('d M Y', strtotime($row['jatuh_tempo'])); ?>
                            <?php if($is_overdue): ?>
                                <br>
                                <span class="badge badge-terlambat mt-1">TERLAMBAT</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong class="text-primary">
                                Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>
                            </strong>
                        </td>
                        <td>
                            <span class="badge badge-belum">
                                Belum Lunas
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fungsi untuk menerapkan tema pada elemen DOM
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        const body = document.body;
        const btn = document.getElementById('themeToggleBtn');
        if (!btn) return;
        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');

        if (theme === 'dark') {
            body.classList.add('dark-theme');
            icon.className = "bi bi-moon-stars-fill text-warning";
            if(text) text.textContent = "Dark Mode";
        } else {
            body.classList.remove('dark-theme');
            icon.className = "bi bi-sun-fill text-warning";
            if(text) text.textContent = "Light Mode";
        }
    }

    // Event listener untuk tombol toggle di navbar
    document.getElementById('themeToggleBtn').addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        
        applyTheme(newTheme);
        localStorage.setItem('theme', newTheme);

        // Optional: Kirim request ke backend/session jika ingin mengubah secara global via AJAX
        fetch(`update_theme.php?tema=${newTheme}`).catch(err => console.error(err));
    });

    document.addEventListener("DOMContentLoaded", function() {
        // Prioritaskan session PHP ($current_tema) yang di-load dari server, lalu fallback ke localStorage
        const serverTheme = "<?= $current_tema ?>";
        applyTheme(serverTheme);
    });
</script>
</body>
</html>