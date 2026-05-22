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
<<<<<<< HEAD
=======
// BUAT FOLDER LOGO JIKA BELUM ADA
// ======================================
if(!is_dir("../assets/logo")){

    mkdir("../assets/logo", 0777, true);
}

// ======================================
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
// AMBIL DATA TOKO
// ======================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM profil_toko LIMIT 1"
);

<<<<<<< HEAD
// JIKA BELUM ADA DATA
if(mysqli_num_rows($query) == 0){

    mysqli_query(
        $conn,
        "INSERT INTO profil_toko VALUES(
            NULL,
            'MITRA AZAM',
            'Toko Bangunan',
            'Jl. Contoh Alamat',
            '08123456789',
            'mitraazam@gmail.com',
            'Sistem kasir modern toko bangunan',
=======
// ======================================
// JIKA QUERY ERROR
// ======================================
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
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
            ''
        )"
    );

<<<<<<< HEAD
=======
    if(!$insert){

        die(
            "Insert Error : " .
            mysqli_error($conn)
        );
    }

>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
    $query = mysqli_query(
        $conn,
        "SELECT * FROM profil_toko LIMIT 1"
    );
}

$toko = mysqli_fetch_assoc($query);

// ======================================
<<<<<<< HEAD
// UPDATE DATA TOKO
// ======================================
if(isset($_POST['simpan'])){

    $nama_toko = htmlspecialchars(
        trim($_POST['nama_toko'])
    );

    $jenis_usaha = htmlspecialchars(
        trim($_POST['jenis_usaha'])
    );

    $alamat = htmlspecialchars(
        trim($_POST['alamat'])
    );

    $telepon = htmlspecialchars(
        trim($_POST['telepon'])
    );

    $email = htmlspecialchars(
        trim($_POST['email'])
    );

    $deskripsi = htmlspecialchars(
        trim($_POST['deskripsi'])
    );

    $logo_lama = $toko['logo'];

    // ======================================
    // UPLOAD LOGO
    // ======================================
    if($_FILES['logo']['name'] != ""){

        $nama_file = $_FILES['logo']['name'];
        $tmp       = $_FILES['logo']['tmp_name'];
        $size      = $_FILES['logo']['size'];

        $ext = strtolower(
            pathinfo($nama_file, PATHINFO_EXTENSION)
        );

        $format = ['jpg','jpeg','png','webp'];

=======
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
    $logo = $toko['logo'];

    // ======================================
    // JIKA ADA UPLOAD LOGO
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

        // ======================================
        // VALIDASI FORMAT
        // ======================================
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
        if(!in_array($ext, $format)){

            echo "
            <script>
<<<<<<< HEAD
                alert('Format logo harus JPG, PNG, atau WEBP');
                window.location='edit_toko.php';
=======

                alert('Format logo harus JPG, JPEG, PNG, atau WEBP');

                window.location='edit_toko.php';

>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
            </script>
            ";

            exit;
        }

<<<<<<< HEAD
=======
        // ======================================
        // VALIDASI UKURAN
        // ======================================
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
        if($size > 2000000){

            echo "
            <script>
<<<<<<< HEAD
                alert('Ukuran logo maksimal 2MB');
                window.location='edit_toko.php';
=======

                alert('Ukuran logo maksimal 2MB');

                window.location='edit_toko.php';

>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
            </script>
            ";

            exit;
        }

<<<<<<< HEAD
        $nama_baru = time() . "_" . rand(100,999) . "." . $ext;

        move_uploaded_file(
            $tmp,
            "../assets/logo/" . $nama_baru
        );

        $logo = $nama_baru;

    }else{

        $logo = $logo_lama;
=======
        // ======================================
        // VALIDASI ERROR
        // ======================================
        if($error !== 0){

            echo "
            <script>

                alert('Terjadi kesalahan upload file');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }

        // ======================================
        // NAMA FILE BARU
        // ======================================
        $nama_baru =
            "logo_" .
            time() .
            "_" .
            rand(100,999) .
            "." .
            $ext;

        // ======================================
        // PATH TUJUAN
        // ======================================
        $tujuan =
            "../assets/logo/" .
            $nama_baru;

        // ======================================
        // UPLOAD FILE
        // ======================================
        if(
            move_uploaded_file(
                $tmp,
                $tujuan
            )
        ){

            // ======================================
            // HAPUS LOGO LAMA
            // ======================================
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
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
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

<<<<<<< HEAD
         WHERE id_toko = '".$toko['id_toko']."'
        "
    );

=======
        WHERE id_toko='".$toko['id_toko']."'
        "
    );

    // ======================================
    // CEK UPDATE
    // ======================================
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
    if($update){

        echo "
        <script>
<<<<<<< HEAD
            alert('Profil toko berhasil diperbarui');
            window.location='profil_toko.php';
=======

            alert('Profil toko berhasil diperbarui');

            window.location='edit_toko.php';

>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
        </script>
        ";

    }else{

        echo "
        <script>
<<<<<<< HEAD
            alert('Gagal memperbarui profil toko');
            window.location='edit_toko.php';
        </script>
        ";
=======

            alert('Gagal update profil toko');

        </script>
        ";

        echo mysqli_error($conn);
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
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
<<<<<<< HEAD
=======
    overflow-x:hidden;
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
}

/* ======================================
SIDEBAR
====================================== */
.sidebar{

    width:270px;
    height:100vh;

    position:fixed;
    left:0;
    top:0;

    background:linear-gradient(
        180deg,
        #ff7b00,
        #d65a00
    );

    padding:25px;
    color:white;
<<<<<<< HEAD
=======

    z-index:1000;
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
}

.logo{

    text-align:center;
    margin-bottom:40px;
}

.logo i{

    font-size:45px;
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
<<<<<<< HEAD
TOPBAR
====================================== */
.topbar{

    background:white;

    padding:25px;

    border-radius:24px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.05);

    margin-bottom:30px;
}

/* ======================================
FORM CARD
====================================== */
.form-card{

    background:white;

    border-radius:24px;

    padding:30px;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.05);
}

