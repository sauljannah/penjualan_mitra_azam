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
                alert('Gagal update data barang: " . mysqli_error($conn) . "');
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
            INSERT INTO barang (kode_barang, nama_barang, harga_beli, harga_jual, stok, stok_minimum, tanggal) 
            VALUES ('$kode', '$nama', '$beli', '$jual', '$stok', '$minimum', NOW())
        ");

        if($simpan){
            echo "
            <script>
                alert('Barang baru berhasil disimpan!');
                window.location='barang.php';
            </script>
            ";
        } else {
            echo "
            <script>
                alert('Gagal menyimpan barang baru: " . mysqli_error($conn) . "');
                window.history.back();
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

        .content{
            padding: 25px;
            margin-top: 75px; 
        }

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
            border-color:#0d6efd;
            box-shadow:0 0 0 0.2rem rgba(13,110,253,0.2);
        }

        /* Badge Tambahan untuk Keuntungan */
        .profit-badge {
            font-size: 13px;
            font-weight: 600;
            margin-top: 6px;
            display: inline-block;
        }

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

        .offcanvas {
            background: linear-gradient(180deg, #0d6efd, #0a46a6) !important;
            color: #ffffff;
            width: 290px !important;
            border-right: none;
        }
        .sidebar-header-custom {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        .profile-section {
            padding: 15px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            margin: 10px 15px;
        }
        .profile-img {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            color: white;
        }
        .profile-info h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: white;
        }
        .profile-info span {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.75);
        }
        
        .sidebar-nav-container {
            padding: 10px 15px;
        }
        .menu-item-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }
        .menu-item-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }
        .menu-item-link i.menu-icon {
            font-size: 18px;
            margin-right: 12px;
        }
        
        .submenu-container {
            background-color: #f1f3f5;
            border-radius: 10px;
            margin: 5px 0 10px 0;
            padding: 6px 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
        }
        .submenu-link {
            display: flex;
            align-items: center;
            padding: 10px 20px 10px 40px;
            color: #333333;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .submenu-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: #0d6efd;
        }
        .submenu-link.active {
            color: #0d6efd;
            font-weight: 600;
            background-color: rgba(13, 110, 253, 0.08);
        }
        .submenu-link i {
            font-size: 16px;
            margin-right: 12px;
            color: #555;
        }
        
        .menu-item-link[aria-expanded="true"] i.arrow-icon {
            transform: rotate(180deg);
        }
        .menu-item-link i.arrow-icon {
            transition: transform 0.2s;
            font-size: 12px;
        }

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
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
  
  <div class="sidebar-header-custom d-flex justify-content-between align-items-center">
    <span class="fs-5 fw-bold text-white d-flex align-items-center gap-2">
        <i class="bi bi-shop"></i> MITRA AZAM
    </span>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="profile-section d-flex align-items-center gap-3">
    <div class="profile-img">
        <i class="bi bi-person-fill"></i>
    </div>
    <div class="profile-info">
        <h6><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></h6>
        <span>
            <i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i> 
            <?= htmlspecialchars(ucfirst($_SESSION['level'] ?? 'Kasir')); ?>
        </span>
    </div>
  </div>

  <div class="offcanvas-body p-0">
    <div class="sidebar-nav-container">
        
        <div class="mb-1">
            <a href="dashboard.php" class="menu-item-link">
                <span><i class="bi bi-speedometer2 menu-icon"></i> Dashboard</span>
            </a>
        </div>
        
        <div class="mb-1">
            <button class="menu-item-link" type="button" data-bs-toggle="collapse" data-bs-target="#menuBarang" aria-expanded="true">
                <span><i class="bi bi-box-seam menu-icon"></i> Data Barang</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse show" id="menuBarang">
                <div class="submenu-container">
                    <a href="barang.php" class="submenu-link"><i class="bi bi-list-ul"></i> Semua Barang</a>
                    <a href="tambah_barang.php" class="submenu-link active"><i class="bi bi-plus-circle"></i> Tambah Barang</a>
                    <a href="stok_barang_masuk.php" class="submenu-link"><i class="bi bi-journal-arrow-down"></i> Stok Barang Masuk</a>
                    <a href="riwayat_barang_masuk.php" class="submenu-link"><i class="bi bi-download"></i> Riwayat Barang Masuk</a>
                </div>
            </div>
        </div>
        
        <div class="mb-1">
            <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuLaporan" aria-expanded="false">
                <span><i class="bi bi-file-earmark-text menu-icon"></i> Laporan</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse" id="menuLaporan">
                <div class="submenu-container">
                    <a href="laporan.php" class="submenu-link"><i class="bi bi-file-earmark-spreadsheet"></i> Ringkasan Laporan</a>
                    <a href="laba_rugi.php" class="submenu-link"><i class="bi bi-cash-coin"></i> Laba Rugi</a>
                </div>
            </div>
        </div>
        
        <div class="mb-1">
            <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuSetting" aria-expanded="false">
                <span><i class="bi bi-gear menu-icon"></i> Setting</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse" id="menuSetting">
                <div class="submenu-container">
                    <a href="setting.php" class="submenu-link"><i class="bi bi-sliders"></i> Pengaturan Umum</a>
                    
                    <?php if ($_SESSION['level'] == 'admin'): ?>
                    <a href="../admin/manajemen_user.php" class="submenu-link"><i class="bi bi-people"></i> Manajemen User</a>
                    <?php endif; ?>
                    
                    <hr class="my-1 text-muted">
                    <a href="../auth/logout.php" class="submenu-link text-danger fw-semibold" >
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </a>
                </div>
            </div>
        </div>

    </div>
  </div>
</div>

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
                        <input type="number" id="harga_beli" name="harga_beli" class="form-control" placeholder="Masukkan Harga Beli" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" id="harga_jual" name="harga_jual" class="form-control" placeholder="Masukkan Harga Jual" required>
                        <div id="info_keuntungan" class="profit-badge text-muted">Keuntungan: 0%</div>
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

<script>
    const hargaBeliInput = document.getElementById('harga_beli');
    const hargaJualInput = document.getElementById('harga_jual');
    const infoKeuntungan = document.getElementById('info_keuntungan');

    function hitungPersenKeuntungan() {
        const beli = parseFloat(hargaBeliInput.value) || 0;
        const jual = parseFloat(hargaJualInput.value) || 0;

        if (beli > 0 && jual > 0) {
            const untung = jual - beli;
            const persen = (untung / beli) * 180; // Rumus: (Untung / Harga Beli) * 100%
            
            if (jual >= beli) {
                infoKeuntungan.innerHTML = `<span class="text-success"><i class="bi bi-graph-up-arrow"></i> Keuntungan: +${persen.toFixed(2)}% (+Rp ${untung.toLocaleString('id-ID')})</span>`;
            } else {
                infoKeuntungan.innerHTML = `<span class="text-danger"><i class="bi bi-graph-down-arrow"></i> Rugi: ${persen.toFixed(2)}% (Harga jual < harga beli)</span>`;
            }
        } else {
            infoKeuntungan.innerHTML = `<span class="text-muted">Keuntungan: 0%</span>`;
        }
    }

    // Jalankan fungsi setiap kali user mengetik sesuatu di field harga beli atau harga jual
    hargaBeliInput.addEventListener('input', hitungPersenKeuntungan);
    hargaJualInput.addEventListener('input', hitungPersenKeuntungan);
</script>

</body>
</html>