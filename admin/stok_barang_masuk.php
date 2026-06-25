<?php
session_start();

// ============================
// SET TIMEZONE WIT (WAKTU INDONESIA TIMUR)
// ============================
date_default_timezone_set('Asia/Jayapura');

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// Mengatur koneksi database agar menggunakan timezone lokal PHP jika didukung server
mysqli_query($conn, "SET time_zone = '" . date('P') . "'");

/* CEK KONEKSI */
if (!isset($conn) || !$conn) {
    die("❌ Koneksi database gagal. Cek config/koneksi.php");
}

/* CEK LOGIN */
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* AMBIL DATA BARANG */
$barang = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang ASC");

if (!$barang) {
    die("Query error barang: " . mysqli_error($conn));
}

/* SIMPAN DATA BARANG MASUK */
if (isset($_POST['simpan'])) {

    $id_barang  = $_POST['id_barang'];
    $jumlah     = (int) $_POST['jumlah'];
    $harga_beli = (int) $_POST['harga_beli'];
    $harga_jual = (int) $_POST['harga_jual']; 
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $get = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang='$id_barang'");
    $data = mysqli_fetch_assoc($get);

    if (!$data) {
        die("Barang tidak ditemukan");
    }

    mysqli_begin_transaction($conn);

    try {
        // INSERT RIWAYAT BARANG MASUK DENGAN HARGA BARU
        mysqli_query($conn, "
            INSERT INTO barang_masuk
            (id_barang, jumlah, harga_beli, harga_jual, keterangan, tanggal)
            VALUES
            ('$id_barang', '$jumlah', '$harga_beli', '$harga_jual', '$keterangan', NOW())
        ");

        // UPDATE STOK + HARGA BELI & HARGA JUAL BARU DI BARANG UTAMA
        mysqli_query($conn, "
            UPDATE barang
            SET 
                stok = stok + $jumlah,
                harga_beli = '$harga_beli',
                harga_jual = '$harga_jual'
            WHERE id_barang = '$id_barang'
        ");

        mysqli_commit($conn);

        echo "<script>
            alert('✔ Barang masuk berhasil disimpan dan harga jual diperbarui');
            window.location='riwayat_barang_masuk.php';
        </script>";
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>
            alert('❌ Gagal menyimpan data');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang Masuk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        body{
            margin:0;
            background:#f4f6fb;
            font-family:'Segoe UI', sans-serif;
        }

        /* CONTENT */
        .content{
            padding: 25px;
            margin-top: 75px;
        }

        /* CARD */
        .card{
            border:none;
            border-radius:18px;
            box-shadow:0 8px 25px rgba(0,0,0,0.05);
        }

        /* FORM */
        .form-label{
            font-weight:bold;
            margin-bottom:8px;
            color:#444;
        }

        .form-control, .form-select{
            border-radius:15px;
            padding:12px;
            border:1px solid #ddd;
            transition:0.3s;
        }

        .form-control:focus, .form-select:focus{
            border-color:#0d6efd;
            box-shadow:0 0 0 0.2rem rgba(13,110,253,0.2);
        }

        /* Kustomisasi khusus input Tom Select agar serasi dengan desain form Anda */
        .ts-control {
            border-radius: 15px !important;
            padding: 12px !important;
            border: 1px solid #ddd !important;
        }
        .ts-wrapper.focus .ts-control {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.2) !important;
        }
        .ts-dropdown {
            border-radius: 12px !important;
            margin-top: 5px;
        }

        /* BUTTON STYLE */
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

        /* SIDEBAR THEME */
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
                    <a href="tambah_barang.php" class="submenu-link"><i class="bi bi-plus-circle"></i> Tambah Barang</a>
                    <a href="stok_barang_masuk.php" class="submenu-link active"><i class="bi bi-journal-arrow-down"></i> Stok Barang Masuk</a>
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
                    <a href="../auth/logout.php" class="submenu-link text-danger fw-semibold" onclick="return confirm('Apakah anda yakin ingin logout?')">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </a>
                </div>
            </div>
        </div>

    </div>
  </div>
</div>

<div class="content">

    <div class="card mb-4 bg-white">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-1">Tambah Stok Barang Masuk</h2>
                <p class="text-muted mb-0">Sinkron otomatis stok + harga beli & jual barang baru</p>
            </div>
            <div class="fw-bold">
                <i class="bi bi-person-circle text-primary"></i>
                <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST">

                <div class="mb-4">
                    <label class="form-label">Nama Barang</label>
                    <select id="select-barang" name="id_barang" class="form-select" required>
                        <option value="">Ketik nama barang di sini...</option>
                        <?php while($b = mysqli_fetch_assoc($barang)) { ?>
                            <option value="<?= $b['id_barang']; ?>">
                                <?= $b['nama_barang']; ?> (Stok: <?= $b['stok']; ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <label class="form-label">Jumlah Masuk</label>
                        <input type="number" name="jumlah" class="form-control" placeholder="0" min="1" required>
                    </div>

                    <div class="col-md-4 mb-4">
                        <label class="form-label">Harga Beli Baru</label>
                        <input type="number" name="harga_beli" class="form-control" placeholder="Rp" min="0" required>
                    </div>

                    <div class="col-md-4 mb-4">
                        <label class="form-label">Harga Jual Baru</label>
                        <input type="number" name="harga_jual" class="form-control" placeholder="Rp" min="0" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Keterangan / Catatan Tambahan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Supplier PT. Jaya Abadi">
                </div>

                <div class="d-flex gap-2 mt-2">
                    <button type="submit" name="simpan" class="btn-custom btn-save flex-fill">
                        <i class="bi bi-save me-2"></i>Simpan Data
                    </button>
                    <a href="riwayat_barang_masuk.php" class="btn-custom btn-back text-decoration-none d-flex align-items-center justify-content-center flex-fill">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

<script>
    // Inisialisasi Tom Select pada dropdown Nama Barang
    new TomSelect("#select-barang", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        },
        placeholder: "Ketik nama barang untuk mencari...",
        allowEmptyOption: false
    });
</script>

</body>
</html>