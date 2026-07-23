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

// ======================================
// INTEGRASI DARK MODE DARI DATABASE (REAL-SYNC)
// ======================================
$queryGlobalSetting = mysqli_query($conn, "SELECT tema FROM setting LIMIT 1");
$globalSetting = mysqli_fetch_assoc($queryGlobalSetting);
$tema_sistem = $globalSetting['tema'] ?? 'light';

// Sinkronisasi otomatis database ke session agar selalu sinkron
$_SESSION['tema'] = $tema_sistem;
$current_tema = $tema_sistem;

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
<html lang="id" data-bs-theme="<?= $current_tema ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Barang Masuk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ===== GLOBAL ===== */
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            color: #212529;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ===== TEMA GELAP (DARK MODE) ===== */
        body.dark-theme { 
            background: #0f172a !important; 
            color: #f8fafc !important; 
        }
        
        body.dark-theme .navbar,
        body.dark-theme .card { 
            background: #1e293b !important; 
            border-color: #334155 !important; 
            color: #f8fafc !important; 
        }

        body.dark-theme .text-muted { 
            color: #94a3b8 !important; 
        }

        body.dark-theme .table {
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        body.dark-theme .table thead th {
            background-color: #1e293b !important;
            color: #f8fafc !important;
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

        /* ===== CONTENT ===== */
        .content {
            padding: 30px;
            margin-top: 75px;
        }

        /* ===== CARD ===== */
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        /* ===== HEADER ===== */
        .card-header {
            background: linear-gradient(135deg, #296bf9, #142b76) !important;
            color: white !important;
            font-weight: 600;
            border-top-left-radius: 18px !important;
            border-top-right-radius: 18px !important;
        }

        /* ===== TABLE ===== */
        .table tbody tr:hover {
            background: #fff7f0;
        }

        .badge {
            padding: 7px 10px;
        }

        /* ===== BUTTON ===== */
        .btn-warning {
            background: linear-gradient(135deg, #296bf9, #142b76);
            border: none;
            color: white;
        }

        .btn-warning:hover {
            opacity: 0.9;
            color: white;
        }

        /* ========================================================
           SIDEBAR IMPLEMENTASI TEMA BIRU ELEGAN & STRUKTUR DROPDOWN
           ======================================================== */
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
        body.dark-theme .submenu-container {
            background-color: #111827 !important;
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
        body.dark-theme .submenu-link {
            color: #cbd5e1 !important;
        }
        .submenu-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: #0d6efd;
        }
        body.dark-theme .submenu-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #60a5fa;
        }
        .submenu-link.active {
            color: #0d6efd;
            font-weight: 600;
            background-color: rgba(13, 110, 253, 0.08);
        }
        body.dark-theme .submenu-link.active {
            color: #60a5fa !important;
            background-color: rgba(96, 165, 250, 0.15);
        }
        .submenu-link i {
            font-size: 16px;
            margin-right: 12px;
            color: #555;
        }
        body.dark-theme .submenu-link i {
            color: #94a3b8;
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
<body class="<?= ($current_tema == 'dark') ? 'dark-theme' : ''; ?>">

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
   
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
                <i class="bi bi-person text-dark fs-4"></i>
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
            <button class="menu-item-link" type="button" data-bs-toggle="collapse" data-bs-target="#menuBarang" aria-expanded="true">
                <span><i class="bi bi-box-seam menu-icon"></i> Data Barang</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse show" id="menuBarang">
                <div class="submenu-container">
                    <a href="barang.php" class="submenu-link"><i class="bi bi-list-ul"></i> Semua Barang</a>
                    <a href="tambah_barang.php" class="submenu-link"><i class="bi bi-plus-circle"></i> Tambah Barang</a>
                    <a href="stok_barang_masuk.php" class="submenu-link"><i class="bi bi-journal-arrow-down"></i> Stok Barang Masuk</a>
                    <a href="riwayat_barang_masuk.php" class="submenu-link active"><i class="bi bi-download"></i> Riwayat Barang Masuk</a>
                </div>
            </div>
        </div>
        
        <!-- DATA HUTANG -->
        <div class="mb-1">
            <a href="data_hutang.php" class="menu-item-link">
                <span>
                    <i class="bi bi-credit-card menu-icon"></i>
                    Data Hutang Customer
                </span>
            </a>
        </div>

        <div class="mb-1">
            <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuLaporan" aria-expanded="false">
                <span><i class="bi bi-file-earmark-text menu-icon"></i> Laporan</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse" id="menuLaporan">
                <div class="submenu-container">
                    <a href="laporan.php" class="submenu-link"><i class="bi bi-file-earmark-spreadsheet"></i> Ringkasan Laporan</a>
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
<script>
// ====================== TEMA MANAGEMENT ======================
function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    if (theme === 'dark') {
        document.body.classList.add('dark-theme');
    } else {
        document.body.classList.remove('dark-theme');
    }

    const btn = document.getElementById('themeToggleBtn');
    if (!btn) return;
    const icon = btn.querySelector('i');
    const text = btn.querySelector('span');

    if (theme === 'dark') {
        icon.className = "bi bi-moon-stars-fill text-warning";
        if(text) text.textContent = "Dark Mode";
    } else {
        icon.className = "bi bi-sun-fill text-warning";
        if(text) text.textContent = "Light Mode";
    }
}

function syncThemeWithSession(theme) {
    fetch('update_theme.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'tema=' + theme
    });
}

document.getElementById('themeToggleBtn').addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = current === 'dark' ? 'light' : 'dark';

    applyTheme(newTheme);
    localStorage.setItem('theme', newTheme);
    syncThemeWithSession(newTheme);
});

document.addEventListener("DOMContentLoaded", function() {
    // Sinkronisasi otomatis langsung mengambil nilai dari database via PHP
    let serverTheme = '<?= $current_tema ?>';
    applyTheme(serverTheme);
});
</script>
</body>
</html>