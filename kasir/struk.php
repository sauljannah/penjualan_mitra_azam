<?php

session_start();

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// =====================================
// PROTEKSI LOGIN
// =====================================
if (!isset($_SESSION['level'])) {

    header("Location: ../auth/login.php");
    exit;
}

// =====================================
// VALIDASI ID PENJUALAN
// =====================================
if (!isset($_GET['id'])) {

    echo "
    <script>

        alert('ID transaksi tidak ditemukan');

        window.location='transaksi.php';

    </script>
    ";

    exit;
}

$id_penjualan = (int) $_GET['id'];

// =====================================
// AMBIL DATA PENJUALAN
// =====================================
$query_penjualan = mysqli_query(

    $conn,

    "SELECT *
     FROM penjualan
     WHERE id_penjualan = '$id_penjualan'"

);

// =====================================
// VALIDASI QUERY
// =====================================
if (!$query_penjualan) {

    die(

        "Query Error : " .
        mysqli_error($conn)

    );
}

// =====================================
// VALIDASI DATA
// =====================================
if (mysqli_num_rows($query_penjualan) === 0) {

    echo "
    <script>

        alert('Data transaksi tidak ditemukan');

        window.location='transaksi.php';

    </script>
    ";

    exit;
}

// =====================================
// DATA PENJUALAN
// =====================================
$penjualan = mysqli_fetch_assoc(
    $query_penjualan
);

// =====================================
// AMBIL DETAIL TRANSAKSI
// =====================================
$query_detail = mysqli_query(

    $conn,

    "SELECT

        detail_penjualan.*,
        barang.nama_barang

     FROM detail_penjualan

     JOIN barang
     ON detail_penjualan.id_barang = barang.id_barang

     WHERE detail_penjualan.id_penjualan = '$id_penjualan'

     ORDER BY detail_penjualan.id_detail ASC"

);

