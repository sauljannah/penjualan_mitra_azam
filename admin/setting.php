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

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Setting Sistem</title>

<!-- Bootstrap -->
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- Bootstrap Icons -->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Google Font -->
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
    background:#f1f5f9;
    overflow-x:hidden;
}

/* =====================================
SIDEBAR
===================================== */
.sidebar{

    width:270px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;

    background:linear-gradient(
        180deg,
        #ff7b00,
        #d65a00
    );

    padding:25px;
    color:white;

    overflow-y:auto;
}

.logo{

    text-align:center;
    margin-bottom:40px;
}

.logo h2{

    font-weight:700;
    margin-top:10px;
}

.logo i{

    font-size:45px;
}

.sidebar-menu{

    margin-top:20px;
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

.sidebar-footer{

    margin-top:50px;

    text-align:center;

    font-size:13px;

    opacity:0.8;
}

/* =====================================
CONTENT
===================================== */
.content{

    margin-left:270px;
    padding:30px;
}

/* =====================================
TOPBAR
===================================== */
.topbar{

    background:white;

    padding:25px;

    border-radius:24px;

    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.05);

    margin-bottom:30px;
}

.topbar h2{

    font-weight:700;
    color:#0f172a;
}

.user-box{

    display:flex;
    align-items:center;
    gap:15px;
}

.user-icon{

    width:50px;
    height:50px;

    border-radius:50%;

    background:#ffedd5;

    display:flex;
    align-items:center;
    justify-content:center;

    color:#ff7b00;

    font-size:24px;
}

/* =====================================
SETTING CARD
===================================== */
.setting-card{

    background:white;

    border-radius:24px;

    padding:25px;

    margin-bottom:25px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    transition:0.3s;

    box-shadow:
    0 8px 20px rgba(0,0,0,0.05);
}

.setting-card:hover{

    transform:translateY(-4px);

    box-shadow:
    0 15px 30px rgba(0,0,0,0.08);
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

/* ICON */
.icon-toko{
    background:#fff7ed;
    color:#f97316;
}

.icon-password{
    background:#fee2e2;
    color:#dc2626;
}

.icon-theme{
    background:#ede9fe;
    color:#7c3aed;
}

.icon-backup{
    background:#dcfce7;
    color:#16a34a;
}

.icon-user{
    background:#dbeafe;
    color:#2563eb;
}

/* TITLE */
.setting-title{

    font-size:24px;
    font-weight:600;

    color:#0f172a;
}

.setting-desc{

    color:#64748b;
    margin-top:5px;
}

/* =====================================
BUTTON
===================================== */
.btn-setting{

    border:none;

    padding:12px 24px;

    border-radius:14px;

    font-weight:600;

    transition:0.3s;

    text-decoration:none;
    display:inline-block;
}

.btn-setting:hover{

    transform:scale(1.05);
    color:white;
}

.btn-toko{
    background:#f97316;
    color:white;
}

.btn-password{
    background:#dc2626;
    color:white;
}

.btn-theme{
    background:#7c3aed;
    color:white;
}

.btn-backup{
    background:#16a34a;
    color:white;
}

.btn-user{
    background:#2563eb;
    color:white;
}

/* =====================================
QUICK SETTING
===================================== */
.quick-setting{

    background:white;

    border-radius:24px;

    padding:25px;

    box-shadow:
    0 8px 20px rgba(0,0,0,0.05);

    margin-top:30px;
}

.quick-setting h4{

    font-weight:700;
    margin-bottom:20px;
}

.switch-box{

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:15px 0;

    border-bottom:1px solid #eee;
}

/* =====================================
RESPONSIVE
===================================== */
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

    .topbar{

        gap:20px;
    }
}

</style>

</head>

<body>

<!-- =====================================
SIDEBAR
===================================== -->
<div class="sidebar">

    <!-- LOGO -->
    <div class="logo">

        <i class="bi bi-shop-window"></i>

        <h2>MITRA AZAM</h2>

    </div>

    <!-- MENU -->
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

    <!-- FOOTER -->
    <div class="sidebar-footer">

        Sistem Kasir Modern v1.0

    </div>

</div>

