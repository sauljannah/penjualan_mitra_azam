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

// Ambil tema dari session (default light)
$current_tema = $_SESSION['tema'] ?? 'light';

// ======================================
// INTEGRASI DARK MODE DARI DATABASE
// ======================================
$queryGlobalSetting = mysqli_query($conn, "SELECT tema FROM setting LIMIT 1");
if ($queryGlobalSetting) {
    $globalSetting = mysqli_fetch_assoc($queryGlobalSetting);
    $tema_sistem = $globalSetting['tema'] ?? 'light';

    if ($tema_sistem !== $current_tema) {
        $_SESSION['tema'] = $tema_sistem;
        $current_tema = $tema_sistem;
    }
}

// ============================
// PROSES SIMPAN DATA
// ============================
if (isset($_POST['simpan'])) {
    $tanggal    = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $kategori   = mysqli_real_escape_string($conn, $_POST['kategori']);
    $jumlah     = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query = "INSERT INTO pengeluaran (tanggal, kategori, jumlah, keterangan) VALUES ('$tanggal', '$kategori', '$jumlah', '$keterangan')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Biaya operasional berhasil disimpan!'); window.location='laba_rugi.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan data: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="<?= $current_tema ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Biaya Operasional</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
            transition: background 0.3s, color 0.3s;
        }

        /* ================= DARK MODE ================= */
        [data-bs-theme="dark"] body {
            background: #0f172a;
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .card {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9;
        }
        [data-bs-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }
        [data-bs-theme="dark"] .navbar {
            background: #1e293b !important;
        }
        [data-bs-theme="dark"] .offcanvas {
            background: linear-gradient(180deg, #1e40af, #1e3a8a) !important;
        }
        [data-bs-theme="dark"] .submenu-container {
            background-color: #334155 !important;
        }
        [data-bs-theme="dark"] .submenu-link {
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        .content {
            padding: 25px;
            margin-top: 75px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }

        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 10px 15px;
        }

        /* ================= SIDEBAR / OFFCANVAS ================= */
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
            width:45px;
            height:45px;
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
        .menu-item-link i.arrow-icon {
            transition: transform 0.2s;
            font-size: 12px;
        }
        .menu-item-link[aria-expanded="true"] i.arrow-icon {
            transform: rotate(180deg);
        }
    </style>
</head>

<body>

<!-- NAVBAR ATAS -->
<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand d-flex align-items-center me-auto ms-2 fw-bold text-primary" href="dashboard.php">
      <i class="bi bi-shop me-2"></i> MITRA AZAM
    </a>
    
    <!-- Tombol Toggle Tema -->
    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-flex align-items-center gap-2 me-3" id="themeToggleBtn" type="button">
        <i class="bi <?= $current_tema == 'dark' ? 'bi-moon-stars-fill text-warning' : 'bi-sun-fill text-warning'; ?>"></i>
        <span class="small fw-semibold d-none d-md-inline"><?= $current_tema == 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
    </button>
  </div>
</nav>

<!-- SIDEBAR / OFFCANVAS -->
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
            <img src="../assets/admin/<?= htmlspecialchars($_SESSION['foto']); ?>" alt="Profil">
        <?php else: ?>
            <div class="d-flex justify-content-center align-items-center w-100 h-100 bg-secondary">
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
            <a href="data_hutang.php" class="menu-item-link">
                <span><i class="bi bi-credit-card menu-icon"></i> Data Hutang Customer</span>
            </a>
        </div>

        <!-- Menu Laporan dengan Submenu Laba Rugi & Biaya Operasional -->
        <div class="mb-1">
            <button class="menu-item-link" type="button" data-bs-toggle="collapse" data-bs-target="#menuLaporan" aria-expanded="true">
                <span><i class="bi bi-file-earmark-text menu-icon"></i> Laporan</span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </button>
            <div class="collapse show" id="menuLaporan">
                <div class="submenu-container">
                    <a href="laporan.php" class="submenu-link"><i class="bi bi-file-earmark-spreadsheet"></i> Ringkasan Laporan</a>
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

<!-- KONTEN UTAMA -->
<div class="content">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary me-3 fs-4">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">Tambah Biaya Operasional</h4>
                    <p class="text-muted mb-0 small">Formulir pencatatan pengeluaran atau beban operasional toko.</p>
                </div>
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Kategori Biaya</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-tag"></i></span>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Gaji Karyawan">Gaji Karyawan</option>
                            <option value="Listrik & Air">Listrik & Air</option>
                            <option value="Transportasi">Transportasi</option>
                            <option value="Biaya Perawatan & Perbaikan">Biaya Perawatan & Perbaikan</option>
                            <option value="Penyusutan Peralatan">Penyusutan Peralatan</option>
                            <option value="Biaya Lain-lain">Biaya Lain-lain</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">Rp</span>
                        <input type="number" name="jumlah" class="form-control" placeholder="Contoh: 50000" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan detail keterangan pengeluaran (opsional)..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <a href="laba_rugi.php" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-2"></i> Kembali
                    </a>
                    <button type="submit" name="simpan" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i> Simpan Biaya
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function syncThemeWithSession(theme) {
    fetch('update_theme.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'tema=' + theme
    }).catch(err => console.error('Gagal sinkronisasi tema:', err));
}

document.getElementById('themeToggleBtn').addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-bs-theme', newTheme);
    
    const icon = document.querySelector('#themeToggleBtn i');
    const text = document.querySelector('#themeToggleBtn span');

    if (newTheme === 'dark') {
        icon.className = "bi bi-moon-stars-fill text-warning";
        if(text) text.textContent = "Dark Mode";
    } else {
        icon.className = "bi bi-sun-fill text-warning";
        if(text) text.textContent = "Light Mode";
    }

    syncThemeWithSession(newTheme);
});
</script>
</body>
</html>