<?php
session_start();

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK LOGIN
// ======================================
if (!isset($_SESSION['level'])) {

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// CEK ID BARANG
// ======================================
if (!isset($_GET['id']) || empty($_GET['id'])) {

    echo "
    <script>
        alert('ID Barang tidak ditemukan');
        window.location='barang.php';
    </script>
    ";

    exit;
}

// ======================================
// AMBIL ID
// ======================================
$id = intval($_GET['id']);

// ======================================
// AMBIL DATA BARANG
// ======================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM barang WHERE id_barang = '$id'"
);

// ======================================
// CEK QUERY
// ======================================
if (!$query) {

    die("Query Error : " . mysqli_error($conn));
}

// ======================================
// CEK DATA ADA / TIDAK
// ======================================
if (mysqli_num_rows($query) == 0) {

    echo "
    <script>
        alert('Data barang tidak ditemukan');
        window.location='barang.php';
    </script>
    ";

    exit;
}

// ======================================
// AMBIL DATA
// ======================================
$d = mysqli_fetch_assoc($query);

// ======================================
// PROSES UPDATE
// ======================================
if (isset($_POST['update'])) {

    $kode = mysqli_real_escape_string(
        $conn,
        trim($_POST['kode_barang'])
    );

    $nama = mysqli_real_escape_string(
        $conn,
        trim($_POST['nama_barang'])
    );

    $beli = intval($_POST['harga_beli']);

    $jual = intval($_POST['harga_jual']);

    $stok = intval($_POST['stok']);

    $minimum = intval($_POST['stok_minimum']);

    // ======================================
    // VALIDASI
    // ======================================
    if (
        empty($kode) ||
        empty($nama)
    ) {

        echo "
        <script>
            alert('Data tidak boleh kosong');
        </script>
        ";

    } else {

        // ======================================
        // QUERY UPDATE
        // ======================================
        $update = mysqli_query(
            $conn,
            "UPDATE barang SET

                kode_barang  = '$kode',
                nama_barang  = '$nama',
                harga_beli   = '$beli',
                harga_jual   = '$jual',
                stok         = '$stok',
                stok_minimum = '$minimum'

            WHERE id_barang = '$id'
            "
        );

        // ======================================
        // CEK UPDATE
        // ======================================
        if ($update) {

            echo "
            <script>
                alert('Data barang berhasil diupdate');
                window.location='barang.php';
            </script>
            ";

            exit;

        } else {

            echo "
            <script>
                alert('Gagal update data');
            </script>
            ";

            echo mysqli_error($conn);
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

<title>Edit Barang</title>

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
}

/* ======================================
SIDEBAR
====================================== */
.sidebar{

    height:100vh;

    background:linear-gradient(
        180deg,
        #ff7b00,
        #d65a00
    );

    color:white;

    position:fixed;
}

.sidebar a{

    color:white;

    text-decoration:none;

    display:block;

    padding:12px;

    border-radius:12px;

    margin-bottom:10px;

    transition:0.3s;
}

.sidebar a:hover{

    background:rgba(255,255,255,0.2);

    transform:translateX(5px);
}

/* ======================================
CONTENT
====================================== */
.content{

    margin-left:16.6%;

    padding:25px;
}

/* ======================================
CARD
====================================== */
.card{

    border:none;

    border-radius:20px;
}

.form-control{

    border-radius:12px;

    padding:12px;
}

.btn{

    border-radius:12px;
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
}

</style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-4">

            <h3 class="text-center fw-bold">
                MITRA AZAM
            </h3>

            <hr>

            <a href="dashboard.php">

                <i class="bi bi-house-door"></i>
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

            <a href="../auth/logout.php">

                <i class="bi bi-box-arrow-right"></i>
                Logout

            </a>

        </div>

        <!-- CONTENT -->
        <div class="col-md-10 content">

            <!-- HEADER -->
            <div class="card shadow mb-4">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h3 class="fw-bold">
                                Edit Barang
                            </h3>

                            <p class="text-muted">
                                Sistem Penjualan Toko Mitra Azam
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

            </div>

            <!-- FORM -->
            <div class="card shadow">

                <div class="card-header bg-warning">

                    <h5 class="mb-0">

                        <i class="bi bi-pencil-square"></i>

                        Form Edit Barang

                    </h5>

                </div>

                <div class="card-body">

                    <form method="POST">

                        <div class="row">

                            <!-- KODE -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Kode Barang

                                </label>

                                <input
                                    type="text"
                                    name="kode_barang"
                                    class="form-control"
                                    value="<?= htmlspecialchars($d['kode_barang']); ?>"
                                    required>

                            </div>

                            <!-- NAMA -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Nama Barang

                                </label>

                                <input
                                    type="text"
                                    name="nama_barang"
                                    class="form-control"
                                    value="<?= htmlspecialchars($d['nama_barang']); ?>"
                                    required>

                            </div>

                            <!-- HARGA BELI -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Harga Beli

                                </label>

                                <input
                                    type="number"
                                    name="harga_beli"
                                    class="form-control"
                                    value="<?= $d['harga_beli']; ?>"
                                    required>

                            </div>

                            <!-- HARGA JUAL -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Harga Jual

                                </label>

                                <input
                                    type="number"
                                    name="harga_jual"
                                    class="form-control"
                                    value="<?= $d['harga_jual']; ?>"
                                    required>

                            </div>

                            <!-- STOK -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Stok

                                </label>

                                <input
                                    type="number"
                                    name="stok"
                                    class="form-control"
                                    value="<?= $d['stok']; ?>"
                                    required>

                            </div>

                            <!-- STOK MINIMUM -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Stok Minimum

                                </label>

                                <input
                                    type="number"
                                    name="stok_minimum"
                                    class="form-control"
                                    value="<?= $d['stok_minimum']; ?>"
                                    required>

                            </div>

                        </div>

                        <!-- BUTTON -->
                        <button
                            type="submit"
                            name="update"
                            class="btn btn-warning">

                            <i class="bi bi-save"></i>
                            Update Barang

                        </button>

                        <a
                            href="barang.php"
                            class="btn btn-secondary">

                            <i class="bi bi-arrow-left"></i>
                            Kembali

                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>