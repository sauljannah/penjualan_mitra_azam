<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if(
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'admin'
){
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// BUAT TABEL SETTING JIKA BELUM ADA
// ======================================
// Perbaikan: Menghilangkan koma di ujung baris kolom notifikasi_stok
mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS setting (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dark_mode ENUM('aktif','nonaktif') NOT NULL DEFAULT 'nonaktif',
        notifikasi_stok ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
        auto_backup ENUM('aktif','nonaktif') NOT NULL DEFAULT 'nonaktif',
        terakhir_backup DATETIME NULL
    )"
);

// ======================================
// CEK DATA SETTING
// ======================================
$cek = mysqli_query(
    $conn,
    "SELECT * FROM setting LIMIT 1"
);

if(mysqli_num_rows($cek) == 0){
    mysqli_query(
        $conn,
        "INSERT INTO setting
        (
            dark_mode,
            notifikasi_stok,
            auto_backup
        )
        VALUES
        (
            'nonaktif',
            'aktif',
            'nonaktif'
        )"
    );
}

// ======================================
// AMBIL DATA SETTING
// ======================================
$querySetting = mysqli_query(
    $conn,
    "SELECT * FROM setting LIMIT 1"
);

$setting = mysqli_fetch_assoc($querySetting);

$dark_mode = $setting['dark_mode'] ?? 'nonaktif';
$notifikasi_stok = $setting['notifikasi_stok'] ?? 'aktif';
$auto_backup = $setting['auto_backup'] ?? 'nonaktif';


// ======================================
// SIMPAN PENGATURAN
// ======================================
if(isset($_POST['simpan_setting'])){
    $dark = isset($_POST['dark_mode']) ? 'aktif' : 'nonaktif';
    $notif = isset($_POST['notifikasi_stok']) ? 'aktif' : 'nonaktif';
    $backup = isset($_POST['auto_backup']) ? 'aktif' : 'nonaktif';

    // Perbaikan: Menyimpan status auto_backup juga ke database
    $update = mysqli_query(
        $conn,
        "UPDATE setting SET
            dark_mode = '$dark',
            notifikasi_stok = '$notif',
            auto_backup = '$backup'
        "
    );

    if($update){
        echo "
        <script>
            alert('Pengaturan berhasil disimpan');
            window.location='setting.php';
        </script>
        ";
        exit;
    }
}

// ======================================
// AUTO BACKUP
// ======================================
if($auto_backup == 'aktif'){
    $terakhir_backup = $setting['terakhir_backup'];
    $backupSekarang = false;

    if($terakhir_backup == NULL){
        $backupSekarang = true;
    }else{
        $selisih = time() - strtotime($terakhir_backup);
        // 7 hari = 604800 detik
        if($selisih >= 604800){
            $backupSekarang = true;
        }
    }

    if($backupSekarang){
        $folderBackup = "../backup/";
        if(!is_dir($folderBackup)){
            mkdir($folderBackup,0777,true);
        }

        $nama_file = "backup_" . date('Y-m-d_H-i-s') . ".sql";
        $path = $folderBackup . $nama_file;
        $database = "penjualan_mitra_azam";

        $command = "C:/xampp/mysql/bin/mysqldump --user=root $database > $path";
        system($command);

        mysqli_query(
            $conn,
            "UPDATE setting SET terakhir_backup = NOW()"
        );
    }
}

