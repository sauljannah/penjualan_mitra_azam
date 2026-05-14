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
// TOTAL BARANG
// ======================================
$q_barang = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM barang"
);

$d_barang = mysqli_fetch_assoc($q_barang);
$total_barang = $d_barang['total'] ?? 0;

// ======================================
// STOK MENIPIS
// ======================================
$q_stok = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM barang
     WHERE stok > 0
     AND stok <= stok_minimum"
);

$d_stok = mysqli_fetch_assoc($q_stok);
$stok_menipis = $d_stok['total'] ?? 0;

// ======================================
// STOK HABIS
// ======================================
$q_habis = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM barang
     WHERE stok <= 0"
);

$d_habis = mysqli_fetch_assoc($q_habis);
$stok_habis = $d_habis['total'] ?? 0;

// ======================================
// TOTAL TRANSAKSI
// ======================================
$q_transaksi = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM penjualan"
);

$d_transaksi = mysqli_fetch_assoc($q_transaksi);
$total_transaksi = $d_transaksi['total'] ?? 0;

// ======================================
// PENDAPATAN HARI INI
// ======================================
$q_hari_ini = mysqli_query(
    $conn,
    "SELECT
    SUM(total_harga) AS total,
    SUM(keuntungan) AS keuntungan
    FROM penjualan
    WHERE DATE(tanggal)=CURDATE()"
);

$d_hari_ini = mysqli_fetch_assoc($q_hari_ini);

$pendapatan_hari_ini = $d_hari_ini['total'] ?? 0;
$keuntungan_hari_ini = $d_hari_ini['keuntungan'] ?? 0;

// ======================================
// PENDAPATAN BULAN INI
// ======================================
$q_bulan = mysqli_query(
    $conn,
    "SELECT
    SUM(total_harga) AS total,
    SUM(keuntungan) AS keuntungan
    FROM penjualan
    WHERE MONTH(tanggal)=MONTH(CURDATE())
    AND YEAR(tanggal)=YEAR(CURDATE())"
);

$d_bulan = mysqli_fetch_assoc($q_bulan);

$pendapatan_bulan = $d_bulan['total'] ?? 0;
$keuntungan_bulan = $d_bulan['keuntungan'] ?? 0;

// ======================================
// PENDAPATAN & KEUNTUNGAN TAHUN INI
// ======================================
$q_tahun = mysqli_query(
    $conn,
    "SELECT
        SUM(total_harga) AS total,
        SUM(keuntungan) AS keuntungan
     FROM penjualan
     WHERE YEAR(tanggal)=YEAR(CURDATE())"
);

$d_tahun = mysqli_fetch_assoc($q_tahun);

$pendapatan_tahun = $d_tahun['total'] ?? 0;
$keuntungan_tahun = $d_tahun['keuntungan'] ?? 0;

// ======================================
// TRANSAKSI TERBARU
// ======================================
$transaksi = mysqli_query(
    $conn,
    "SELECT *
    FROM penjualan
    ORDER BY id_penjualan DESC
    LIMIT 5"
);

// ======================================
// GRAFIK PENJUALAN
// ======================================
$grafik = mysqli_query(
    $conn,
    "SELECT
    DATE(tanggal) AS tgl,
    SUM(total_harga) AS total

    FROM penjualan

    GROUP BY DATE(tanggal)

    ORDER BY DATE(tanggal) ASC

    LIMIT 7"
);

$label_grafik = [];
$data_grafik  = [];

while($g = mysqli_fetch_assoc($grafik)){

    $label_grafik[] = date(
        'd M',
        strtotime($g['tgl'])
    );

    $data_grafik[] = (int)$g['total'];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Dashboard Admin</title>

<!-- Bootstrap -->
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- Bootstrap Icon -->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
    color:#2d3436;
}

/* ===================================
SIDEBAR
=================================== */
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:250px;
    height:100vh;
    background:linear-gradient(
        180deg,
        #ff7b00,
        #ff9d2f
    );
    padding:25px 18px;
    overflow-y:auto;
    box-shadow:4px 0 20px rgba(0,0,0,0.08);
}

