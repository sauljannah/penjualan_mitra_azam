<?php

session_start();

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ============================
// PROTEKSI LOGIN
// ============================
if (!isset($_SESSION['level'])) {

    header("Location: ../auth/login.php");
    exit;
}

// ============================
// FILTER TANGGAL
// ============================
$tanggal_awal  = "";
$tanggal_akhir = "";

$where = "";

if (isset($_POST['filter'])) {

    $tanggal_awal  = mysqli_real_escape_string(
        $conn,
        $_POST['tanggal_awal']
    );

    $tanggal_akhir = mysqli_real_escape_string(
        $conn,
        $_POST['tanggal_akhir']
    );

    $where = "
        WHERE tanggal BETWEEN
        '$tanggal_awal'
        AND
        '$tanggal_akhir'
    ";
}

// ============================
// TOTAL PENJUALAN
// ============================
$total_penjualan = 0;

$query_penjualan = mysqli_query(
    $conn,
    "SELECT
        SUM(total_harga) AS total_penjualan
     FROM penjualan
     $where"
);

if (!$query_penjualan) {

    die(
        "Query Penjualan Error : " .
        mysqli_error($conn)
    );
}

$data_penjualan = mysqli_fetch_assoc(
    $query_penjualan
);

$total_penjualan =
$data_penjualan['total_penjualan'] ?? 0;

// ============================
// TOTAL KEUNTUNGAN
// ============================
$total_keuntungan = 0;

$query_keuntungan = mysqli_query(
    $conn,
    "SELECT
        SUM(keuntungan) AS total_keuntungan
     FROM penjualan
     $where"
);

if (!$query_keuntungan) {

    die(
        "Query Keuntungan Error : " .
        mysqli_error($conn)
    );
}

$data_keuntungan = mysqli_fetch_assoc(
    $query_keuntungan
);

$total_keuntungan =
$data_keuntungan['total_keuntungan'] ?? 0;

// ============================
// TOTAL MODAL
// ============================
$total_modal = 0;

$query_modal = mysqli_query(
    $conn,
    "SELECT
        SUM(
            detail_penjualan.jumlah *
            barang.harga_beli
        ) AS total_modal

     FROM detail_penjualan

     JOIN barang
     ON detail_penjualan.id_barang =
        barang.id_barang"
);

if (!$query_modal) {

    die(
        "Query Modal Error : " .
        mysqli_error($conn)
    );
}

$data_modal = mysqli_fetch_assoc(
    $query_modal
);

$total_modal =
$data_modal['total_modal'] ?? 0;

// ============================
// HITUNG LABA / RUGI
// ============================
$laba_bersih =
$total_penjualan - $total_modal;

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Laporan Laba Rugi</title>

<!-- Bootstrap -->
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- Bootstrap Icons -->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f6f9;
    overflow-x:hidden;
    font-family:Arial,sans-serif;
}

/* =========================
SIDEBAR
========================= */
.sidebar{
    width:260px;
    height:100vh;
    position:fixed;
    background:
        linear-gradient(
        135deg,
        #296bf9,
        #142b76
    );
    padding:20px;
    color:white;
}

.sidebar h3{
    text-align:center;
    margin-bottom:25px;
}

.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:14px;
    border-radius:12px;
    margin-bottom:12px;
    transition:0.3s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.2);
    transform:translateX(5px);
}

/* =========================
CONTENT
========================= */
.content{
    margin-left:270px;
    padding:25px;
}

.card{
    border:none;
    border-radius:20px;
    box-shadow:0 4px 15px rgba(0,0,0,0.06);
}

.summary-card{
    transition:0.3s;
}

.summary-card:hover{
    transform:translateY(-5px);
}

.btn{
    border-radius:10px;
}

.table tbody tr:hover{
    background:#f1f1f1;
}

.icon-box{
    width:70px;
    height:70px;
    border-radius:18px;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:30px;
    color:white;
}

.bg-blue{
    background:#0d6efd;
}

.bg-red{
    background:#dc3545;
}

.bg-green{
    background:#198754;
}

.bg-warning2{
    background:#ffc107;
    color:black;
}

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

@media print{

    .sidebar,
    .btn,
    form{
        display:none;
    }

    .content{
        margin-left:0;
    }

    body{
        background:white;
    }
}

</style>

</head>

<body>

