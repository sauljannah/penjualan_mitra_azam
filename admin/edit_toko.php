<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if(
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'admin'
){

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// BUAT FOLDER LOGO
// ======================================
if(!is_dir("../assets/logo")){

    mkdir("../assets/logo", 0777, true);
}

// ======================================
// AMBIL DATA TOKO
// ======================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM profil_toko LIMIT 1"
);

if(!$query){

    die(
        "Query Error : " .
        mysqli_error($conn)
    );
}

// ======================================
// JIKA DATA BELUM ADA
// ======================================
if(mysqli_num_rows($query) == 0){

    $insert = mysqli_query(
        $conn,
        "INSERT INTO profil_toko
        (
            nama_toko,
            jenis_usaha,
            alamat,
            telepon,
            email,
            deskripsi,
            logo
        )

        VALUES
        (
            'MITRA AZAM',
            'Toko Bangunan',
            'Alamat Toko',
            '08123456789',
            'mitraazam@gmail.com',
            'Sistem Kasir Modern',
            ''
        )"
    );

    if(!$insert){

        die(
            "Insert Error : " .
            mysqli_error($conn)
        );
    }

    $query = mysqli_query(
        $conn,
        "SELECT * FROM profil_toko LIMIT 1"
    );
}

$toko = mysqli_fetch_assoc($query);

// ======================================
// PROSES UPDATE
// ======================================
if(isset($_POST['simpan'])){

    $nama_toko = mysqli_real_escape_string(
        $conn,
        trim($_POST['nama_toko'])
    );

    $jenis_usaha = mysqli_real_escape_string(
        $conn,
        trim($_POST['jenis_usaha'])
    );

    $alamat = mysqli_real_escape_string(
        $conn,
        trim($_POST['alamat'])
    );

    $telepon = mysqli_real_escape_string(
        $conn,
        trim($_POST['telepon'])
    );

    $email = mysqli_real_escape_string(
        $conn,
        trim($_POST['email'])
    );

    $deskripsi = mysqli_real_escape_string(
        $conn,
        trim($_POST['deskripsi'])
    );

    // ======================================
    // LOGO LAMA
    // ======================================
    $logo = isset($toko['logo'])
        ? $toko['logo']
        : '';

    // ======================================
    // UPLOAD LOGO
    // ======================================
    if(
        isset($_FILES['logo']) &&
        $_FILES['logo']['name'] != ""
    ){

        $nama_file = $_FILES['logo']['name'];
        $tmp       = $_FILES['logo']['tmp_name'];
        $size      = $_FILES['logo']['size'];
        $error     = $_FILES['logo']['error'];

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

        // VALIDASI FORMAT
        if(!in_array($ext, $format)){

            echo "
            <script>

                alert('Format logo harus JPG, JPEG, PNG, atau WEBP');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }

        // VALIDASI UKURAN
        if($size > 2000000){

            echo "
            <script>

                alert('Ukuran logo maksimal 2MB');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }

        // VALIDASI ERROR
        if($error !== 0){

            echo "
            <script>

                alert('Terjadi kesalahan upload file');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }

        // NAMA FILE BARU
        $nama_baru =
            "logo_" .
            time() .
            "_" .
            rand(100,999) .
            "." .
            $ext;

        $tujuan =
            "../assets/logo/" .
            $nama_baru;

        // UPLOAD FILE
        if(
            move_uploaded_file(
                $tmp,
                $tujuan
            )
        ){

            // HAPUS LOGO LAMA
            if(
                $logo != "" &&
                file_exists(
                    "../assets/logo/" . $logo
                )
            ){

                unlink(
                    "../assets/logo/" . $logo
                );
            }

            $logo = $nama_baru;

        }else{

            echo "
            <script>

                alert('Gagal upload logo');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }
    }

    // ======================================
    // UPDATE DATABASE
    // ======================================
    $update = mysqli_query(
        $conn,
        "UPDATE profil_toko SET

            nama_toko   = '$nama_toko',
            jenis_usaha = '$jenis_usaha',
            alamat      = '$alamat',
            telepon     = '$telepon',
            email       = '$email',
            deskripsi   = '$deskripsi',
            logo        = '$logo'

        WHERE id_toko='".$toko['id_toko']."'
        "
    );

    // ======================================
    // CEK UPDATE
    // ======================================
    if($update){

        echo "
        <script>

            alert('Profil toko berhasil diperbarui');

            window.location='setting.php';

        </script>
        ";

    }else{

        echo "
        <script>

            alert('Gagal update profil toko');

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

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Profil Toko</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

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
    background:#f4f6f9;
    font-family:'Segoe UI',sans-serif;
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
    font-weight:bold;
}

.sidebar-menu a{

    display:flex;
    align-items:center;
    gap:12px;

    color:white;
    text-decoration:none;

    padding:14px 18px;

    margin-bottom:12px;

    border-radius:14px;

    transition:0.3s;
}

.sidebar-menu a:hover{

    background:rgba(255,255,255,0.15);

    transform:translateX(5px);
}

.sidebar-menu a.active{

    background:white;
    color:#ff7b00;
    font-weight:bold;
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

    border-radius:20px;

    margin-bottom:30px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.05);
}