.logo{
    color:white;
    font-size:28px;
    font-weight:700;
    text-align:center;
    margin-bottom:40px;
}

.logo i{
    margin-right:8px;
}

.sidebar-menu{
    list-style:none;
    padding:0;
}

.sidebar-menu li{
    margin-bottom:10px;
}

.sidebar-menu a{
    display:flex;
    align-items:center;
    gap:12px;
    text-decoration:none;
    color:white;
    padding:14px 16px;
    border-radius:15px;
    transition:0.3s;
    font-size:15px;
    font-weight:500;
}

.sidebar-menu a:hover{
    background:rgba(255,255,255,0.18);
    transform:translateX(5px);
}

/* ===================================
CONTENT
=================================== */
.content{
    margin-left:250px;
    padding:25px;
}

/* ===================================
TOPBAR
=================================== */
.topbar{
    background:white;
    border-radius:25px;
    padding:22px 28px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
    box-shadow:0 6px 18px rgba(0,0,0,0.04);
}

.topbar h2{
    font-size:30px;
    font-weight:700;
}

.topbar p{
    color:#7f8c8d;
    margin-top:4px;
}

.profile-admin{
    background:#fff3e8;
    color:#ff7b00;
    padding:10px 18px;
    border-radius:14px;
    font-weight:600;
}

/* ===================================
CARD
=================================== */
.dashboard-card{
    background:white;
    border-radius:25px;
    padding:24px;
    box-shadow:0 6px 18px rgba(0,0,0,0.04);
    border:1px solid #eef2f7;
    transition:0.3s;
    overflow:hidden;
    position:relative;
    text-decoration:none;
    color:inherit;
    display:block;
    cursor:pointer;
}

.dashboard-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    color:inherit;
}

.card-flex{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.dashboard-card h6{
    color:#7f8c8d;
    font-size:14px;
    margin-bottom:10px;
}

.dashboard-card h3{
    font-size:30px;
    font-weight:700;
}

.icon-box{
    width:65px;
    height:65px;
    border-radius:20px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
}

/* WARNA */
.orange{
    background:#fff3e8;
    color:#ff7b00;
}

.blue{
    background:#eaf2ff;
    color:#0d6efd;
}

.red{
    background:#ffeaea;
    color:#dc3545;
}

.yellow{
    background:#fff8e5;
    color:#f39c12;
}

.green{
    background:#e9fff2;
    color:#198754;
}

.purple{
    background:#f3ecff;
    color:#6f42c1;
}
.gold{
    background:#fff8e1;
    color:#f39c12;
}

/* ===================================
CHART
=================================== */
.chart-box{
    background:white;
    border-radius:28px;
    padding:28px;
    box-shadow:0 6px 18px rgba(0,0,0,0.04);
    border:1px solid #eef2f7;
    height:100%;
}

.chart-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.chart-header h4{
    font-weight:700;
    margin-bottom:5px;
}

.chart-badge{
    background:#fff3e8;
    color:#ff7b00;
    padding:8px 16px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
}

.chart-container{
    position:relative;
    height:350px;
}

/* ===================================
INCOME CARD
=================================== */
.income-card{
    background:white;
    border-radius:25px;
    padding:24px;
    box-shadow:0 6px 18px rgba(0,0,0,0.04);
    border:1px solid #eef2f7;
    margin-bottom:20px;
    transition:0.3s;
}

.income-card:hover{
    transform:translateY(-4px);
}

.income-card small{
    color:#7f8c8d;
}

.income-card h4{
    margin-top:10px;
    font-size:28px;
    font-weight:700;
}

.income-footer{
    margin-top:18px;
    padding-top:18px;
    border-top:1px solid #eef2f7;
    display:flex;
    justify-content:space-between;
}

.income-tag{
    padding:8px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}

.tag-orange{
    background:#fff3e8;
    color:#ff7b00;
}

.tag-blue{
    background:#eaf2ff;
    color:#0d6efd;
}

/* ===================================
TRANSAKSI
=================================== */
.transaction-box{
    margin-top:25px;
    background:white;
    border-radius:28px;
    padding:28px;
    box-shadow:0 6px 18px rgba(0,0,0,0.04);
    border:1px solid #eef2f7;
}

.transaction-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.transaction-header h4{
    font-weight:700;
}

.transaction-count{
    background:#fff3e8;
    color:#ff7b00;
    padding:9px 16px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
}

.table{
    border-collapse:separate;
    border-spacing:0 12px;
}

.table thead th{
    background:#fff8f1;
    color:#ff7b00;
    border:none;
    padding:16px;
    font-size:14px;
}

.table tbody tr{
    background:white;
    box-shadow:0 3px 12px rgba(0,0,0,0.03);
    transition:0.3s;
}

.table tbody tr:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
}

