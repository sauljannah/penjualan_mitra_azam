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
// FILTER TANGGAL / PERIODE
// ============================
$periode       = $_POST['periode'] ?? 'semua';
$tanggal_hari  = $_POST['tanggal_hari'] ?? '';
$bulan         = $_POST['bulan'] ?? '';
$tahun_bulan   = $_POST['tahun_bulan'] ?? date('Y');
$tanggal_awal  = $_POST['tanggal_awal'] ?? '';
$tanggal_akhir = $_POST['tanggal_akhir'] ?? '';

$where = "";

// PROSES FILTER DATA BERDASARKAN PILIHAN PERIODE
if (isset($_POST['filter'])) {
    if ($periode == 'harian' && !empty($tanggal_hari)) {
        $where = " WHERE DATE(p.tanggal) = '$tanggal_hari' ";
    } elseif ($periode == 'mingguan' && !empty($tanggal_awal) && !empty($tanggal_akhir)) {
        $where = " WHERE p.tanggal BETWEEN '$tanggal_awal 00:00:00' AND '$tanggal_akhir 23:59:59' ";
    } elseif ($periode == 'bulanan' && !empty($bulan)) {
        $where = " WHERE MONTH(p.tanggal) = '$bulan' AND YEAR(p.tanggal) = '$tahun_bulan' ";
    }
}

