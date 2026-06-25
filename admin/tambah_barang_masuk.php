<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/load_theme.php';


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

/* SIMPAN */
if (isset($_POST['simpan'])) {

    $id_barang  = $_POST['id_barang'];
    $jumlah     = (int) $_POST['jumlah'];
    $harga_beli = (int) $_POST['harga_beli'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $get = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang='$id_barang'");
    $data = mysqli_fetch_assoc($get);

    if (!$data) {
        die("Barang tidak ditemukan");
    }

    $harga_jual = $data['harga_jual'];

    mysqli_begin_transaction($conn);

    try {

        // INSERT RIWAYAT BARANG MASUK
        mysqli_query($conn, "
            INSERT INTO barang_masuk
            (id_barang, jumlah, harga_beli, harga_jual, keterangan, tanggal)
            VALUES
            ('$id_barang', '$jumlah', '$harga_beli', '$harga_jual', '$keterangan', NOW())
        ");

        // UPDATE STOK + HARGA DI BARANG UTAMA
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
            alert('✔ Barang masuk berhasil disimpan');
            window.location='barang_masuk.php';
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

    <style>
        body{
            margin:0;
            background:#f4f6fb;
            font-family:'Segoe UI', sans-serif;
        }
        /* TEMA GELAP (DARK MODE) - OPTIMIZED */
        body.dark-theme { background: #0f172a; color: #191717ac; }
        body.dark-theme .topbar, 
        body.dark-theme .dashboard-card, 
        body.dark-theme .chart-box, 
        body.dark-theme .income-card, 
        body.dark-theme .transaction-box { background: #1e293b; border-color: #334155; color: #ffffff; }
        body.dark-theme .topbar p, 
        body.dark-theme .dashboard-card h6, 
        body.dark-theme .income-card small,
        body.dark-theme .text-muted { color: #cbd5e1 !important; }
        body.dark-theme .table tbody tr { background: #0e0d0dce; color: #020202; box-shadow: 0 3px 12px rgba(0,0,0,0.2); }
        body.dark-theme .table thead th { background: #334155; color: #ff7b00; }
        body.dark-theme .table td { color: #121111a8; }
        /* CONTENT */
        .content{
            padding: 25px;
            margin-top: 75px; /* Jarak aman dari fixed navbar */
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
            border-color:#ff7b00;
            box-shadow:0 0 0 0.2rem rgba(255,123,0,0.2);
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
    </style>
</head>

<body class="<?= ($tema_sistem ?? 'light') == 'dark' ? 'dark-theme' : ''; ?>">


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
              <li><a class="dropdown-item" href="barang.php"><i class="bi bi-list-ul me-2"></i> Semua Barang</a></li>
              <li><a class="dropdown-item" href="tambah_barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Tambah Stok Masuk</a></li>
              <li><a class="dropdown-item" href="tambah_barang.php"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a></li>
              <li><a class="dropdown-item active" href="barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Barang Masuk</a></li>
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

    <div class="card mb-4 bg-white">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-1">Tambah Barang Masuk</h2>
                <p class="text-muted mb-0">Sinkron otomatis stok + harga barang</p>
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
                    <select name="id_barang" class="form-select" required>
                        <option value="">Pilih Barang</option>
                        <?php while($b = mysqli_fetch_assoc($barang)) { ?>
                            <option value="<?= $b['id_barang']; ?>">
                                <?= $b['nama_barang']; ?> (Stok: <?= $b['stok']; ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Jumlah Masuk</label>
                        <input type="number" name="jumlah" class="form-control" placeholder="0" min="1" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Harga Beli Baru</label>
                        <input type="number" name="harga_beli" class="form-control" placeholder="Rp" min="0" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Supplier</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="">
                </div>

                <div class="d-flex gap-2 mt-2">
                    <button type="submit" name="simpan" class="btn-custom btn-save flex-fill">
                        <i class="bi bi-save me-2"></i>Simpan Data
                    </button>
                    <a href="barang_masuk.php" class="btn-custom btn-back text-decoration-none d-flex align-items-center justify-content-center flex-fill">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const isDark = "<?= isset($tema_sistem) ? $tema_sistem : 'light'; ?>" === 'dark';
    if (isDark) {
        document.body.classList.add('dark-theme');
    }
</script>

</body>
</html>