.table td{
    border:none;
    padding:18px 15px;
    vertical-align:middle;
}

.status{
    background:#e9fff2;
    color:#198754;
    padding:8px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:700;
}

/* ===================================
RESPONSIVE
=================================== */
@media(max-width:992px){

    .sidebar{
        width:100%;
        height:auto;
        position:relative;
    }

    .content{
        margin-left:0;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }

}

</style>

</head>

<body>

<!-- ===================================
SIDEBAR
=================================== -->
<div class="sidebar">

    <div class="logo">

        <i class="bi bi-building"></i>
        MITRA AZAM

    </div>

    <ul class="sidebar-menu">

        <li>
            <a href="dashboard.php">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="barang.php">
                <i class="bi bi-box-seam"></i>
                Data Barang
            </a>
        </li>

        <li>
            <a href="tambah_barang.php">
                <i class="bi bi-plus-circle"></i>
                Tambah Barang
            </a>
        </li>

        <li>
            <a href="laporan.php">
                <i class="bi bi-file-earmark-text"></i>
                Laporan
            </a>
        </li>

        <li>
            <a href="barang_masuk.php">
                <i class="bi bi-box-arrow-in-down"></i>
                Barang Masuk
            </a>
        </li>

        <li>
            <a href="laba_rugi.php">
                <i class="bi bi-cash-stack"></i>
                Laba Rugi
            </a>
        </li>

        <li>
            <a href="manajemen_user.php">
                <i class="bi bi-people"></i>
                Manajemen User
            </a>
        </li>
        
        <li>
            <a href="setting.php">

                <i class="bi bi-gear"></i>
                Setting

            </a>
        </li>

        <li>
            <a href="../auth/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </li>

    </ul>

</div>

