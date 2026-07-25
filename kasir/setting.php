<?php
session_start();
date_default_timezone_set('Asia/Jayapura');
require_once '../config/koneksi.php';
/** @var mysqli $conn */

// Proteksi Login & Level Kasir
if (!isset($_SESSION['level']) || $_SESSION['level'] != "kasir") {
    header("Location: ../auth/login.php");
    exit;
}

$pesan = "";
$tipe_pesan = "";
$username_session = $_SESSION['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_baru = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $tema_baru = mysqli_real_escape_string($conn, $_POST['tema'] ?? 'light');
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Ambil data user saat ini dari database
    $query_cek = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username_session'");
    $user_data = mysqli_fetch_assoc($query_cek);

    if ($user_data) {
        $foto_db = $user_data['foto'] ?? '';

        // Proses Upload Foto Profil Baru (jika ada file yang diunggah)
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_size = $_FILES['foto']['size'];
            $ekstensi_dibolehkan = ['png', 'jpg', 'jpeg', 'webp'];
            $ekstensi_file = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($ekstensi_file, $ekstensi_dibolehkan)) {
                if ($file_size <= 2 * 1024 * 1024) { // Maksimal ukuran 2MB
                    $nama_file_baru = 'avatar_' . time() . '_' . rand(100, 999) . '.' . $ekstensi_file;
                    $direktori_tujuan = '../uploads/' . $nama_file_baru; 

                    // Buat folder uploads jika belum ada
                    if (!is_dir('../uploads')) {
                        mkdir('../uploads', 0777, true);
                    }

                    if (move_uploaded_file($file_tmp, $direktori_tujuan)) {
                        if (!empty($user_data['foto']) && file_exists('../uploads/' . $user_data['foto'])) {
                            unlink('../uploads/' . $user_data['foto']);
                        }
                        $foto_db = $nama_file_baru;
                        $_SESSION['foto'] = $foto_db; 
                    } else {
                        $pesan = "Gagal mengunggah foto profil!";
                        $tipe_pesan = "danger";
                    }
                } else {
                    $pesan = "Ukuran foto terlalu besar! Maksimal 2MB.";
                    $tipe_pesan = "danger";
                }
            } else {
                $pesan = "Format file foto tidak valid! Gunakan JPG, JPEG, PNG, atau WEBP.";
                $tipe_pesan = "danger";
            }
        }

        // Jika tidak ada error saat upload foto, lanjutkan update nama dan tema ke database
        if ($tipe_pesan != "danger") {
            // Cek apakah kolom tema tersedia di tabel, jika ya update juga
            $update_profil = mysqli_query($conn, "UPDATE users SET nama = '$nama_baru', foto = '$foto_db' WHERE username = '$username_session'");
            
            // Simpan preferensi tema ke session
            $_SESSION['tema'] = $tema_baru;
            $_SESSION['nama'] = $nama_baru; 
            $pesan = "Pengaturan profil berhasil diperbarui!";
            $tipe_pesan = "success";
        }

        // Jika kolom password diisi, proses ganti password
        if (!empty($password_lama) || !empty($password_baru) || !empty($konfirmasi_password)) {
            if (password_verify($password_lama, $user_data['password'])) {
                if ($password_baru === $konfirmasi_password) {
                    if (strlen($password_baru) >= 6) {
                        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                        $update_pass = mysqli_query($conn, "UPDATE users SET password = '$password_hash' WHERE username = '$username_session'");
                        
                        if ($update_pass) {
                            $pesan = "Profil dan Password berhasil diperbarui!";
                            $tipe_pesan = "success";
                        }
                    } else {
                        $pesan = "Password baru minimal harus 6 karakter!";
                        $tipe_pesan = "danger";
                    }
                } else {
                    $pesan = "Konfirmasi password baru tidak cocok!";
                    $tipe_pesan = "danger";
                }
            } else {
                $pesan = "Password lama yang Anda masukkan salah!";
                $tipe_pesan = "danger";
            }
        }
    } else {
        $pesan = "Data user tidak ditemukan!";
        $tipe_pesan = "danger";
    }
}

