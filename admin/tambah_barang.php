<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if(!isset($_SESSION['level'])){
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// SIMPAN DATA BARANG
// ======================================
if(isset($_POST['simpan'])){

    // Mengamankan inputan string dari celah SQL Injection
    $kode     = mysqli_real_escape_string($conn, trim(htmlspecialchars($_POST['kode_barang'])));
    $nama     = mysqli_real_escape_string($conn, trim(htmlspecialchars($_POST['nama_barang'])));
    $beli     = (int) $_POST['harga_beli'];
    $jual     = (int) $_POST['harga_jual'];
    $stok     = (int) $_POST['stok'];
    $minimum  = (int) $_POST['stok_minimum'];

    // ======================================
    // VALIDASI INPUT
    // ======================================
    if(empty($kode) || empty($nama)){
        echo "
        <script>
            alert('Kode dan Nama Barang wajib diisi!');
            window.history.back();
        </script>
        ";
        exit;
    }

    // ======================================
    // VALIDASI HARGA
    // ======================================
    if($jual < $beli){
        echo "
        <script>
            alert('Harga jual tidak boleh lebih kecil dari harga beli!');
            window.history.back();
        </script>
        ";
        exit;
    }

    // ======================================
    // CEK BARANG SUDAH ADA
    // ======================================
    $cek_barang = mysqli_query($conn, "SELECT * FROM barang WHERE kode_barang = '$kode' AND nama_barang = '$nama'");

    // ======================================
    // JIKA BARANG SUDAH ADA (TAMBAH STOK + UPDATE HARGA)
    // ======================================
    if(mysqli_num_rows($cek_barang) > 0){
        $data_lama = mysqli_fetch_assoc($cek_barang);
        $stok_baru = $data_lama['stok'] + $stok;

        $update = mysqli_query($conn, "
            UPDATE barang SET 
                harga_beli   = '$beli',
                harga_jual   = '$jual',
                stok         = '$stok_baru',
                stok_minimum = '$minimum'
            WHERE kode_barang = '$kode' AND nama_barang = '$nama'
        ");

        if($update){
            echo "
            <script>
                alert('Stok berhasil ditambahkan dan harga diperbarui');
                window.location='barang.php';
            </script>
            ";
        }else{
            echo "
            <script>
                alert('Gagal update data barang');
                window.history.back();
            </script>
            ";
        }

    }else{
        // ======================================
        // CEK KODE DIPAKAI BARANG LAIN
        // ======================================
        $cek_kode_lain = mysqli_query($conn, "SELECT * FROM barang WHERE kode_barang = '$kode'");

        if(mysqli_num_rows($cek_kode_lain) > 0){
            echo "
            <script>
                alert('Kode barang sudah digunakan untuk barang lain!');
                window.history.back();
            </script>
            ";
            exit;
        }

        // ======================================
        // SIMPAN BARANG BARU
        // ======================================
        $simpan = mysqli_query($conn, "
            INSERT INTO barang 
            VALUES(NULL, '$kode', '$nama', '$beli', '$jual', '$stok', '$minimum', NOW())
        ");

        if($simpan){
            echo "
            <script>
                window.location='barang.php';
            </script>
            ";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body{
            margin:0;
            background:#f4f6f9;
            font-family:Arial,sans-serif;
        }

        /* Penyesuaian konten agar tidak tertutup Navbar Fixed-Top */
        .content{
            padding: 25px;
            margin-top: 75px; 
        }

        /* ===================================
        CARD FORM
        =================================== */
        .form-card{
            background:white;
            border-radius:25px;
            box-shadow:0 8px 20px rgba(0,0,0,0.05);
            overflow:hidden;
        }

        .form-header{
            background: linear-gradient(135deg, #2563eb, #1e40af);
            padding:20px;
            color:white;
        }

        .form-body{
            padding:30px;
        }

        /* ===================================
        FORM INPUT
        =================================== */
        .form-label{
            font-weight:bold;
            margin-bottom:8px;
            color:#444;
        }

        .form-control{
            border-radius:15px;
            padding:12px;
            border:1px solid #ddd;
            transition:0.3s;
        }

        .form-control:focus{
            border-color:#ff7b00;
            box-shadow:0 0 0 0.2rem rgba(255,123,0,0.2);
        }

        /* ===================================
        BUTTON
        =================================== */
        .btn-custom{
            padding:12px 25px;
            border:none;
            border-radius:15px;
            font-weight:bold;
            transition:0.3s;
        }

        .btn-save{
            background:linear-gradient(135deg, #198754, #20c997);
            color:white;
        }

        .btn-save:hover{
            transform:translateY(-2px);
            color:white;
        }

        .btn-back{
            background:#6c757d;
            color:white;
        }

        .btn-back:hover{
            background:#5c636a;
            color:white;
        }
    </style>
</head>
<body>

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
    
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasNavbarLabel">
          <i class="bi bi-shop"></i> MITRA AZAM
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      
      <div class="offcanvas-body">
        <ul class="navbar-nav justify-content-start flex-grow-1 pe-3">
          
          <li class="nav-item mb-2">
            <a class="nav-link fw-semibold" href="dashboard.php">
              <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
            </a>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle active fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-box-seam me-2 text-primary"></i> Data Barang
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="barang.php"><i class="bi bi-list-ul me-2"></i> Semua Barang</a></li><li><a class="dropdown-item" href="tambah_barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Tambah Stok Masuk</a></li>
              <li><a class="dropdown-item active" href="tambah_barang.php"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a></li>
              <li><a class="dropdown-item" href="barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Barang Masuk</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-file-earmark-text me-2 text-primary"></i> Laporan
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="laporan.php"><i class="bi bi-file-earmark-ruled me-2"></i> Ringkasan Laporan</a></li>
              <li><a class="dropdown-item" href="laba_rugi.php"><i class="bi bi-cash-stack me-2"></i> Laba Rugi</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown mb-2">
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-gear me-2 text-primary"></i> Setting
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="setting.php"><i class="bi bi-sliders me-2"></i> Pengaturan Umum</a></li>
              <li><a class="dropdown-item" href="manajemen_user.php"><i class="bi bi-people me-2"></i> Manajemen User</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger fw-bold" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </li>

        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="content">

    <div class="card mb-4 bg-white border-0 shadow-sm rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-1">Tambah Barang</h2>
                <p class="text-muted mb-0">Form input data barang toko bangunan</p>
            </div>
            <div>
                <h5>
                    <i class="bi bi-person-circle text-primary"></i>
                    <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
                </h5>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-header">
            <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Form Tambah Barang</h4>
        </div>

        <div class="form-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="kode_barang" class="form-control" placeholder="Masukkan Kode Barang" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" placeholder="Masukkan Nama Barang" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Harga Beli</label>
                        <input type="number" name="harga_beli" class="form-control" placeholder="Masukkan Harga Beli" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" name="harga_jual" class="form-control" placeholder="Masukkan Harga Jual" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Jumlah Stok</label>
                        <input type="number" name="stok" class="form-control" placeholder="Masukkan Jumlah Stok" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Stok Minimum</label>
                        <input type="number" name="stok_minimum" class="form-control" placeholder="Minimal Stok" required>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" name="simpan" class="btn-custom btn-save">
                        <i class="bi bi-save me-2"></i>Simpan Barang
                    </button>
                    <a href="barang.php" class="btn-custom btn-back text-decoration-none d-flex align-items-center">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>