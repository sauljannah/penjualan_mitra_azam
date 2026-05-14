<?php

session_start();
require_once 'config/koneksi.php';

/*
|--------------------------------------------------------------------------
| REDIRECT LOGIN
|--------------------------------------------------------------------------
*/

if(isset($_SESSION['level'])){

    if($_SESSION['level'] == 'admin'){
        header("Location: admin/dashboard.php");
        exit;
    }

    if($_SESSION['level'] == 'kasir'){
        header("Location: kasir/dashboard.php");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| PROFIL TOKO
|--------------------------------------------------------------------------
*/

$nama_toko   = "TOKO MITRA AZAM";
$jenis_usaha = "Toko Retail";
$alamat      = "-";
$telepon     = "-";
$email       = "-";
$deskripsi   = "Sistem Informasi Penjualan";
$logo        = "logo.png";

$qProfil = mysqli_query(
    $conn,
    "SELECT * FROM profil_toko LIMIT 1"
);

if($qProfil && mysqli_num_rows($qProfil) > 0){

    $profil = mysqli_fetch_assoc($qProfil);

    $nama_toko   = $profil['nama_toko'];
    $jenis_usaha = $profil['jenis_usaha'];
    $alamat      = $profil['alamat'];
    $telepon     = $profil['telepon'];
    $email       = $profil['email'];
    $deskripsi   = $profil['deskripsi'];
    $logo        = $profil['logo'];
}

/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$total_barang = 0;
$total_customer = 0;
$total_transaksi = 0;
$total_piutang = 0;

$q = mysqli_query($conn,"SELECT COUNT(*) total FROM barang");
if($q){
    $total_barang = mysqli_fetch_assoc($q)['total'];
}

$q = mysqli_query($conn,"SELECT COUNT(*) total FROM customer");
if($q){
    $total_customer = mysqli_fetch_assoc($q)['total'];
}

$q = mysqli_query($conn,"SELECT COUNT(*) total FROM penjualan");
if($q){
    $total_transaksi = mysqli_fetch_assoc($q)['total'];
}

$q = mysqli_query($conn,"SELECT COUNT(*) total FROM piutang");
if($q){
    $total_piutang = mysqli_fetch_assoc($q)['total'];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= $nama_toko; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet">

<style>

*{
    font-family:'Poppins',sans-serif;
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html{
    scroll-behavior:smooth;
}

body{
    background:#f8fafc;
    overflow-x:hidden;
}

/* ==========================
NAVBAR
========================== */

.navbar{
    background:rgba(255,255,255,.95);
    backdrop-filter:blur(12px);
    box-shadow:0 5px 25px rgba(0,0,0,.08);
}

.navbar-brand{
    font-weight:800;
    font-size:22px;
}

/* ==========================
HERO
========================== */

.hero{

    min-height:100vh;

    display:flex;
    align-items:center;

    background:
    linear-gradient(
    135deg,
    rgba(37,99,235,.95),
    rgba(30,64,175,.95)
    ),
    url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45f?w=1800');

    background-size:cover;
    background-position:center;

    color:white;

    position:relative;
}

.hero::after{

    content:'';

    position:absolute;

    bottom:0;
    left:0;

    width:100%;
    height:120px;

    background:
    linear-gradient(
    to top,
    #f8fafc,
    transparent
    );
}

.hero h1{

    font-size:70px;
    font-weight:800;
    line-height:1.1;
}

.hero p{

    font-size:20px;
    opacity:.95;
}

.logo-box{

    width:280px;
    height:280px;

    background:
    rgba(255,255,255,.15);

    backdrop-filter:blur(20px);

    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    margin:auto;

    border:
    2px solid rgba(255,255,255,.25);

    animation:
    float 4s ease-in-out infinite;
}

.logo-box img{

    width:200px;
}

@keyframes float{

    0%{
        transform:translateY(0);
    }

    50%{
        transform:translateY(-15px);
    }

    100%{
        transform:translateY(0);
    }
}

/* ==========================
BUTTON
========================== */

.btn-premium{

    display:inline-block;

    padding:16px 35px;

    background:white;

    color:#2563eb;

    border-radius:50px;

    text-decoration:none;

    font-weight:700;

    transition:.3s;
}

.btn-premium:hover{

    transform:translateY(-4px);

    background:#eff6ff;

    color:#1e3a8a;

    box-shadow:
    0 15px 30px rgba(0,0,0,.2);
}

/* ==========================
SECTION TITLE
========================== */

.section-title{

    font-size:42px;
    font-weight:800;
    color:#1e3a8a;
}

/* ==========================
FEATURE CARD
========================== */

.card-feature{

    border:none;

    border-radius:30px;

    background:white;

    padding:40px;

    height:100%;

    transition:.4s;

    box-shadow:
    0 10px 30px rgba(0,0,0,.06);
}

.card-feature:hover{

    transform:
    translateY(-12px);

    box-shadow:
    0 25px 50px rgba(37,99,235,.15);
}

.card-feature i{

    font-size:60px;

    background:
    linear-gradient(
    135deg,
    #2563eb,
    #1e40af
    );

    -webkit-background-clip:text;

    -webkit-text-fill-color:transparent;
}

/* ==========================
STATISTIK
========================== */

.stat-box{

    background:white;

    border-radius:30px;

    padding:35px;

    text-align:center;

    transition:.3s;

    box-shadow:
    0 10px 25px rgba(0,0,0,.05);
}

.stat-box:hover{

    transform:translateY(-8px);
}

.stat-box h2{

    font-size:55px;

    font-weight:800;

    color:#2563eb;
}

/* ==========================
ABOUT
========================== */

.about-box{

    background:white;

    border-radius:35px;

    padding:50px;

    box-shadow:
    0 15px 35px rgba(0,0,0,.06);
}

/* ==========================
CTA
========================== */

.cta{

    background:
    linear-gradient(
    135deg,
    #2563eb,
    #1e40af
    );

    color:white;

    padding:90px 0;

    border-radius:40px;
}

.cta h2{

    font-size:45px;

    font-weight:800;
}

/* ==========================
FOOTER
========================== */

.footer{

    background:#0f172a;

    color:white;

    margin-top:80px;

    padding:70px 0;
}

.footer img{

    filter:
    drop-shadow(
    0 0 15px rgba(255,255,255,.3)
    );
}

.footer h4{

    font-weight:700;
}

.footer p{

    opacity:.8;
}

/* ==========================
RESPONSIVE
========================== */

@media(max-width:768px){

    .hero{
        text-align:center;
    }

    .hero h1{
        font-size:42px;
    }

    .logo-box{

        width:220px;
        height:220px;

        margin-top:40px;
    }

    .logo-box img{
        width:150px;
    }

    .section-title{
        font-size:32px;
    }
}


/* ===== PREMIUM UI 2026 ===== */
body{
background:#0f172a;
background-image:
radial-gradient(circle at top left, rgba(37,99,235,.25), transparent 30%),
radial-gradient(circle at bottom right, rgba(124,58,237,.25), transparent 30%);
}

.navbar{
backdrop-filter:blur(20px)!important;
border-bottom:1px solid rgba(255,255,255,.08);
}

.hero{
background:linear-gradient(135deg,#0f172a,#1e293b,#2563eb)!important;
}

.section-title{
background:linear-gradient(135deg,#2563eb,#7c3aed);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

.card-feature,
.stat-box,
.about-box{
backdrop-filter:blur(15px);
border:1px solid rgba(255,255,255,.08);
}

.card-feature:hover{
transform:translateY(-15px) scale(1.03)!important;
}

.btn-premium{
background:linear-gradient(135deg,#2563eb,#7c3aed)!important;
color:#fff!important;
box-shadow:0 15px 35px rgba(37,99,235,.35);
}

.btn-premium:hover{
background:linear-gradient(135deg,#1d4ed8,#6d28d9)!important;
}

.footer{
background:linear-gradient(135deg,#020617,#0f172a)!important;
}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg sticky-top">

<div class="container">

<a class="navbar-brand" href="#">

<img
src="assets/logo/<?= $logo; ?>"
width="40">

<?= $nama_toko; ?>

</a>

<a href="auth/login.php"
class="btn btn-primary">

<i class="bi bi-box-arrow-in-right"></i>

Login

</a>

</div>

</nav>

<!-- HERO -->

<section class="hero">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-7">

<span class="badge bg-light text-primary px-3 py-2 mb-3">

<?= $jenis_usaha; ?>

</span>

<h1>

<?= $nama_toko; ?>

</h1>

<p class="my-4">

<?= $deskripsi; ?>

</p>

<a href="auth/login.php"
class="btn-premium">

<i class="bi bi-box-arrow-in-right"></i>

Masuk Sistem

</a>

</div>

<div class="col-lg-5 text-center">

<div class="logo-box">

<img
src="assets/logo/<?= $logo; ?>">

</div>

</div>

</div>

</div>

</section>

<!-- FITUR -->

<section class="py-5">

<div class="container">

<h2 class="section-title text-center mb-5">

Fitur Unggulan

</h2>

<div class="row g-4">

<div class="col-md-4">

<div class="card-feature text-center">

<i class="bi bi-cart-check-fill"></i>

<h4 class="mt-3">
Transaksi Cepat
</h4>

<p>
Tunai, Transfer,
QRIS dan Hutang.
</p>

</div>

</div>

<div class="col-md-4">

<div class="card-feature text-center">

<i class="bi bi-box-seam-fill"></i>

<h4 class="mt-3">
Manajemen Stok
</h4>

<p>
Stok masuk dan keluar
otomatis tercatat.
</p>

</div>

</div>

<div class="col-md-4">

<div class="card-feature text-center">

<i class="bi bi-graph-up-arrow"></i>

<h4 class="mt-3">
Laporan Lengkap
</h4>

<p>
Penjualan, stok dan piutang.
</p>

</div>

</div>

</div>

</div>

</section>

<!-- STATISTIK -->

<section class="py-5 bg-light">

<div class="container">

<div class="row g-4">

<div class="col-md-3">

<div class="stat-box">

<h2><?= $total_barang; ?></h2>

Barang

</div>

</div>

<div class="col-md-3">

<div class="stat-box">

<h2><?= $total_customer; ?></h2>

Customer

</div>

</div>

<div class="col-md-3">

<div class="stat-box">

<h2><?= $total_transaksi; ?></h2>

Transaksi

</div>

</div>

<div class="col-md-3">

<div class="stat-box">

<h2><?= $total_piutang; ?></h2>

Piutang

</div>

</div>

</div>

</div>

</section>

<!-- ABOUT -->

<section class="py-5">

<div class="container">

<div class="about-box">

<div class="row">

<div class="col-lg-6">

<h2 class="fw-bold text-primary">

Tentang Toko

</h2>

<p>

<?= $alamat; ?>

</p>

<p>

<i class="bi bi-telephone-fill"></i>

<?= $telepon; ?>

</p>

<p>

<i class="bi bi-envelope-fill"></i>

<?= $email; ?>

</p>

</div>

<div class="col-lg-6">

<h5 class="fw-bold">

Role Sistem

</h5>

<ul>

<li>Admin</li>
<li>Kasir</li>

</ul>

<h5 class="fw-bold mt-4">

Metode Pembayaran

</h5>

<ul>

<li>Tunai</li>
<li>Transfer</li>
<li>QRIS</li>
<li>Hutang</li>

</ul>

</div>

</div>

</div>

</div>

</section>

<section class="container my-5">

<div class="cta text-center">

<h2>
Kelola Bisnis Lebih Mudah
</h2>

<p class="mt-3">
Pantau stok, transaksi, pelanggan dan laporan
dalam satu sistem terintegrasi.
</p>

<a href="auth/login.php"
class="btn btn-light btn-lg mt-3">

<i class="bi bi-box-arrow-in-right"></i>
 Login Sekarang

</a>

</div>

</section>

<!-- FOOTER -->

<div class="footer">

<div class="container text-center">

<img
src="assets/logo/<?= $logo; ?>"
width="80"
class="mb-3">

<h4><?= $nama_toko; ?></h4>

<p>

<?= $alamat; ?>

</p>

<p>

© <?= date('Y'); ?> <?= $nama_toko; ?>

</p>

</div>

</div>

</body>
</html>