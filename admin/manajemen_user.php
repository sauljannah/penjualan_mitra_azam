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

    $nama = htmlspecialchars(
        trim($_POST['nama'])
    );

    $username = htmlspecialchars(
        trim($_POST['username'])
    );

    $password_asli = trim($_POST['password']);

    $password = password_hash(
        $password_asli,
        PASSWORD_DEFAULT
    );

    $level = htmlspecialchars(
        trim($_POST['level'])
    );

    // ==========================
    // VALIDASI
    // ==========================
    if(
        empty($nama) ||
        empty($username) ||
        empty($password_asli) ||
        empty($level)
    ){

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
    $cek = mysqli_query(
        $conn,
        "SELECT * FROM users
         WHERE username='$username'"
    );

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
    $simpan = mysqli_query(
        $conn,
        "INSERT INTO users(
            nama,
            username,
            password,
            level,
            status
        ) VALUES (
            '$nama',
            '$username',
            '$password',
            '$level',
            'aktif'
        )"
    );

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

    $hapus = mysqli_query(
        $conn,
        "DELETE FROM users
         WHERE id_user='$id'"
    );

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

    $password_baru = password_hash(
        '123456',
        PASSWORD_DEFAULT
    );

    $reset = mysqli_query(
        $conn,
        "UPDATE users
         SET password='$password_baru'
         WHERE id_user='$id'"
    );

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
    $cek_user = mysqli_query(
        $conn,
        "SELECT * FROM users
         WHERE id_user='$id'"
    );

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
        if(
            strtolower(trim($data_user['status'])) == 'aktif'
        ){

            $status_baru = 'nonaktif';

        }else{

            $status_baru = 'aktif';
        }

        // ==========================
        // UPDATE STATUS
        // ==========================
        $update = mysqli_query(
            $conn,
            "UPDATE users
             SET status='$status_baru'
             WHERE id_user='$id'"
        );

        // ==========================
        // CEK UPDATE
        // ==========================
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
$user = mysqli_query(
    $conn,
    "SELECT * FROM users
     ORDER BY id_user DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Manajemen User</title>

<!-- Bootstrap -->
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- Bootstrap Icons -->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f6f9;
    overflow-x:hidden;
    font-family:Arial,sans-serif;
}

/* =========================
SIDEBAR
========================= */
.sidebar{
    width:260px;
    height:100vh;
    position:fixed;
    background:linear-gradient(
        135deg,
        #296bf9,
        #142b76
    );
    padding:20px;
    color:white;
}

.sidebar h3{
    text-align:center;
    margin-bottom:30px;
    font-weight:bold;
}

.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:14px;
    border-radius:12px;
    margin-bottom:12px;
    transition:0.3s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.2);
    transform:translateX(5px);
}

/* =========================
CONTENT
========================= */
.content{
    margin-left:270px;
    padding:25px;
}

