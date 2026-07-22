<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// Proteksi Login & Level Admin
if(!isset($_SESSION['level']) || $_SESSION['level'] != 'admin'){ 
    header("Location: ../auth/login.php"); 
    exit; 
}

// ==========================================
// 1. PROSES SIMPAN PENGATURAN (SANGAT KRAKATAU / MUTLAK)
// ==========================================
if(isset($_POST['simpan_setting'])){
    // Ambil kiriman form, jika tidak dicentang maka otomatis 'light'
    $dark = isset($_POST['dark_mode']) ? 'dark' : 'light';
    $notif = isset($_POST['notifikasi_stok']) ? 'aktif' : 'nonaktif';
    $backup = isset($_POST['auto_backup']) ? 'aktif' : 'nonaktif';
    
    // Kita gunakan query UPDATE tanpa WHERE terlebih dahulu untuk memastikan SEMUA baris di tabel setting berubah.
    // Jika tabel Anda punya primary key seperti id_setting, disarankan menggantinya menjadi: UPDATE setting SET ... WHERE id_setting = 1
    $updateQuery = "UPDATE setting SET tema = '$dark', notifikasi_stok = '$notif', auto_backup = '$backup'";
    $exec = mysqli_query($conn, $updateQuery);
    
    if($exec) {
        // Simpan ke session agar konsisten dengan halaman lain
        $_SESSION['tema'] = $dark;

        // Alihkan menggunakan JavaScript murni + timestamp buster agar browser tidak memakan cache lama
        echo "<script>
                alert('Pengaturan BERHASIL disimpan! Sistem beralih ke: " . strtoupper($dark) . "'); 
                window.location.href='setting.php?v=" . time() . "';
              </script>";
        exit;
    } else {
        echo "<script>alert('Gagal update database: " . mysqli_error($conn) . "');</script>";
    }
}

// ==========================================
// 2. AMBIL DATA SETTING TERBARU DARI DATABASE
// ==========================================
$querySetting = mysqli_query($conn, "SELECT * FROM setting LIMIT 1");
$setting = mysqli_fetch_assoc($querySetting);

// Validasi super ketat: pastikan bernilai string 'dark' untuk mode gelap. Selain itu, WAJIB 'light'
if (isset($setting['tema']) && trim(strtolower($setting['tema'])) === 'dark') {
    $dark_mode = 'dark';
} else {
    $dark_mode = 'light';
}

// Sinkronisasi ke session
$_SESSION['tema'] = $dark_mode;

$notifikasi_stok = $setting['notifikasi_stok'] ?? 'aktif';
$auto_backup = $setting['auto_backup'] ?? 'nonaktif';

// Logika Auto Backup
if($auto_backup == 'aktif'){
    $terakhir_backup = $setting['terakhir_backup'];
    $backupSekarang = ($terakhir_backup == NULL) || (time() - strtotime($terakhir_backup) >= 604800);

    if($backupSekarang){
        $folderBackup = "../backup/";
        if(!is_dir($folderBackup)){ mkdir($folderBackup, 0777, true); }

        $nama_file = "backup_" . date('Y-m-d_H-i-s') . ".sql";
        $path = $folderBackup . $nama_file;
        $database = "penjualan_mitra_azam";
        $mysqldump = "D:/xampp/mysql/bin/mysqldump.exe";

        $command = sprintf(
            '""%s" --user=root --host=localhost %s > %s"',
            $mysqldump,
            escapeshellarg($database),
            escapeshellarg($path)
        );
        
        $output = [];
        $resultCode = 0;
        exec("cmd.exe /c " . $command . " 2>&1", $output, $resultCode);

        if($resultCode === 0){
            mysqli_query($conn, "UPDATE setting SET terakhir_backup = NOW() LIMIT 1");
        }
    }
}

