<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ... (Bagian PROTEKSI LOGIN & BUAT TABEL tetap sama) ...
if(!isset($_SESSION['level']) || $_SESSION['level'] != 'admin'){ header("Location: ../auth/login.php"); exit; }

// ... (Bagian AMBIL DATA SETTING tetap sama) ...
$querySetting = mysqli_query($conn, "SELECT * FROM setting LIMIT 1");
$setting = mysqli_fetch_assoc($querySetting);
$dark_mode = $setting['tema'] ?? 'light'; 
$notifikasi_stok = $setting['notifikasi_stok'] ?? 'aktif';
$auto_backup = $setting['auto_backup'] ?? 'nonaktif';

// ... (Bagian SIMPAN PENGATURAN tetap sama) ...
if(isset($_POST['simpan_setting'])){
    $dark = isset($_POST['dark_mode']) ? 'dark' : 'light';
    $notif = isset($_POST['notifikasi_stok']) ? 'aktif' : 'nonaktif';
    $backup = isset($_POST['auto_backup']) ? 'aktif' : 'nonaktif';
    mysqli_query($conn, "UPDATE setting SET tema = '$dark', notifikasi_stok = '$notif', auto_backup = '$backup' LIMIT 1");
    echo "<script>alert('Pengaturan berhasil disimpan!'); window.location='setting.php';</script>";
    exit;
}

// ======================================
// LOGIKA AUTO BACKUP DIPERBAIKI
// ======================================
if($auto_backup == 'aktif'){
    $terakhir_backup = $setting['terakhir_backup'];
    $backupSekarang = ($terakhir_backup == NULL) || (time() - strtotime($terakhir_backup) >= 604800);

    if($backupSekarang){
        $folderBackup = "../backup/";
        if(!is_dir($folderBackup)){ mkdir($folderBackup, 0777, true); }

        $nama_file = "backup_" . date('Y-m-d_H-i-s') . ".sql";
        $path = $folderBackup . $nama_file;
        $database = "penjualan_mitra_azam";
        $mysqldump = "C:/xampp/mysql/bin/mysqldump.exe"; // Pastikan path ini benar di PC Anda

        // Perintah dengan pengamanan path
        $command = "$mysqldump --user=root --password= --host=localhost " . escapeshellarg($database) . " > " . escapeshellarg($path);
        
        $output = [];
        $resultCode = 0;
        exec($command . " 2>&1", $output, $resultCode);

        if($resultCode === 0){
            mysqli_query($conn, "UPDATE setting SET terakhir_backup = NOW() LIMIT 1");
        } else {
            // Jika ingin melihat error di log/browser:
            error_log("Backup gagal: " . implode("\n", $output));
        }
    }
}

