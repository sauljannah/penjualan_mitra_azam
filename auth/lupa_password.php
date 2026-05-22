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
// RESET PASSWORD
// ======================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ==================================
    // AMBIL INPUT
    // ==================================
    $username = trim(
        $_POST['username'] ?? ''
    );

    $password_baru = trim(
        $_POST['password_baru'] ?? ''
    );

    $konfirmasi_password = trim(
        $_POST['konfirmasi_password'] ?? ''
    );

    // ==================================
    // VALIDASI INPUT
    // ==================================
    if (
        empty($username) ||
        empty($password_baru) ||
        empty($konfirmasi_password)
    ) {

        echo "
        <script>
            alert('Semua field wajib diisi!');
            window.location='lupa_password.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // VALIDASI PASSWORD
    // ==================================
    if (strlen($password_baru) < 6) {

        echo "
        <script>
            alert('Password minimal 6 karakter!');
            window.location='lupa_password.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // CEK KONFIRMASI PASSWORD
    // ==================================
    if (
        $password_baru !==
        $konfirmasi_password
    ) {

        echo "
        <script>
            alert('Konfirmasi password tidak cocok!');
            window.location='lupa_password.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // CEK USERNAME
    // ==================================
    $stmt = mysqli_prepare(

        $conn,

        "SELECT id_user
         FROM users
         WHERE username = ?
         LIMIT 1"

    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $username
    );

    mysqli_stmt_execute($stmt);

    $result =
    mysqli_stmt_get_result($stmt);

    // ==================================
    // JIKA USERNAME TIDAK ADA
    // ==================================
    if (mysqli_num_rows($result) < 1) {

        echo "
        <script>
            alert('Username tidak ditemukan!');
            window.location='lupa_password.php';
        </script>
        ";

        exit;
    }

    // ==================================
    // HASH PASSWORD BARU
    // ==================================
    $password_hash = password_hash(
        $password_baru,
        PASSWORD_DEFAULT
    );

    // ==================================
    // UPDATE PASSWORD
    // ==================================
    $update = mysqli_prepare(

        $conn,

        "UPDATE users
         SET password = ?
         WHERE username = ?"

    );

    mysqli_stmt_bind_param(

        $update,
        "ss",
        $password_hash,
        $username

    );

    $simpan =
    mysqli_stmt_execute($update);

    // ==================================
    // CEK HASIL UPDATE
    // ==================================
    if ($simpan) {

        echo "
        <script>
            alert('Password berhasil direset!');
            window.location='login.php';
        </script>
        ";

        exit;

    } else {

        echo "
        <script>
            alert('Reset password gagal!');
            window.location='lupa_password.php';
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

<title>Lupa Password</title>

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

    background:
    linear-gradient(
        to right,
        #0d6efd,
        #0dcaf0
    );

    min-height:100vh;

    display:flex;

    align-items:center;

    justify-content:center;

    font-family:'Segoe UI',sans-serif;
}

.reset-card{

    width:100%;
    max-width:420px;

    border:none;

    border-radius:20px;

    overflow:hidden;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.15);
}

.card-header{

    background:#0d6efd;

    color:white;

    text-align:center;

    padding:30px 20px;
}

.card-header h3{

    font-weight:700;
    margin-bottom:5px;
}

.card-body{

    padding:30px;
}

.form-label{

    font-weight:600;
}

.form-control{

    border-radius:12px;

    padding:12px;

    border:1px solid #ced4da;
}

.form-control:focus{

    border-color:#0d6efd;

    box-shadow:
    0 0 0 0.2rem rgba(13,110,253,0.2);
}

.btn-reset{

    background:#0d6efd;

    color:white;

    font-weight:600;

    border:none;

    border-radius:12px;

    padding:12px;

    transition:0.3s;
}

.btn-reset:hover{

    background:#0b5ed7;

    color:white;

    transform:translateY(-2px);
}

.card-footer{

    background:white;

    text-align:center;

    padding:20px;

    border:none;
}

.card-footer a{

    color:#0d6efd;

    text-decoration:none;

    font-weight:600;
}

.card-footer a:hover{

    text-decoration:underline;
}

.logo-icon{

    width:80px;
    height:80px;

    background:rgba(255,255,255,0.2);

    border-radius:50%;

    display:flex;

    align-items:center;

    justify-content:center;

    margin:auto auto 15px;

    font-size:35px;
}

</style>

</head>

<body>

<div class="reset-card card">

    <!-- HEADER -->
    <div class="card-header">

        <div class="logo-icon">

            <i class="bi bi-shield-lock-fill"></i>

        </div>

        <h3>

            RESET PASSWORD

        </h3>

        <p class="mb-0">

            Sistem Penjualan Toko Mitra Azam

        </p>

    </div>

    <!-- BODY -->
    <div class="card-body">

        <form method="POST">

            <!-- USERNAME -->
            <div class="mb-3">

                <label class="form-label">

                    Username

                </label>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Masukkan username"
                    required>

            </div>

            <!-- PASSWORD BARU -->
            <div class="mb-3">

                <label class="form-label">

                    Password Baru

                </label>

                <input
                    type="password"
                    name="password_baru"
                    class="form-control"
                    placeholder="Minimal 6 karakter"
                    required>

            </div>

            <!-- KONFIRMASI PASSWORD -->
            <div class="mb-4">

                <label class="form-label">

                    Konfirmasi Password

                </label>

                <input
                    type="password"
                    name="konfirmasi_password"
                    class="form-control"
                    placeholder="Ulangi password baru"
                    required>

            </div>

            <!-- BUTTON -->
            <button
                type="submit"
                class="btn btn-reset w-100">

                <i class="bi bi-arrow-repeat"></i>

                RESET PASSWORD

            </button>

        </form>

    </div>

    <!-- FOOTER -->
    <div class="card-footer">

        <a href="login.php">

            <i class="bi bi-arrow-left"></i>

            Kembali ke Login

        </a>

    </div>

</div>

</body>
</html>