<!-- ===================================
CONTENT
=================================== -->
<div class="content">

    <!-- TOPBAR -->
    <div class="topbar">

        <div>

            <h2>Dashboard Admin</h2>

            <p>Sistem Informasi Toko Bangunan Mitra Azam</p>

        </div>

        <div class="profile-admin">

            <i class="bi bi-person-circle"></i>

            <?= htmlspecialchars($_SESSION['nama']); ?>

        </div>

    </div>

    <!-- CARD -->
    <div class="row g-4">

        <div class="col-md-6 col-lg-4">

            <a href="barang.php" class="dashboard-card">

                <div class="card-flex">

                    <div>

                        <h6>Total Barang</h6>
                        <h3><?= $total_barang; ?></h3>

                    </div>

                    <div class="icon-box orange">

                        <i class="bi bi-box-seam"></i>

                    </div>

                </div>

            </a>

        </div>

       <div class="col-md-6 col-lg-4">

            <a href="laporan.php" class="dashboard-card">

                <div class="card-flex">

                    <div>

                        <h6>Total Transaksi</h6>
                        <h3><?= $total_transaksi; ?></h3>

                    </div>

                    <div class="icon-box blue">

                        <i class="bi bi-receipt"></i>

                    </div>

                </div>

            </a>

        </div>

        <div class="col-md-6 col-lg-4">

            <a href="barang.php?filter=habis" class="dashboard-card">

                <div class="card-flex">

                    <div>

                        <h6>Stok Habis</h6>
                        <h3><?= $stok_habis; ?></h3>

                    </div>

                    <div class="icon-box red">

                        <i class="bi bi-x-circle"></i>

                    </div>

                </div>

            </a>

        </div>

        <div class="col-md-6 col-lg-4">

            <a href="barang.php?filter=menipis" class="dashboard-card">

                <div class="card-flex">

                    <div>

                        <h6>Stok Menipis</h6>
                        <h3><?= $stok_menipis; ?></h3>

                    </div>

                    <div class="icon-box yellow">

                        <i class="bi bi-exclamation-circle"></i>

                    </div>

                </div>

            </a>

        </div>

        <div class="col-md-6 col-lg-4">

            <div class="dashboard-card">

                <div class="card-flex">

                    <div>

                        <h6>Keuntungan Hari Ini</h6>

                        <h3>
                            Rp <?= number_format($keuntungan_hari_ini,0,',','.'); ?>
                        </h3>

                    </div>

                    <div class="icon-box green">

                        <i class="bi bi-cash-stack"></i>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-lg-4">

            <div class="dashboard-card">

                <div class="card-flex">

                    <div>

                        <h6>Keuntungan Bulan</h6>

                        <h3>
                            Rp <?= number_format($keuntungan_bulan,0,',','.'); ?>
                        </h3>

                    </div>

                    <div class="icon-box purple">

                        <i class="bi bi-graph-up"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

        <div class="col-md-6 col-lg-4">

            <div class="dashboard-card">

                <div class="card-flex">

                    <div>

                        <h6>Keuntungan Tahunan</h6>

                        <h3>
                            Rp <?= number_format($keuntungan_tahun,0,',','.'); ?>
                        </h3>

                    </div>

                    <div class="icon-box gold">

                    <i class="bi bi-calendar3"></i>

                    </div>

                </div>

            </div>

        </div>

    <!-- GRAFIK -->
    <div class="row mt-4 g-4">

        <div class="col-lg-8">

            <div class="chart-box">

                <div class="chart-header">

                    <div>

                        <h4>

                            <i class="bi bi-bar-chart-line-fill text-warning"></i>
                            Grafik Penjualan

                        </h4>

                        <small class="text-muted">

                            Statistik penjualan 7 hari terakhir

                        </small>

                    </div>

                    <div class="chart-badge">

                        Penjualan Aktif

                    </div>

                </div>

                <div class="chart-container">

                    <canvas id="grafikPenjualan"></canvas>

                </div>

            </div>

        </div>

        <!-- INCOME -->
        <div class="col-lg-4">

            <div class="income-card">

                <small>Pendapatan Hari Ini</small>

                <h4>

                    Rp <?= number_format($pendapatan_hari_ini,0,',','.'); ?>

                </h4>

                <div class="income-footer">

                    <div>

                        <small>Keuntungan</small>

                        <div class="fw-bold">

                            Rp <?= number_format($keuntungan_hari_ini,0,',','.'); ?>

                        </div>

                    </div>

                    <div>

                        <span class="income-tag tag-orange">

                            Hari Ini

                        </span>

                    </div>

                </div>

            </div>

            <div class="income-card">

                <small>Pendapatan Bulan Ini</small>

                <h4>

                    Rp <?= number_format($pendapatan_bulan,0,',','.'); ?>

                </h4>

                <div class="income-footer">

                    <div>

                        <small>Keuntungan</small>

                        <div class="fw-bold">

                            Rp <?= number_format($keuntungan_bulan,0,',','.'); ?>

                        </div>

                    </div>

                    <div>

                        <span class="income-tag tag-blue">

                            Bulanan

                        </span>

                    </div>

                </div>

            </div>

            <div class="income-card">

                <small>Pendapatan Tahun Ini</small>

                <h4>

                    Rp <?= number_format($pendapatan_tahun,0,',','.'); ?>

                </h4>

                <div class="income-footer">

                    <div>

                        <small>Keuntungan</small>

                        <div class="fw-bold">

                            Rp <?= number_format($keuntungan_tahun,0,',','.'); ?>

                        </div>

                    </div>

                    <div>

                        <span class="income-tag tag-orange">

                            Tahunan

                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- TRANSAKSI -->
    <div class="transaction-box">

        <div class="transaction-header">

            <div>

                <h4>

                    <i class="bi bi-clock-history text-warning"></i>
                    Transaksi Terbaru

                </h4>

                <small class="text-muted">

                    Data transaksi terbaru toko

                </small>

            </div>

            <div class="transaction-count">

                <?= $total_transaksi; ?> Transaksi

            </div>

        </div>

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>

                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Bayar</th>
                        <th>Kembalian</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                $no = 1;

                while($t = mysqli_fetch_assoc($transaksi)):

                ?>

                <tr>

                    <td><?= $no++; ?></td>

                    <td>

                        <div class="fw-bold">

                            <?= date('d M Y', strtotime($t['tanggal'])); ?>

                        </div>

                        <small class="text-muted">

                            <?= date('H:i', strtotime($t['tanggal'])); ?>

                        </small>

                    </td>

                    <td class="fw-bold text-success">

                        Rp <?= number_format($t['total_harga'],0,',','.'); ?>

                    </td>

                    <td>

                        Rp <?= number_format($t['bayar'],0,',','.'); ?>

                    </td>

                    <td>

                        Rp <?= number_format($t['kembali'],0,',','.'); ?>

                    </td>

                    <td>

                        <span class="status">

                            Berhasil

                        </span>

                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ===================================
