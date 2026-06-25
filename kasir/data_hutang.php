<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// =====================================
// PROTEKSI LOGIN (Kasir & Admin Boleh Masuk)
// =====================================
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

// =====================================
// PROSES PELUNASAN HUTANG (UPDATE STATUS)
// =====================================
if (isset($_POST['aksi']) && $_POST['aksi'] == 'lunasi') {
    $id_penjualan = mysqli_real_escape_string($conn, $_POST['id_penjualan']);
    
    // Update status menjadi Lunas agar otomatis hilang dari daftar piutang aktif
    $query_update = mysqli_query($conn, "UPDATE penjualan SET status_pembayaran = 'Lunas' WHERE id_penjualan = '$id_penjualan'");
    
    if ($query_update) {
        $_SESSION['sukses'] = "Hutang berhasil dilunasi dan telah dihapus dari daftar aktif.";
    } else {
        $_SESSION['gagal'] = "Gagal memperbarui status pelunasan: " . mysqli_error($conn);
    }
    header("Location: data_hutang.php");
    exit;
}

// =====================================
// AMBIL DATA HUTANG YANG BELUM LUNAS
// =====================================
$query_hutang = mysqli_query(
    $conn,
    "SELECT * FROM penjualan WHERE metode_pembayaran = 'Hutang' AND status_pembayaran = 'Belum Lunas' ORDER BY jatuh_tempo ASC"
);

