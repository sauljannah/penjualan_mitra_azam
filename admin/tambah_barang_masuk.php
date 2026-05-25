<?php
session_start();
require_once '../config/koneksi.php';

/* CEK KONEKSI */
if (!isset($conn) || !$conn) {
    die("❌ Koneksi database gagal. Cek config/koneksi.php");
}

/* CEK LOGIN */
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* AMBIL DATA BARANG */
$barang = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang ASC");

if (!$barang) {
    die("Query error barang: " . mysqli_error($conn));
}

/* SIMPAN */
if (isset($_POST['simpan'])) {

    $id_barang  = $_POST['id_barang'];
    $jumlah     = (int) $_POST['jumlah'];
    $harga_beli = (int) $_POST['harga_beli'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $get = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang='$id_barang'");
    $data = mysqli_fetch_assoc($get);

    if (!$data) {
        die("Barang tidak ditemukan");
    }

    $harga_jual = $data['harga_jual'];

    mysqli_begin_transaction($conn);

    try {

        // INSERT RIWAYAT BARANG MASUK
        mysqli_query($conn, "
            INSERT INTO barang_masuk
            (id_barang, jumlah, harga_beli, harga_jual, keterangan, tanggal)
            VALUES
            ('$id_barang', '$jumlah', '$harga_beli', '$harga_jual', '$keterangan', NOW())
        ");

        // UPDATE STOK + HARGA DI BARANG UTAMA
        mysqli_query($conn, "
            UPDATE barang
            SET 
                stok = stok + $jumlah,
                harga_beli = '$harga_beli',
                harga_jual = '$harga_jual'
            WHERE id_barang = '$id_barang'
        ");

        mysqli_commit($conn);

        echo "<script>
            alert('✔ Barang masuk berhasil disimpan');
            window.location='barang_masuk.php';
        </script>";
        exit;

    } catch (Exception $e) {

        mysqli_rollback($conn);

        echo "<script>
            alert('❌ Gagal menyimpan data');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Barang Masuk</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    margin:0;
    background:#f4f6fb;
    font-family:Segoe UI;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    width:250px;
    height:100vh;
    background:linear-gradient(180deg,#ff7b00,#ff5200);
    color:#fff;
    padding:20px;
}

.sidebar h3{
    font-weight:800;
    text-align:center;
    margin-bottom:30px;
}

.sidebar a{
    display:flex;
    gap:10px;
    color:#fff;
    text-decoration:none;
    padding:12px;
    border-radius:10px;
    margin-bottom:8px;
    transition:.2s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.2);
}

/* CONTENT */
.content{
    margin-left:250px;
    padding:25px;
}

/* HEADER */
.header-box{
    background:linear-gradient(135deg,#ff7b00,#ff5200);
    color:#fff;
    padding:20px;
    border-radius:15px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

/* CARD */
.card{
    margin-top:20px;
    border:none;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

/* FORM */
.form-control{
    border-radius:10px;
    padding:10px;
}

/* BUTTON STYLE */
.btn-warning{
    border-radius:10px;
    font-weight:600;
}

.btn-secondary{
    border-radius:10px;
    font-weight:600;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>MITRA AZAM</h3>

    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="barang.php"><i class="bi bi-box-seam"></i> Barang</a>
    <a href="barang_masuk.php"><i class="bi bi-box-arrow-in-down"></i> Barang Masuk</a>
</div>

<!-- CONTENT -->
<div class="content">

    <!-- HEADER -->
    <div class="header-box">

        <div>
            <h3 class="mb-1">Tambah Barang Masuk</h3>
            <small>Sinkron otomatis stok + harga barang</small>
        </div>

        <div class="d-flex align-items-center gap-2 fw-bold">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($_SESSION['nama']); ?>
        </div>

    </div>

    <!-- FORM -->
    <div class="card p-4">

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Barang</label>
                <select name="id_barang" class="form-control" required>
                    <option value="">Pilih Barang</option>
                    <?php while($b = mysqli_fetch_assoc($barang)) { ?>
                        <option value="<?= $b['id_barang']; ?>">
                            <?= $b['nama_barang']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Jumlah</label>
                <input type="number" name="jumlah" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Harga Beli</label>
                <input type="number" name="harga_beli" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <input type="text" name="keterangan" class="form-control">
            </div>

            <!-- BUTTON SIMPAN & KEMBALI -->
            <div class="d-flex gap-2 mt-3">

                <button class="btn btn-warning flex-fill" name="simpan">
                    <i class="bi bi-save"></i> SIMPAN DATA
                </button>

                <a href="barang_masuk.php" class="btn btn-secondary flex-fill">
                    <i class="bi bi-arrow-left"></i> KEMBALI
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>