=======
CARD
====================================== */
.card-custom{

    background:white;

    border-radius:30px;

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

    color:white;

    padding:30px;
}

.card-body-custom{

    padding:35px;
}

/* ======================================
FORM
====================================== */
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
.form-label{

    font-weight:600;
    margin-bottom:8px;
}

<<<<<<< HEAD
.form-control,
textarea{

    border-radius:14px;
    padding:12px;
}

.logo-preview{

    width:120px;
    height:120px;

    object-fit:cover;

    border-radius:20px;

    border:3px solid #eee;
}

.btn-save{

    background:#ff7b00;
=======
.form-control{

    border-radius:15px;

    padding:12px;

    border:1px solid #ddd;
}

.form-control:focus{

    border-color:#ff7b00;

    box-shadow:
    0 0 0 0.2rem rgba(255,123,0,0.2);
}

/* ======================================
LOGO PREVIEW
====================================== */
.logo-preview{

    width:160px;
    height:160px;

    object-fit:cover;

    border-radius:25px;

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

>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
    color:white;

    border:none;

    padding:12px 25px;

    border-radius:14px;

    font-weight:600;
<<<<<<< HEAD
=======

    transition:0.3s;
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
}

.btn-save:hover{

<<<<<<< HEAD
    background:#e56d00;
=======
    transform:translateY(-2px);

>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
    color:white;
}

.btn-back{

    background:#6c757d;
<<<<<<< HEAD
=======

>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
    color:white;

    border:none;

    padding:12px 25px;

    border-radius:14px;

    font-weight:600;
<<<<<<< HEAD
=======

    text-decoration:none;
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
}

.btn-back:hover{

    background:#5a6268;
<<<<<<< HEAD
    color:white;
}

=======

    color:white;
}

/* ======================================
RESPONSIVE
====================================== */
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
@media(max-width:768px){

    .sidebar{

        position:relative;
        width:100%;
        height:auto;
    }

    .content{

        margin-left:0;
    }
<<<<<<< HEAD

    .topbar{

        flex-direction:column;
        gap:20px;
        align-items:flex-start;
    }
=======
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
}

</style>

</head>

<body>

<<<<<<< HEAD
<!-- ======================================
SIDEBAR
====================================== -->
=======
<!-- SIDEBAR -->
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
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

        <a href="tambah_barang.php">

            <i class="bi bi-plus-circle"></i>
            Tambah Barang

        </a>

        <a href="laporan.php">

            <i class="bi bi-file-earmark-text"></i>
            Laporan

        </a>

        <a href="laba_rugi.php">

            <i class="bi bi-cash-stack"></i>
            Laba Rugi

        </a>

        <a href="manajemen_user.php">

            <i class="bi bi-people"></i>
            Manajemen User

        </a>

        <a href="setting.php"
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

<<<<<<< HEAD
<!-- ======================================
CONTENT
====================================== -->
<div class="content">

    <!-- TOPBAR -->
    <div class="topbar">

        <div>

            <h2 class="fw-bold">

                <i class="bi bi-shop"></i>
                Edit Profil Toko

            </h2>

            <p class="text-muted mb-0">

                Kelola informasi toko dan identitas usaha

            </p>

        </div>

        <div>

            <h5>

                <i class="bi bi-person-circle"></i>

                <?= htmlspecialchars($_SESSION['nama']); ?>

            </h5>

        </div>

    </div>

    <!-- FORM -->
    <div class="form-card">

        <form method="POST"
              enctype="multipart/form-data">

            <div class="row">

                <!-- NAMA TOKO -->
                <div class="col-md-6 mb-4">

                    <label class="form-label">

                        Nama Toko

                    </label>

                    <input
