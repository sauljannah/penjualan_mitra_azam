
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
// AMBIL DATA TOKO
// ======================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM profil_toko LIMIT 1"
);

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
            ''
        )"
    );

    $query = mysqli_query(
        $conn,
        "SELECT * FROM profil_toko LIMIT 1"
    );
}

$toko = mysqli_fetch_assoc($query);

// ======================================
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

        if(!in_array($ext, $format)){

            echo "
            <script>
                alert('Format logo harus JPG, PNG, atau WEBP');
                window.location='edit_toko.php';
            </script>
            ";

            exit;
        }

        if($size > 2000000){

            echo "
            <script>
                alert('Ukuran logo maksimal 2MB');
                window.location='edit_toko.php';
            </script>
            ";

            exit;
        }

        $nama_baru = time() . "_" . rand(100,999) . "." . $ext;

        move_uploaded_file(
            $tmp,
            "../assets/logo/" . $nama_baru
        );

        $logo = $nama_baru;

    }else{

        $logo = $logo_lama;
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

         WHERE id_toko = '".$toko['id_toko']."'
        "
    );

    if($update){

        echo "
        <script>
            alert('Profil toko berhasil diperbarui');
            window.location='profil_toko.php';
        </script>
        ";

    }else{

        echo "
        <script>
            alert('Gagal memperbarui profil toko');
            window.location='edit_toko.php';
        </script>
        ";
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

.form-label{

    font-weight:600;
    margin-bottom:8px;
}

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
    color:white;

    border:none;

    padding:12px 25px;

    border-radius:14px;

    font-weight:600;
}

.btn-save:hover{

    background:#e56d00;
    color:white;
}

.btn-back{

    background:#6c757d;
    color:white;

    border:none;

    padding:12px 25px;

    border-radius:14px;

    font-weight:600;
}

.btn-back:hover{

    background:#5a6268;
    color:white;
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

    .topbar{

        flex-direction:column;
        gap:20px;
        align-items:flex-start;
    }
}

</style>

</head>

<body>

<!-- ======================================
SIDEBAR
====================================== -->
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
                        type="text"
                        name="nama_toko"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['nama_toko']); ?>"
                        required>

                </div>

                <!-- JENIS USAHA -->
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

                <!-- TELEPON -->
                <div class="col-md-6 mb-4">

                    <label class="form-label">

                        Nomor Telepon

                    </label>

                    <input
                        type="text"
                        name="telepon"
                        class="form-control"
                        value="<?= htmlspecialchars($toko['telepon']); ?>"
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
                        value="<?= htmlspecialchars($toko['email']); ?>">

                </div>

                <!-- ALAMAT -->
                <div class="col-md-12 mb-4">

                    <label class="form-label">

                        Alamat Toko

                    </label>

                    <textarea
                        name="alamat"
                        rows="3"
                        class="form-control"
                        required><?= htmlspecialchars($toko['alamat']); ?></textarea>

                </div>

                <!-- DESKRIPSI -->
                <div class="col-md-12 mb-4">

                    <label class="form-label">

                        Deskripsi Toko

                    </label>

                    <textarea
                        name="deskripsi"
                        rows="4"
                        class="form-control"><?= htmlspecialchars($toko['deskripsi']); ?></textarea>

                </div>

                <!-- LOGO -->
                <div class="col-md-12 mb-4">

                    <label class="form-label">

                        Logo Toko

                    </label>

                    <input
                        type="file"
                        name="logo"
                        class="form-control">

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

    </div>

</div>

</body>
</html>