// Cek Stok Menipis
$jumlah_stok_menipis = 0;
if($notifikasi_stok == 'aktif'){
    $cekStok = mysqli_query($conn, "SELECT * FROM barang WHERE stok <= 5");
    if($cekStok){
        $jumlah_stok_menipis = mysqli_num_rows($cekStok);
    }
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="<?= $dark_mode ?>">
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
            transition: background 0.15s ease, color 0.15s ease;
        }

        /* -----------------------------------------
           FORCE TEMA LIGHT / TERANG (DEFAULT)
           Menggunakan !important agar tidak ditimpa framework luar
           ----------------------------------------- */
        body { background: #f1f5f9 !important; color: #1e293b !important; padding-top: 70px; }
        .setting-card, .quick-setting { background: #ffffff !important; box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important; border: none !important; border-radius: 24px; padding: 25px; margin-bottom: 25px; }
        .switch-box { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #e2e8f0 !important; }
        .text-deskripsi { color: #64748b !important; }
        .navbar-custom { background-color: #ffffff !important; border-bottom: 1px solid #e2e8f0 !important; }
        .navbar-custom .navbar-brand { color: #0d6efd !important; }

        /* -----------------------------------------
           FORCE TEMA DARK / GELAP 
           ----------------------------------------- */
        [data-bs-theme="dark"] body { background: #0f172a !important; color: #f8fafc !important; }
        [data-bs-theme="dark"] .setting-card, 
        [data-bs-theme="dark"] .quick-setting { 
            background: #1e293b !important; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3) !important; 
        }
        [data-bs-theme="dark"] .switch-box { border-bottom: 1px solid #334155 !important; }
        [data-bs-theme="dark"] .text-deskripsi { color: #94a3b8 !important; }
        [data-bs-theme="dark"] .navbar-custom { background-color: #1e293b !important; border-bottom: 1px solid #334155 !important; }
        [data-bs-theme="dark"] .navbar-brand { color: #3b82f6 !important; }
        [data-bs-theme="dark"] .navbar-toggler { background-color: #334155 !important; color: white !important; }

        /* LAYOUT DAN ORNAMEN UTAMA */
        .content{ padding: 25px; }
        .setting-card { display: flex; justify-content: space-between; align-items: center; }
        .setting-left { display: flex; align-items: center; gap: 20px; }
        .setting-icon { width: 70px; height: 70px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 30px; flex-shrink: 0; }
        
        .icon-toko{ background: #fff7ed; color: #f97316; }
        .icon-user{ background: #dbeafe; color: #2563eb; }
        .icon-password{ background: #fee2e2; color: #dc2626; }
        .icon-backup{ background: #dcfce7; color: #16a34a; }

        .setting-title{ font-size: 24px; font-weight: 600; }
        .btn-setting { border: none; padding: 12px 24px; border-radius: 14px; font-weight: 600; text-decoration: none; color: white; transition: 0.3s; display: inline-block; }
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

        /* SIDEBAR STYLING */
        .offcanvas { background: linear-gradient(180deg, #0d6efd, #0a46a6) !important; color: #ffffff !important; width: 290px !important; border-right: none !important; }
        .sidebar-header-custom { padding: 20px 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.15); }
        .profile-section { padding: 15px; background: rgba(0, 0, 0, 0.1); border-radius: 12px; margin: 10px 15px; }
        .profile-img { width: 44px; height: 44px; background: rgba(255, 255, 255, 0.25); border: 2px solid rgba(255, 255, 255, 0.5); border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 22px; color: white; }
        .profile-info h6 { margin: 0; font-size: 14px; font-weight: 600; color: white; }
        .profile-info span { font-size: 12px; color: rgba(255, 255, 255, 0.75); }
        .sidebar-nav-container { padding: 10px 15px; }
        
        .menu-item-link {
            display: flex; align-items: center; justify-content: space-between; padding: 12px 15px; color: rgba(255, 255, 255, 0.9); text-decoration: none; border-radius: 10px; font-size: 15px; font-weight: 500; transition: all 0.2s ease; background: transparent; border: none; width: 100%; text-align: left;
        }
        .menu-item-link:hover { background-color: rgba(255, 255, 255, 0.15); color: #ffffff; }
        .menu-item-link i.menu-icon { font-size: 18px; margin-right: 12px; }
        
        .submenu-container { background-color: #f1f3f5; border-radius: 10px; margin: 5px 0 10px 0; padding: 6px 0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.03); }
        .submenu-link { display: flex; align-items: center; padding: 10px 20px 10px 40px; color: #333333; text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .submenu-link:hover { background-color: rgba(0, 0, 0, 0.05); color: #0d6efd; }
        .submenu-link.active { color: #0d6efd; font-weight: 600; background-color: rgba(13, 110, 253, 0.08); }
        .submenu-link i { font-size: 16px; margin-right: 12px; color: #555; }
        
        .menu-item-link[aria-expanded="true"] i.arrow-icon { transform: rotate(180deg); }
        .menu-item-link i.arrow-icon { transition: transform 0.2s; font-size: 12px; }
    </style>
</head>

<body class="<?= ($dark_mode === 'dark') ? 'dark-theme' : ''; ?>">

<nav id="mainNavbar" class="navbar fixed-top shadow-sm navbar-custom">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold" href="dashboard.php">
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
    <?php if (!empty($_SESSION['foto']) && file_exists("../assets/admin/" . $_SESSION['foto'])) { ?>
        <img src="../assets/admin/<?= htmlspecialchars($_SESSION['foto']); ?>"
             style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
    <?php } else { ?>
        <i class="bi bi-person-fill"></i>
<?php } ?>
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
            <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuLaporan" aria-expanded="false">
                <span><i class="bi bi-file-earmark-text menu-icon"></i> Laporan</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse" id="menuLaporan">
                <div class="submenu-container">
                    <a href="laporan.php" class="submenu-link"><i class="bi bi-file-earmark-spreadsheet"></i> Ringkasan Laporan</a>
                
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
        
        <div class="mb-1">
            <button class="menu-item-link" type="button" data-bs-toggle="collapse" data-bs-target="#menuSetting" aria-expanded="true">
                <span><i class="bi bi-gear menu-icon"></i> Setting</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse show" id="menuSetting">
                <div class="submenu-container">
                    <a href="setting.php" class="submenu-link active"><i class="bi bi-sliders"></i> Pengaturan Umum</a>
                    <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="manajemen_user.php" class="submenu-link"><i class="bi bi-people"></i> Manajemen User</a>
                    <?php endif; ?>
                    <hr class="my-1 text-muted">
                    <a href="../auth/logout.php" class="submenu-link text-danger fw-semibold" onclick="return confirm('Apakah anda yakin ingin logout?')">
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

    <form method="POST" action="">
        <div class="quick-setting">
            <h4><i class="bi bi-sliders me-2 text-primary"></i>Pengaturan Cepat</h4>

            <div class="switch-box">
                <div>
                    <h6 class="mb-1 fw-bold">Dark Mode</h6>
                    <small class="text-deskripsi">Aktifkan atau nonaktifkan tema gelap aplikasi</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="dark_mode" id="darkToggle" value="1" <?= ($dark_mode === 'dark') ? 'checked' : ''; ?>>
                </div>
            </div>

            <div class="switch-box">
                <div>
                    <h6 class="mb-1 fw-bold">Notifikasi Stok</h6>
                    <small class="text-deskripsi">Tampilkan notifikasi jika stok barang menipis</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="notifikasi_stok" value="1" <?= ($notifikasi_stok === 'aktif') ? 'checked' : ''; ?>>
                </div>
            </div>

            <div class="switch-box border-0">
                <div>
                    <h6 class="mb-1 fw-bold">Auto Backup</h6>
                    <small class="text-deskripsi">Backup otomatis database sistem setiap minggu</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="auto_backup" value="1" <?= ($auto_backup === 'aktif') ? 'checked' : ''; ?>>
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
// Interaksi realtime preview sakelar di browser sebelum disimpan
document.getElementById('darkToggle').addEventListener('change', function() {
    if(this.checked) {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        document.body.classList.add('dark-theme');
    } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
        document.body.classList.remove('dark-theme');
    }
});
</script>
</body>
</html>