=======
<!-- CONTENT -->
<div class="content">

    <div class="card-custom">

        <!-- HEADER -->
        <div class="card-header-custom">

            <div class="d-flex
                        justify-content-between
                        align-items-center
                        flex-wrap
                        gap-3">

                <div>

                    <h2 class="fw-bold">

                        <i class="bi bi-shop"></i>
                        Edit Profil Toko

                    </h2>

                    <p class="mb-0">

                        Kelola identitas dan logo toko

                    </p>

                </div>

                <div>

                    <i class="bi bi-person-circle"></i>

                    <?= htmlspecialchars($_SESSION['nama']); ?>

                </div>

            </div>

        </div>

        <!-- BODY -->
        <div class="card-body-custom">

            <form method="POST"
                  enctype="multipart/form-data">

                <div class="row">

                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Nama Toko

                        </label>

                        <input
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
                        type="text"
                        name="nama_toko"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['nama_toko']); ?>"
                        required>

<<<<<<< HEAD
                </div>

                <!-- JENIS USAHA -->
                <div class="col-md-6 mb-4">

                    <label class="form-label">

                        Jenis Usaha

                    </label>

                    <input
=======
                    </div>

                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Jenis Usaha

                        </label>

                        <input
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
                        type="text"
                        name="jenis_usaha"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['jenis_usaha']); ?>"
                        required>

<<<<<<< HEAD
                </div>

                <!-- TELEPON -->
                <div class="col-md-6 mb-4">

                    <label class="form-label">

                        Nomor Telepon

                    </label>

                    <input
=======
                    </div>

                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Nomor Telepon

                        </label>

                        <input
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
                        type="text"
                        name="telepon"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['telepon']); ?>"
                        required>

<<<<<<< HEAD
                </div>

                <!-- EMAIL -->
                <div class="col-md-6 mb-4">

                    <label class="form-label">

                        Email

                    </label>

                    <input
=======
                    </div>

                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Email

                        </label>

                        <input
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['email']); ?>">

<<<<<<< HEAD
                </div>

                <!-- ALAMAT -->
                <div class="col-md-12 mb-4">

                    <label class="form-label">

                        Alamat Toko

                    </label>

                    <textarea
=======
                    </div>

                    <div class="col-md-12 mb-4">

                        <label class="form-label">

                            Alamat

                        </label>

                        <textarea
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
                        name="alamat"
                        rows="3"
                        class="form-control"
                        required><?= htmlspecialchars($toko['alamat']); ?></textarea>

<<<<<<< HEAD
                </div>

                <!-- DESKRIPSI -->
                <div class="col-md-12 mb-4">

                    <label class="form-label">

                        Deskripsi Toko

                    </label>

                    <textarea
=======
                    </div>

                    <div class="col-md-12 mb-4">

                        <label class="form-label">

                            Deskripsi

                        </label>

                        <textarea
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
                        name="deskripsi"
                        rows="4"
                        class="form-control"><?= htmlspecialchars($toko['deskripsi']); ?></textarea>

<<<<<<< HEAD
                </div>

                <!-- LOGO -->
                <div class="col-md-12 mb-4">

                    <label class="form-label">

                        Logo Toko

                    </label>

                    <input
=======
                    </div>

                    <div class="col-md-12 mb-4">

                        <label class="form-label">

                            Upload Logo

                        </label>

                        <input
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21
                        type="file"
                        name="logo"
                        class="form-control">

<<<<<<< HEAD
                    <small class="text-muted">

                        Format: JPG, PNG, WEBP (maksimal 2MB)

                    </small>

                </div>

                <!-- PREVIEW LOGO -->
                <div class="col-md-12 mb-4">

                    <?php if($toko['logo'] != ""): ?>

                        <img
                            src="../assets/logo/<?= $toko['logo']; ?>"
                            class="logo-preview">

                    <?php else: ?>

                        <img
                            src="https://via.placeholder.com/120"
                            class="logo-preview">

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
                    Simpan Perubahan

                </button>

                <a
                    href="profil_toko.php"
                    class="btn-back text-decoration-none">

                    <i class="bi bi-arrow-left"></i>
                    Kembali

                </a>

            </div>

        </form>
=======
                        <small class="text-muted">

                            JPG, PNG, WEBP maksimal 2MB

                        </small>

                        <br>

                        <?php if($toko['logo'] != ""): ?>

                            <img
                            src="../assets/logo/<?= $toko['logo']; ?>"
                            class="logo-preview">

                        <?php else: ?>

                            <img
                            src="https://via.placeholder.com/160"
                            class="logo-preview">

                        <?php endif; ?>

                    </div>

                </div>

                <div class="d-flex gap-2">

                    <button
                    type="submit"
                    name="simpan"
                    class="btn btn-save">

                        <i class="bi bi-save-fill"></i>
                        Simpan Perubahan

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
>>>>>>> f205cce7a69e52192516bd713030aa7fd325ed21

    </div>

</div>

</body>
</html>