/* ======================================
CARD
====================================== */
.card-custom{

    background:white;

    border-radius:20px;

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
}

.form-control{

    border-radius:12px;
    padding:12px;
}

.form-control:focus{

    border-color:#ff7b00;

    box-shadow:
    0 0 0 0.2rem rgba(255,123,0,0.2);
}

/* ======================================
LOGO
====================================== */
.logo-preview{

    width:150px;
    height:150px;

    object-fit:cover;

    border-radius:20px;

    border:4px solid #eee;

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

    border-radius:12px;

    font-weight:600;
}

.btn-save:hover{

    opacity:0.9;
    color:white;
}

.btn-back{

    background:#6c757d;

    color:white;

    padding:12px 25px;

    border-radius:12px;

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

                <i class="bi bi-shop text-warning"></i>

                Edit Profil Toko

            </h2>

            <p class="text-muted mb-0">

                Kelola identitas toko

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

            <h4>

                <i class="bi bi-pencil-square"></i>

                Form Edit Toko

            </h4>

        </div>

        <div class="card-body-custom">

            <form
            method="POST"
            enctype="multipart/form-data">

                <div class="row">

                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Nama Toko

                        </label>

                        <input
                        type="text"
                        name="nama_toko"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['nama_toko']); ?>"
                        required>

                    </div>

                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Jenis Usaha

                        </label>

                        <input
                        type="text"
                        name="jenis_usaha"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['jenis_usaha']); ?>"
                        required>

                    </div>

                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Telepon

                        </label>

                        <input
                        type="text"
                        name="telepon"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['telepon']); ?>">

                    </div>

                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Email

                        </label>

                        <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['email']); ?>">

                    </div>

                    <div class="col-md-12 mb-4">

                        <label class="form-label">

                            Alamat

                        </label>

                        <textarea
                        name="alamat"
                        rows="3"
                        class="form-control"><?= htmlspecialchars($toko['alamat']); ?></textarea>

                    </div>

                    <div class="col-md-12 mb-4">

                        <label class="form-label">

                            Deskripsi

                        </label>

                        <textarea
                        name="deskripsi"
                        rows="4"
                        class="form-control"><?= htmlspecialchars($toko['deskripsi']); ?></textarea>

                    </div>

                    <div class="col-md-12 mb-4">

                        <label class="form-label">

                            Upload Logo

                        </label>

                        <input
                        type="file"
                        name="logo"
                        class="form-control">

                        <small class="text-muted">

                            JPG, PNG, JPEG, WEBP maksimal 2MB

                        </small>

                        <br>

                        <?php if(!empty($toko['logo'])): ?>

                            <img
                            src="../assets/logo/<?= $toko['logo']; ?>"
                            class="logo-preview">

                        <?php else: ?>

                            <img
                            src="https://via.placeholder.com/150"
                            class="logo-preview">

                        <?php endif; ?>

                    </div>

                </div>

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