.card{
    border:none;
    border-radius:20px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

.card-header{
    border-radius:20px 20px 0 0 !important;
}

.btn{
    border-radius:10px;
}

.form-control,
.form-select{
    border-radius:10px;
    padding:10px;
}

.table tbody tr:hover{
    background:#f8f9fa;
}

.badge{
    padding:8px 12px;
    border-radius:10px;
}

@media(max-width:768px){

    .sidebar{
        position:relative;
        width:100%;
        height:auto;
    }

    .content{
        margin-left:0;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h3>

        <i class="bi bi-building"></i>
        MITRA AZAM

    </h3>

    <a href="dashboard.php">

        <i class="bi bi-speedometer2"></i>
        Dashboard

    </a>

    <a href="barang.php">

        <i class="bi bi-box-seam"></i>
        Data Barang

    </a>

    <a href="laporan.php">

        <i class="bi bi-file-earmark-text"></i>
        Laporan

    </a>

    <a href="laba_rugi.php">

        <i class="bi bi-cash-stack"></i>
        Laba Rugi

    </a>

    <a href="manajemen_user.php"
       style="background:rgba(255,255,255,0.2);">

        <i class="bi bi-people"></i>
        Manajemen User

    </a>

    <a href="../auth/logout.php">

        <i class="bi bi-box-arrow-right"></i>
        Logout

    </a>

</div>

<!-- CONTENT -->
<div class="content">

    <!-- HEADER -->
    <div class="card mb-4">

        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

            <div>

                <h3 class="fw-bold">

                    Manajemen User

                </h3>

                <p class="mb-0 text-muted">

                    Kelola akun admin dan kasir

                </p>

            </div>

            <div>

                <h5>

                    <i class="bi bi-person-circle"></i>

                    <?= htmlspecialchars($_SESSION['nama']); ?>

                </h5>

            </div>

        </div>

    </div>

    <!-- FORM TAMBAH USER -->
    <div class="card mb-4">

        <div class="card-header bg-primary text-white">

            <i class="bi bi-person-plus-fill"></i>
            Tambah User Baru

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row">

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            required>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            required>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            required>

                    </div>

                    <div class="col-md-2 mb-3">

                        <label class="form-label">
                            Level
                        </label>

                        <select
                            name="level"
                            class="form-select"
                            required>

                            <option value="">
                                -- Pilih --
                            </option>

                            <option value="admin">
                                Admin
                            </option>

                            <option value="kasir">
                                Kasir
                            </option>

                        </select>

                    </div>

                    <div class="col-md-1 d-grid">

                        <label>&nbsp;</label>

                        <button
                            type="submit"
                            name="tambah"
                            class="btn btn-success">

                            <i class="bi bi-save-fill"></i>

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- TABEL USER -->
    <div class="card">

        <div class="card-header bg-dark text-white">

            <i class="bi bi-table"></i>
            Data User

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-warning text-center">

                    <tr>

                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th width="250">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                <?php if(mysqli_num_rows($user) > 0): ?>

                    <?php $no = 1; ?>

                    <?php while($u = mysqli_fetch_assoc($user)): ?>

                    <tr>

                        <td class="text-center">

                            <?= $no++; ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($u['nama']); ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($u['username']); ?>

                        </td>

                        <td class="text-center">

                            <?php if($u['level'] == 'admin'): ?>

                                <span class="badge bg-primary">

                                    Admin

                                </span>

                            <?php else: ?>

                                <span class="badge bg-success">

                                    Kasir

                                </span>

                            <?php endif; ?>

                        </td>

                        <td class="text-center">

                            <?php if(strtolower(trim($u['status'])) == 'aktif'): ?>

                                <span class="badge bg-success">

                                    Aktif

                                </span>

                            <?php else: ?>

                                <span class="badge bg-danger">

                                    Nonaktif

                                </span>

                            <?php endif; ?>

                        </td>

                        <td class="text-center">

                            <!-- RESET PASSWORD -->
                            <a
                                href="?reset=<?= $u['id_user']; ?>"
                                class="btn btn-warning btn-sm"
                                onclick="return confirm('Reset password menjadi 123456?')">

                                <i class="bi bi-key-fill"></i>

                            </a>

                            <!-- STATUS -->
                            <a
                                href="?status=<?= $u['id_user']; ?>"
                                class="btn btn-info btn-sm text-white"
                                onclick="return confirm('Ubah status user ini?')">

                                <?php if(strtolower(trim($u['status'])) == 'aktif'): ?>

                                    <i class="bi bi-toggle-on"></i>

                                <?php else: ?>

                                    <i class="bi bi-toggle-off"></i>

                                <?php endif; ?>

                            </a>

                            <!-- HAPUS -->
                            <a
                                href="?hapus=<?= $u['id_user']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus user ini?')">

                                <i class="bi bi-trash-fill"></i>

                            </a>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6"
                            class="text-center text-danger">

                            Data user kosong

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>