// Ambil data terbaru untuk ditampilkan di form
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username_session'");
$data = mysqli_fetch_assoc($query_user);
$foto_profil = (!empty($data['foto']) && file_exists('../uploads/' . $data['foto'])) ? '../uploads/' . $data['foto'] : '';
$current_tema = $_SESSION['tema'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="<?= $current_tema; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Akun - Kasir</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f1f5f9; overflow-x: hidden; padding-top: 75px; color: #334155; transition: background 0.3s ease, color 0.3s ease; }
        
        /* Dark Mode Styling Variables */
        [data-bs-theme="dark"] body { background: #0f172a; color: #f8fafc; }
        [data-bs-theme="dark"] .navbar { background: #1e293b !important; border-bottom: 1px solid #334155 !important; }
        [data-bs-theme="dark"] .card { background: #1e293b !important; color: #f8fafc; border: 1px solid #334155 !important; }
        [data-bs-theme="dark"] .form-control { background-color: #0f172a !important; color: #f8fafc !important; border-color: #334155 !important; }
        [data-bs-theme="dark"] .form-control[readonly] { background-color: #1e293b !important; color: #94a3b8 !important; }
        [data-bs-theme="dark"] .input-group-text { background-color: #334155 !important; color: #f8fafc !important; border-color: #334155 !important; }
        [data-bs-theme="dark"] .section-title { color: #f8fafc !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }

        .navbar { height: 70px; background: #ffffff !important; border-bottom: 1px solid #e2e8f0; }
        
        /* Sidebar Tema Warna Biru */
        .offcanvas { background: linear-gradient(180deg, #1e40af, #2563eb) !important; color: #ffffff; width: 280px !important; border-right: none; }
        .sidebar-header-custom { padding: 24px 20px 10px 20px; }
        .logo { font-size: 20px; font-weight: 700; color: white; display: flex; align-items: center; gap: 10px; }
        .sidebar-profile { background: rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 14px; margin: 15px; display: flex; align-items: center; gap: 12px; border: 1px solid rgba(255, 255, 255, 0.15); }
        .profile-avatar { width: 42px; height: 42px; background: #ffffff; color: #2563eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 600; overflow: hidden; }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-info h6 { margin: 0; font-size: 14px; font-weight: 600; color: white; }
        .profile-info span { font-size: 11px; color: #e0e7ff; display: flex; align-items: center; gap: 5px; }
        .sidebar-nav-container { padding: 5px 15px 20px 15px; }
        .sidebar-nav-container a { display: flex; align-items: center; gap: 12px; color: #e0e7ff; text-decoration: none; padding: 12px 16px; margin-bottom: 6px; border-radius: 12px; transition: 0.2s ease; font-weight: 500; font-size: 14px; }
        .sidebar-nav-container a:hover, .sidebar-nav-container a.active { background: rgba(255, 255, 255, 0.2); color: #ffffff; }

        .content { padding: 20px 30px 40px 30px; max-width: 1200px; margin: 0 auto; }
        .card { border: none; border-radius: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); background: #ffffff; }
        
        /* Banner Header */
        .header-banner { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border-radius: 20px; padding: 30px; margin-bottom: 25px; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2); }
        .user-pill { background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(5px); padding: 8px 16px; border-radius: 50rem; font-size: 14px; font-weight: 500; border: 1px solid rgba(255, 255, 255, 0.2); }

        /* Form Styling */
        .form-label { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 16px; border: 1.5px solid #e2e8f0; font-size: 14px; color: #1e293b; background-color: #f8fafc; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        .form-control[readonly] { background-color: #f1f5f9; color: #64748b; border-color: #e2e8f0; cursor: not-allowed; }
        
        .section-title { font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .btn-save { background: #3b82f6; border: none; border-radius: 12px; padding: 14px; font-weight: 600; font-size: 15px; color: white; transition: 0.2s; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        .btn-save:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4); }
        
        /* Avatar Preview Box */
        .avatar-preview-container { display: flex; align-items: center; gap: 20px; }
        .current-avatar { width: 85px; height: 85px; border-radius: 50%; object-fit: cover; border: 3px solid #e2e8f0; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #64748b; }
        
        /* Floating Toast Notification Box */
        .toast-container-custom { position: fixed; top: 20px; right: 20px; z-index: 1080; }
    </style>
</head>
<body>

<!-- Toast Notification Container (Notifikasi Melayang Otomatis) -->
<div class="toast-container-custom">
    <?php if(!empty($pesan)): ?>
        <div id="liveToast" class="toast align-items-center text-white bg-<?= $tipe_pesan == 'success' ? 'success' : 'danger'; ?> border-0 shadow-lg show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2 py-3 px-4 fs-6 fw-medium">
                    <i class="bi <?= $tipe_pesan == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> fs-5"></i>
                    <div><?= $pesan; ?></div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Navbar -->
<nav class="navbar fixed-top px-4">
  <div class="container-fluid d-flex align-items-center justify-content-between p-0">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light border d-flex align-items-center justify-content-center p-2 rounded-3 shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarKasir" style="width: 42px; height: 42px;">
          <i class="bi bi-list fs-5 text-dark"></i>
        </button>
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2 m-0 p-0 fs-5" href="dashboard.php">
          <i class="bi bi-shop-window"></i> MITRA AZAM
        </a>
    </div>
    <!-- Quick Theme Switcher on Navbar -->
    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 d-flex align-items-center gap-2" id="themeToggleBtn" onclick="toggleQuickTheme()">
        <i class="bi <?= $current_tema == 'dark' ? 'bi-moon-stars-fill text-warning' : 'bi-sun-fill text-warning'; ?>"></i>
        <span class="small fw-semibold d-none d-sm-inline"><?= $current_tema == 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
    </button>
  </div>
</nav>

<!-- Sidebar Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarKasir">
  <div class="sidebar-header-custom d-flex justify-content-between align-items-center">
    <div class="logo"><i class="bi bi-shop-window text-white"></i> MITRA AZAM</div>
    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="sidebar-profile">
      <div class="profile-avatar">
          <?php if(!empty($foto_profil)): ?>
              <img src="<?= $foto_profil; ?>" alt="Avatar">
          <?php else: ?>
              <i class="bi bi-person-fill"></i>
          <?php endif; ?>
      </div>
      <div class="profile-info">
          <h6><?= htmlspecialchars($_SESSION['nama'] ?? 'Kasir Utama'); ?></h6>
          <span><i class="bi bi-circle-fill text-warning" style="font-size: 7px;"></i> <?= htmlspecialchars(ucfirst($_SESSION['level'] ?? 'Kasir')); ?></span>
      </div>
  </div>
  <div class="offcanvas-body p-0">
    <div class="sidebar-nav-container">
        <a href="dashboard.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="transaksi.php"><i class="bi bi-cart3"></i> Transaksi</a>
        <a href="data_hutang.php"><i class="bi bi-wallet2"></i> Data Hutang Customer</a>
        <a href="riwayat_transaksi.php"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a>
        <hr class="text-white-50 my-3 opacity-25">
        <a href="setting.php" class="active"><i class="bi bi-gear-wide-connected"></i> Setting Akun</a>
        <a href="../auth/logout.php" class="text-white"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="content">
    <!-- Header Banner -->
    <div class="header-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1">Pengaturan Akun & Tampilan</h3>
            <p class="mb-0 text-white-50">Kelola informasi profil pribadi, foto profil, preferensi tema, dan perbarui kata sandi.</p>
        </div>
        <div class="user-pill d-flex align-items-center gap-2">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nama'] ?? ''); ?>
        </div>
    </div>

    <!-- Form Setting Card -->
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <!-- Bagian Informasi Profil & Foto -->
                <div class="section-title mb-3">
                    <i class="bi bi-person-badge text-primary fs-5"></i> Informasi Profil & Foto
                </div>
                
                <div class="row mb-4 align-items-center">
                    <div class="col-md-12 mb-4">
                        <label class="form-label d-block mb-2">Foto Profil</label>
                        <div class="avatar-preview-container">
                            <?php if(!empty($foto_profil)): ?>
                                <img src="<?= $foto_profil; ?>" alt="Foto Profil" class="current-avatar shadow-sm">
                            <?php else: ?>
                                <div class="current-avatar shadow-sm"><i class="bi bi-person-fill"></i></div>
                            <?php endif; ?>
                            <div>
                                <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .webp">
                                <small class="text-muted mt-1 d-block">Format yang diizinkan: JPG, JPEG, PNG, WEBP. Maksimal ukuran 2MB.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username <span class="text-muted fw-normal">(Tidak dapat diubah)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 border-1.5"><i class="bi bi-at text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($data['username'] ?? ''); ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 border-1.5"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="nama" class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($data['nama'] ?? ''); ?>" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                </div>

                <hr class="text-muted my-4 opacity-25">

                <!-- Bagian Pengaturan Tampilan (Tema) -->
                <div class="section-title mb-3">
                    <i class="bi bi-palette text-primary fs-5"></i> Preferensi Tampilan Sistem
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pilih Tema Antarmuka</label>
                        <select name="tema" id="selectTema" class="form-select" onchange="previewTheme(this.value)">
                            <option value="light" <?= $current_tema == 'light' ? 'selected' : ''; ?>>☀️ Light Mode (Terang)</option>
                            <option value="dark" <?= $current_tema == 'dark' ? 'selected' : ''; ?>>🌙 Dark Mode (Gelap)</option>
                        </select>
                        <small class="text-muted mt-1 d-block">Pilih mode tampilan dashboard sesuai kenyamanan mata Anda.</small>
                    </div>
                </div>

                <hr class="text-muted my-4 opacity-25">

                <!-- Bagian Keamanan / Ganti Password -->
                <div class="section-title mb-1">
                    <i class="bi bi-shield-lock text-primary fs-5"></i> Ubah Password
                </div>
                <p class="text-muted small mb-4">Biarkan kosong form sandi di bawah ini jika Anda tidak ingin mengubah password akun Anda.</p>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" placeholder="••••••••">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password baru">
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <button type="submit" class="btn btn-save w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-check2-circle fs-5"></i> Simpan Perubahan Pengaturan
                </button>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fungsi untuk pratinjau tema langsung tanpa refresh halaman penuh
    function previewTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
    }

    // Toggle cepat dari Navbar
    function toggleQuickTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        document.getElementById('selectTema').value = newTheme;
        localStorage.setItem('theme', newTheme);
        
        // Perbarui ikon navbar
        const icon = document.querySelector('#themeToggleBtn i');
        if (newTheme === 'dark') {
            icon.className = "bi bi-moon-stars-fill text-warning";
        } else {
            icon.className = "bi bi-sun-fill text-warning";
        }
    }

    // Auto-hide toast notification setelah 4 detik
    document.addEventListener("DOMContentLoaded", function() {
        var toastEl = document.getElementById('liveToast');
        if (toastEl) {
            setTimeout(function() {
                var toast = new bootstrap.Toast(toastEl);
                toast.hide();
            }, 4000);
        }
    });
</script>
</body>
</html>