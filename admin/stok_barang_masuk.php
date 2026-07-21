<?php
session_start();

// ============================
// SET TIMEZONE WIT (WAKTU INDONESIA TIMUR)
// ============================
date_default_timezone_set('Asia/Jayapura');

require_once '../config/koneksi.php';
require_once '../config/load_theme.php';

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

// Ambil tema dari session
$current_tema = $_SESSION['tema'] ?? 'light';

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
<html lang="id" data-bs-theme="<?= $current_tema ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang Masuk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f6fb;
            font-family: 'Segoe UI', sans-serif;
            color: #212529;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* TEMA GELAP (DARK MODE) */
        body.dark-theme { 
            background: #0f172a !important; 
            color: #f8fafc !important; 
        }
        
        body.dark-theme .navbar,
        body.dark-theme .card { 
            background: #1e293b !important; 
            border-color: #334155 !important; 
            color: #f8fafc !important; 
        }

        body.dark-theme .text-muted { 
            color: #94a3b8 !important; 
        }

        body.dark-theme .form-label {
            color: #e2e8f0 !important;
        }

        body.dark-theme .form-control, 
        body.dark-theme .form-select {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }

        body.dark-theme .form-control:focus, 
        body.dark-theme .form-select:focus {
            background-color: #0f172a !important;
            color: #ffffff !important;
            border-color: #0d6efd !important;
        }

        /* Tom Select Dark Mode Styling */
        body.dark-theme .ts-control {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }
        body.dark-theme .ts-dropdown {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }
        body.dark-theme .ts-dropdown .option {
            color: #cbd5e1 !important;
        }
        body.dark-theme .ts-dropdown .option.active {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        /* CONTENT */
        .content {
            padding: 25px;
            margin-top: 75px;
        }

        /* CARD */
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        /* FORM */
        .form-label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #444;
        }

        .form-control, .form-select {
            border-radius: 15px;
            padding: 12px;
            border: 1px solid #ddd;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.2);
        }

        /* Kustomisasi khusus input Tom Select */
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
        .btn-custom {
            padding: 12px 25px;
            border: none;
            border-radius: 15px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-save {
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            color: white;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5c636a;
            color: white;
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
            width: 55px;
            height: 55px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            border: 2px solid rgba(255,255,255,.5);
        }
        .profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
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
        body.dark-theme .submenu-container {
            background-color: #111827 !important;
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
        body.dark-theme .submenu-link {
            color: #cbd5e1 !important;
        }
        .submenu-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: #0d6efd;
        }
        body.dark-theme .submenu-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #60a5fa;
        }
        .submenu-link.active {
            color: #0d6efd;
            font-weight: 600;
            background-color: rgba(13, 110, 253, 0.08);
        }
        body.dark-theme .submenu-link.active {
            color: #60a5fa !important;
            background-color: rgba(96, 165, 250, 0.15);
        }
        .submenu-link i {
            font-size: 16px;
            margin-right: 12px;
            color: #555;
        }
        body.dark-theme .submenu-link i {
            color: #94a3b8;
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

<body class="<?= ($tema_sistem ?? 'light') == 'dark' ? 'dark-theme' : ''; ?>">

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
   
    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-flex align-items-center gap-2 me-3" id="themeToggleBtn">
        <i class="bi <?= $current_tema == 'dark' ? 'bi-moon-stars-fill text-warning' : 'bi-sun-fill text-warning'; ?>"></i>
        <span class="small fw-semibold d-none d-md-inline"><?= $current_tema == 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
    </button>
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
     <?php if (!empty($_SESSION['foto']) && file_exists("../assets/admin/" . $_SESSION['foto'])) { ?>
        <img src="../assets/admin/<?= htmlspecialchars($_SESSION['foto']); ?>">
    <?php } else { ?>
        <i class="bi bi-person-fill text-dark fs-4"></i>
    <?php } ?>
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
        
        <!-- DATA HUTANG -->
        <div class="mb-1">
            <a href="data_hutang.php" class="menu-item-link">
                <span><i class="bi bi-credit-card menu-icon"></i> Data Hutang Customer</span>
            </a>
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
                    <a href="../auth/logout.php" class="submenu-link text-danger fw-semibold">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </a>
                </div>
            </div>
        </div>

    </div>
  </div>
</div>

<div class="content">

    <div class="card mb-4">
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

    // Sinkronisasi tema tambahan lewat script jika diperlukan
    const isDark = "<?= isset($tema_sistem) ? $tema_sistem : 'light'; ?>" === 'dark';
    if (isDark) {
        document.body.classList.add('dark-theme');
    }

    // Dark Mode
    function initTheme() {
        const savedTheme = localStorage.getItem('theme') || '<?= $current_tema ?>';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    
        const btn = document.getElementById('themeToggleBtn');
        if (!btn) return;
        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');
        if (savedTheme === 'dark') {
            icon.className = "bi bi-moon-stars-fill text-warning";
            if(text) text.textContent = "Dark Mode";
        } else {
            icon.className = "bi bi-sun-fill text-warning";
            if(text) text.textContent = "Light Mode";
        }
    }

    document.getElementById('themeToggleBtn').addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        const icon = document.querySelector('#themeToggleBtn i');
        const text = document.querySelector('#themeToggleBtn span');
        if (newTheme === 'dark') {
            icon.className = "bi bi-moon-stars-fill text-warning";
            if(text) text.textContent = "Dark Mode";
        } else {
            icon.className = "bi bi-sun-fill text-warning";
            if(text) text.textContent = "Light Mode";
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        initTheme();
        toggleFilterInput();
    });
</script>

</body>
</html>