// ======================================
// CEK STOK MENIPIS
// ======================================
$jumlah_stok_menipis = 0;
if($notifikasi_stok == 'aktif'){
    $cekStok = mysqli_query($conn, "SELECT * FROM barang WHERE stok <= 5");
    if($cekStok){
        $jumlah_stok_menipis = mysqli_num_rows($cekStok);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Sistem</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            transition: background 0.3s, color 0.3s;
        }

        /* TEMA LIGHT (DEFAULT) */
        body { background: #f1f5f9; color: #000000; }
        .setting-card, .quick-setting { background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: none; border-radius: 24px; padding: 25px; margin-bottom: 25px; }
        .switch-box { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #e2e8f0; }
        .text-deskripsi { color: #64748b; }

        /* TEMA DARK */
        body.dark-theme { background: #0f172a; color: #ffffff; }
        body.dark-theme .setting-card, body.dark-theme .quick-setting { background: #1e293b; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        body.dark-theme .switch-box { border-bottom: 1px solid #334155; }
        body.dark-theme .text-deskripsi { color: #cbd5e1; }

        /* LAYOUT UTAMA */
        .content{ padding: 25px; margin-top: 75px; }
        .setting-card { display: flex; justify-content: space-between; align-items: center; }
        .setting-left { display: flex; align-items: center; gap: 20px; }
        .setting-icon { width: 70px; height: 70px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 30px; }
        
        .icon-toko{ background: #fff7ed; color: #f97316; }
        .icon-user{ background: #dbeafe; color: #2563eb; }
        .icon-password{ background: #fee2e2; color: #dc2626; }
        .icon-backup{ background: #dcfce7; color: #16a34a; }

        .setting-title{ font-size: 24px; font-weight: 600; }
        .btn-setting{ border: none; padding: 12px 24px; border-radius: 14px; font-weight: 600; text-decoration: none; color: white; transition: 0.3s; }
        .btn-setting:hover{ transform: scale(1.05); color: white; }
        .btn-toko{ background: #f97316; }
        .btn-user{ background: #2563eb; }
        .btn-password{ background: #dc2626; }
        .btn-backup{ background: #16a34a; }
        .quick-setting h4{ margin-bottom: 20px; font-weight: 700; }
        .alert-custom{ background: #fee2e2; color: #991b1b; padding: 18px; border-radius: 18px; margin-bottom: 25px; font-weight: 600; }

        @media(max-width: 768px){
            .setting-card{ flex-direction: column; align-items: flex-start; gap: 20px; }
            .btn-setting { width: 100%; text-align: center; }
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

<body class="<?= $dark_mode == 'dark' ? 'dark-theme text-white' : ''; ?>">

<<<<<<< HEAD
<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
=======
<nav id="mainNavbar" class="navbar fixed-top shadow-sm <?= $dark_mode == 'dark' ? 'navbar-dark bg-dark' : 'bg-body-tertiary'; ?>">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
>>>>>>> 6de78987371e39bb3f02ab6b721ad0894952bcd1
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
<<<<<<< HEAD
=======
    
    <div class="offcanvas offcanvas-start <?= $dark_mode == 'dark' ? 'text-bg-dark bg-dark' : ''; ?>" tabindex="-1" id="offcanvasNavbar">
      <div class="offcanvas-header border-bottom <?= $dark_mode == 'dark' ? 'border-secondary' : ''; ?>">
        <h5 class="offcanvas-title fw-bold text-primary"><i class="bi bi-shop"></i> MITRA AZAM</h5>
        <button type="button" class="btn-close <?= $dark_mode == 'dark' ? 'btn-close-white' : ''; ?>" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav justify-content-start flex-grow-1 pe-3">
          <li class="nav-item mb-2"><a class="nav-link fw-semibold" href="dashboard.php"><i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard</a></li>
          
          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-box-seam me-2 text-primary"></i> Data Barang</a>
            <ul class="dropdown-menu <?= $dark_mode == 'dark' ? 'dropdown-menu-dark' : ''; ?>">
              <li><a class="dropdown-item" href="barang.php"><i class="bi bi-list-ul me-2"></i> Semua Barang</a></li>
              <li><a class="dropdown-item" href="tambah_barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Tambah Stok Masuk</a></li>
              <li><a class="dropdown-item" href="tambah_barang.php"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a></li>
              <li><a class="dropdown-item" href="barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Barang Masuk</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-file-earmark-text me-2 text-primary"></i> Laporan</a>
            <ul class="dropdown-menu <?= $dark_mode == 'dark' ? 'dropdown-menu-dark' : ''; ?>">
              <li><a class="dropdown-item" href="laporan.php"><i class="bi bi-file-earmark-ruled me-2"></i> Ringkasan Laporan</a></li>
              <li><a class="dropdown-item" href="laba_rugi.php"><i class="bi bi-cash-stack me-2"></i> Laba Rugi</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle active fw-semibold" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-gear-fill me-2 text-primary"></i> Setting</a>
            <ul class="dropdown-menu <?= $dark_mode == 'dark' ? 'dropdown-menu-dark' : ''; ?>">
              <li><a class="dropdown-item" href="setting.php"><i class="bi bi-sliders me-2"></i> Pengaturan Umum</a></li>
              <li><a class="dropdown-item" href="manajemen_user.php"><i class="bi bi-people me-2"></i> Manajemen User</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger fw-bold" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
>>>>>>> 6de78987371e39bb3f02ab6b721ad0894952bcd1
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
    <?php if($notifikasi_stok == 'aktif' && $jumlah_stok_menipis > 0): ?>
        <div class="alert-custom"><i class="bi bi-bell-fill me-2"></i> Ada <?= $jumlah_stok_menipis; ?> barang dengan stok menipis.</div>
    <?php endif; ?>

    <div class="quick-setting d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-gear-fill text-warning me-2"></i>Pengaturan Sistem</h2>
            <p class="text-deskripsi mb-0">Kelola pengaturan aplikasi toko bangunan</p>
        </div>
        <div class="fw-bold"><i class="bi bi-person-circle text-primary me-1"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
    </div>

    <div class="setting-card">
        <div class="setting-left">
            <div class="setting-icon icon-toko"><i class="bi bi-shop"></i></div>
            <div><div class="setting-title">Profil Toko</div><div class="text-deskripsi">Kelola data toko</div></div>
        </div>
        <a href="edit_toko.php" class="btn-setting btn-toko">Edit Toko</a>
    </div>

    <div class="setting-card">
        <div class="setting-left">
            <div class="setting-icon icon-user"><i class="bi bi-person-circle"></i></div>
            <div><div class="setting-title">Akun Admin</div><div class="text-deskripsi">Kelola akun administrator</div></div>
        </div>
        <a href="edit_admin.php" class="btn-setting btn-user">Edit Admin</a>
    </div>

    <div class="setting-card">
        <div class="setting-left">
            <div class="setting-icon icon-password"><i class="bi bi-shield-lock-fill"></i></div>
            <div><div class="setting-title">Password</div><div class="text-deskripsi">Ganti password akun</div></div>
        </div>
        <a href="ganti_password.php" class="btn-setting btn-password">Ganti Password</a>
    </div>

    <div class="setting-card">
        <div class="setting-left">
            <div class="setting-icon icon-backup"><i class="bi bi-database-fill"></i></div>
            <div><div class="setting-title">Backup Database</div><div class="text-deskripsi">Backup manual database</div></div>
        </div>
        <a href="backup.php" class="btn-setting btn-backup">Backup Sekarang</a>
    </div>

    <form method="POST">
        <div class="quick-setting">
            <h4><i class="bi bi-sliders me-2 text-primary"></i>Pengaturan Cepat</h4>

            <div class="switch-box">
                <div>
                    <h6 class="mb-1 fw-bold">Dark Mode</h6>
                    <small class="text-deskripsi">Aktifkan atau nonaktifkan tema gelap aplikasi</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="dark_mode" id="darkToggle" <?= $dark_mode == 'dark' ? 'checked' : ''; ?>>
                </div>
            </div>

            <div class="switch-box">
                <div>
                    <h6 class="mb-1 fw-bold">Notifikasi Stok</h6>
                    <small class="text-deskripsi">Tampilkan notifikasi jika stok barang menipis</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="notifikasi_stok" <?= $notifikasi_stok == 'aktif' ? 'checked' : ''; ?>>
                </div>
            </div>

            <div class="switch-box border-0">
                <div>
                    <h6 class="mb-1 fw-bold">Auto Backup</h6>
                    <small class="text-deskripsi">Backup otomatis database sistem setiap minggu</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="auto_backup" <?= $auto_backup == 'aktif' ? 'checked' : ''; ?>>
                </div>
            </div>

            <button type="submit" name="simpan_setting" class="btn-setting btn-toko mt-4 w-100 text-center">
                <i class="bi bi-save-fill me-2"></i>Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('darkToggle').addEventListener('change', function() {
    const body = document.body;
    const navbar = document.getElementById('mainNavbar');
    const offcanvas = document.getElementById('offcanvasNavbar');
    const dropdowns = document.querySelectorAll('.dropdown-menu');

    if(this.checked) {
        body.classList.add('dark-theme', 'text-white');
        navbar.classList.remove('bg-body-tertiary');
        navbar.classList.add('navbar-dark', 'bg-dark');
        offcanvas.classList.add('text-bg-dark', 'bg-dark');
        dropdowns.forEach(dd => dd.classList.add('dropdown-menu-dark'));
    } else {
        body.classList.remove('dark-theme', 'text-white');
        navbar.classList.add('bg-body-tertiary');
        navbar.classList.remove('navbar-dark', 'bg-dark');
        offcanvas.classList.remove('text-bg-dark', 'bg-dark');
        dropdowns.forEach(dd => dd.classList.remove('dropdown-menu-dark'));
    }
});
</script>
</body>
</html>