// ======================================
// CEK STOK MENIPIS
// ======================================
$jumlah_stok_menipis = 0;
if($notifikasi_stok == 'aktif'){
    $cekStok = mysqli_query(
        $conn,
        "SELECT * FROM barang WHERE stok <= 5"
    );
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
        }

        body{
            background: <?= $dark_mode == 'aktif' ? '#0f172a' : '#f1f5f9'; ?>;
            color: <?= $dark_mode == 'aktif' ? '#ffffff' : '#000000'; ?>;
            overflow-x: hidden;
        }

        /* CONTENT */
        .content{
            padding: 25px;
            margin-top: 75px; 
        }

        /* CARD */
        .setting-card,
        .quick-setting{
            background: <?= $dark_mode == 'aktif' ? '#1e293b' : '#ffffff'; ?>;
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: none;
        }

        .setting-card{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .setting-left{
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .setting-icon{
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .icon-toko{ background: #fff7ed; color: #f97316; }
        .icon-user{ background: #dbeafe; color: #2563eb; }
        .icon-password{ background: #fee2e2; color: #dc2626; }
        .icon-backup{ background: #dcfce7; color: #16a34a; }

        .setting-title{
            font-size: 24px;
            font-weight: 600;
        }

        .setting-desc{
            color: <?= $dark_mode == 'aktif' ? '#cbd5e1' : '#64748b'; ?>;
        }

        /* BUTTON */
        .btn-setting{
            border: none;
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 600;
            text-decoration: none;
            color: white;
            transition: 0.3s;
        }

        .btn-setting:hover{
            transform: scale(1.05);
            color: white;
        }

        .btn-toko{ background: #f97316; }
        .btn-user{ background: #2563eb; }
        .btn-password{ background: #dc2626; }
        .btn-backup{ background: #16a34a; }

        /* QUICK SETTING */
        .quick-setting h4{
            margin-bottom: 20px;
            font-weight: 700;
        }

        .switch-box{
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid <?= $dark_mode == 'aktif' ? '#334155' : '#e2e8f0'; ?>;
        }

        /* ALERT */
        .alert-custom{
            background: #dcfce7;
            color: #166534;
            padding: 18px;
            border-radius: 18px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        /* RESPONSIVE */
        @media(max-width: 768px){
            .setting-card{
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            .btn-setting {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<nav class="navbar fixed-top shadow-sm <?= $dark_mode == 'aktif' ? 'navbar-dark bg-dark' : 'bg-body-tertiary'; ?>">
  <div class="container-fluid">
    
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
    
    <div class="offcanvas offcanvas-start <?= $dark_mode == 'aktif' ? 'text-bg-dark' : ''; ?>" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasNavbarLabel">
          <i class="bi bi-shop"></i> MITRA AZAM
        </h5>
        <button type="button" class="btn-close <?= $dark_mode == 'aktif' ? 'btn-close-white' : ''; ?>" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
            <ul class="dropdown-menu <?= $dark_mode == 'aktif' ? 'dropdown-menu-dark' : ''; ?>">
              <li><a class="dropdown-item" href="barang.php">Semua Barang</a></li>
              <li><a class="dropdown-item" href="tambah_barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Tambah Stok Masuk</a></li>
              <li><a class="dropdown-item" href="tambah_barang.php">Tambah Barang</a></li>
              <li><a class="dropdown-item" href="barang_masuk.php">Barang Masuk</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-file-earmark-text me-2 text-primary"></i> Laporan
            </a>
            <ul class="dropdown-menu <?= $dark_mode == 'aktif' ? 'dropdown-menu-dark' : ''; ?>">
              <li><a class="dropdown-item active" href="laporan.php"><i class="bi bi-file-earmark-ruled me-2"></i> Ringkasan Laporan</a></li>
              <li><a class="dropdown-item" href="laba_rugi.php"><i class="bi bi-cash-stack me-2"></i> Laba Rugi</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle active fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-gear-fill me-2 text-primary"></i> Setting
            </a>
            <ul class="dropdown-menu <?= $dark_mode == 'aktif' ? 'dropdown-menu-dark' : ''; ?>">
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

    <?php if($notifikasi_stok == 'aktif' && $jumlah_stok_menipis > 0): ?>
        <div class="alert-custom">
            <i class="bi bi-bell-fill me-2"></i> Ada <?= $jumlah_stok_menipis; ?> barang dengan stok menipis.
        </div>
    <?php endif; ?>

    <div class="quick-setting d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-gear-fill text-warning me-2"></i>Pengaturan Sistem
            </h2>
            <p class="text-muted mb-0">Kelola pengaturan aplikasi toko bangunan</p>
        </div>
        <div class="fw-bold">
            <i class="bi bi-person-circle text-primary me-1"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?>
        </div>
    </div>

    <div class="setting-card">
        <div class="setting-left">
            <div class="setting-icon icon-toko">
                <i class="bi bi-shop"></i>
            </div>
            <div>
                <div class="setting-title">Profil Toko</div>
                <div class="setting-desc">Kelola data toko</div>
            </div>
        </div>
        <a href="edit_toko.php" class="btn-setting btn-toko">Edit Toko</a>
    </div>

    <div class="setting-card">
        <div class="setting-left">
            <div class="setting-icon icon-user">
                <i class="bi bi-person-circle"></i>
            </div>
            <div>
                <div class="setting-title">Akun Admin</div>
                <div class="setting-desc">Kelola akun administrator</div>
            </div>
        </div>
        <a href="edit_admin.php" class="btn-setting btn-user">Edit Admin</a>
    </div>

    <div class="setting-card">
        <div class="setting-left">
            <div class="setting-icon icon-password">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <div class="setting-title">Password</div>
                <div class="setting-desc">Ganti password akun</div>
            </div>
        </div>
        <a href="ganti_password.php" class="btn-setting btn-password">Ganti Password</a>
    </div>

    <div class="setting-card">
        <div class="setting-left">
            <div class="setting-icon icon-backup">
                <i class="bi bi-database-fill"></i>
            </div>
            <div>
                <div class="setting-title">Backup Database</div>
                <div class="setting-desc">Backup manual database</div>
            </div>
        </div>
        <a href="backup.php" class="btn-setting btn-backup">Backup Sekarang</a>
    </div>

    <form method="POST">
        <div class="quick-setting">
            <h4><i class="bi bi-sliders me-2 text-primary"></i>Pengaturan Cepat</h4>

            <div class="switch-box">
                <div>
                    <h6 class="mb-1 fw-bold">Dark Mode</h6>
                    <small class="text-muted">Aktifkan tema gelap aplikasi</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="dark_mode" <?= $dark_mode == 'aktif' ? 'checked' : ''; ?>>
                </div>
            </div>

            <div class="switch-box">
                <div>
                    <h6 class="mb-1 fw-bold">Notifikasi Stok</h6>
                    <small class="text-muted">Tampilkan notifikasi jika stok barang menipis</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="notifikasi_stok" <?= $notifikasi_stok == 'aktif' ? 'checked' : ''; ?>>
                </div>
            </div>

            <div class="switch-box border-0">
                <div>
                    <h6 class="mb-1 fw-bold">Auto Backup</h6>
                    <small class="text-muted">Backup otomatis database sistem setiap minggu</small>
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
</body>
</html>