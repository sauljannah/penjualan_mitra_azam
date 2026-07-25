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

// Ambil tema dari session (default light)
$current_tema = $_SESSION['tema'] ?? 'light';

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

    if (empty($kode) || empty($nama)) {
        echo "
        <script>
            alert('Data tidak boleh kosong');
        </script>
        ";
    } else {
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
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="<?= $current_tema ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body{
            background:#f4f6f9;
            overflow-x:hidden;
            font-family:Arial,sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        /* ================= DARK MODE ================= */
        [data-bs-theme="dark"] body { 
            background: #0f172a; 
            color: #ffffff; 
        }
        [data-bs-theme="dark"] .card { 
            background: #1e293b; 
            color: #ffffff; 
            box-shadow: 0 5px 15px rgba(255,255,255,0.03);
        }
        [data-bs-theme="dark"] .card-body { color: #ffffff; }
        [data-bs-theme="dark"] .table { color: #ffffff; }
        [data-bs-theme="dark"] .table-bordered { border-color: #334155; }
        [data-bs-theme="dark"] .table > :not(caption) > * > * { background-color: #1e293b; color: #fff; }
        [data-bs-theme="dark"] .table-hover tbody tr:hover { background: #334155 !important; }
        [data-bs-theme="dark"] .text-muted { color: #cbd5e1 !important; }
        
        /* Navbar & Offcanvas Dark Mode */
        [data-bs-theme="dark"] .navbar,
        [data-bs-theme="dark"] .offcanvas { 
            background-color: #1e293b !important; 
            color: #ffffff !important;
            border-color: #334155 !important;
        }
        [data-bs-theme="dark"] .navbar-brand,
        [data-bs-theme="dark"] .nav-link,
        [data-bs-theme="dark"] .offcanvas-title { 
            color: #ffffff !important; 
        }
        [data-bs-theme="dark"] .navbar-toggler-icon {
            filter: invert(1);
        }
        [data-bs-theme="dark"] .dropdown-menu { 
            background: #0f172a; 
            border: 1px solid #334155; 
        }
        [data-bs-theme="dark"] .dropdown-item { color: #ffffff; }
        [data-bs-theme="dark"] .dropdown-item:hover { background: #334155; }
        [data-bs-theme="dark"] .dropdown-divider { border-color: #334155; }

        /* Penyesuaian konten agar tidak tertimpa Navbar Fixed-Top */
        .content{
            padding: 25px;
            margin-top: 75px; 
        }

        .card{
            border:none;
            border-radius:20px;
            box-shadow:0 5px 15px rgba(0,0,0,0.05);
            transition:.3s;
            cursor:pointer;
        }

        .card:hover{
            transform:translateY(-5px);
            box-shadow:0 12px 25px rgba(0,0,0,.15);
        }

        .btn{
            border-radius:10px;
        }

        .table tbody tr:hover{
            background:#f1f1f1;
        }

        .badge{
            padding:8px 12px;
        }

        /* ========================================================
           SIDEBAR IMPLEMENTASI TEMA BIRU ELEGAN & STRUKTUR DROPDOWN
           ======================================================== */
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
        .submenu-link.text-danger i {
            color: #dc3545;
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
        <button class="navbar-toggler me-3" type="button" id="sidebarToggle">
        <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
        <i class="bi bi-shop me-2"></i> MITRA AZAM
        </a>
        
        <!-- Tombol Dark Mode -->
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-flex align-items-center gap-2 ms-auto" id="themeToggleBtn">
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
          <?php if (!empty($_SESSION['foto']) && file_exists("../assets/admin/" . $_SESSION['foto'])): ?>
                        <img src="../assets/admin/<?= htmlspecialchars($_SESSION['foto']); ?>" class="user-avatar" alt="Profil">
                    <?php else: ?>
                        <div class="user-avatar-default">
                            <i class="bi bi-person text-white"></i>
                        </div>
                    <?php endif; ?>
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
                <button class="menu-item-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuBarang" aria-expanded="false">
                    <span><i class="bi bi-box-seam menu-icon"></i> Data Barang</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </button>
                <div class="collapse" id="menuBarang">
                    <div class="submenu-container">
                        <a href="barang.php" class="submenu-link"><i class="bi bi-list-ul"></i> Semua Barang</a>
                        <a href="tambah_barang.php" class="submenu-link"><i class="bi bi-plus-circle"></i> Tambah Barang</a>
                        <a href="stok_barang_masuk.php" class="submenu-link"><i class="bi bi-journal-arrow-down"></i> Stok Barang Masuk</a>
                        <a href="riwayat_barang_masuk.php" class="submenu-link"><i class="bi bi-download"></i> Riwayat Barang Masuk</a>
                    </div>
                </div>
            </div>
            
            <div class="mb-1">
                <button class="menu-item-link" type="button" data-bs-toggle="collapse" data-bs-target="#menuLaporan" aria-expanded="true">
                    <span><i class="bi bi-file-earmark-text menu-icon"></i> Laporan</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </button>
                <div class="collapse show" id="menuLaporan">
                    <div class="submenu-container">
                        <a href="laporan.php" class="submenu-link active"><i class="bi bi-file-earmark-spreadsheet"></i> Ringkasan Laporan</a>
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

    <div class="content" id="mainContent">
        
        <div class="card shadow mb-4">
            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.3rem;">Edit Barang</h4>
                        <p class="text-muted small mb-0 d-none d-sm-block">Sistem Penjualan Toko Mitra Azam</p>
                    </div>
                </div>
                <div>
                    <h6 class="mb-0 text-secondary fw-semibold">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
                    </h6>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Toggle Sidebar Hamburger
const sidebarToggle = document.getElementById('sidebarToggle');
const offcanvas = document.getElementById('offcanvasNavbar');

if (sidebarToggle && offcanvas) {
    sidebarToggle.addEventListener('click', () => {
        const bsOffcanvas = new bootstrap.Offcanvas(offcanvas);
        bsOffcanvas.toggle();
    });
}

// Dark Mode Script
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
    initTheme();
});

document.addEventListener("DOMContentLoaded", initTheme);
</script>
</body>
</html>