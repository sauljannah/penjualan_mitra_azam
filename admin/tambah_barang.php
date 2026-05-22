<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if(!isset($_SESSION['level'])){

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// SIMPAN DATA BARANG
// ======================================
if(isset($_POST['simpan'])){

    $kode     = trim(htmlspecialchars($_POST['kode_barang']));
    $nama     = trim(htmlspecialchars($_POST['nama_barang']));
    $beli     = (int) $_POST['harga_beli'];
    $jual     = (int) $_POST['harga_jual'];
    $stok     = (int) $_POST['stok'];
    $minimum  = (int) $_POST['stok_minimum'];

    // ======================================
    // VALIDASI INPUT
    // ======================================
    if(
        empty($kode) ||
        empty($nama)
    ){

        echo "
        <script>
            alert('Kode dan Nama Barang wajib diisi!');
            window.history.back();
        </script>
        ";

        exit;
    }

    // ======================================
    // VALIDASI HARGA
    // ======================================
    if($jual < $beli){

        echo "
        <script>
            alert('Harga jual tidak boleh lebih kecil dari harga beli!');
            window.history.back();
        </script>
        ";

        exit;
    }

    // ======================================
    // CEK KODE BARANG
    // ======================================
    // ATURAN:
    // 1. Jika kode barang sama
    //    DAN nama barang sama
    //    => BOLEH disimpan
    //
    // 2. Jika kode barang sama
    //    TAPI nama barang berbeda
    //    => TIDAK BOLEH
    // ======================================

    $cek_kode = mysqli_query(

        $conn,

        "SELECT *
         FROM barang
         WHERE kode_barang = '$kode'"

    );

    $boleh_simpan = true;

    if(mysqli_num_rows($cek_kode) > 0){

        while($data_cek = mysqli_fetch_assoc($cek_kode)){

            // ======================================
            // JIKA KODE SAMA
            // TAPI NAMA BERBEDA
            // ======================================
            if(
                strtolower(trim($data_cek['nama_barang']))
                !=
                strtolower(trim($nama))
            ){

                $boleh_simpan = false;
                break;
            }
        }
    }

    // ======================================
    // JIKA TIDAK BOLEH SIMPAN
    // ======================================
    if(!$boleh_simpan){

        echo "
        <script>
            alert('Kode barang sudah digunakan untuk barang lain!');
            window.history.back();
        </script>
        ";

        exit;
    }

    // ======================================
    // SIMPAN DATA
    // ======================================
    $simpan = mysqli_query(

        $conn,

        "INSERT INTO barang VALUES(
            NULL,
            '$kode',
            '$nama',
            '$beli',
            '$jual',
            '$stok',
            '$minimum',
            NOW()
        )"

    );

    // ======================================
    // CEK HASIL SIMPAN
    // ======================================
    if($simpan){

        echo "
        <script>
            alert('Data Barang Berhasil Ditambahkan');
            window.location='barang.php';
        </script>
        ";

    }else{

        echo "
        <script>
            alert('Data Gagal Disimpan');
            window.history.back();
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

<title>Tambah Barang</title>

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
    margin:0;
    background:#f4f6f9;
    font-family:Arial,sans-serif;
}

/* ===================================
SIDEBAR
=================================== */
.sidebar{

    width:260px;
    height:100vh;
    position:fixed;
    background:linear-gradient(
        180deg,
        #ff7b00,
        #d65a00
    );

    padding:20px;
    overflow-y:auto;
}

.sidebar h3{

    color:white;
    text-align:center;
    margin-bottom:30px;
    font-weight:bold;
}

.sidebar a{

    display:block;
    color:white;
    text-decoration:none;
    padding:13px;
    border-radius:12px;
    margin-bottom:10px;
    transition:0.3s;
}

.sidebar a:hover{

    background:rgba(255,255,255,0.2);
    transform:translateX(5px);
}

/* ===================================
CONTENT
=================================== */
.content{

    margin-left:260px;
    padding:25px;
}

/* ===================================
TOPBAR
=================================== */
.topbar{

    background:white;
    padding:20px;
    border-radius:20px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    margin-bottom:25px;
}

/* ===================================
CARD FORM
=================================== */
.form-card{

    background:white;
    border-radius:25px;
    box-shadow:0 8px 20px rgba(0,0,0,0.05);
    overflow:hidden;
}

.form-header{

    background:linear-gradient(
        135deg,
        #ff7b00,
        #ff9f43
    );

    padding:20px;
    color:white;
}

.form-body{

    padding:30px;
}

/* ===================================
FORM INPUT
=================================== */
.form-label{

    font-weight:bold;
    margin-bottom:8px;
    color:#444;
}

.form-control{

    border-radius:15px;
    padding:12px;
    border:1px solid #ddd;
    transition:0.3s;
}

.form-control:focus{

    border-color:#ff7b00;
    box-shadow:0 0 0 0.2rem rgba(255,123,0,0.2);
}

/* ===================================
BUTTON
=================================== */
.btn-custom{

    padding:12px 25px;
    border:none;
    border-radius:15px;
    font-weight:bold;
    transition:0.3s;
}

.btn-save{

    background:linear-gradient(
        135deg,
        #198754,
        #20c997
    );

    color:white;
}

.btn-save:hover{

    transform:translateY(-2px);
    color:white;
}

.btn-back{

    background:#6c757d;
    color:white;
}

.btn-back:hover{

    background:#5c636a;
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
}

</style>

</head>

<body>

<!-- ===================================
SIDEBAR
=================================== -->
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

    <a href="tambah_barang.php"
       style="background:rgba(255,255,255,0.2);">

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
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap">

        <div>

            <h2 class="fw-bold">

                Tambah Barang

            </h2>

            <p class="text-muted mb-0">

                Form input data barang toko bangunan

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

        <div class="form-header">

            <h4 class="mb-0">

                <i class="bi bi-box-seam"></i>
                Form Tambah Barang

            </h4>

        </div>

        <div class="form-body">

            <form method="POST">

                <div class="row">

                    <!-- KODE -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Kode Barang

                        </label>

                        <input type="text"
                               name="kode_barang"
                               class="form-control"
                               placeholder="Masukkan Kode Barang"
                               required>

                    </div>

                    <!-- NAMA -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Nama Barang

                        </label>

                        <input type="text"
                               name="nama_barang"
                               class="form-control"
                               placeholder="Masukkan Nama Barang"
                               required>

                    </div>

                    <!-- HARGA BELI -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Harga Beli

                        </label>

                        <input type="number"
                               name="harga_beli"
                               class="form-control"
                               placeholder="Masukkan Harga Beli"
                               required>

                    </div>

                    <!-- HARGA JUAL -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Harga Jual

                        </label>

                        <input type="number"
                               name="harga_jual"
                               class="form-control"
                               placeholder="Masukkan Harga Jual"
                               required>

                    </div>

                    <!-- STOK -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Jumlah Stok

                        </label>

                        <input type="number"
                               name="stok"
                               class="form-control"
                               placeholder="Masukkan Jumlah Stok"
                               required>

                    </div>

                    <!-- STOK MINIMUM -->
                    <div class="col-md-6 mb-4">

                        <label class="form-label">

                            Stok Minimum

                        </label>

                        <input type="number"
                               name="stok_minimum"
                               class="form-control"
                               placeholder="Minimal Stok"
                               required>

                    </div>

                </div>

                <!-- BUTTON -->
                <div class="mt-3 d-flex gap-2">

                    <button type="submit"
                            name="simpan"
                            class="btn btn-custom btn-save">

                        <i class="bi bi-save"></i>
                        Simpan Barang

                    </button>

                    <a href="barang.php"
                       class="btn btn-custom btn-back">

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