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
            auto_backup,
            terakhir_backup
        )

        VALUES
        (
            'nonaktif',
            'aktif',
            'nonaktif',
            NULL
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

$dark_mode =
    $setting['dark_mode']
    ?? 'nonaktif';

$notifikasi_stok =
    $setting['notifikasi_stok']
    ?? 'aktif';

$auto_backup =
    $setting['auto_backup']
    ?? 'nonaktif';

// ======================================
// SIMPAN PENGATURAN
// ======================================
if(isset($_POST['simpan_setting'])){

    $dark =
        isset($_POST['dark_mode'])
        ? 'aktif'
        : 'nonaktif';

    $notif =
        isset($_POST['notifikasi_stok'])
        ? 'aktif'
        : 'nonaktif';

    $backup =
        isset($_POST['auto_backup'])
        ? 'aktif'
        : 'nonaktif';

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

    $terakhir_backup =
        $setting['terakhir_backup'];

    $backupSekarang = false;

    if($terakhir_backup == NULL){

        $backupSekarang = true;

    }else{

        $selisih =
            time() -
            strtotime($terakhir_backup);

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

        $nama_file =
            "backup_" .
            date('Y-m-d_H-i-s') .
            ".sql";

        $path =
            $folderBackup .
            $nama_file;

        $database = "penjualan_mitra_azam";

        $command =
            "C:/xampp/mysql/bin/mysqldump --user=root $database > $path";

        system($command);

        mysqli_query(
            $conn,
            "UPDATE setting
             SET terakhir_backup = NOW()"
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
        "SELECT * FROM barang
         WHERE stok <= 5"
    );

    $jumlah_stok_menipis =
        mysqli_num_rows($cekStok);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Setting Sistem</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link
href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{

    background:
    <?= $dark_mode == 'aktif'
        ? '#0f172a'
        : '#f1f5f9'; ?>;

    color:
    <?= $dark_mode == 'aktif'
        ? '#ffffff'
        : '#000000'; ?>;

    overflow-x:hidden;
}

/* SIDEBAR */
.sidebar{

    width:270px;
    height:100vh;

    position:fixed;
    top:0;
    left:0;

    background:linear-gradient(
        180deg,
        #ff7b00,
        #d65a00
    );

    padding:25px;
    color:white;
}

.logo{

    text-align:center;
    margin-bottom:40px;
}

.logo i{

    font-size:45px;
}

.logo h2{

    margin-top:10px;
    font-weight:700;
}

.sidebar-menu a{

    display:flex;
    align-items:center;
    gap:12px;

    color:white;
    text-decoration:none;

    padding:14px 18px;

    margin-bottom:12px;

    border-radius:16px;

    transition:0.3s;
}

.sidebar-menu a:hover{

    background:rgba(255,255,255,0.15);

    transform:translateX(5px);
}

.sidebar-menu a.active{

    background:white;
    color:#ff7b00;

    font-weight:600;
}

/* CONTENT */
.content{

    margin-left:270px;
    padding:30px;
}

/* TOPBAR */
.topbar{

    background:
    <?= $dark_mode == 'aktif'
        ? '#1e293b'
        : '#ffffff'; ?>;

    padding:25px;

    border-radius:24px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-bottom:30px;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.05);
}

/* CARD */
.setting-card,
.quick-setting{

    background:
    <?= $dark_mode == 'aktif'
        ? '#1e293b'
        : '#ffffff'; ?>;

    border-radius:24px;

    padding:25px;

    margin-bottom:25px;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.05);
}

.setting-card{

    display:flex;
    justify-content:space-between;
    align-items:center;
}

.setting-left{

    display:flex;
    align-items:center;
    gap:20px;
}

.setting-icon{

    width:70px;
    height:70px;

    border-radius:20px;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:30px;
}

.icon-toko{
    background:#fff7ed;
    color:#f97316;
}

.icon-user{
    background:#dbeafe;
    color:#2563eb;
}

.icon-password{
    background:#fee2e2;
    color:#dc2626;
}

.icon-backup{
    background:#dcfce7;
    color:#16a34a;
}

.setting-title{

    font-size:24px;
    font-weight:600;
}

.setting-desc{

    color:
    <?= $dark_mode == 'aktif'
        ? '#cbd5e1'
        : '#64748b'; ?>;
}

/* BUTTON */
.btn-setting{

    border:none;

    padding:12px 24px;

    border-radius:14px;

    font-weight:600;

    text-decoration:none;

    color:white;

    transition:0.3s;
}

.btn-setting:hover{

    transform:scale(1.05);
    color:white;
}

.btn-toko{
    background:#f97316;
}

.btn-user{
    background:#2563eb;
}

.btn-password{
    background:#dc2626;
}

.btn-backup{
    background:#16a34a;
}

/* QUICK */
.quick-setting h4{

    margin-bottom:20px;
    font-weight:700;
}

.switch-box{

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:15px 0;

    border-bottom:1px solid #ddd;
}

/* ALERT */
.alert-custom{

    background:#dcfce7;
    color:#166534;

    padding:18px;

    border-radius:18px;

    margin-bottom:25px;

    font-weight:600;
}

/* RESPONSIVE */
@media(max-width:768px){

    .sidebar{

        position:relative;
        width:100%;
        height:auto;
    }

    .content{

        margin-left:0;
    }

    .setting-card{

        flex-direction:column;
        align-items:flex-start;
        gap:20px;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo">

        <i class="bi bi-shop-window"></i>

        <h2>MITRA AZAM</h2>

    </div>

    <div class="sidebar-menu">

        <a href="dashboard.php">

            <i class="bi bi-speedometer2"></i>
            Dashboard

        </a>

        <a href="barang.php">

            <i class="bi bi-box-seam"></i>
            Data Barang

        </a>

        <a href="tambah_barang.php">

            <i class="bi bi-plus-circle"></i>
            Tambah Barang

        </a>

        <a href="laporan.php">

            <i class="bi bi-file-earmark-text"></i>
            Laporan

        </a>

        <a href="laba_rugi.php">

            <i class="bi bi-cash-stack"></i>
            Laba Rugi

        </a>

        <a href="manajemen_user.php">

            <i class="bi bi-people"></i>
            Manajemen User

        </a>

        <a href="setting.php"
           class="active">

            <i class="bi bi-gear-fill"></i>
            Setting

        </a>

        <a href="../auth/logout.php">

            <i class="bi bi-box-arrow-right"></i>
            Logout

        </a>

    </div>

</div>

<!-- CONTENT -->
<div class="content">

    <!-- NOTIFIKASI STOK -->
    <?php if(
        $notifikasi_stok == 'aktif' &&
        $jumlah_stok_menipis > 0
    ): ?>

        <div class="alert-custom">

            <i class="bi bi-bell-fill"></i>

            Ada
            <?= $jumlah_stok_menipis; ?>
            barang dengan stok menipis.

        </div>

    <?php endif; ?>

    <!-- TOPBAR -->
    <div class="topbar">

        <div>

            <h2>

                <i class="bi bi-gear-fill text-warning"></i>
                Pengaturan Sistem

            </h2>

            <p class="text-muted mb-0">

                Kelola pengaturan aplikasi toko bangunan

            </p>

        </div>

        <div>

            <strong>

                <?= htmlspecialchars($_SESSION['nama']); ?>

            </strong>

        </div>

    </div>

    <!-- PROFIL TOKO -->
    <div class="setting-card">

        <div class="setting-left">

            <div class="setting-icon icon-toko">

                <i class="bi bi-shop"></i>

            </div>

            <div>

                <div class="setting-title">

                    Profil Toko

                </div>

                <div class="setting-desc">

                    Kelola data toko

                </div>

            </div>

        </div>

        <a href="edit_toko.php"
           class="btn-setting btn-toko">

            Edit Toko

        </a>

    </div>

    <!-- AKUN ADMIN -->
    <div class="setting-card">

        <div class="setting-left">

            <div class="setting-icon icon-user">

                <i class="bi bi-person-circle"></i>

            </div>

            <div>

                <div class="setting-title">

                    Akun Admin

                </div>

                <div class="setting-desc">

                    Kelola akun administrator

                </div>

            </div>

        </div>

        <a href="edit_admin.php"
           class="btn-setting btn-user">

            Edit Admin

        </a>

    </div>

    <!-- PASSWORD -->
    <div class="setting-card">

        <div class="setting-left">

            <div class="setting-icon icon-password">

                <i class="bi bi-shield-lock-fill"></i>

            </div>

            <div>

                <div class="setting-title">

                    Password

                </div>

                <div class="setting-desc">

                    Ganti password akun

                </div>

            </div>

        </div>

        <a href="ganti_password.php"
           class="btn-setting btn-password">

            Ganti Password

        </a>

    </div>

    <!-- BACKUP -->
    <div class="setting-card">

        <div class="setting-left">

            <div class="setting-icon icon-backup">

                <i class="bi bi-database-fill"></i>

            </div>

            <div>

                <div class="setting-title">

                    Backup Database

                </div>

                <div class="setting-desc">

                    Backup manual database

                </div>

            </div>

        </div>

        <a href="backup.php"
           class="btn-setting btn-backup">

            Backup Sekarang

        </a>

    </div>

    <!-- QUICK SETTING -->
    <form method="POST">

        <div class="quick-setting">

            <h4>

                <i class="bi bi-sliders"></i>
                Pengaturan Cepat

            </h4>

            <!-- DARK MODE -->
            <div class="switch-box">

                <div>

                    <h6>Dark Mode</h6>

                    <small>

                        Aktifkan tema gelap

                    </small>

                </div>

                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="dark_mode"

                        <?= $dark_mode == 'aktif'
                            ? 'checked'
                            : ''; ?>>

                </div>

            </div>

            <!-- NOTIFIKASI -->
            <div class="switch-box">

                <div>

                    <h6>Notifikasi Stok</h6>

                    <small>

                        Tampilkan notifikasi stok menipis

                    </small>

                </div>

                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="notifikasi_stok"

                        <?= $notifikasi_stok == 'aktif'
                            ? 'checked'
                            : ''; ?>>

                </div>

            </div>

            <!-- AUTO BACKUP -->
            <div class="switch-box border-0">

                <div>

                    <h6>Auto Backup</h6>

                    <small>

                        Backup otomatis setiap minggu

                    </small>

                </div>

                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="auto_backup"

                        <?= $auto_backup == 'aktif'
                            ? 'checked'
                            : ''; ?>>

                </div>

            </div>

            <button
                type="submit"
                name="simpan_setting"
                class="btn-setting btn-toko mt-4">

                <i class="bi bi-save-fill"></i>
                Simpan Pengaturan

            </button>

        </div>

    </form>

</div>

</body>
</html>