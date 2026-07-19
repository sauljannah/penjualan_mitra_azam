<?php
session_start();

// ============================
// SET TIMEZONE WIT (WAKTU INDONESIA TIMUR)
// ============================
date_default_timezone_set('Asia/Jayapura');

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
// FILTER
// =====================================
$filter = $_GET['filter'] ?? '';
$cari = $_GET['cari'] ?? '';

// Query Data Hutang
$where = "
metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
";


/*
|--------------------------------------------------------------------------
| FILTER TANGGAL
|--------------------------------------------------------------------------
*/

if ($filter == 'hariini') {

    $where .= " AND DATE(jatuh_tempo)=CURDATE()";

} elseif ($filter == 'mendekati') {

    $where .= "
    AND jatuh_tempo BETWEEN DATE_ADD(CURDATE(),INTERVAL 1 DAY)
    AND DATE_ADD(CURDATE(),INTERVAL 3 DAY)
    ";

} elseif ($filter == 'terlambat') {

    $where .= "
    AND jatuh_tempo < CURDATE()
    ";

}


/*
|--------------------------------------------------------------------------
| FILTER NAMA CUSTOMER
|--------------------------------------------------------------------------
*/

if (!empty($cari)) {

    $cari = mysqli_real_escape_string($conn, $cari);

    $where .= "
    AND nama_customer LIKE '%$cari%'
    ";

}


/*
|--------------------------------------------------------------------------
| QUERY AKHIR
|--------------------------------------------------------------------------
*/

$sql = "
SELECT *
FROM penjualan
WHERE $where
ORDER BY jatuh_tempo ASC
";

$query_hutang = mysqli_query($conn, $sql);

if (!$query_hutang) {
    die("Query Error : " . mysqli_error($conn));
}

// Total hutang aktif
$q_total = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
");
$total_hutang = mysqli_fetch_assoc($q_total)['total'];


// Hari ini
$q_hariini = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND DATE(jatuh_tempo)=CURDATE()
");
$total_hariini = mysqli_fetch_assoc($q_hariini)['total'];


// Mendekati
$q_mendekati = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND jatuh_tempo BETWEEN DATE_ADD(CURDATE(),INTERVAL 1 DAY)
AND DATE_ADD(CURDATE(),INTERVAL 3 DAY)
");
$total_mendekati = mysqli_fetch_assoc($q_mendekati)['total'];


// Terlambat
$q_terlambat = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM penjualan
WHERE metode_pembayaran='Hutang'
AND status_pembayaran='Belum Lunas'
AND jatuh_tempo < CURDATE()
");
$total_terlambat = mysqli_fetch_assoc($q_terlambat)['total'];


// Judul Filter

if ($filter == 'hariini') {
    $judul = "Hutang Jatuh Tempo Hari Ini";
}

if ($filter == 'mendekati') {
    $judul = "Hutang Mendekati Jatuh Tempo";
}

if ($filter == 'terlambat') {
    $judul = "Hutang Lewat Jatuh Tempo";
}

?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Data Hutang Customer</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
    padding:25px;
}

