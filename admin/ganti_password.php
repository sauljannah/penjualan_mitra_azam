<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK LOGIN
// ======================================
if(
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'admin'
){

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// CEK SESSION USER
// ======================================
if(!isset($_SESSION['id_user'])){

    echo "
    <script>

        alert('Session user tidak ditemukan');

        window.location='../auth/login.php';

    </script>
    ";

    exit;
}

$id_user = $_SESSION['id_user'];

// ======================================
// PROSES GANTI PASSWORD
// ======================================
if(isset($_POST['ganti'])){

    $password_lama = md5(
        mysqli_real_escape_string(
            $conn,
            trim($_POST['password_lama'])
        )
    );

    $password_baru = trim($_POST['password_baru']);

    $konfirmasi    = trim($_POST['konfirmasi_password']);

    // ======================================
    // VALIDASI PASSWORD BARU
    // ======================================
    if(strlen($password_baru) < 6){

        echo "
        <script>

            alert('Password minimal 6 karakter');

        </script>
        ";

    }elseif($password_baru != $konfirmasi){

        echo "
        <script>

            alert('Konfirmasi password tidak cocok');

        </script>
        ";

    }else{

        // ENKRIPSI PASSWORD BARU
        $password_baru_md5 = md5($password_baru);

        // ======================================
        // CEK PASSWORD LAMA
        // ======================================
        $cek = mysqli_query(
            $conn,
            "SELECT * FROM users
             WHERE id_user='$id_user'
             AND password='$password_lama'"
        );

        // ======================================
        // JIKA QUERY ERROR
        // ======================================
        if(!$cek){

            die(
                "Query Error : " .
                mysqli_error($conn)
            );
        }

        // ======================================
        // JIKA PASSWORD BENAR
        // ======================================
        if(mysqli_num_rows($cek) > 0){

            // UPDATE PASSWORD
            $update = mysqli_query(
                $conn,
                "UPDATE users SET
                 password='$password_baru_md5'
                 WHERE id_user='$id_user'"
            );

            if($update){

                echo "
                <script>

                    alert('Password berhasil diganti');

                    window.location='setting.php';

                </script>
                ";

            }else{

                echo "
                <script>

                    alert('Gagal mengganti password');

                </script>
                ";

                echo mysqli_error($conn);
            }

        }else{

            echo "
            <script>

                alert('Password lama salah');

            </script>
            ";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Ganti Password</title>

<!-- Bootstrap -->
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- Bootstrap Icons -->
<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Google Font -->
<link
href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:#f1f5f9;
}

/* ======================================
CONTAINER
====================================== */
.password-container{

    min-height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    padding:20px;
}

/* ======================================
CARD
====================================== */
.password-card{

    width:100%;
    max-width:500px;

    background:white;

    border-radius:25px;

    overflow:hidden;

    box-shadow:
    0 10px 30px rgba(0,0,0,0.08);
}

.card-header-custom{

    background:linear-gradient(
        135deg,
        #ff7b00,
        #ff9f43
    );

    padding:30px;

    color:white;

    text-align:center;
}

.card-header-custom i{

    font-size:45px;
}

.card-header-custom h3{

    margin-top:10px;
    font-weight:700;
}

/* ======================================
FORM
====================================== */
.card-body-custom{

    padding:35px;
}

.form-label{

    font-weight:600;

    margin-bottom:8px;

    color:#334155;
}

.form-control{

    border-radius:14px;

    padding:12px;

    border:1px solid #dcdcdc;
}

.form-control:focus{

    border-color:#ff7b00;

    box-shadow:
    0 0 0 0.2rem rgba(255,123,0,0.2);
}

/* ======================================
BUTTON
====================================== */
.btn-save{

    background:linear-gradient(
        135deg,
        #198754,
        #20c997
    );

    border:none;

    color:white;

    padding:12px 20px;

    border-radius:14px;

    font-weight:600;

    transition:0.3s;
}

.btn-save:hover{

    transform:translateY(-2px);

    color:white;
}

.btn-back{

    background:#6c757d;

    color:white;

    padding:12px 20px;

    border-radius:14px;

    font-weight:600;

    text-decoration:none;
}

.btn-back:hover{

    background:#5c636a;

    color:white;
}

/* ======================================
SHOW PASSWORD
====================================== */
.password-wrapper{

    position:relative;
}

.toggle-password{

    position:absolute;

    top:50%;
    right:15px;

    transform:translateY(-50%);

    cursor:pointer;

    color:#64748b;
}

</style>

</head>

<body>

<div class="password-container">

    <div class="password-card">

        <!-- HEADER -->
        <div class="card-header-custom">

            <i class="bi bi-shield-lock-fill"></i>

            <h3>Ganti Password</h3>

            <p class="mb-0">

                Sistem Penjualan Toko Mitra Azam

            </p>

        </div>

        <!-- BODY -->
        <div class="card-body-custom">

            <form method="POST">

                <!-- PASSWORD LAMA -->
                <div class="mb-4">

                    <label class="form-label">

                        Password Lama

                    </label>

                    <div class="password-wrapper">

                        <input
                        type="password"
                        name="password_lama"
                        id="password_lama"
                        class="form-control"
                        required>

                        <i
                        class="bi bi-eye-slash-fill toggle-password"
                        onclick="togglePassword('password_lama', this)"></i>

                    </div>

                </div>

                <!-- PASSWORD BARU -->
                <div class="mb-4">

                    <label class="form-label">

                        Password Baru

                    </label>

                    <div class="password-wrapper">

                        <input
                        type="password"
                        name="password_baru"
                        id="password_baru"
                        class="form-control"
                        required>

                        <i
                        class="bi bi-eye-slash-fill toggle-password"
                        onclick="togglePassword('password_baru', this)"></i>

                    </div>

                </div>

                <!-- KONFIRMASI PASSWORD -->
                <div class="mb-4">

                    <label class="form-label">

                        Konfirmasi Password Baru

                    </label>

                    <div class="password-wrapper">

                        <input
                        type="password"
                        name="konfirmasi_password"
                        id="konfirmasi_password"
                        class="form-control"
                        required>

                        <i
                        class="bi bi-eye-slash-fill toggle-password"
                        onclick="togglePassword('konfirmasi_password', this)"></i>

                    </div>

                </div>

                <!-- BUTTON -->
                <div class="d-flex gap-2">

                    <button
                    type="submit"
                    name="ganti"
                    class="btn btn-save">

                        <i class="bi bi-save-fill"></i>
                        Simpan Password

                    </button>

                    <a
                    href="setting.php"
                    class="btn-back">

                        <i class="bi bi-arrow-left"></i>
                        Kembali

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- JAVASCRIPT -->
<script>

function togglePassword(id, icon){

    const input = document.getElementById(id);

    if(input.type === "password"){

        input.type = "text";

        icon.classList.remove(
            "bi-eye-slash-fill"
        );

        icon.classList.add(
            "bi-eye-fill"
        );

    }else{

        input.type = "password";

        icon.classList.remove(
            "bi-eye-fill"
        );

        icon.classList.add(
            "bi-eye-slash-fill"
        );
    }
}

</script>

</body>
</html>