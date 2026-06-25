<?php

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK KONEKSI DATABASE
// ======================================
if (!$conn) {

    die(
        "Koneksi database gagal : " .
        mysqli_connect_error()
    );
}

// ======================================
// REGISTER USER
// ======================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ==================================
    // AMBIL INPUT
    // ==================================
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $konfirmasi = trim($_POST['konfirmasi_password'] ?? '');
    $level = trim($_POST['level'] ?? '');

    // ==================================
    // VALIDASI INPUT
    // ==================================
    if (
        empty($nama) ||
        empty($username) ||
        empty($password) ||
        empty($konfirmasi) ||
        empty($level)
    ) {

        echo "
        <script>
            alert('Semua field wajib diisi!');
            window.location='registrasi.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // VALIDASI USERNAME
    // ==================================
    if (strlen($username) < 4) {

        echo "
        <script>
            alert('Username minimal 4 karakter!');
            window.location='registrasi.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // VALIDASI PASSWORD
    // ==================================
    if (strlen($password) < 6) {

        echo "
        <script>
            alert('Password minimal 6 karakter!');
            window.location='registrasi.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // KONFIRMASI PASSWORD
    // ==================================
    if ($password !== $konfirmasi) {

        echo "
        <script>
            alert('Konfirmasi password tidak cocok!');
            window.location='registrasi.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // VALIDASI LEVEL
    // ==================================
    if (
        $level != 'admin'
    ) {

        echo "
        <script>
            alert('Level user tidak valid!');
            window.location='registrasi.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // CEK USERNAME
    // ==================================
    $cek = mysqli_prepare(
        $conn,
        "SELECT id_user
         FROM users
         WHERE username = ?"
    );

    mysqli_stmt_bind_param(
        $cek,
        "s",
        $username
    );

    mysqli_stmt_execute($cek);

    $result_cek = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($result_cek) > 0) {

        echo "
        <script>
            alert('Username sudah digunakan!');
            window.location='registrasi.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // HASH PASSWORD
    // ==================================
    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // ==================================
    // SIMPAN USER
    // ==================================
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (

            nama,
            username,
            password,
            level

        ) VALUES (

            ?, ?, ?, ?

        )"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $nama,
        $username,
        $password_hash,
        $level
    );

    $simpan = mysqli_stmt_execute($stmt);

    // ==================================
    // CEK HASIL
    // ==================================
    if ($simpan) {

        echo "
        <script>
            alert('Registrasi berhasil!');
            window.location='login.php';
        </script>
        ";

        exit;

    } else {

        echo "
        <script>
            alert('Registrasi gagal!');
            window.location='registrasi.php';
        </script>
        ";

        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Registrasi User</title>

<!-- Bootstrap -->
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- Bootstrap Icons -->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    min-height:100vh;

    background:
    linear-gradient(
        to right,
        #0d6efd,
        #0dcaf0
    );

    display:flex;

    align-items:center;

    justify-content:center;

    font-family:'Segoe UI',sans-serif;

    padding:20px;
}

.register-card{

    width:100%;
    max-width:470px;

    border:none;

    border-radius:25px;

    overflow:hidden;

    background:white;

    box-shadow:
    0 10px 30px rgba(0,0,0,0.15);
}

.card-header{

    background:#0d6efd;

    color:white;

    border:none;

    text-align:center;

    padding:35px 25px;
}

.logo-icon{

    width:90px;
    height:90px;

    margin:auto;

    border-radius:50%;

    background:rgba(255,255,255,0.2);

    display:flex;

    align-items:center;

    justify-content:center;

    color:white;

    font-size:40px;

    margin-bottom:15px;
}

.card-header h3{

    font-weight:bold;
    margin-bottom:5px;
}

.card-header p{

    margin:0;
    opacity:0.9;
}

.card-body{

    padding:30px;
}

.form-label{

    font-weight:600;
    color:#2d3436;
}

.form-control,
.form-select{

    border-radius:12px;

    padding:12px;

    border:1px solid #ced4da;
}

.form-control:focus,
.form-select:focus{

    border-color:#0d6efd;

    box-shadow:
    0 0 0 0.2rem rgba(13,110,253,0.2);
}

.input-group-text{

    background:#e7f1ff;

    color:#0d6efd;

    border-radius:12px 0 0 12px;
}

.btn-register{

    background:#0d6efd;

    color:white;

    border:none;

    border-radius:12px;

    padding:12px;

    font-weight:bold;

    transition:0.3s;
}

.btn-register:hover{

    background:#0b5ed7;

    color:white;

    transform:translateY(-2px);
}

.card-footer{

    background:white;

    text-align:center;

    border:none;

    padding-bottom:25px;
}

.card-footer a{

    color:#0d6efd;

    text-decoration:none;

    font-weight:bold;
}

.card-footer a:hover{

    text-decoration:underline;
}

.password-info{

    font-size:13px;
    color:#6c757d;
    margin-top:5px;
}

</style>

</head>

<body>

<div class="register-card">

    <!-- HEADER -->
    <div class="card-header">

        <div class="logo-icon">

            <i class="bi bi-person-plus-fill"></i>

        </div>

        <h3>

            REGISTRASI USER

        </h3>

        <p>

            Sistem Penjualan Toko Mitra Azam

        </p>

    </div>

    <!-- BODY -->
    <div class="card-body">

        <form method="POST">

            <!-- NAMA -->
            <div class="mb-3">

                <label class="form-label">

                    Nama Lengkap

                </label>

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="bi bi-person"></i>

                    </span>

                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        placeholder="Masukkan nama lengkap"
                        required>

                </div>

            </div>

            <!-- USERNAME -->
            <div class="mb-3">

                <label class="form-label">

                    Username

                </label>

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="bi bi-at"></i>

                    </span>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Masukkan username"
                        required>

                </div>

            </div>

            <!-- PASSWORD -->
            <div class="mb-3">

                <label class="form-label">

                    Password

                </label>

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="bi bi-lock"></i>

                    </span>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        required>

                </div>

                <div class="password-info">

                    Password minimal 6 karakter

                </div>

            </div>

            <!-- KONFIRMASI PASSWORD -->
            <div class="mb-3">

                <label class="form-label">

                    Konfirmasi Password

                </label>

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="bi bi-shield-lock"></i>

                    </span>

                    <input
                        type="password"
                        name="konfirmasi_password"
                        class="form-control"
                        placeholder="Ulangi password"
                        required>

                </div>

            </div>

            <!-- LEVEL -->
            <div class="mb-4">

                <label class="form-label">

                    Level User

                </label>

                <select
                    name="level"
                    class="form-select"
                    required>

                    <option value="">

                        -- Pilih Level --

                    </option>

                    <option value="admin">

                        Admin

                    </option>

                </select>

            </div>

            <!-- BUTTON -->
            <button
                type="submit"
                class="btn btn-register w-100">

                <i class="bi bi-check-circle-fill"></i>

                DAFTAR SEKARANG

            </button>

        </form>

    </div>

    <!-- FOOTER -->
    <div class="card-footer">

        Sudah punya akun?

        <a href="login.php">

            Login disini

        </a>

    </div>

</div>

</body>
</html>