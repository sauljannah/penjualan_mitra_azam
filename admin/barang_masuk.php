<?php
session_start();
require_once '../config/koneksi.php';

/* =========================
   FIX ERROR $conn
========================= */
if (!isset($conn) || !$conn) {
    die("❌ Koneksi database gagal. Pastikan file config/koneksi.php benar dan variabel \$conn tersedia.");
}

/* =========================
   CEK LOGIN
========================= */
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   DATA BARANG MASUK
========================= */
$query = mysqli_query($conn, "
SELECT bm.*, b.nama_barang, b.kode_barang
FROM barang_masuk bm
JOIN barang b ON bm.id_barang = b.id_barang
ORDER BY bm.id_masuk DESC
");

if (!$query) {
    die("❌ Query gagal: " . mysqli_error($conn));
}

/* =========================
   TOTAL BARANG MASUK
========================= */
$q_total = mysqli_query($conn, "
SELECT COALESCE(SUM(jumlah),0) AS total
FROM barang_masuk
");

$d_total = mysqli_fetch_assoc($q_total);
$total_masuk = $d_total['total'] ?? 0;

/* =========================
   TOTAL TRANSAKSI
========================= */
$q_count = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM barang_masuk
");

$d_count = mysqli_fetch_assoc($q_count);
$total_transaksi = $d_count['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Barang Masuk</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

/* ===== GLOBAL ===== */
body{
    background:#f4f6fb;
    font-family:'Segoe UI',sans-serif;
}

/* ===== SIDEBAR ===== */
.sidebar{
    width:260px;
    height:100vh;
    position:fixed;
    background:linear-gradient(180deg,#ff7b00,#ff5200);
    padding:20px;
    color:#fff;
}

.sidebar h3{
    text-align:center;
    font-weight:800;
    margin-bottom:30px;
}

.sidebar a{
    display:block;
    color:#fff;
    text-decoration:none;
    padding:12px;
    border-radius:12px;
    margin-bottom:8px;
    transition:0.3s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.2);
    transform:translateX(5px);
}

/* ===== CONTENT ===== */
.content{
    margin-left:260px;
    padding:30px;
}

/* ===== CARD ===== */
.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(0,0,0,0.05);
}

/* ===== HEADER ===== */
.card-header{
    background:linear-gradient(135deg,#ff7b00,#ff5200) !important;
    color:white !important;
    font-weight:600;
}

/* ===== TABLE ===== */
.table tbody tr:hover{
    background:#fff7f0;
}

.badge{
    padding:7px 10px;
}

/* ===== BUTTON ===== */
.btn-warning{
    background:linear-gradient(135deg,#ff7b00,#ff5200);
    border:none;
    color:white;
}

.btn-warning:hover{
    opacity:0.9;
}

</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h3><i class="bi bi-building"></i> MITRA AZAM</h3>

    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="barang.php"><i class="bi bi-box-seam"></i> Data Barang</a>
    <a href="barang_masuk.php" style="background:rgba(255,255,255,0.25);">
        <i class="bi bi-box-arrow-in-down"></i> Barang Masuk
    </a>
    <a href="tambah_barang_masuk.php"><i class="bi bi-plus-circle"></i> Tambah</a>
    <a href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>

</div>

<!-- CONTENT -->
<div class="content">

    <!-- HEADER -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between flex-wrap">

            <div>
                <h3>Riwayat Barang Masuk</h3>
                <small class="text-muted">Manajemen stok gudang</small>
            </div>

            <div class="fw-bold">
                <i class="bi bi-person-circle"></i>
                <?= htmlspecialchars($_SESSION['nama']); ?>
            </div>

        </div>
    </div>

    <!-- STAT -->
    <div class="row mb-4">

        <div class="col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3><?= $total_transaksi; ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3><?= $total_masuk; ?></h3>
                    <p>Total Barang Masuk</p>
                </div>
            </div>
        </div>

    </div>

    <!-- BUTTON -->
    <a href="tambah_barang_masuk.php" class="btn btn-warning mb-3">
        <i class="bi bi-plus-circle"></i> Tambah Barang Masuk
    </a>

    <!-- TABLE -->
    <div class="card">

        <div class="card-header">
            Data Barang Masuk
        </div>

        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-warning text-center">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Harga Beli</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>

                <?php if(mysqli_num_rows($query) > 0): ?>
                <?php $no = 1; ?>

                <?php while($d = mysqli_fetch_assoc($query)): ?>

                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= date('d-m-Y', strtotime($d['tanggal'])); ?></td>
                        <td><?= $d['kode_barang']; ?></td>
                        <td><?= $d['nama_barang']; ?></td>
                        <td><span class="badge bg-primary"><?= $d['jumlah']; ?></span></td>
                        <td>Rp <?= number_format($d['harga_beli'],0,',','.'); ?></td>
                        <td><?= $d['keterangan']; ?></td>
                    </tr>

                <?php endwhile; ?>
                <?php else: ?>

                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Tidak ada data
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>