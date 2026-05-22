<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK LOGIN ADMIN
// ======================================
if (
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'admin'
) {

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// AMBIL ID USER
// ======================================
$id_user = $_SESSION['id_user'];

// ======================================
// AMBIL DATA ADMIN
// ======================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM users
     WHERE id_user = '$id_user'"
);

if (!$query) {

    die(
        "Query Error : " .
        mysqli_error($conn)
    );
}

$admin = mysqli_fetch_assoc($query);

// ======================================
// CEK JIKA DATA TIDAK ADA
// ======================================
if (!$admin) {

    die("Data admin tidak ditemukan");
}

// ======================================
// CEGAH WARNING ARRAY KEY
// ======================================
$nama     = $admin['nama'] ?? '';
$username = $admin['username'] ?? '';
$email    = $admin['email'] ?? '';
$telepon  = $admin['telepon'] ?? '';
$foto     = $admin['foto'] ?? '';

// ======================================
// PROSES UPDATE
// ======================================
if (isset($_POST['simpan'])) {

    $nama = mysqli_real_escape_string(
        $conn,
        trim($_POST['nama'])
    );

    $username = mysqli_real_escape_string(
        $conn,
        trim($_POST['username'])
    );

    $email = mysqli_real_escape_string(
        $conn,
        trim($_POST['email'])
    );

    $telepon = mysqli_real_escape_string(
        $conn,
        trim($_POST['telepon'])
    );

    // ======================================
    // VALIDASI USERNAME DUPLIKAT
    // ======================================
    $cek_username = mysqli_query(
        $conn,
        "SELECT * FROM users
         WHERE username='$username'
         AND id_user != '$id_user'"
    );

    if (mysqli_num_rows($cek_username) > 0) {

        echo "
        <script>

            alert('Username sudah digunakan');

            window.location='edit_admin.php';

        </script>
        ";

        exit;
    }

    // ======================================
    // FOLDER FOTO
    // ======================================
    $folder = "../assets/admin/";

    if (!file_exists($folder)) {

        mkdir($folder, 0777, true);
    }

    // ======================================
    // UPLOAD FOTO
    // ======================================
    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['name'] != ""
    ) {

        $nama_file = $_FILES['foto']['name'];
        $tmp       = $_FILES['foto']['tmp_name'];
        $size      = $_FILES['foto']['size'];
        $error     = $_FILES['foto']['error'];

        $ext = strtolower(
            pathinfo(
                $nama_file,
                PATHINFO_EXTENSION
            )
        );

        $format = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        // VALIDASI FILE
        if ($error !== 0) {

            echo "
            <script>

                alert('Gagal upload foto');

            </script>
            ";

        } elseif (!in_array($ext, $format)) {

            echo "
            <script>

                alert('Format foto harus JPG, PNG, JPEG, WEBP');

            </script>
            ";

        } elseif ($size > 2000000) {

            echo "
            <script>

                alert('Ukuran foto maksimal 2MB');

            </script>
            ";

        } else {

            // ======================================
            // NAMA FILE BARU
            // ======================================
            $nama_baru =
                "admin_" .
                time() .
                "_" .
                rand(100, 999) .
                "." .
                $ext;

            $tujuan = $folder . $nama_baru;

            // ======================================
            // PINDAH FILE
            // ======================================
            if (
                move_uploaded_file(
                    $tmp,
                    $tujuan
                )
            ) {

                // ======================================
                // HAPUS FOTO LAMA
                // ======================================
                if (
                    $foto != "" &&
                    file_exists($folder . $foto)
                ) {

                    unlink($folder . $foto);
                }

                $foto = $nama_baru;

            } else {

                echo "
                <script>

                    alert('Upload foto gagal');

                </script>
                ";
            }
        }
    }

    // ======================================
    // UPDATE DATABASE
    // ======================================
    $update = mysqli_query(
        $conn,
        "UPDATE users SET

            nama     = '$nama',
            username = '$username',
            email    = '$email',
            telepon  = '$telepon',
            foto     = '$foto'

         WHERE id_user = '$id_user'"
    );

    // ======================================
    // CEK UPDATE
    // ======================================
    if ($update) {

        $_SESSION['nama'] = $nama;

        echo "
        <script>

            alert('Profil admin berhasil diperbarui');

            window.location='setting.php';

        </script>
        ";

    } else {

        echo "
        <script>

            alert('Gagal memperbarui profil');

        </script>
        ";

        echo mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Profil Admin</title>

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
    background:#f4f7fb;
    overflow-x:hidden;
}

/* ======================================
SIDEBAR
====================================== */
.sidebar{

    width:270px;
    height:100vh;

    position:fixed;
    top:0;
    left:0;

    background:linear-gradient(
        180deg,
        #ff7b00,
        #d65a00
    );

    padding:25px;
    color:white;
}

.logo{

    text-align:center;
    margin-bottom:40px;
}

.logo i{

    font-size:50px;
}

.logo h2{

    margin-top:10px;
    font-weight:700;
}