// ============================
// QUERY DATA PENJUALAN + NAMA KASIR
// ============================
// DIKEMBALIKAN KE p.id_user (Silakan ganti p.id_user menjadi nama kolom yang tepat jika di database bernama p.id_kasir)
$query = mysqli_query($conn, "
    SELECT p.*, u.nama AS nama_kasir
    FROM penjualan p
    LEFT JOIN users u ON p.id_user = u.id_user
    $where
    ORDER BY p.id_penjualan DESC
");

// VALIDASI QUERY
if (!$query) {
    die("Query Error : " . mysqli_error($conn));
}

// ============================
// AKUMULASI TOTAL PENJUALAN PERIODE
// ============================
$total_penjualan = 0;
$total_penjualan_query = mysqli_query($conn, "
    SELECT SUM(p.total_harga) AS total_penjualan
    FROM penjualan p
    $where
");

if ($total_penjualan_query) {
    $data_penjualan = mysqli_fetch_assoc($total_penjualan_query);
    $total_penjualan = $data_penjualan['total_penjualan'] ?? 0;
}

// ============================
// TOTAL KEUNTUNGAN (DIJAGA AGAR TIDAK TERHAPUS)
// ============================
$total_keuntungan = 0;
$total_keuntungan_query = mysqli_query($conn, "
    SELECT SUM(p.keuntungan) AS total_keuntungan
    FROM penjualan p
    $where
");

if ($total_keuntungan_query) {
    $data_keuntungan = mysqli_fetch_assoc($total_keuntungan_query);
    $total_keuntungan = $data_keuntungan['total_keuntungan'] ?? 0;
}

// ============================
// TOTAL TRANSAKSI
// ============================
$total_transaksi = 0;
$total_transaksi_query = mysqli_query($conn, "
    SELECT COUNT(p.id_penjualan) AS total_transaksi
    FROM penjualan p
    $where
");

if ($total_transaksi_query) {
    $data_transaksi = mysqli_fetch_assoc($total_transaksi_query);
    $total_transaksi = $data_transaksi['total_transaksi'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }
        .content {
            padding: 25px;
            margin-top: 75px;
        }
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }
        .stat-card {
            transition: 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .table tbody tr:hover {
            background: #fff7f0;
        }
        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .form-control, .form-select {
            border-radius: 12px;
            padding: 10px;
        }
        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 26px;
            color: white;
        }
        .bg-orange { background: linear-gradient(135deg, #ff7b00, #ff5200); }
        .bg-green { background: linear-gradient(135deg, #198754, #20c997); }
        .bg-blue { background: linear-gradient(135deg, #296bf9, #142b76); }

        @media print {
            .navbar, .btn, form, .navbar-toggler, .offcanvas, .filter-section {
                display: none !important;
            }
            .content {
                margin-top: 0 !important;
                padding: 0 !important;
            }
            body {
                background: white;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
</head>

<body>

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
  </div>
</nav>

<div class="content">

    <div class="card mb-4 bg-white">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-1">LAPORAN PENJUALAN</h2>
                <p class="text-muted mb-0">Sistem Informasi Toko Bangunan Mitra Azam</p>
            </div>
            <div class="fw-bold">
                <i class="bi bi-person-circle text-primary"></i>
                <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Akumulasi Total Penjualan</span>
                        <h3 class="fw-bold mb-0 text-primary">Rp <?= number_format($total_penjualan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box bg-blue">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Keuntungan</span>
                        <h3 class="fw-bold mb-0 text-success">Rp <?= number_format($total_keuntungan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="icon-box bg-green">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold d-block mb-1">Total Transaksi</span>
                        <h3 class="fw-bold mb-0 text-orange"><?= $total_transaksi; ?></h3>
                    </div>
                    <div class="icon-box bg-orange">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 filter-section">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-funnel me-2"></i>Filter Periode Laporan</h5>
            <form method="POST" id="formFilter">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-bold text-muted">Pilih Periode</label>
                        <select name="periode" id="periode" class="form-select" onchange="toggleFilterInput()" required>
                            <option value="semua" <?= $periode == 'semua' ? 'selected' : ''; ?>>Semua Data</option>
                            <option value="harian" <?= $periode == 'harian' ? 'selected' : ''; ?>>Harian</option>
                            <option value="mingguan" <?= $periode == 'mingguan' ? 'selected' : ''; ?>>Mingguan</option>
                            <option value="bulanan" <?= $periode == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                        </select>
                    </div>

                    <div class="col-md-5 mb-3 filter-input" id="input-harian" style="display: none;">
                        <label class="form-label small fw-bold text-muted">Pilih Tanggal</label>
                        <input type="date" name="tanggal_hari" class="form-control" value="<?= htmlspecialchars($tanggal_hari); ?>">
                    </div>

                    <div class="col-md-5 mb-3 filter-input" id="input-mingguan" style="display: none;">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Tanggal Awal</label>
                                <input type="date" name="tanggal_awal" class="form-control" value="<?= htmlspecialchars($tanggal_awal); ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Tanggal Akhir</label>
                                <input type="date" name="tanggal_akhir" class="form-control" value="<?= htmlspecialchars($tanggal_akhir); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 mb-3 filter-input" id="input-bulanan" style="display: none;">
                        <div class="row">
                            <div class="col-7">
                                <label class="form-label small fw-bold text-muted">Pilih Bulan</label>
                                <select name="bulan" class="form-select">
                                    <option value="">-- Pilih Bulan --</option>
                                    <?php
                                    $nama_bulan = [
                                        1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni",
                                        7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
                                    ];
                                    foreach ($nama_bulan as $num => $name) {
                                        $selected = ($bulan == $num) ? 'selected' : '';
                                        echo "<option value='$num' $selected>$name</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-bold text-muted">Tahun</label>
                                <input type="number" name="tahun_bulan" class="form-control" value="<?= htmlspecialchars($tahun_bulan); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3 d-flex gap-2">
                        <button type="submit" name="filter" class="btn btn-primary flex-fill">
                            <i class="bi bi-search me-2"></i>Filter
                        </button>
                        <button type="button" onclick="window.print()" class="btn btn-success flex-fill">
                            <i class="bi bi-printer me-2"></i>Print
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="padding: 15px;">No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Nama Kasir</th>
                        <th>Total Nilai Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($query) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while($d = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $no++; ?></td>
                            <td class="text-center fw-semibold text-secondary">TRX-<?= str_pad($d['id_penjualan'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td class="text-center"><?= date('d-m-Y H:i', strtotime($d['tanggal'])); ?></td>
                            <td class="text-center">
                                <?php 
                                if (!empty($d['nama_kasir'])) {
                                    echo htmlspecialchars($d['nama_kasir']);
                                } else {
                                    echo '<span class="text-muted italic">ID Kasir: ' . htmlspecialchars($d['id_user'] ?? 'Kosong/NULL') . ' (Belum Terelasi)</span>';
                                }
                                ?>
                            </td>
                            <td class="text-end px-5 fw-bold">Rp <?= number_format($d['total_harga'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-danger py-4 fw-bold">
                            <i class="bi bi-exclamation-circle me-2"></i> Data laporan pada periode tersebut tidak ditemukan atau kosong.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleFilterInput() {
    var periode = document.getElementById('periode').value;
    
    document.querySelectorAll('.filter-input').forEach(function(el) {
        el.style.display = 'none';
        el.querySelectorAll('input, select').forEach(ins => ins.removeAttribute('required'));
    });

    if (periode === 'harian') {
        var div = document.getElementById('input-harian');
        div.style.display = 'block';
        div.querySelector('input').setAttribute('required', 'required');
    } else if (periode === 'mingguan') {
        var div = document.getElementById('input-mingguan');
        div.style.display = 'block';
        div.querySelectorAll('input').forEach(ins => ins.setAttribute('required', 'required'));
    } else if (periode === 'bulanan') {
        var div = document.getElementById('input-bulanan');
        div.style.display = 'block';
        div.querySelector('select').setAttribute('required', 'required');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    toggleFilterInput();
});
</script>
</body>
</html>