<!-- =====================================
CONTENT
===================================== -->
<div class="content">

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

        <div class="user-box">

            <div class="user-icon">

                <i class="bi bi-person-fill"></i>

            </div>

            <div>

                <h6 class="mb-0 fw-bold">

                    <?= htmlspecialchars($_SESSION['nama']); ?>

                </h6>

                <small class="text-muted">

                    Administrator

                </small>

            </div>

        </div>

    </div>

    <!-- =====================================
    PROFIL TOKO
    ===================================== -->
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

                    Kelola nama toko, alamat, nomor telepon dan logo toko

                </div>

            </div>

        </div>

        <!-- PERBAIKAN -->
        <a href="edit_toko.php"
           class="btn-setting btn-toko">

            <i class="bi bi-pencil-square"></i>
            Edit Toko

        </a>

    </div>

    <!-- =====================================
    MANAJEMEN ADMIN
    ===================================== -->
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

                    Kelola informasi akun administrator sistem

                </div>

            </div>

        </div>

        <!-- PERBAIKAN -->
        <a href="edit_admin.php"
           class="btn-setting btn-user">

            <i class="bi bi-pencil-fill"></i>
            Edit Admin

        </a>

    </div>

    <!-- =====================================
    PASSWORD
    ===================================== -->
    <div class="setting-card">

        <div class="setting-left">

            <div class="setting-icon icon-password">

                <i class="bi bi-shield-lock-fill"></i>

            </div>

            <div>

                <div class="setting-title">

                    Password & Keamanan

                </div>

                <div class="setting-desc">

                    Ubah password dan tingkatkan keamanan akun

                </div>

            </div>

        </div>

        <!-- PERBAIKAN -->
        <a href="ganti_password.php"
           class="btn-setting btn-password">

            <i class="bi bi-key-fill"></i>
            Ganti Password

        </a>

    </div>

    <!-- =====================================
    TEMA
    ===================================== -->
    <div class="setting-card">

        <div class="setting-left">

            <div class="setting-icon icon-theme">

                <i class="bi bi-palette-fill"></i>

            </div>

            <div>

                <div class="setting-title">

                    Tema Dashboard

                </div>

                <div class="setting-desc">

<<<<<<< HEAD
                    Atur tampilan sistem modern
=======
<<<<<<< HEAD
                    Atur tampilan sistem kasir modern
=======
                    Aktifkan dark mode dashboard
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
>>>>>>> 70397315fb8477f211f3c2c005604bd718b9a348

                </div>

            </div>

        </div>

<<<<<<< HEAD
        <a href="tema.php"
           class="btn-setting btn-theme">
=======
<<<<<<< HEAD
        <a href="#"
           class="btn-setting btn-theme">

            <i class="bi bi-brush-fill"></i>
            Atur Tema

        </a>
=======
        <button
            class="btn-setting btn-theme"
            onclick="toggleDarkMode()">
>>>>>>> 70397315fb8477f211f3c2c005604bd718b9a348

            <i class="bi bi-brush-fill"></i>
            Atur Tema

<<<<<<< HEAD
        </a>
=======
        </button>
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
>>>>>>> 70397315fb8477f211f3c2c005604bd718b9a348

    </div>

    <!-- =====================================
    BACKUP
    ===================================== -->
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

<<<<<<< HEAD
                    Simpan dan backup data sistem toko
=======
<<<<<<< HEAD
                    Simpan dan backup data sistem toko
=======
                    Download backup database sistem
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
>>>>>>> 70397315fb8477f211f3c2c005604bd718b9a348

                </div>

            </div>

        </div>

<<<<<<< HEAD
        <a href="#"
=======
        <a href="backup.php"
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
           class="btn-setting btn-backup">

            <i class="bi bi-cloud-arrow-down-fill"></i>
            Backup Sekarang

        </a>

    </div>

    <!-- =====================================
    QUICK SETTING
    ===================================== -->
    <div class="quick-setting">

        <h4>

            <i class="bi bi-sliders"></i>
            Pengaturan Cepat

        </h4>

        <!-- DARK MODE -->
        <div class="switch-box">

            <div>

                <h6 class="mb-1">

                    Dark Mode

                </h6>

                <small class="text-muted">

                    Aktifkan mode gelap dashboard

                </small>

            </div>

            <div class="form-check form-switch">

                <input
                    class="form-check-input"
                    type="checkbox">

            </div>

        </div>

        <!-- NOTIFIKASI -->
        <div class="switch-box">

            <div>

                <h6 class="mb-1">

                    Notifikasi Stok

                </h6>

                <small class="text-muted">

                    Tampilkan notifikasi stok menipis

                </small>

            </div>

            <div class="form-check form-switch">

                <input
                    class="form-check-input"
                    type="checkbox"
                    checked>

            </div>

        </div>

        <!-- AUTO BACKUP -->
        <div class="switch-box border-0">

            <div>

                <h6 class="mb-1">

                    Auto Backup

                </h6>

                <small class="text-muted">

                    Backup otomatis setiap minggu

                </small>

            </div>

            <div class="form-check form-switch">

                <input
                    class="form-check-input"
                    type="checkbox">

            </div>

        </div>

    </div>

</div>

</body>
</html>