.sidebar-menu a{

    display:flex;
    align-items:center;
    gap:12px;

    color:white;
    text-decoration:none;

    padding:14px 18px;

    margin-bottom:12px;

    border-radius:16px;

    transition:0.3s;
}

.sidebar-menu a:hover{

    background:rgba(255,255,255,0.15);

    transform:translateX(5px);
}

.sidebar-menu a.active{

    background:white;
    color:#ff7b00;

    font-weight:600;
}

/* ======================================
CONTENT
====================================== */
.content{

    margin-left:270px;
    padding:30px;
}

/* ======================================
TOPBAR
====================================== */
.topbar{

    background:white;

    padding:25px;

    border-radius:24px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-bottom:30px;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.05);
}

/* ======================================
CARD
====================================== */
.card-custom{

    background:white;

    border-radius:24px;

    overflow:hidden;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.05);
}

.card-header-custom{

    background:linear-gradient(
        135deg,
        #ff7b00,
        #ff9f43
    );

    color:white;

    padding:25px;
}

.card-body-custom{

    padding:30px;
}

/* ======================================
FORM
====================================== */
.form-label{

    font-weight:600;
    margin-bottom:8px;
}

.form-control{

    border-radius:14px;
    padding:12px;

    border:1px solid #ddd;
}

.form-control:focus{

    border-color:#ff7b00;

    box-shadow:
    0 0 0 0.2rem rgba(255,123,0,0.2);
}

/* ======================================
FOTO
====================================== */
.foto-preview{

    width:150px;
    height:150px;

    object-fit:cover;

    border-radius:50%;

    border:5px solid #eee;

    margin-top:15px;
}

/* ======================================
BUTTON
====================================== */
.btn-save{

    background:linear-gradient(
        135deg,
        #ff7b00,
        #ff9f43
    );

    border:none;

    color:white;

    padding:12px 25px;

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

    padding:12px 25px;

    border-radius:14px;

    text-decoration:none;

    font-weight:600;
}

.btn-back:hover{

    background:#5a6268;

    color:white;
}

/* ======================================
RESPONSIVE
====================================== */
@media(max-width:768px){

    .sidebar{

        position:relative;
        width:100%;
        height:auto;
    }

    .content{

        margin-left:0;
    }

    .topbar{

        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo">

        <i class="bi bi-shop-window"></i>

        <h2>MITRA AZAM</h2>

    </div>

    <div class="sidebar-menu">

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

        <a href="manajemen_user.php">

            <i class="bi bi-people"></i>
            Manajemen User

        </a>

        <a
        href="setting.php"
        class="active">

            <i class="bi bi-gear-fill"></i>
            Setting

        </a>

        <a href="../auth/logout.php">

            <i class="bi bi-box-arrow-right"></i>
            Logout

        </a>

    </div>

</div>

<!-- CONTENT -->
<div class="content">

    <!-- TOPBAR -->
    <div class="topbar">

        <div>

            <h2 class="fw-bold">

                <i class="bi bi-person-circle text-warning"></i>
                Edit Profil Admin

            </h2>

            <p class="text-muted mb-0">

                Kelola informasi administrator

            </p>

        </div>

        <div>

            <strong>

                <?= htmlspecialchars($_SESSION['nama']); ?>

            </strong>

        </div>

    </div>

    <!-- CARD -->
    <div class="card-custom">

        <div class="card-header-custom">

            <h4 class="mb-0">

                <i class="bi bi-pencil-square"></i>
                Form Edit Profil

            </h4>

        </div>

        <div class="card-body-custom">

            <form
            method="POST"
            enctype="multipart/form-data">

                <div class="row">

                    <!-- NAMA -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Nama Lengkap

                        </label>

                        <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="<?= htmlspecialchars($nama); ?>"
                        required>

                    </div>

                    <!-- USERNAME -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Username

                        </label>

                        <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= htmlspecialchars($username); ?>"
                        required>

                    </div>

                    <!-- EMAIL -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Email

                        </label>

                        <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($email); ?>">

                    </div>

                    <!-- TELEPON -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Nomor Telepon

                        </label>

                        <input
                        type="text"
                        name="telepon"
                        class="form-control"
                        value="<?= htmlspecialchars($telepon); ?>">

                    </div>

                    <!-- FOTO -->
                    <div class="col-md-12 mb-4">

                        <label class="form-label">

                            Foto Profil

                        </label>

                        <input
                        type="file"
                        name="foto"
                        class="form-control">

                        <small class="text-muted">

                            JPG, PNG, JPEG, WEBP maksimal 2MB

                        </small>

                        <br>

                        <?php if($foto != ""): ?>

                            <img
                            src="../assets/admin/<?= htmlspecialchars($foto); ?>"
                            class="foto-preview">

                        <?php else: ?>

                            <img
                            src="https://via.placeholder.com/150"
                            class="foto-preview">

                        <?php endif; ?>

                    </div>

                </div>

                <!-- BUTTON -->
                <div class="d-flex gap-2">

                    <button
                    type="submit"
                    name="simpan"
                    class="btn-save">

                        <i class="bi bi-save-fill"></i>
                        Simpan

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

</body>
</html>