// =====================================
// VALIDASI QUERY DETAIL
// =====================================
if (!$query_detail) {

    die(

        "Query Error : " .
        mysqli_error($conn)

    );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Struk Pembayaran</title>

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

/* =====================================
GLOBAL
===================================== */
*{
    font-family:'Poppins',sans-serif;
}

body{

    background:
    linear-gradient(
    135deg,
    #eff6ff,
    #f8fafc);

    min-height:100vh;

    padding:30px;
}

/* =====================================
STRUK
===================================== */
.struk{

    width:430px;

    max-width:100%;

    background:white;

    margin:auto;

    border-radius:28px;

    overflow:hidden;

    box-shadow:
    0 10px 35px rgba(0,0,0,0.1);
}

/* =====================================
HEADER
===================================== */
.header{

    background:
    linear-gradient(
    135deg,
    #2563eb,
    #1d4ed8);

    color:white;

    padding:30px 25px;

    text-align:center;
}

.header h2{

    margin:0;

    font-weight:700;

    letter-spacing:1px;
}

.header p{

    margin:5px 0;

    font-size:14px;

    opacity:0.9;
}

/* =====================================
CONTENT
===================================== */
.content{

    padding:25px;
}

/* =====================================
INFO
===================================== */
.info-box{

    background:#f8fafc;

    border-radius:18px;

    padding:18px;

    margin-bottom:20px;
}

.info-table{

    width:100%;
}

.info-table td{

    padding:6px 0;

    font-size:14px;
}

.label{

    color:#64748b;
}

.value{

    text-align:right;

    font-weight:600;
}

/* =====================================
GARIS
===================================== */
.line{

    border-top:2px dashed #cbd5e1;

    margin:20px 0;
}

/* =====================================
ITEM
===================================== */
.item{

    margin-bottom:18px;
}

.item-name{

    font-weight:600;

    color:#0f172a;

    margin-bottom:6px;
}

.item-detail{

    display:flex;

    justify-content:space-between;

    font-size:14px;

    color:#475569;
}

/* =====================================
TOTAL
===================================== */
.total-box{

    background:#eff6ff;

    border-radius:18px;

    padding:18px;
}

.total-table{

    width:100%;
}

.total-table td{

    padding:8px 0;

    font-size:15px;
}

.total-final{

    font-size:18px;

    font-weight:700;

    color:#2563eb;
}

/* =====================================
FOOTER
===================================== */
.footer{

    text-align:center;

    margin-top:25px;

    padding-top:20px;

    border-top:1px dashed #cbd5e1;
}

.footer h6{

    font-weight:600;

    color:#0f172a;
}

.footer p{

    font-size:13px;

    color:#64748b;

    margin:0;
}

/* =====================================
BUTTON
===================================== */
.btn-area{

    display:flex;

    gap:10px;

    flex-wrap:wrap;

    justify-content:center;

    margin-top:25px;
}

.btn{

    border-radius:14px;

    padding:10px 18px;

    font-weight:500;
}

/* =====================================
PRINT
===================================== */
@media print{

    body{

        background:white;

        padding:0;
    }

    .btn-area{

        display:none;
    }

    .struk{

        box-shadow:none;

        width:100%;

        border-radius:0;
    }
}

</style>

</head>

<body>

<div class="struk">

    <!-- =====================================
    HEADER
    ===================================== -->
    <div class="header">

        <h2>

            <i class="bi bi-shop"></i>

            TOKO MITRA AZAM

        </h2>

        <p>

            Sistem Informasi Penjualan

        </p>

        <p>

            Jl. Hj. Falaq Desa Luhu Dusun Limboro
            Kecamatan Huamual,
            Kabupaten Seram Bagian Barat

        </p>

    </div>

    <!-- =====================================
    CONTENT
    ===================================== -->
    <div class="content">

        <!-- INFO -->
        <div class="info-box">

            <table class="info-table">

                <tr>

                    <td class="label">

                        Tanggal

                    </td>

                    <td class="value">

                        <?= date(
                        'd-m-Y H:i',
                        strtotime($penjualan['tanggal'])
                        ); ?>

                    </td>

                </tr>

                <tr>

                    <td class="label">

                        Kasir

                    </td>

                    <td class="value">

                        <?= htmlspecialchars(
                        $penjualan['kasir']
                        ); ?>

                    </td>

                </tr>

                <tr>

                    <td class="label">

                        No Transaksi

                    </td>

                    <td class="value">

                        TRX-<?= $penjualan['id_penjualan']; ?>

                    </td>

                </tr>

                <tr>

                    <td class="label">

                        Pembayaran

                    </td>

                    <td class="value">

                        <?= htmlspecialchars(
                        $penjualan['metode_pembayaran']
                        ); ?>

                    </td>

                </tr>

                <?php if(
                    !empty($penjualan['referensi'])
                ): ?>

                <tr>

                    <td class="label">

                        Referensi

                    </td>

                    <td class="value">

                        <?= htmlspecialchars(
                        $penjualan['referensi']
                        ); ?>

                    </td>

                </tr>

                <?php endif; ?>

            </table>

        </div>

        <div class="line"></div>

        <!-- DETAIL BARANG -->
        <?php while($detail = mysqli_fetch_assoc($query_detail)): ?>

        <div class="item">

            <div class="item-name">

                <?= htmlspecialchars(
                $detail['nama_barang']
                ); ?>

            </div>

            <div class="item-detail">

                <span>

                    <?= $detail['jumlah']; ?>

                    x

                    Rp <?= number_format(
                    $detail['harga'],
                    0,
                    ',',
                    '.'
                    ); ?>

                </span>

                <strong>

                    Rp <?= number_format(

                    $detail['jumlah']
                    *
                    $detail['harga'],

                    0,
                    ',',
                    '.'

                    ); ?>

                </strong>

            </div>

        </div>

        <?php endwhile; ?>

        <div class="line"></div>

        <!-- TOTAL -->
        <div class="total-box">

            <table class="total-table">

                <tr>

                    <td>

                        Total

                    </td>

                    <td class="text-end total-final">

                        Rp <?= number_format(

                        $penjualan['total_harga'],
                        0,
                        ',',
                        '.'

                        ); ?>

                    </td>

                </tr>

                <tr>

                    <td>

                        Bayar

                    </td>

                    <td class="text-end">

                        Rp <?= number_format(

                        $penjualan['bayar'],
                        0,
                        ',',
                        '.'

                        ); ?>

                    </td>

                </tr>

                <tr>

                    <td>

                        Kembalian

                    </td>

                    <td class="text-end text-success fw-bold">

                        Rp <?= number_format(

                        $penjualan['kembali'],
                        0,
                        ',',
                        '.'

                        ); ?>

                    </td>

                </tr>

            </table>

        </div>

        <!-- FOOTER -->
        <div class="footer">

            <h6>

                Terima Kasih 🙏

            </h6>

            <p>

                Terima kasih telah berbelanja
                di Toko Mitra Azam

            </p>

        </div>

        <!-- BUTTON -->
        <div class="btn-area">

            <!-- PRINT -->
            <button
            onclick="window.print()"
            class="btn btn-success">

                <i class="bi bi-printer"></i>

                Print

            </button>

            <!-- TRANSAKSI -->
            <a
            href="transaksi.php"
            class="btn btn-primary">

                <i class="bi bi-cart-plus"></i>

                Transaksi Lagi

            </a>

            <!-- RIWAYAT -->
            <a
            href="riwayat_transaksi.php"
            class="btn btn-dark">

                <i class="bi bi-clock-history"></i>

                Riwayat

            </a>

        </div>

    </div>

</div>

</body>
</html>