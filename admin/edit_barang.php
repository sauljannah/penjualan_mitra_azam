<?php
session_start();

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK LOGIN
// ======================================
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// CEK ID BARANG
// ======================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "
    <script>
        alert('ID Barang tidak ditemukan');
        window.location='barang.php';
    </script>
    ";
    exit;
}

// ======================================
// AMBIL ID & DATA BARANG (Prepared Statement)
// ======================================
$id = intval($_GET['id']);

$stmt = mysqli_prepare($conn, "SELECT * FROM barang WHERE id_barang = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

// ======================================
// CEK DATA ADA / TIDAK
// ======================================
if (mysqli_num_rows($query) == 0) {
    echo "
    <script>
        alert('Data barang tidak ditemukan');
        window.location='barang.php';
    </script>
    ";
    exit;
}

// AMBIL DATA
$d = mysqli_fetch_assoc($query);

// ======================================
// PROSES UPDATE
// ======================================
if (isset($_POST['update'])) {
    $kode    = trim($_POST['kode_barang']);
    $nama    = trim($_POST['nama_barang']);
    $beli    = intval($_POST['harga_beli']);
    $jual    = intval($_POST['harga_jual']);
    $stok    = intval($_POST['stok']);
    $minimum = intval($_POST['stok_minimum']);

    // ======================================
    // VALIDASI
    // ======================================
    if (empty($kode) || empty($nama)) {
        echo "
        <script>
            alert('Data tidak boleh kosong');
        </script>
        ";
    } else {
        // ======================================
        // QUERY UPDATE DENGAN PREPARED STATEMENT
        // ======================================
        $stmt_update = mysqli_prepare(
            $conn, 
            "UPDATE barang SET 
                kode_barang  = ?, 
                nama_barang  = ?, 
                harga_beli   = ?, 
                harga_jual   = ?, 
                stok         = ?, 
                stok_minimum = ? 
             WHERE id_barang = ?"
        );
        
        mysqli_stmt_bind_param($stmt_update, "ssiiiii", $kode, $nama, $beli, $jual, $stok, $minimum, $id);
        $update = mysqli_stmt_execute($stmt_update);

        if ($update) {
            echo "
            <script>
                alert('Data barang berhasil diupdate');
                window.location='barang.php';
            </script>
            ";
            exit;
        } else {
            echo "
            <script>
                alert('Gagal update data');
            </script>
            ";
            echo mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
        }

        /* ======================================
        SIDEBAR STYLE
        ====================================== */
        .sidebar {
            height: 100vh;
            background: #0056e2;
            color: white;
            position: fixed;
            width: 16.666667%;
            overflow-y: auto;
        }

        /* Kotak Profil User */
        .user-profile-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }
        .user-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 12px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #2ec4b6;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        /* Navigasi Utama */
        .sidebar a, .sidebar .btn-toggle {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: 0.2s;
            width: 100%;
            border: none;
            background: transparent;
            font-weight: 500;
        }

        .sidebar a:hover, .sidebar .btn-toggle:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        /* Submenu Container (Kotak Putih Dropdown) */
        .sidebar-submenu {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 6px;
            margin: 5px 0 12px 0;
        }

        .sidebar-submenu a {
            color: #334155 !important;
            padding: 10px 16px;
            margin-bottom: 2px;
            font-size: 0.95rem;
        }

        .sidebar-submenu a:hover {
            background: #f1f5f9 !important;
            color: #0056e2 !important;
        }

        /* Highlight Sub-menu Aktif */
        .sidebar-submenu a.active-sub {
            background: #e0f2fe !important;
            color: #0284c7 !important;
            font-weight: 600;
        }

        /* Rotasi Ikon Panah Dropdown */
        .btn-toggle::after {
            font-family: "bootstrap-icons";
            content: "\f282";
            transition: transform 0.3s;
            font-size: 0.8rem;
        }
        .btn-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        /* ======================================
        CONTENT & CARDS
        ====================================== */
        .content {
            margin-left: 16.666667%;
            padding: 25px;
        }

        .card {
            border: none;
            border-radius: 20px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px;
        }

        .btn {
            border-radius: 12px;
            padding: 10px 20px;
        }

        @media(max-width:768px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="fw-bold text-white mb-0"><i class="bi bi-shop me-2"></i>MITRA AZAM</h3>
                <i class="bi bi-x-lg text-white d-md-none" style="cursor: pointer;"></i>
            </div>
            
            <div class="user-profile-box">
                <div class="user-avatar">
                    <i class="bi bi-person text-white"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-white text-capitalize"><?= htmlspecialchars($_SESSION['nama'] ?? 'Saul'); ?></h6>
                    <small class="text-white-50 d-flex align-items-center mt-1">
                        <span class="status-dot"></span> <?= htmlspecialchars($_SESSION['level'] ?? 'Admin'); ?>
                    </small>
                </div>
            </div>
            
            <a href="dashboard.php">
                <span class="d-flex align-items-center"><i class="bi bi-speedometer2 me-3"></i> Dashboard</span>
            </a>
            
            <div>
                <button class="btn-toggle" data-bs-toggle="collapse" data-bs-target="#barang-collapse" aria-expanded="true">
                    <span class="d-flex align-items-center"><i class="bi bi-box-seam me-3"></i> Data Barang</span>
                </button>
                <div class="collapse show" id="barang-collapse">
                    <div class="sidebar-submenu">
                        <a href="barang.php"><i class="bi bi-list-ul me-2"></i> Semua Barang</a>
                        <a href="tambah_barang.php"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a>
                        <a href="stok_barang_masuk.php"><i class="bi bi-box-arrow-in-down me-2"></i> Stok Barang Masuk</a>
                        <a href="riwayat_barang_masuk.php"><i class="bi bi-clock-history me-2"></i> Riwayat Barang Masuk</a>
                        <a href="#" class="active-sub d-none"><i class="bi bi-pencil-square me-2"></i> Edit Barang</a>
                    </div>
                </div>
            </div>

            <div>
                <button class="btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#laporan-collapse" aria-expanded="false">
                    <span class="d-flex align-items-center"><i class="bi bi-file-earmark-bar-graph me-3"></i> Laporan</span>
                </button>
                <div class="collapse" id="laporan-collapse">
                    <div class="sidebar-submenu">
                        <a href="laporan.php"><i class="bi bi-file-earmark-text me-2"></i> Ringkasan Laporan</a>
                        <a href="laba_rugi.php"><i class="bi bi-cash-stack me-2"></i> Laba Rugi</a>
                    </div>
                </div>
            </div>

            <div>
                <button class="btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#setting-collapse" aria-expanded="false">
                    <span class="d-flex align-items-center"><i class="bi bi-gear me-3"></i> Setting</span>
                </button>
                <div class="collapse" id="setting-collapse">
                    <div class="sidebar-submenu">
                        <a href="setting.php"><i class="bi bi-sliders me-2"></i> Pengaturan Cepat</a>
                        <a href="manajemen_user.php"><i class="bi bi-people me-2"></i> Manajemen User</a>
                        <a href="../auth/logout.php" class="text-danger fw-bold"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-10 content">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold mb-1">Edit Barang</h3>
                            <p class="text-muted mb-0">Sistem Penjualan Toko Mitra Azam</p>
                        </div>
                        <div>
                            <h5 class="mb-0 text-secondary">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-primary text-white py-3" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square me-2"></i> Form Edit Barang
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Kode Barang</label>
                                <input type="text" name="kode_barang" class="form-control" value="<?= htmlspecialchars($d['kode_barang']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($d['nama_barang']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Harga Beli</label>
                                <input type="number" name="harga_beli" class="form-control" value="<?= htmlspecialchars($d['harga_beli']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Harga Jual</label>
                                <input type="number" name="harga_jual" class="form-control" value="<?= htmlspecialchars($d['harga_jual']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Stok</label>
                                <input type="number" name="stok" class="form-control" value="<?= htmlspecialchars($d['stok']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Stok Minimum</label>
                                <input type="number" name="stok_minimum" class="form-control" value="<?= htmlspecialchars($d['stok_minimum']); ?>" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="update" class="btn btn-primary px-4 me-2">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="barang.php" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>