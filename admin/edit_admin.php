<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK LOGIN ADMIN
// ======================================
if (
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'admin'
) {
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// AMBIL ID USER
// ======================================
$id_user = $_SESSION['id_user'];

// ======================================
// AMBIL DATA ADMIN
// ======================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE id_user = '$id_user'"
);

if (!$query) {
    die(
        "Query Error : " .
        mysqli_error($conn)
    );
}

$admin = mysqli_fetch_assoc($query);

// ======================================
// CEK JIKA DATA TIDAK ADA
// ======================================
if (!$admin) {
    die("Data admin tidak ditemukan");
}

// ======================================
// CEGAH WARNING ARRAY KEY
// ======================================
$nama     = $admin['nama'] ?? '';
$username = $admin['username'] ?? '';
$email    = $admin['email'] ?? '';
$telepon  = $admin['telepon'] ?? '';
$foto     = $admin['foto'] ?? '';

// ======================================
// PROSES UPDATE
// ======================================
if (isset($_POST['simpan'])) {

    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $telepon = mysqli_real_escape_string($conn, trim($_POST['telepon']));

    // ======================================
    // VALIDASI USERNAME DUPLIKAT
    // ======================================
    $cek_username = mysqli_query(
        $conn,
        "SELECT * FROM users WHERE username='$username' AND id_user != '$id_user'"
    );

    if (mysqli_num_rows($cek_username) > 0) {
        echo "
        <script>
            alert('Username sudah digunakan');
            window.location='edit_admin.php';
        </script>
        ";
        exit;
    }

    // ======================================
    // FOLDER FOTO
    // ======================================
    $folder = "../assets/admin/";
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    // ======================================
    // UPLOAD FOTO
    // ======================================
    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['name'] != ""
    ) {
        $nama_file = $_FILES['foto']['name'];
        $tmp       = $_FILES['foto']['tmp_name'];
        $size      = $_FILES['foto']['size'];
        $error     = $_FILES['foto']['error'];

        $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $format = ['jpg', 'jpeg', 'png', 'webp'];

        // VALIDASI FILE
        if ($error !== 0) {
            echo "<script>alert('Gagal upload foto');</script>";
        } elseif (!in_array($ext, $format)) {
            echo "<script>alert('Format foto harus JPG, PNG, JPEG, WEBP');</script>";
        } elseif ($size > 2000000) {
            echo "<script>alert('Ukuran foto maksimal 2MB');</script>";
        } else {
            
            // NAMA FILE BARU
            $nama_baru = "admin_" . time() . "_" . rand(100, 999) . "." . $ext;
            $tujuan = $folder . $nama_baru;

            // PINDAH FILE
            if (move_uploaded_file($tmp, $tujuan)) {
                // HAPUS FOTO LAMA
                if ($foto != "" && file_exists($folder . $foto)) {
                    unlink($folder . $foto);
                }
                $foto = $nama_baru;
            } else {
                echo "<script>alert('Upload foto gagal');</script>";
            }
        }
    }

    // ======================================
    // UPDATE DATABASE
    // ======================================
    $update = mysqli_query(
        $conn,
        "UPDATE users SET
            nama     = '$nama',
            username = '$username',
            email    = '$email',
            telepon  = '$telepon',
            foto     = '$foto'
         WHERE id_user = '$id_user'"
    );

    // ======================================
    // CEK UPDATE & SINKRONISASI SESSION FOTO
    // ======================================
    if ($update) {
        $_SESSION['nama'] = $nama;
        $_SESSION['foto'] = $foto; // Mengupdate session foto secara realtime

        echo "
        <script>
            alert('Profil admin berhasil diperbarui');
            window.location='setting.php';
        </script>
        ";
    } else {
        echo "<script>alert('Gagal memperbarui profil');</script>";
        echo mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f4f7fb;
            overflow-x: hidden;
        }

        /* ======================================
        SIDEBAR STYLE (Biru Modern Berdasarkan Kode Sebelumnya)
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
        
        .user-avatar-container {
            position: relative;
            margin-right: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        
        .user-avatar-default {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
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
            border-radius: 14px;
            padding: 12px;
            border: 1px solid #ddd;
        }

        .foto-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #eee;
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
                <div class="user-avatar-container">
                    <?php if (!empty($_SESSION['foto']) && file_exists("../assets/admin/" . $_SESSION['foto'])): ?>
                        <img src="../assets/admin/<?= htmlspecialchars($_SESSION['foto']); ?>" class="user-avatar" alt="Profil">
                    <?php else: ?>
                        <div class="user-avatar-default">
                            <i class="bi bi-person text-white"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-white text-capitalize"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></h6>
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
                            <h3 class="fw-bold mb-1"><i class="bi bi-person-circle text-primary me-2"></i>Edit Profil Admin</h3>
                            <p class="text-muted mb-0">Kelola informasi data kredensial administrator</p>
                        </div>
                        <div>
                            <h5 class="mb-0 text-secondary">
                                <i class="bi bi-user me-1"></i>
                                <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-primary text-white py-3" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square me-2"></i> Form Edit Profil
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($nama); ?>" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username); ?>" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email); ?>">
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Nomor Telepon</label>
                                <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($telepon); ?>">
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-semibold">Foto Profil</label>
                                <input type="file" name="foto" class="form-control mb-2">
                                <small class="text-muted d-block mb-3">Format JPG, PNG, JPEG, WEBP (Maksimal 2MB)</small>

                                <?php if ($foto != "" && file_exists("../assets/admin/" . $foto)): ?>
                                    <img src="../assets/admin/<?= htmlspecialchars($foto); ?>" class="foto-preview" alt="Pratinjau">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/150" class="foto-preview" alt="Kosong">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-2">
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