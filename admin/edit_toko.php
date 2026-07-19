<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if(
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'admin'
){
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// BUAT FOLDER LOGO
// ======================================
if(!is_dir("../assets/logo")){
    mkdir("../assets/logo", 0777, true);
}

// ======================================
// AMBIL DATA TOKO
// ======================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM profil_toko LIMIT 1"
);

if(!$query){
    die(
        "Query Error : " .
        mysqli_error($conn)
    );
}

// ======================================
// JIKA DATA BELUM ADA
// ======================================
if(mysqli_num_rows($query) == 0){
    $insert = mysqli_query(
        $conn,
        "INSERT INTO profil_toko
        (
            nama_toko,
            jenis_usaha,
            alamat,
            telepon,
            email,
            deskripsi,
            logo
        )
        VALUES
        (
            'MITRA AZAM',
            'Toko Bangunan',
            'Alamat Toko',
            '08123456789',
            'mitraazam@gmail.com',
            'Sistem Kasir Modern',
            ''
        )"
    );

    if(!$insert){
        die(
            "Insert Error : " .
            mysqli_error($conn)
        );
    }

    $query = mysqli_query(
        $conn,
        "SELECT * FROM profil_toko LIMIT 1"
    );
}

$toko = mysqli_fetch_assoc($query);

