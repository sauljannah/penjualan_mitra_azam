<?php

session_start();

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK KONEKSI DATABASE
// ======================================
if (!$conn) {

    die(
        "Koneksi database gagal : " .
        mysqli_connect_error()
    );
}

// ======================================
// CEK LOGIN
// ======================================
if (!isset($_SESSION['level'])) {

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// CEK LEVEL KASIR
// ======================================
if ($_SESSION['level'] != "kasir") {

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// TOTAL BARANG
// ======================================
$query_barang = mysqli_query(

    $conn,

    "SELECT COUNT(*) AS total
     FROM barang"

);

$data_barang = mysqli_fetch_assoc(
    $query_barang
);

$total_barang =
$data_barang['total'] ?? 0;

// ======================================
// TOTAL TRANSAKSI
// ======================================
$query_penjualan = mysqli_query(

    $conn,

    "SELECT COUNT(*) AS total
     FROM penjualan"

);

$data_penjualan = mysqli_fetch_assoc(
    $query_penjualan
);

$total_penjualan =
$data_penjualan['total'] ?? 0;

// ======================================
// TOTAL PENDAPATAN
// ======================================
$query_pendapatan = mysqli_query(

    $conn,

    "SELECT SUM(total_harga) AS total
     FROM penjualan"

);

$data_pendapatan = mysqli_fetch_assoc(
    $query_pendapatan
);

$total_pendapatan =
$data_pendapatan['total'] ?? 0;

// ======================================
// PENDAPATAN HARI INI
// ======================================
$query_hari_ini = mysqli_query(

    $conn,

    "SELECT SUM(total_harga) AS total
     FROM penjualan
     WHERE DATE(tanggal)=CURDATE()"

);

$data_hari_ini = mysqli_fetch_assoc(
    $query_hari_ini
);

$pendapatan_hari_ini =
$data_hari_ini['total'] ?? 0;

// ======================================
// TRANSAKSI TERBARU
// ======================================
$query_transaksi = mysqli_query(

    $conn,

    "SELECT *
     FROM penjualan
     ORDER BY id_penjualan DESC
     LIMIT 5"

);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Dashboard Kasir</title>

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
    font-family:'Poppins',sans-serif;
}

body{
    background:#f1f5f9;
    overflow-x:hidden;
}

/* ======================================
SIDEBAR
====================================== */
.sidebar{

    height:100vh;

    background:
    linear-gradient(
    180deg,
    #2563eb,
    #1e3a8a);

    color:white;

    position:fixed;

    width:260px;

    padding:25px;

    overflow-y:auto;
}

.logo{

    text-align:center;

    font-size:28px;

    font-weight:700;

    margin-bottom:35px;
}

.sidebar a{

    display:block;

    color:white;

    text-decoration:none;

    padding:14px 18px;

    margin-bottom:12px;

    border-radius:14px;

    transition:0.3s;
}

.sidebar a:hover{

    background:rgba(255,255,255,0.2);

    transform:translateX(5px);
}

.sidebar i{
    margin-right:10px;
}

/* ======================================
CONTENT
====================================== */
.content{

    margin-left:260px;

    padding:30px;
}

.welcome{

    font-weight:700;

    color:#0f172a;
}

/* ======================================
TOPBAR
====================================== */
.topbar{

    background:white;

    border-radius:24px;

    padding:25px;

    margin-bottom:30px;

    box-shadow:
    0 8px 20px rgba(0,0,0,0.05);
}

/* ======================================
CARD DASHBOARD
====================================== */
.card-dashboard{

    border:none;

    border-radius:24px;

    overflow:hidden;

    transition:0.3s;
}

.card-dashboard:hover{

    transform:translateY(-6px);

    box-shadow:
    0 12px 25px rgba(0,0,0,0.12);
}

.bg-blue{

    background:
    linear-gradient(
    135deg,
    #3b82f6,
    #2563eb);

    color:white;
}

.bg-green{

    background:
    linear-gradient(
    135deg,
    #10b981,
    #059669);

    color:white;
}

.bg-orange{

    background:
    linear-gradient(
    135deg,
    #f59e0b,
    #d97706);

    color:white;
}

.icon-card{

    font-size:50px;

    opacity:0.7;
}

/* ======================================
TABLE
====================================== */
.table-card{

    border:none;

    border-radius:24px;

    overflow:hidden;

    box-shadow:
    0 8px 20px rgba(0,0,0,0.05);
}

.table thead{

    background:#0f172a;

    color:white;
}

.table tbody tr:hover{

    background:#f8fafc;
}

/* ======================================
RESPONSIVE
====================================== */
@media(max-width:768px){

    .sidebar{

        position:relative;

        width:100%;

        height:auto;
    }

    .content{

        margin-left:0;
    }
}

</style>

</head>

<body>

<!-- ======================================
SIDEBAR
====================================== -->
<div class="sidebar">

    <div class="logo">

        <i class="bi bi-shop"></i>

        MITRA AZAM

    </div>

    <a href="dashboard.php">

        <i class="bi bi-house-door-fill"></i>

        Dashboard

    </a>

    <a href="transaksi.php">

        <i class="bi bi-cart-fill"></i>

        Transaksi

    </a>

    <a href="riwayat_transaksi.php">

        <i class="bi bi-clock-history"></i>

        Riwayat Transaksi

    </a>

    <a href="../auth/logout.php">

        <i class="bi bi-box-arrow-right"></i>

        Logout

    </a>

</div>

<!-- ======================================
CONTENT
====================================== -->
<div class="content">

    <!-- TOPBAR -->
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap">

        <div>

            <h2 class="welcome">

                Dashboard Kasir

            </h2>

            <p class="text-muted mb-0">

                Sistem Penjualan Toko Mitra Azam

            </p>

        </div>

        <div>

            <h5>

                <i class="bi bi-person-circle"></i>

                <?= htmlspecialchars($_SESSION['nama']); ?>

            </h5>

        </div>

    </div>

    <!-- ======================================
    CARD DASHBOARD
    ====================================== -->
    <div class="row g-4">

        <!-- TOTAL BARANG -->
        <div class="col-md-4">

            <div class="card card-dashboard bg-blue shadow">

                <div class="card-body">

                    <div class="d-flex
                    justify-content-between
                    align-items-center">

                        <div>

                            <h1>

                                <?= $total_barang; ?>

                            </h1>

                            <p class="mb-0">

                                Total Barang

                            </p>

                        </div>

                        <i class="bi bi-box-seam icon-card"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- TOTAL TRANSAKSI -->
        <div class="col-md-4">

            <div class="card card-dashboard bg-green shadow">

                <div class="card-body">

                    <div class="d-flex
                    justify-content-between
                    align-items-center">

                        <div>

                            <h1>

                                <?= $total_penjualan; ?>

                            </h1>

                            <p class="mb-0">

                                Total Transaksi

                            </p>

                        </div>

                        <i class="bi bi-cart-check icon-card"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- TOTAL PENDAPATAN -->
        <div class="col-md-4">

            <div class="card card-dashboard bg-orange shadow">

                <div class="card-body">

                    <div class="d-flex
                    justify-content-between
                    align-items-center">

                        <div>

                            <h4>

                                Rp
                                <?= number_format(
                                $total_pendapatan,
                                0,
                                ',',
                                '.'
                                ); ?>

                            </h4>

                            <p class="mb-0">

                                Total Pendapatan

                            </p>

                        </div>

                        <i class="bi bi-cash-stack icon-card"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- ======================================
    PENDAPATAN HARI INI
    ====================================== -->
    <div class="row mt-4">

        <div class="col-lg-4">

            <div class="card card-dashboard shadow border-0">

                <div class="card-body bg-success text-white rounded-4">

                    <div class="d-flex
                    justify-content-between
                    align-items-center">

                        <div>

                            <small>

                                Pendapatan Hari Ini

                            </small>

                            <h3 class="fw-bold mt-2">

                                Rp
                                <?= number_format(
                                $pendapatan_hari_ini,
                                0,
                                ',',
                                '.'
                                ); ?>

                            </h3>

                        </div>

                        <div class="icon-card">

                            <i class="bi bi-wallet2"></i>

                        </div>

                    </div>

                    <hr>

                    <p class="mb-0">

                        Total pemasukan transaksi hari ini

                    </p>

                </div>

            </div>

        </div>

    </div>

    <!-- ======================================
    TRANSAKSI TERBARU
    ====================================== -->
    <div class="card table-card mt-5">

        <div class="card-header bg-dark text-white p-3">

            <h5 class="mb-0">

                <i class="bi bi-clock-history"></i>

                Transaksi Terbaru

            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Kembalian</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    while($d = mysqli_fetch_assoc($query_transaksi)){

                    ?>

                    <tr>

                        <td>

                            <?= $no++; ?>

                        </td>

                        <td>

                            <?= date(
                                'd-m-Y H:i',
                                strtotime($d['tanggal'])
                            ); ?>

                        </td>

                        <td>

                            Rp
                            <?= number_format(
                            $d['total_harga'],
                            0,
                            ',',
                            '.'
                            ); ?>

                        </td>

                        <td>

                            Rp
                            <?= number_format(
                            $d['bayar'],
                            0,
                            ',',
                            '.'
                            ); ?>

                        </td>

                        <td>

                            Rp
                            <?= number_format(
                            $d['kembali'],
                            0,
                            ',',
                            '.'
                            ); ?>

                        </td>

                    </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>