CHART JS MODERN
=================================== -->
<script>

const ctx =
document.getElementById(
'grafikPenjualan'
).getContext('2d');

// GRADIENT MODERN
const gradient =
ctx.createLinearGradient(
0,
0,
0,
350
);

gradient.addColorStop(
0,
'rgba(255,123,0,0.35)'
);

gradient.addColorStop(
1,
'rgba(255,123,0,0.02)'
);

new Chart(ctx, {

    type:'line',

    data:{

        labels:
        <?= json_encode($label_grafik); ?>,

        datasets:[{

            label:'Penjualan',

            data:
            <?= json_encode($data_grafik); ?>,

            borderColor:'#ff7b00',

            backgroundColor:gradient,

            fill:true,

            tension:0.45,

            borderWidth:4,

            pointRadius:6,

            pointHoverRadius:8,

            pointBackgroundColor:'#ffffff',

            pointBorderColor:'#ff7b00',

            pointBorderWidth:3

        }]
    },

    options:{

        responsive:true,

        maintainAspectRatio:false,

        interaction:{
            intersect:false,
            mode:'index'
        },

        plugins:{

            legend:{

                display:true,

                labels:{
                    color:'#555',
                    usePointStyle:true,
                    pointStyle:'circle',
                    padding:20
                }

            },

            tooltip:{

                backgroundColor:'#ffffff',

                titleColor:'#333',

                bodyColor:'#666',

                borderColor:'#eee',

                borderWidth:1,

                padding:14,

                displayColors:false,

                callbacks:{

                    label:function(context){

                        return ' Rp ' +
                        context.raw.toLocaleString('id-ID');

                    }

                }

            }

        },

        scales:{

            x:{

                grid:{
                    display:false
                },

                ticks:{
                    color:'#888',
                    font:{
                        size:12
                    }
                }

            },

            y:{

                beginAtZero:true,

                grid:{
                    color:'rgba(0,0,0,0.05)',
                    drawBorder:false
                },

                ticks:{

                    color:'#888',

                    callback:function(value){

                        return 'Rp ' +
                        value.toLocaleString('id-ID');

                    }

                }

            }

        }

    }

});

</script>

</body>
</html>