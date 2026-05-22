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
// SIMPAN PROFIL TOKO
// ======================================
if(isset($_POST['simpan'])){

    $nama_toko     = htmlspecialchars($_POST['nama_toko']);
    $pemilik       = htmlspecialchars($_POST['pemilik']);
    $telepon       = htmlspecialchars($_POST['telepon']);
    $email         = htmlspecialchars($_POST['email']);
    $alamat        = htmlspecialchars($_POST['alamat']);
    $deskripsi     = htmlspecialchars($_POST['deskripsi']);

    // ============================
    // CEK DATA SUDAH ADA / BELUM
    // ============================
    $cek = mysqli_query(
        $conn,
        "SELECT * FROM profil_toko LIMIT 1"
    );

    // ============================
    // UPDATE
    // ============================
    if(mysqli_num_rows($cek) > 0){

        $update = mysqli_query(
            $conn,
            "UPDATE profil_toko SET
                nama_toko='$nama_toko',
                pemilik='$pemilik',
                telepon='$telepon',
                email='$email',
                alamat='$alamat',
                deskripsi='$deskripsi'
            WHERE id_profil=1"
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
            </script>
            ";
        }

    }else{

        // ============================
        // INSERT
        // ============================
        $insert = mysqli_query(
            $conn,
            "INSERT INTO profil_toko VALUES(
                1,
                '$nama_toko',
                '$pemilik',
                '$telepon',
                '$email',
                '$alamat',
                '$deskripsi'
            )"
        );

        if($insert){

            echo "
            <script>
                alert('Profil toko berhasil disimpan');
                window.location='profil_toko.php';
            </script>
            ";

        }else{

            echo "
            <script>
                alert('Gagal menyimpan profil toko');
            </script>
            ";
        }
    }
}

// ======================================
// AMBIL DATA PROFIL
// ======================================
$data = mysqli_query(
    $conn,
    "SELECT * FROM profil_toko LIMIT 1"
);

$profil = mysqli_fetch_assoc($data);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Profil Toko</title>

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
    overflow-x:hidden;
}

/* ===================================
SIDEBAR
=================================== */
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

.sidebar a{

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

.sidebar a:hover{

    background:rgba(255,255,255,0.15);

    transform:translateX(5px);
}

.sidebar a.active{

    background:white;
    color:#ff7b00;

    font-weight:600;
}

/* ===================================
CONTENT
=================================== */
.content{

    margin-left:270px;
    padding:30px;
}

/* ===================================
TOPBAR
=================================== */
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

.user-box{

    display:flex;
    align-items:center;
    gap:15px;
}

.user-icon{

    width:50px;
    height:50px;

    border-radius:50%;

    background:#ffedd5;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:24px;

    color:#ff7b00;
}

/* ===================================
CARD
=================================== */
.form-card{

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

/* ===================================
FORM
=================================== */
.form-label{

    font-weight:600;
    margin-bottom:8px;
}

.form-control,
textarea{

    border-radius:14px !important;

    padding:12px;

    border:1px solid #ddd;
}

.form-control:focus,
textarea:focus{

    border-color:#ff7b00;

    box-shadow:
    0 0 0 0.2rem rgba(255,123,0,0.2);
}

/* ===================================
BUTTON
=================================== */
.btn-save{

    background:linear-gradient(
        135deg,
        #198754,
        #20c997
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

/* ===================================
RESPONSIVE
=================================== */
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
    }
}

</style>

</head>

<body>

<!-- ===================================
SIDEBAR
=================================== -->
<div class="sidebar">

    <div class="logo">

        <i class="bi bi-shop-window"></i>

        <h2>MITRA AZAM</h2>

    </div>

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

    <a href="setting.php">

        <i class="bi bi-gear-fill"></i>
        Setting

    </a>

    <a href="profil_toko.php"
       class="active">

        <i class="bi bi-shop"></i>
        Profil Toko

    </a>

    <a href="../auth/logout.php">

        <i class="bi bi-box-arrow-right"></i>
        Logout

    </a>

</div>

<!-- ===================================
CONTENT
=================================== -->
<div class="content">

    <!-- TOPBAR -->
    <div class="topbar">

        <div>

            <h2 class="fw-bold">

                <i class="bi bi-shop text-warning"></i>
                Profil Toko

            </h2>

            <p class="text-muted mb-0">

                Kelola informasi toko bangunan

            </p>

        </div>

        <div class="user-box">

            <div class="user-icon">

                <i class="bi bi-person-fill"></i>

            </div>

            <div>

                <h6 class="mb-0 fw-bold">

                    <?= htmlspecialchars($_SESSION['nama']); ?>

                </h6>

                <small class="text-muted">

                    Administrator

                </small>

            </div>

        </div>

    </div>

    <!-- FORM -->
    <div class="form-card">

        <div class="card-header-custom">

            <h4 class="mb-0">

                <i class="bi bi-pencil-square"></i>
                Edit Profil Toko

            </h4>

        </div>

        <div class="card-body-custom">

            <form method="POST">

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
                            value="<?= $profil['nama_toko'] ?? ''; ?>"
                            required>

                    </div>

                    <!-- PEMILIK -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Nama Pemilik

                        </label>

                        <input
                            type="text"
                            name="pemilik"
                            class="form-control"
                            value="<?= $profil['pemilik'] ?? ''; ?>"
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
                            value="<?= $profil['telepon'] ?? ''; ?>"
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
                            value="<?= $profil['email'] ?? ''; ?>">

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
                            required><?= $profil['alamat'] ?? ''; ?></textarea>

                    </div>

                    <!-- DESKRIPSI -->
                    <div class="col-md-12 mb-4">

                        <label class="form-label">

                            Deskripsi Toko

                        </label>

                        <textarea
                            name="deskripsi"
                            rows="4"
                            class="form-control"><?= $profil['deskripsi'] ?? ''; ?></textarea>

                    </div>

                </div>

                <!-- BUTTON -->
                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-save">

                    <i class="bi bi-save-fill"></i>
                    Simpan Profil

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>