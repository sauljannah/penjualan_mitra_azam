<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN ADMIN
// ======================================
if(
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'admin'
){
    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// TAMBAH USER
// ======================================
if(isset($_POST['tambah'])){

    $nama = htmlspecialchars(trim($_POST['nama']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password_asli = trim($_POST['password']);
    $password = password_hash($password_asli, PASSWORD_DEFAULT);
    $level = htmlspecialchars(trim($_POST['level']));

    // ==========================
    // VALIDASI
    // ==========================
    if(empty($nama) || empty($username) || empty($password_asli) || empty($level)){
        echo "
        <script>
            alert('Semua field wajib diisi');
            window.location='manajemen_user.php';
        </script>
        ";
        exit;
    }

    // ==========================
    // CEK USERNAME
    // ==========================
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

    if(mysqli_num_rows($cek) > 0){
        echo "
        <script>
            alert('Username sudah digunakan');
            window.location='manajemen_user.php';
        </script>
        ";
        exit;
    }

    // ==========================
    // SIMPAN USER
    // ==========================
    $simpan = mysqli_query($conn, "INSERT INTO users(nama, username, password, level, status) VALUES ('$nama', '$username', '$password', '$level', 'aktif')");

    if($simpan){
        echo "
        <script>
            alert('User berhasil ditambahkan');
            window.location='manajemen_user.php';
        </script>
        ";
    }else{
        echo "
        <script>
            alert('Gagal menambahkan user');
            window.location='manajemen_user.php';
        </script>
        ";
    }
}

// ======================================
// HAPUS USER
// ======================================
if(isset($_GET['hapus'])){

    $id = (int) $_GET['hapus'];

    // ==========================
    // ADMIN TIDAK BISA HAPUS DIRI SENDIRI
    // ==========================
    if($id == $_SESSION['id_user']){
        echo "
        <script>
            alert('Anda tidak bisa menghapus akun sendiri');
            window.location='manajemen_user.php';
        </script>
        ";
        exit;
    }

    $hapus = mysqli_query($conn, "DELETE FROM users WHERE id_user='$id'");

    if($hapus){
        echo "
        <script>
            alert('User berhasil dihapus');
            window.location='manajemen_user.php';
        </script>
        ";
    }else{
        echo "
        <script>
            alert('Gagal menghapus user');
            window.location='manajemen_user.php';
        </script>
        ";
    }
    exit;
}

// ======================================
// RESET PASSWORD
// ======================================
if(isset($_GET['reset'])){

    $id = (int) $_GET['reset'];
    $password_baru = password_hash('123456', PASSWORD_DEFAULT);

    $reset = mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE id_user='$id'");

    if($reset){
        echo "
        <script>
            alert('Password berhasil direset menjadi 123456');
            window.location='manajemen_user.php';
        </script>
        ";
    }else{
        echo "
        <script>
            alert('Gagal reset password');
            window.location='manajemen_user.php';
        </script>
        ";
    }
    exit;
}

// ======================================
// UBAH STATUS USER
// ======================================
if(isset($_GET['status'])){

    $id = intval($_GET['status']);

    // ==========================
    // CEK USER
    // ==========================
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id'");

    if(mysqli_num_rows($cek_user) > 0){

        $data_user = mysqli_fetch_assoc($cek_user);

        // ==========================
        // ADMIN TIDAK BISA UBAH DIRI SENDIRI
        // ==========================
        if($id == $_SESSION['id_user']){
            echo "
            <script>
                alert('Anda tidak bisa mengubah status akun sendiri');
                window.location='manajemen_user.php';
            </script>
            ";
            exit;
        }

        // ==========================
        // STATUS BARU
        // ==========================
        if(strtolower(trim($data_user['status'])) == 'aktif'){
            $status_baru = 'nonaktif';
        }else{
            $status_baru = 'aktif';
        }

        // ==========================
        // UPDATE STATUS
        // ==========================
        $update = mysqli_query($conn, "UPDATE users SET status='$status_baru' WHERE id_user='$id'");

        if($update){
            echo "
            <script>
                alert('Status berhasil diubah menjadi $status_baru');
                window.location='manajemen_user.php';
            </script>
            ";
        }else{
            echo "
            <script>
                alert('Gagal mengubah status');
                window.location='manajemen_user.php';
            </script>
            ";
        }

    }else{
        echo "
        <script>
            alert('User tidak ditemukan');
            window.location='manajemen_user.php';
        </script>
        ";
    }
    exit;
}

// ======================================
// DATA USER
// ======================================
$user = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        .content {
            padding: 25px;
            margin-top: 75px;
        }

        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }

        .card-header {
            border-radius: 18px 18px 0 0 !important;
            font-weight: 600;
        }

        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 10px;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 600;
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
      <i class="bi bi-shop-window me-2"></i> MITRA AZAM
    </a>
    
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasNavbarLabel">
          <i class="bi bi-shop-window me-2"></i> MITRA AZAM
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
            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-box-seam me-2 text-primary"></i> Data Barang
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="barang.php"><i class="bi bi-list-ul me-2"></i> Semua Barang</a></li>
              <li><a class="dropdown-item" href="tambah_barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Tambah Stok Masuk</a></li>
              <li><a class="dropdown-item" href="tambah_barang.php"><i class="bi bi-plus-circle me-2"></i> Tambah Barang</a></li>
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
            <a class="nav-link dropdown-toggle active fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                <h2 class="fw-bold mb-1">Manajemen User</h2>
                <p class="mb-0 text-muted">Kelola akun admin dan kasir sistem toko</p>
            </div>
            <div class="fw-bold">
                <i class="bi bi-person-circle text-primary me-1"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white p-3">
            <i class="bi bi-person-plus-fill me-2"></i> Tambah User Baru
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-bold text-muted">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Username login" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-bold text-muted">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label small fw-bold text-muted">Level Hak Akses</label>
                        <select name="level" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3 d-grid">
                        <button type="submit" name="tambah" class="btn btn-success w-100">
                            <i class="bi bi-save-fill"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white p-3">
            <i class="bi bi-table me-2"></i> Data User Terdaftar
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="70">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th class="text-center">Level</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($user) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while($u = mysqli_fetch_assoc($user)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="fw-semibold text-secondary"><?= htmlspecialchars($u['nama']); ?></td>
                        <td><?= htmlspecialchars($u['username']); ?></td>
                        <td class="text-center">
                            <?php if($u['level'] == 'admin'): ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-success">Kasir</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if(strtolower(trim($u['status'])) == 'aktif'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="?reset=<?= $u['id_user']; ?>" class="btn btn-warning btn-sm" title="Reset Password" onclick="return confirm('Reset password menjadi 123456?')">
                                <i class="bi bi-key-fill"></i>
                            </a>

                            <a href="?status=<?= $u['id_user']; ?>" class="btn btn-info btn-sm text-white" title="Ubah Status" onclick="return confirm('Ubah status user ini?')">
                                <?php if(strtolower(trim($u['status'])) == 'aktif'): ?>
                                    <i class="bi bi-toggle-on fs-6"></i>
                                <?php else: ?>
                                    <i class="bi bi-toggle-off fs-6"></i>
                                <?php endif; ?>
                            </a>

                            <a href="?hapus=<?= $u['id_user']; ?>" class="btn btn-danger btn-sm" title="Hapus User" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-danger p-4 fw-bold">Data user kosong</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>