if (!$query_hutang) {
    die("Query Error : " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Hutang Customer - MITRA AZAM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *{ font-family:'Poppins',sans-serif; }
        body{ background:#f1f5f9; overflow-x:hidden; padding-top: 70px; }

        /* ======================================
        SIDEBAR MODEREN (OFFCANVAS) - SINKRON TRANSAKSI.PHP
        ====================================== */
        .offcanvas { 
            background: linear-gradient(180deg, #2563eb, #1e3a8a) !important; 
            color: #ffffff; 
            width: 290px !important; 
            border-right: none; 
        }
        .sidebar-header-custom { padding: 25px 20px 10px 20px; }
        .logo{ font-size:24px; font-weight:700; color: white; display: flex; align-items: center; gap: 10px; }
        
        .sidebar-profile {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 16px;
            padding: 15px;
            margin: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .profile-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.6);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .profile-info h6 { margin: 0; font-size: 14px; font-weight: 600; color: white; }
        .profile-info span { font-size: 12px; color: rgba(255, 255, 255, 0.75); display: flex; align-items: center; gap: 5px; }

        .sidebar-nav-container { padding: 5px 15px 20px 15px; }
        .sidebar-nav-container a {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            padding: 14px 18px;
            margin-bottom: 10px;
            border-radius: 14px;
            transition: 0.2s ease;
            font-weight: 500;
        }
        .sidebar-nav-container a:hover, .sidebar-nav-container a.active {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            transform: translateX(4px);
        }
        .sidebar-nav-container i { font-size: 18px; margin-right: 12px; }

        /* ======================================
        CONTENT & CARDS (TEMA BLUE - SINKRON)
        ====================================== */
        .content{ padding:20px 30px; }
        .card{ border:none; border-radius:22px; overflow:hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .card-header{ border:none; font-weight:600; padding:18px 22px; }
        .card-body{ padding:25px; }
        
        /* Menggunakan gradasi biru khas transaksi.php */
        .header-box{ background:linear-gradient(135deg, #2563eb, #1d4ed8); color:white; } 
        .user-box { background: rgba(255, 255, 255, 0.18); padding: 10px 18px; border-radius: 14px; }
        
        .table{ vertical-align:middle; }
        .table thead{ background:#eff6ff; }
        .table thead th{ color:#1e3a8a; border:none; font-weight:600; padding: 15px; }
        .table tbody tr{ transition:0.2s; }
        .table tbody tr:hover{ background:#f1f5f9; }
        .table td{ border-color:#eef2f7; padding: 15px 12px; }
        
        .btn{ border-radius:12px; padding:10px 18px; font-weight:500; }
        /* Tombol aksi lunasi menggunakan warna biru moderen utama */
        .btn-primary-custom { background:#2563eb; color: white; border:none; }
        .btn-primary-custom:hover { background:#1d4ed8; color: white; }
    </style>
</head>
<body>

<nav class="navbar bg-white fixed-top shadow-sm" style="height: 65px;">
  <div class="container-fluid px-4">
    <button class="btn btn-primary d-flex align-items-center justify-content-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarKasir" style="width:42px; height:42px; padding:0;">
      <i class="bi bi-list fs-4"></i>
    </button>
    <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2 m-0" href="dashboard.php">
      <i class="bi bi-shop"></i> MITRA AZAM
    </a>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarKasir">
  <div class="sidebar-header-custom d-flex justify-content-between align-items-center">
    <div class="logo">
        <i class="bi bi-shop"></i> MITRA AZAM
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="sidebar-profile">
      <div class="profile-avatar">
          <i class="bi bi-person-fill"></i>
      </div>
      <div class="profile-info">
          <h6><?= htmlspecialchars($_SESSION['nama'] ?? 'Kasir Utama'); ?></h6>
          <span><i class="bi bi-circle-fill text-success" style="font-size: 7px;"></i> <?= htmlspecialchars(ucfirst($_SESSION['level'] ?? 'Kasir')); ?></span>
      </div>
  </div>

  <div class="offcanvas-body p-0">
    <div class="sidebar-nav-container">
        <a href="dashboard.php">
            <i class="bi bi-house-door-fill"></i> Dashboard
        </a>
        <a href="transaksi.php">
            <i class="bi bi-cart-fill"></i> Transaksi
        </a>
        <a href="data_hutang.php" class="active">
            <i class="bi bi-people-fill"></i> Data Hutang Customer
        </a>
        <a href="riwayat_transaksi.php">
            <i class="bi bi-clock-history"></i> Riwayat Transaksi
        </a>
        <hr class="text-white-50 my-3">
        <a href="../auth/logout.php">
            <i class="bi bi-box-arrow-right text-danger"></i> <span class="text-white">Logout</span>
        </a>
    </div>
  </div>
</div>

<div class="content">
    
    <div class="card header-box mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Manajemen Hutang Customer</h2>
                    <p class="mb-0 opacity-75">Daftar Piutang Aktif Toko Mitra Azam</p>
                </div>
                <div class="user-box border border-white border-opacity-25 fw-semibold">
                    <i class="bi bi-calendar-check me-1"></i>
                    <?= mysqli_num_rows($query_hutang); ?> Tagihan Aktif
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['sukses'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:14px;">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['sukses']; unset($_SESSION['sukses']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['gagal'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:14px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $_SESSION['gagal']; unset($_SESSION['gagal']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom p-3 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-person-lines-fill text-primary fs-5"></i> 
            <span class="fw-bold text-secondary" style="font-size:15px;">Tabel Customer Belum Lunas</span>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No. Nota</th>
                        <th>Nama Customer</th>
                        <th>Tanggal Transaksi</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total Hutang</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query_hutang) < 1): ?>
                        <tr>
                            <td colspan="7" class="text-center p-5 text-muted">
                                <i class="bi bi-emoji-smile fs-1 d-block mb-2 text-success"></i>
                                Semua tagihan lunas! Tidak ada data hutang aktif.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($query_hutang)): 
                            $hari_ini = date('Y-m-d');
                            $is_overdue = ($hari_ini > $row['jatuh_tempo']);
                        ?>
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">#<?= $row['id_penjualan']; ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_customer']); ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_transaksi'] ?? date('Y-m-d'))); ?></td>
                            <td>
                                <span class="<?= $is_overdue ? 'text-danger fw-bold' : ''; ?>">
                                    <?= date('d M Y', strtotime($row['jatuh_tempo'])); ?>
                                    <?php if($is_overdue): ?>
                                        <span class="badge bg-danger p-1 text-white ms-1" style="font-size:10px; font-weight:400;">TERLEWAT</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold text-primary">
                                Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark px-3 rounded-pill" style="font-weight: 500;">Belum Lunas</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-primary-custom btn-sm px-3 d-flex align-items-center gap-1 mx-auto" 
                                        data-id="<?= $row['id_penjualan']; ?>"
                                        data-nama="<?= htmlspecialchars($row['nama_customer']); ?>"
                                        data-total="Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalPelunasan">
                                    <i class="bi bi-cash-coin"></i> Lunasi
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPelunasan" tabindex="-1" aria-labelledby="modalPelunasanLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 22px; overflow:hidden; border:none;">
      <div class="modal-header bg-primary text-white p-3">
        <h5 class="modal-title fw-bold" id="modalPelunasanLabel"><i class="bi bi-check2-circle me-1"></i> Konfirmasi Pelunasan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
          <div class="modal-body p-4">
            <p class="text-muted mb-3">Apakah Anda yakin customer ini sudah melakukan pembayaran lunas?</p>
            <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Nama Customer:</span>
                    <span id="modal-nama" class="fw-bold text-dark"></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary">Total Tagihan:</span>
                    <span id="modal-total" class="fw-bold text-primary fs-5"></span>
                </div>
            </div>
            <input type="hidden" name="id_penjualan" id="modal-id">
            <input type="hidden" name="aksi" value="lunasi">
            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Data akan diperbarui dan otomatis hilang dari daftar piutang aktif.</small>
          </div>
          <div class="modal-footer p-3 bg-light border-0">
            <button type="button" class="btn btn-secondary bg-opacity-10 text-dark border-0 px-4" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary px-4 border-0" style="background:#2563eb;">Ya, Sudah Lunas</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// OPER DATA KE MODAL
const modalPelunasan = document.getElementById('modalPelunasan');
modalPelunasan.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    const total = button.getAttribute('data-total');

    document.getElementById('modal-id').value = id;
    document.getElementById('modal-nama').textContent = nama;
    document.getElementById('modal-total').textContent = total;
});
</script>
</body>
</html>