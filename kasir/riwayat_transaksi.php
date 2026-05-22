<?php

session_start();

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if (!isset($_SESSION['level'])) {

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// CEK LEVEL
// ======================================
if ($_SESSION['level'] != "kasir") {

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// PENCARIAN
// ======================================
$cari = "";

$query = "
SELECT *
FROM penjualan
ORDER BY id_penjualan DESC
";

if (isset($_GET['cari'])) {

    $cari = mysqli_real_escape_string(
        $conn,
        trim($_GET['cari'])
    );

    $query = "
    SELECT *
    FROM penjualan
    WHERE tanggal LIKE '%$cari%'
    ORDER BY id_penjualan DESC
    ";
}

// ======================================
// AMBIL DATA TRANSAKSI
// ======================================
$data = mysqli_query($conn, $query);

if (!$data) {

    die(
        "Query Error : " .
        mysqli_error($conn)
    );
}

// ======================================
// TOTAL TRANSAKSI
// ======================================
$total_transaksi = 0;

$total_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM penjualan"
);

if ($total_query) {

    $row = mysqli_fetch_assoc(
        $total_query
    );

    $total_transaksi = $row['total'];
}

// ======================================
// TOTAL PENDAPATAN
// ======================================
$total_pendapatan = 0;

$pendapatan_query = mysqli_query(
    $conn,
    "SELECT SUM(total_harga) AS total FROM penjualan"
);

if ($pendapatan_query) {

    $row_pendapatan = mysqli_fetch_assoc(
        $pendapatan_query
    );

    $total_pendapatan =
    $row_pendapatan['total'];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Riwayat Transaksi</title>

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

/* ======================================
GLOBAL
====================================== */
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

    width:260px;

    height:100vh;

    position:fixed;

    top:0;
    left:0;

    background:linear-gradient(
    180deg,
    #2563eb,
    #1e3a8a);

    padding:25px 15px;

    color:white;

    box-shadow:
    4px 0 15px rgba(0,0,0,0.1);

    z-index:1000;
}

.logo{

    text-align:center;

    font-size:28px;

    font-weight:700;

    margin-bottom:30px;
}

.sidebar a{

    display:flex;

    align-items:center;

    gap:12px;

    color:white;

    text-decoration:none;

    padding:14px 16px;

    border-radius:14px;

    margin-bottom:12px;

    transition:0.3s;
}

.sidebar a:hover{

    background:rgba(255,255,255,0.15);

    transform:translateX(5px);
}

.sidebar i{
    font-size:18px;
}

/* ======================================
CONTENT
====================================== */
.content{

    margin-left:260px;

    padding:30px;
}

/* ======================================
CARD
====================================== */
.card{

    border:none;

    border-radius:24px;

    overflow:hidden;

    box-shadow:
    0 5px 20px rgba(0,0,0,0.08);
}

.card-header{

    border:none;

    padding:18px 22px;

    font-weight:600;
}

.card-body{
    padding:25px;
}

/* ======================================
HEADER
====================================== */
.header-card{

    background:linear-gradient(
    135deg,
    #2563eb,
    #3b82f6);

    color:white;
}

.user-box{

    background:rgba(255,255,255,0.15);

    padding:10px 18px;

    border-radius:14px;

    font-weight:500;
}

/* ======================================
STATISTIK
====================================== */
.stat-card{

    border-radius:22px;

    color:white;

    padding:20px;

    transition:0.3s;
}

.stat-card:hover{

    transform:translateY(-5px);
}

.bg-blue{

    background:linear-gradient(
    135deg,
    #3b82f6,
    #2563eb);
}

.bg-green{

    background:linear-gradient(
    135deg,
    #10b981,
    #059669);
}

.icon-stat{

    font-size:40px;

    opacity:0.7;
}

/* ======================================
TABLE
====================================== */
.table{

    vertical-align:middle;
}

.table thead{

    background:#eff6ff;
}

.table thead th{

    color:#1e3a8a;

    border:none;

    font-weight:600;
}

.table tbody tr{

    transition:0.2s;
}

.table tbody tr:hover{

    background:#f8fafc;
}

.table td{

    border-color:#eef2f7;
}

/* ======================================
FORM
====================================== */
.form-control{

    border-radius:14px;

    border:1px solid #dbeafe;

    padding:12px;
}

.form-control:focus{

    border-color:#2563eb;

    box-shadow:
    0 0 0 4px rgba(37,99,235,0.1);
}

/* ======================================
BUTTON
====================================== */
.btn{

    border-radius:12px;

    font-weight:500;
}

.btn-success{

    border:none;

    background:#10b981;
}

.btn-success:hover{

    background:#059669;
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

        padding:20px;
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

        KASIR

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

    <!-- HEADER -->
    <div class="card header-card mb-4">

        <div class="card-body">

            <div class="d-flex
            justify-content-between
            align-items-center
            flex-wrap gap-3">

                <div>

                    <h2 class="fw-bold">

                        Riwayat Transaksi

                    </h2>

                    <p class="mb-0">

                        Sistem Kasir Toko Mitra Azam

                    </p>

                </div>

                <div class="user-box">

                    <i class="bi bi-person-circle"></i>

                    <?= htmlspecialchars($_SESSION['nama']); ?>

                </div>

            </div>

        </div>

    </div>

    <!-- STATISTIK -->
    <div class="row mb-4 g-4">

        <!-- TOTAL TRANSAKSI -->
        <div class="col-md-4">

            <div class="stat-card bg-blue shadow">

                <div class="d-flex
                justify-content-between
                align-items-center">

                    <div>

                        <h2 class="fw-bold">

                            <?= $total_transaksi; ?>

                        </h2>

                        <p class="mb-0">

                            Total Transaksi

                        </p>

                    </div>

                    <i class="bi bi-cart-check icon-stat"></i>

                </div>

            </div>

        </div>

        <!-- TOTAL PENDAPATAN -->
        <div class="col-md-4">

            <div class="stat-card bg-green shadow">

                <div class="d-flex
                justify-content-between
                align-items-center">

                    <div>

                        <h5 class="fw-bold">

                            Rp
                            <?= number_format(
                            $total_pendapatan,
                            0,
                            ',',
                            '.'
                            ); ?>

                        </h5>

                        <p class="mb-0">

                            Total Pendapatan

                        </p>

                    </div>

                    <i class="bi bi-cash-stack icon-stat"></i>

                </div>

            </div>

        </div>

    </div>

    <!-- TABEL -->
    <div class="card">

        <div class="card-header bg-primary text-white">

            <div class="d-flex
            justify-content-between
            align-items-center
            flex-wrap gap-3">

                <h5 class="mb-0">

                    <i class="bi bi-table"></i>

                    Data Transaksi

                </h5>

                <!-- SEARCH -->
                <form method="GET">

                    <div class="input-group">

                        <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari tanggal..."
                        value="<?= htmlspecialchars($cari); ?>">

                        <button
                        class="btn btn-light">

                            <i class="bi bi-search"></i>

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr class="text-center">

                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Kembali</th>
                            <th>Keuntungan</th>
                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if(mysqli_num_rows($data) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while($d = mysqli_fetch_assoc($data)): ?>

                        <tr>

                            <!-- NO -->
                            <td class="text-center fw-semibold">

                                <?= $no++; ?>

                            </td>

                            <!-- TANGGAL -->
                            <td>

                                <?= htmlspecialchars($d['tanggal']); ?>

                            </td>

                            <!-- TOTAL -->
                            <td class="text-end fw-semibold">

                                Rp <?= number_format(
                                $d['total_harga'],
                                0,
                                ',',
                                '.'
                                ); ?>

                            </td>

                            <!-- BAYAR -->
                            <td class="text-end">

                                Rp <?= number_format(
                                $d['bayar'],
                                0,
                                ',',
                                '.'
                                ); ?>

                            </td>

                            <!-- KEMBALI -->
                            <td class="text-end">

                                Rp <?= number_format(
                                $d['kembali'],
                                0,
                                ',',
                                '.'
                                ); ?>

                            </td>

                            <!-- LABA -->
                            <td class="text-end text-success fw-bold">

                                Rp <?= number_format(
                                $d['keuntungan'],
                                0,
                                ',',
                                '.'
                                ); ?>

                            </td>

                            <!-- AKSI -->
                            <td class="text-center">

                                <a
                                href="struk.php?id=<?= $d['id_penjualan']; ?>"
                                target="_blank"
                                class="btn btn-success btn-sm">

                                    <i class="bi bi-receipt"></i>

                                    Struk

                                </a>

                            </td>

                        </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                            colspan="7"
                            class="text-center text-danger py-4">

                                <i class="bi bi-exclamation-circle"></i>

                                Data transaksi tidak ditemukan

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>