.header-card{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:white;
    border-radius:25px;
    padding:30px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.active-card{

    border:3px solid #2563eb;

}

.stat-card{

    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
    transition:.3s;
    cursor:pointer;

}

.stat-card:hover{

    transform:translateY(-8px);
    box-shadow:0 10px 25px rgba(0,0,0,.15);

}

.stat-card{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
    transition:.3s;
}

.stat-card:hover{
    transform:translateY(-5px);
}

.stat-card h6{
    color:#6c757d;
    margin-bottom:10px;
}

.stat-card h2{
    font-weight:bold;
}

.filter-btn{
    border-radius:12px;
    padding:10px 18px;
    margin-right:8px;
    margin-bottom:10px;
    font-weight:600;
}

.card{
    border:none;
    border-radius:25px;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
}

.card-header{
    padding:20px;
}

.table-card{
    overflow:hidden;
}

.table th{
    background:#eff6ff;
    color:#1d4ed8;
    font-weight:600;
}

.table td{
    vertical-align:middle;
}

.badge-terlambat{
    background:#dc3545;
    color:white;
    font-size:11px;
    padding:6px 10px;
}

.badge-belum{
    background:#ffc107;
    color:black;
    padding:8px 14px;
    border-radius:30px;
    font-weight:600;
}

*{
    font-family:'Poppins',sans-serif;
}

body{
    background:#f1f5f9;
    overflow-x:hidden;
    padding-top:70px;
}


/* SIDEBAR */

.offcanvas{
    background:linear-gradient(
        180deg,
        #2563eb,
        #1e3a8a
    ) !important;

    color:white;
    width:290px !important;
    border-right:none;
}


.sidebar-header-custom{
    padding:25px 20px 10px 20px;
}


.logo{
    font-size:24px;
    font-weight:700;
    color:white;
    display:flex;
    align-items:center;
    gap:10px;
}


.sidebar-profile{
    background:rgba(0,0,0,.15);
    border-radius:16px;
    padding:15px;
    margin:15px;

    display:flex;
    align-items:center;
    gap:12px;
}


.profile-avatar{

    width:45px;
    height:45px;

    border-radius:50%;

    background:rgba(255,255,255,.2);
    border:2px solid rgba(255,255,255,.6);

    display:flex;
    justify-content:center;
    align-items:center;

    color:white;
    font-size:20px;

}


.profile-info h6{
    margin:0;
    font-size:14px;
    font-weight:600;
    color:white;
}


.profile-info span{
    font-size:12px;
    color:rgba(255,255,255,.75);
}


.sidebar-nav-container{
    padding:5px 15px 20px 15px;
}


.sidebar-nav-container a{

    display:flex;
    align-items:center;

    color:rgba(255,255,255,.9);

    text-decoration:none;

    padding:14px 18px;
    margin-bottom:10px;

    border-radius:14px;

    transition:.2s ease;
    font-weight:500;

}


.sidebar-nav-container a:hover,
.sidebar-nav-container a.active{

    background:rgba(255,255,255,.18);
    color:white;

    transform:translateX(4px);

}


.sidebar-nav-container i{
    margin-right:12px;
    font-size:18px;
}

/* TOMBOL LUNASI MODERN */
.btn-lunasi {
    background: linear-gradient(135deg, #10b981, #34d399);
    border: none;
    color: white;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 12px;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-lunasi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    color: white;
}

</style>

</head>
<body>

<nav class="navbar bg-white fixed-top shadow-sm"
style="height:65px;">

<div class="container-fluid px-4
d-flex align-items-center
justify-content-start gap-3">


<button
class="btn btn-primary
d-flex align-items-center gap-2"

type="button"

data-bs-toggle="offcanvas"
data-bs-target="#sidebarKasir">

<i class="bi bi-list fs-5"></i>

</button>


<a class="navbar-brand fw-bold text-primary
d-flex align-items-center gap-2
m-0 p-0"

href="dashboard.php">

<i class="bi bi-shop"></i>
MITRA AZAM

</a>

</div>

</nav>


<div class="offcanvas offcanvas-start"
tabindex="-1"
id="sidebarKasir">


<div class="sidebar-header-custom
d-flex justify-content-between
align-items-center">


<div class="logo">

<i class="bi bi-shop"></i>
MITRA AZAM

</div>


<button
type="button"
class="btn-close btn-close-white"
data-bs-dismiss="offcanvas">

</button>

</div>



<div class="sidebar-profile">

<div class="profile-avatar">

<i class="bi bi-person-fill"></i>

</div>


<div class="profile-info">

<h6>

<?= htmlspecialchars($_SESSION['nama']); ?>

</h6>

<span>

<i class="bi bi-circle-fill text-success"
style="font-size:7px;"></i>

<?= ucfirst($_SESSION['level']); ?>

</span>

</div>

</div>



<div class="offcanvas-body p-0">

<div class="sidebar-nav-container">


<a href="dashboard.php">

<i class="bi bi-house-door-fill"></i>

Dashboard

</a>



<a href="transaksi.php">

<i class="bi bi-cart-fill"></i>

Transaksi

</a>



<a href="data_hutang.php"
class="active">

<i class="bi bi-people-fill"></i>

Data Hutang Customer

</a>



<a href="riwayat_transaksi.php">

<i class="bi bi-clock-history"></i>

Riwayat Transaksi

</a>


<hr class="text-white-50 my-3">


<a href="../auth/logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>


</div>

</div>

</div>

<div class="container-fluid mt-4">
    <div class="header-card mb-4">

<h2>
    <i class="bi bi-credit-card"></i>
    Data Hutang Customer
    </h2>

    <p class="mb-0">
    Daftar piutang customer yang belum lunas.
    </p>

</div>

<div class="row g-4 mb-4">

    <!-- TOTAL HUTANG -->
    <div class="col-md-3">
        <a href="data_hutang.php?filter=&cari=<?= urlencode($cari); ?>"
        class="text-decoration-none">

            <div class="stat-card">

                <h6>Total Hutang</h6>
                <h2><?= $total_hutang ?></h2>

            </div>

        </a>
    </div>


    <!-- HARI INI -->
    <div class="col-md-3">
        <a href="data_hutang.php?filter=hariini&cari=<?= urlencode($cari); ?>"
        class="text-decoration-none">

            <div class="stat-card <?= $filter == 'hariini' ? 'active-card' : ''; ?>">

                <h6>Hari Ini</h6>
                <h2><?= $total_hariini ?></h2>

            </div>

        </a>
    </div>


    <!-- MENDEKATI -->
    <div class="col-md-3">
        <a href="data_hutang.php?filter=mendekati&cari=<?= urlencode($cari); ?>"
        class="text-decoration-none">

<div class="stat-card <?= $filter == 'mendekati' ? 'active-card' : ''; ?>">
                <h6>Mendekati</h6>
                <h2><?= $total_mendekati ?></h2>

            </div>

        </a>
    </div>


    <!-- TERLAMBAT -->
    <div class="col-md-3">
        <a href="data_hutang.php?filter=terlambat&cari=<?= urlencode($cari); ?>"
        class="text-decoration-none">

<div class="stat-card <?= $filter == 'terlambat' ? 'active-card' : ''; ?>">
                <h6>Terlambat</h6>
                <h2><?= $total_terlambat ?></h2>

            </div>

        </a>
    </div>

</div>




    

<div class="card mb-4">

<div class="card-body">

<form method="GET">

<div class="row g-3 align-items-center">

<div class="col-md-8">

<input type="text"
name="cari"
class="form-control"
placeholder="Cari nama customer..."
value="<?= htmlspecialchars($cari); ?>">

</div>


<div class="col-md-2">

<input type="hidden"
name="filter"
value="<?= $filter; ?>">

<button type="submit"
class="btn btn-primary w-100">

<i class="bi bi-search"></i>
Cari

</button>

</div>


<div class="col-md-2">

    <a href="data_hutang.php"
    class="btn btn-secondary w-100">

Reset

</a>

</div>

</div>

</form>

</div>

</div>
    <!-- TABEL -->

 <!-- TABEL DATA HUTANG -->

<div class="card table-card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center flex-wrap">

            <h5 class="mb-0">
                <i class="bi bi-table me-2 text-primary"></i>
                Data Hutang

            </h5>

            <span class="badge bg-primary fs-6">
                <?= mysqli_num_rows($query_hutang); ?> Data
            </span>

        </div>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover align-middle">

            <thead>

                <tr>
                    <th>No Nota</th>
                    <th>Nama Customer</th>
                    <th>Tanggal Transaksi</th>
                    <th>Jatuh Tempo</th>
                    <th>Total Hutang</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>

            </thead>

            <tbody>

            <?php if(mysqli_num_rows($query_hutang) < 1): ?>

                <tr>

                    <td colspan="7" class="text-center py-5 text-muted">

                        <i class="bi bi-check-circle fs-2 d-block mb-2"></i>

                        Tidak ada data hutang customer.

                    </td>

                </tr>

            <?php else: ?>

                <?php while($row = mysqli_fetch_assoc($query_hutang)): ?>

                <?php
                $is_overdue =
                date('Y-m-d') > $row['jatuh_tempo'];
                ?>

                <tr>
                    <td>
                        <strong>
                            #<?= $row['id_penjualan']; ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['nama_customer']); ?>
                    </td>

                    <td>
                        <?= date(
                            'd M Y',
                            strtotime($row['tanggal'])
                        ); ?>
                    </td>

                    <td>
                        <?= date(
                            'd M Y',
                            strtotime($row['jatuh_tempo'])
                        ); ?>

                        <?php if($is_overdue): ?>

                            <br>

                            <span class="badge badge-terlambat">

                                TERLAMBAT

                            </span>

                        <?php endif; ?>
                    </td>

                    <td>
                        <strong class="text-primary">
                            Rp <?= number_format(
                                $row['total_harga'],
                                0,
                                ',',
                                '.'
                            ); ?>
                        </strong>
                    </td>

                    <td>
                        <span class="badge badge-belum">
                            Belum Lunas
                        </span>
                    </td>

                    <!-- TOMBOL LUNASI -->
                    <td class="text-center">
                        <button onclick="konfirmasiLunasi(<?= $row['id_penjualan']; ?>)" 
                                class="btn btn-lunasi btn-sm">
                            <i class="bi bi-check-circle-fill"></i> Lunasi Sekarang
                        </button>
                    </td>

                </tr>

                <?php endwhile; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- MODAL KONFIRMASI MODERN -->
<div class="modal fade" id="konfirmasiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title text-success">
          <i class="bi bi-shield-check"></i> Konfirmasi Pelunasan
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <i class="bi bi-question-circle-fill text-warning" style="font-size: 3.5rem;"></i>
        <h5 class="mt-3">Apakah Anda yakin ingin melunasi hutang ini?</h5>
        <p class="text-muted">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
        <a id="btnLunasiConfirm" href="#" class="btn btn-success px-4">
          <i class="bi bi-check-circle"></i> Ya, Lunasi
        </a>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Fungsi Konfirmasi dengan Modal
function konfirmasiLunasi(id) {
    const modal = new bootstrap.Modal(document.getElementById('konfirmasiModal'));
    const confirmBtn = document.getElementById('btnLunasiConfirm');
    
    confirmBtn.href = 'lunasi_hutang.php?id=' + id;
    modal.show();
}
</script>

</body>
</html>