<!-- =========================
SIDEBAR
========================= -->
<div class="sidebar">

    <h3>

        <i class="bi bi-building"></i>
        MITRA AZAM

    </h3>

    <a href="dashboard.php">

        <i class="bi bi-speedometer2"></i>
        Dashboard

    </a>

    <a href="barang.php">

        <i class="bi bi-box-seam"></i>
        Data Barang

    </a>

    <a href="tambah_barang.php"
       style="background:rgba(255,255,255,0.2);">

        <i class="bi bi-plus-circle"></i>
        Tambah Barang

    </a>

    <a href="laporan.php">

        <i class="bi bi-file-earmark-text"></i>
        Laporan

    </a>

    <a href="barang_masuk.php">
        <i class="bi bi-box-arrow-in-down"></i>
         Barang Masuk
                
    </a>

    <a href="laba_rugi.php">

        <i class="bi bi-cash-stack"></i>
        Laba Rugi

    </a>

    <a href="manajemen_user.php">

        <i class="bi bi-people"></i>
        Manajemen User

    </a>

    <a href="setting.php">

         <i class="bi bi-gear"></i>
         Setting

    </a>

    <a href="../auth/logout.php">

        <i class="bi bi-box-arrow-right"></i>
        Logout

    </a>

</div>

<!-- =========================
CONTENT
========================= -->
<div class="content">

    <!-- HEADER -->
    <div class="card mb-4">

        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

            <div>

                <h3 class="fw-bold">

                    Laporan Laba Rugi

                </h3>

                <p class="mb-0 text-muted">

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

    </div>

    <!-- FILTER -->
    <div class="card mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">

                Filter Tanggal

            </h5>

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Tanggal Awal

                        </label>

                        <input
                            type="date"
                            name="tanggal_awal"
                            class="form-control"
                            value="<?= htmlspecialchars($tanggal_awal); ?>"
                            required>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Tanggal Akhir

                        </label>

                        <input
                            type="date"
                            name="tanggal_akhir"
                            class="form-control"
                            value="<?= htmlspecialchars($tanggal_akhir); ?>"
                            required>

                    </div>

                    <div class="col-md-4 mb-3 d-flex align-items-end">

                        <button
                            type="submit"
                            name="filter"
                            class="btn btn-primary me-2">

                            <i class="bi bi-search"></i>
                            Filter

                        </button>

                        <button
                            type="button"
                            onclick="window.print()"
                            class="btn btn-success">

                            <i class="bi bi-printer"></i>
                            Print

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- =========================
    SUMMARY
    ========================= -->
    <div class="row mb-4">

        <!-- TOTAL PENJUALAN -->
        <div class="col-md-4 mb-3">

            <div class="card bg-primary text-white summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h5>Total Penjualan</h5>

                        <h3>

                            Rp <?= number_format(
                                $total_penjualan,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </h3>

                    </div>

                    <div class="icon-box bg-blue">

                        <i class="bi bi-cash-stack"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- TOTAL MODAL -->
        <div class="col-md-4 mb-3">

            <div class="card bg-danger text-white summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h5>Total Modal</h5>

                        <h3>

                            Rp <?= number_format(
                                $total_modal,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </h3>

                    </div>

                    <div class="icon-box bg-red">

                        <i class="bi bi-wallet2"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- LABA -->
        <div class="col-md-4 mb-3">

            <?php if($laba_bersih >= 0): ?>

                <div class="card bg-success text-white summary-card">

            <?php else: ?>

                <div class="card bg-warning2 summary-card">

            <?php endif; ?>

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h5>

                            <?= $laba_bersih >= 0
                            ? 'Laba Bersih'
                            : 'Kerugian'; ?>

                        </h5>

                        <h3>

                            Rp <?= number_format(
                                $laba_bersih,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </h3>

                    </div>

                    <div class="icon-box bg-green">

                        <i class="bi bi-graph-up-arrow"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- TABEL -->
    <div class="card">

        <div class="card-header bg-dark text-white">

            <h5 class="mb-0">

                Ringkasan Keuangan

            </h5>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped">

                <tr>

                    <th width="70%">
                        Total Penjualan
                    </th>

                    <th>

                        Rp <?= number_format(
                            $total_penjualan,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </th>

                </tr>

                <tr>

                    <th>
                        Total Modal
                    </th>

                    <th>

                        Rp <?= number_format(
                            $total_modal,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </th>

                </tr>

                <tr>

                    <th>

                        <?= $laba_bersih >= 0
                        ? 'Laba Bersih'
                        : 'Kerugian'; ?>

                    </th>

                    <th>

                        Rp <?= number_format(
                            $laba_bersih,
                            0,
                            ',',
                            '.'
                        ); ?>

                    </th>

                </tr>

            </table>

        </div>

    </div>

</div>

</body>
</html>