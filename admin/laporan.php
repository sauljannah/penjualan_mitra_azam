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
$where         = "";

// FILTER DATA
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
// QUERY DATA PENJUALAN
// ============================
$query = mysqli_query(
    $conn,
    "
    SELECT *
    FROM penjualan
    $where
    ORDER BY id_penjualan DESC
    "
);

// VALIDASI QUERY
if (!$query) {

    die(
        "Query Error : " .
        mysqli_error($conn)
    );
}

// ============================
// TOTAL PENJUALAN
// ============================
$total_penjualan = 0;

$total_penjualan_query = mysqli_query(
    $conn,
    "
    SELECT
    SUM(total_harga) AS total_penjualan
    FROM penjualan
    $where
    "
);

if ($total_penjualan_query) {

    $data_penjualan = mysqli_fetch_assoc(
        $total_penjualan_query
    );

    $total_penjualan =
    $data_penjualan['total_penjualan'] ?? 0;
}

// ============================
// TOTAL KEUNTUNGAN
// ============================
$total_keuntungan = 0;

$total_keuntungan_query = mysqli_query(
    $conn,
    "
    SELECT
    SUM(keuntungan) AS total_keuntungan
    FROM penjualan
    $where
    "
);

if ($total_keuntungan_query) {

    $data_keuntungan = mysqli_fetch_assoc(
        $total_keuntungan_query
    );

    $total_keuntungan =
    $data_keuntungan['total_keuntungan'] ?? 0;
}

// ============================
// TOTAL TRANSAKSI
// ============================
$total_transaksi = 0;

$total_transaksi_query = mysqli_query(
    $conn,
    "
    SELECT
    COUNT(id_penjualan) AS total_transaksi
    FROM penjualan
    $where
    "
);

if ($total_transaksi_query) {

    $data_transaksi = mysqli_fetch_assoc(
        $total_transaksi_query
    );

    $total_transaksi =
    $data_transaksi['total_transaksi'] ?? 0;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Laporan Penjualan</title>

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
            font-family:Arial,sans-serif;
            overflow-x:hidden;
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
            margin-bottom:30px;
            font-weight:bold;
        }

        .sidebar a{
            display:block;
            color:white;
            text-decoration:none;
            padding:13px;
            border-radius:12px;
            margin-bottom:10px;
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

        .stat-card{
            transition:0.3s;
        }

        .stat-card:hover{
            transform:translateY(-5px);
        }

        .table tbody tr:hover{
            background:#f1f1f1;
        }

        .btn{
            border-radius:10px;
        }

        .form-control{
            border-radius:10px;
        }

        .icon-box{
            width:65px;
            height:65px;
            border-radius:18px;
            display:flex;
            justify-content:center;
            align-items:center;
            font-size:28px;
            color:white;
        }

        .bg-orange{
            background:#ff6b00;
        }

        .bg-green{
            background:#198754;
        }

        .bg-blue{
            background:#0d6efd;
        }

        .table thead{
            vertical-align:middle;
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
                padding:0;
            }

            body{
                background:white;
            }

            .card{
                box-shadow:none;
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

    <a href="barang.php"
    
        <i class="bi bi-box-seam"></i>
        Data Barang

    </a>

    </a>

    <a href="tambah_barang.php">

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

                <h2>
                    LAPORAN PENJUALAN
                </h2>

                <p class="text-muted mb-0">
                    Sistem Informasi Toko Bangunan Mitra Azam
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

    <!-- =========================
         STATISTIK
    ========================= -->
    <div class="row mb-4">

        <!-- TOTAL PENJUALAN -->
        <div class="col-md-4 mb-3">

            <div class="card stat-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h5>Total Penjualan</h5>

                        <h4>

                            Rp <?= number_format(
                                $total_penjualan,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </h4>

                    </div>

                    <div class="icon-box bg-blue">

                        <i class="bi bi-cash-stack"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- TOTAL KEUNTUNGAN -->
        <div class="col-md-4 mb-3">

            <div class="card stat-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h5>Total Keuntungan</h5>

                        <h4>

                            Rp <?= number_format(
                                $total_keuntungan,
                                0,
                                ',',
                                '.'
                            ); ?>

                        </h4>

                    </div>

                    <div class="icon-box bg-green">

                        <i class="bi bi-graph-up-arrow"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- TOTAL TRANSAKSI -->
        <div class="col-md-4 mb-3">

            <div class="card stat-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h5>Total Transaksi</h5>

                        <h4>

                            <?= $total_transaksi; ?>

                        </h4>

                    </div>

                    <div class="icon-box bg-orange">

                        <i class="bi bi-receipt"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- =========================
         FILTER
    ========================= -->
    <div class="card mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">

                <i class="bi bi-funnel"></i>
                Filter Laporan

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

                    <div class="col-md-4 mb-3 d-flex align-items-end gap-2">

                        <button
                            type="submit"
                            name="filter"
                            class="btn btn-primary">

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
         TABEL LAPORAN
    ========================= -->
    <div class="card">

        <div class="card-header bg-dark text-white">

            <h5 class="mb-0">

                <i class="bi bi-table"></i>
                Data Penjualan

            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-warning text-center">

                        <tr>

                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Total Harga</th>
                            <th>Bayar</th>
                            <th>Kembalian</th>
                            <th>Keuntungan</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if(mysqli_num_rows($query) > 0): ?>

                        <?php
                        $no = 1;

                        while($d = mysqli_fetch_assoc($query)):
                        ?>

                        <tr>

                            <td class="text-center">

                                <?= $no++; ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($d['tanggal']); ?>

                            </td>

                            <td>

                                Rp <?= number_format(
                                    $d['total_harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </td>

                            <td>

                                Rp <?= number_format(
                                    $d['bayar'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </td>

                            <td>

                                Rp <?= number_format(
                                    $d['kembali'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                            </td>

                            <td>

                                <span class="badge bg-success p-2">

                                    Rp <?= number_format(
                                        $d['keuntungan'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>

                                </span>

                            </td>

                        </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-danger">

                                Data laporan tidak ditemukan

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