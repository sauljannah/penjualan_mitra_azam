<?php
session_start();

include '../config/koneksi.php';

// CEK LOGIN
if(!isset($_SESSION['level'])){
    header("location:../auth/login.php");
    exit;
}

// CEK ID
if(!isset($_GET['id'])){

    echo "
    <script>

        alert('ID Barang Tidak Ditemukan');

        window.location='barang.php';

    </script>
    ";

    exit;
}

$id = $_GET['id'];

// AMBIL DATA BARANG
$query = mysqli_query($conn,
"SELECT * FROM barang
WHERE id_barang='$id'");

// CEK DATA
if(mysqli_num_rows($query) == 0){

    echo "
    <script>

        alert('Data Barang Tidak Ada');

        window.location='barang.php';

    </script>
    ";

    exit;
}

$d = mysqli_fetch_assoc($query);

// PROSES UPDATE
if(isset($_POST['update'])){

    $kode     = mysqli_real_escape_string(
                    $conn,
                    $_POST['kode_barang']
                );

    $nama     = mysqli_real_escape_string(
                    $conn,
                    $_POST['nama_barang']
                );

    $beli     = $_POST['harga_beli'];

    $jual     = $_POST['harga_jual'];

    $stok     = $_POST['stok'];

    $minimum  = $_POST['stok_minimum'];

    // QUERY UPDATE
    $update = mysqli_query($conn,

    "UPDATE barang SET

        kode_barang  = '$kode',
        nama_barang  = '$nama',
        harga_beli   = '$beli',
        harga_jual   = '$jual',
        stok         = '$stok',
        stok_minimum = '$minimum'

    WHERE id_barang='$id'
    ");

    // CEK BERHASIL
    if($update){

        echo "
        <script>

            alert('Data Barang Berhasil Diupdate');

            window.location='barang.php';

        </script>
        ";

    }else{

        echo "
        <script>

            alert('Gagal Update Data');

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

    <title>Edit Barang</title>

    <!-- Bootstrap -->
    <link href=
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
    href=
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body{
            background:#f4f6f9;
        }

        .sidebar{
            height:100vh;
            background:linear-gradient(
                180deg,
                #0d6efd,
                #0b5ed7
            );
            color:white;
            position:fixed;
        }

        .sidebar a{
            color:white;
            text-decoration:none;
            display:block;
            padding:12px;
            border-radius:10px;
            margin-bottom:10px;
            transition:0.3s;
        }

        .sidebar a:hover{
            background:rgba(255,255,255,0.2);
        }

        .content{
            margin-left:16.6%;
            padding:25px;
        }

        .card{
            border:none;
            border-radius:20px;
        }

        .form-control{
            border-radius:10px;
            padding:10px;
        }

        .btn{
            border-radius:10px;
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-4">

            <h3 class="text-center">
                ADMIN
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

                            <h3>
                                Edit Barang
                            </h3>

                            <p>
                                Sistem Penjualan Toko Mitra Azam
                            </p>

                        </div>

                        <div>

                            <h5>

                                Admin :
                                <?= $_SESSION['nama']; ?>

                            </h5>

                        </div>

                    </div>

                </div>

            </div>

            <!-- FORM -->
            <div class="card shadow">

                <div class="card-header bg-warning">

                    <h5>

                        <i class="bi bi-pencil-square"></i>

                        Form Edit Barang

                    </h5>

                </div>

                <div class="card-body">

                    <form method="POST">

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Kode Barang

                                </label>

                                <input type="text"
                                       name="kode_barang"
                                       class="form-control"
                                       value="<?= $d['kode_barang']; ?>"
                                       required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Nama Barang

                                </label>

                                <input type="text"
                                       name="nama_barang"
                                       class="form-control"
                                       value="<?= $d['nama_barang']; ?>"
                                       required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Harga Beli

                                </label>

                                <input type="number"
                                       name="harga_beli"
                                       class="form-control"
                                       value="<?= $d['harga_beli']; ?>"
                                       required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Harga Jual

                                </label>

                                <input type="number"
                                       name="harga_jual"
                                       class="form-control"
                                       value="<?= $d['harga_jual']; ?>"
                                       required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Stok

                                </label>

                                <input type="number"
                                       name="stok"
                                       class="form-control"
                                       value="<?= $d['stok']; ?>"
                                       required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Stok Minimum

                                </label>

                                <input type="number"
                                       name="stok_minimum"
                                       class="form-control"
                                       value="<?= $d['stok_minimum']; ?>"
                                       required>

                            </div>

                        </div>

                        <button type="submit"
                                name="update"
                                class="btn btn-warning">

                            <i class="bi bi-save"></i>

                            Update Barang

                        </button>

                        <a href="barang.php"
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