// ======================================
// PROSES UPDATE
// ======================================
if(isset($_POST['simpan'])){

    $nama_toko = mysqli_real_escape_string(
        $conn,
        trim($_POST['nama_toko'])
    );

    $jenis_usaha = mysqli_real_escape_string(
        $conn,
        trim($_POST['jenis_usaha'])
    );

    $alamat = mysqli_real_escape_string(
        $conn,
        trim($_POST['alamat'])
    );

    $telepon = mysqli_real_escape_string(
        $conn,
        trim($_POST['telepon'])
    );

    $email = mysqli_real_escape_string(
        $conn,
        trim($_POST['email'])
    );

    $deskripsi = mysqli_real_escape_string(
        $conn,
        trim($_POST['deskripsi'])
    );

    // ======================================
    // LOGO LAMA
    // ======================================
    $logo = isset($toko['logo']) ? $toko['logo'] : '';

    // ======================================
    // UPLOAD LOGO
    // ======================================
    if(
        isset($_FILES['logo']) &&
        $_FILES['logo']['name'] != ""
    ){
        $nama_file = $_FILES['logo']['name'];
        $tmp       = $_FILES['logo']['tmp_name'];
        $size      = $_FILES['logo']['size'];
        $error     = $_FILES['logo']['error'];

        $ext = strtolower(
            pathinfo(
                $nama_file,
                PATHINFO_EXTENSION
            )
        );

        $format = ['jpg', 'jpeg', 'png', 'webp'];

        // VALIDASI FORMAT
        if(!in_array($ext, $format)){
            echo "
            <script>
                alert('Format logo harus JPG, JPEG, PNG, atau WEBP');
                window.location='edit_toko.php';
            </script>
            ";
            exit;
        }

        // VALIDASI UKURAN
        if($size > 2000000){
            echo "
            <script>
                alert('Ukuran logo maksimal 2MB');
                window.location='edit_toko.php';
            </script>
            ";
            exit;
        }

        // VALIDASI ERROR
        if($error !== 0){
            echo "
            <script>
                alert('Terjadi kesalahan upload file');
                window.location='edit_toko.php';
            </script>
            ";
            exit;
        }

        // NAMA FILE BARU
        $nama_baru = "logo_" . time() . "_" . rand(100,999) . "." . $ext;
        $tujuan = "../assets/logo/" . $nama_baru;

        // UPLOAD FILE
        if(move_uploaded_file($tmp, $tujuan)){
            // HAPUS LOGO LAMA
            if(
                $logo != "" &&
                file_exists("../assets/logo/" . $logo)
            ){
                unlink("../assets/logo/" . $logo);
            }
            $logo = $nama_baru;
        }else{
            echo "
            <script>
                alert('Gagal upload logo');
                window.location='edit_toko.php';
            </script>
            ";
            exit;
        }
    }

    // ======================================
    // UPDATE DATABASE
    // ======================================
    $update = mysqli_query(
        $conn,
        "UPDATE profil_toko SET
            nama_toko   = '$nama_toko',
            jenis_usaha = '$jenis_usaha',
            alamat      = '$alamat',
            telepon     = '$telepon',
            email       = '$email',
            deskripsi   = '$deskripsi',
            logo        = '$logo'
        WHERE id_toko='".$toko['id_toko']."'"
    );

    // ======================================
    // CEK UPDATE
    // ======================================
    if($update){
        echo "
        <script>
            alert('Profil toko berhasil diperbarui');
            window.location='setting.php';
        </script>
        ";
    }else{
        echo "
        <script>
            alert('Gagal update profil toko');
        </script>
        ";
        echo mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Toko</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
        }

        /* ======================================
        SIDEBAR STYLE (Konsisten Biru Modern)
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
        .profile-img{
            width:55px;
            height:55px;
            border-radius:50%;
            overflow:hidden;
            flex-shrink:0;

            display:flex;
            justify-content:center;
            align-items:center;

            background:#fff;
            border:2px solid rgba(255,255,255,.5);
}

        .profile-img img{
            width:100%;
            height:100%;
            object-fit:cover;
            border-radius:50%;
            display:block;
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

        .logo-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 20px;
            border: 4px solid #eee;
            margin-top: 15px;
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
                  <?php if (!empty($_SESSION['foto']) && file_exists("../assets/admin/" . $_SESSION['foto'])): ?>
                        <img src="../assets/admin/<?= htmlspecialchars($_SESSION['foto']); ?>" class="user-avatar" alt="Profil">
                    <?php else: ?>
                        <div class="user-avatar-default">
                            <i class="bi bi-person text-white"></i>
                        </div>
                    <?php endif; ?>
             
                 
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
                <button class="btn-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#barang-collapse" aria-expanded="false">
                    <span class="d-flex align-items-center"><i class="bi bi-box-seam me-3"></i> Data Barang</span>
                </button>
                <div class="collapse" id="barang-collapse">
                    <div class="sidebar-submenu">
                        <a href="barang.php"><i class="bi bi-list-ul me-2"></i> Semua Barang</a>
                        <a href="tambah_barang.php"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a>
                        <a href="stok_masuk.php"><i class="bi bi-box-arrow-in-down me-2"></i> Stok Barang Masuk</a>
                        <a href="riwayat_masuk.php"><i class="bi bi-clock-history me-2"></i> Riwayat Barang Masuk</a>
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
                <button class="btn-toggle" data-bs-toggle="collapse" data-bs-target="#setting-collapse" aria-expanded="true">
                    <span class="d-flex align-items-center"><i class="bi bi-gear me-3"></i> Setting</span>
                </button>
                <div class="collapse show" id="setting-collapse">
                    <div class="sidebar-submenu">
                        <a href="setting.php" class="active-sub"><i class="bi bi-sliders me-2"></i> Pengaturan Cepat</a>
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
                            <h3 class="fw-bold mb-1"><i class="bi bi-shop text-primary me-2"></i>Edit Profil Toko</h3>
                            <p class="text-muted mb-0">Kelola identitas utama Toko Mitra Azam</p>
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
                        <i class="bi bi-pencil-square me-2"></i> Form Edit Toko
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nama Toko</label>
                                <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($toko['nama_toko']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Jenis Usaha</label>
                                <input type="text" name="jenis_usaha" class="form-control" value="<?= htmlspecialchars($toko['jenis_usaha']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Telepon</label>
                                <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($toko['telepon']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($toko['email']); ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Alamat</label>
                                <textarea name="alamat" rows="2" class="form-control"><?= htmlspecialchars($toko['alamat']); ?></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" rows="3" class="form-control"><?= htmlspecialchars($toko['deskripsi']); ?></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Upload Logo</label>
                                <input type="file" name="logo" class="form-control mb-2">
                                <small class="text-muted d-block mb-2">Format JPG, PNG, JPEG, WEBP (Maksimal 2MB)</small>
                                
                                <?php if(!empty($toko['logo'])): ?>
                                    <img src="../assets/logo/<?= $toko['logo']; ?>" class="logo-preview">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/150" class="logo-preview">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="simpan" class="btn btn-primary px-4 me-2">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="setting.php" class="btn btn-outline-secondary px-4">
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