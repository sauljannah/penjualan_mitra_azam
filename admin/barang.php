<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// =====================================
// PROTEKSI LOGIN
// =====================================
if (!isset($_SESSION['level'])) {

    header("Location: ../auth/login.php");
    exit;
}

/// =====================================
// PENCARIAN & PENGURUTAN
// =====================================
$cari = isset($_GET['cari']) ? $_GET['cari'] : "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_barang';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validasi input
$allowed_sort = ['kode_barang', 'nama_barang', 'harga_beli', 'harga_jual', 'stok'];
if (!in_array($sort, $allowed_sort)) { $sort = 'id_barang'; }
if ($order !== 'ASC' && $order !== 'DESC') { $order = 'DESC'; }

$query = "SELECT * FROM barang";

if ($cari != "") {
    $c = mysqli_real_escape_string($conn, $cari);
    $query .= " WHERE nama_barang LIKE '%$c%' OR kode_barang LIKE '%$c%'";
}

$query .= " ORDER BY $sort $order";
$data = mysqli_query($conn, $query);

// =====================================
// AMBIL DATA BARANG
// =====================================
$data = mysqli_query($conn, $query);

if (!$data) {
    die("Query Error : " . mysqli_error($conn));
}

// =====================================
// AMBIL DATA BARANG
// =====================================
$data = mysqli_query($conn, $query);

if (!$data) {

    die(
        "Query Error : " .
        mysqli_error($conn)
    );
}

// =====================================
// TOTAL BARANG
// =====================================
$total_barang = 0;

$q_total = mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM barang"
);

if ($q_total) {

    $d_total = mysqli_fetch_assoc($q_total);

    $total_barang = $d_total['total'];
}

// =====================================
// STOK MENIPIS
// =====================================
$stok_menipis = 0;

$q_stok = mysqli_query(
    $conn,
    "SELECT COUNT(*) as total
     FROM barang
     WHERE stok > 0
     AND stok <= stok_minimum"
);

if ($q_stok) {

    $d_stok = mysqli_fetch_assoc($q_stok);

    $stok_menipis = $d_stok['total'];
}

// =====================================
// STOK HABIS
// =====================================
$q_habis = mysqli_query(
    $conn,
    "SELECT *
     FROM barang
     WHERE stok <= 0"
);

$total_habis = mysqli_num_rows($q_habis);

// =====================================
// CEK KODE BARANG GANDA
// =====================================
// KETENTUAN:
// 1. Nama barang sama -> kode boleh sama
// 2. Nama barang beda -> kode tidak boleh sama
// =====================================

$peringatan_kode = "";

$q_kode = mysqli_query(
    $conn,
    "
    SELECT
        kode_barang,
        GROUP_CONCAT(DISTINCT nama_barang) AS nama_barang,
        COUNT(DISTINCT nama_barang) AS total_nama
    FROM barang
    GROUP BY kode_barang
    HAVING total_nama > 1
    "
);

if(mysqli_num_rows($q_kode) > 0){

    $peringatan_kode .= "
    <div class='alert alert-danger'>
        <h6>
            <i class='bi bi-exclamation-triangle-fill'></i>
            Kode Barang Duplikat Tidak Valid
        </h6>
    ";

    while($kode = mysqli_fetch_assoc($q_kode)){

        $peringatan_kode .= "
        <div class='mt-2'>
            <b>Kode :</b> ".$kode['kode_barang']." <br>
            <b>Digunakan oleh :</b> ".$kode['nama_barang']."
        </div>
        ";
    }

    $peringatan_kode .= "</div>";
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Data Barang</title>

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
    overflow-x:hidden;
    font-family:Arial,sans-serif;
}

/* ======================
SIDEBAR
====================== */
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
    color:white;
}

.sidebar h3{
    text-align:center;
    margin-bottom:25px;
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

/* ======================
CONTENT
====================== */
.content{
    margin-left:270px;
    padding:25px;
}

.card{
    border:none;
    border-radius:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.btn{
    border-radius:10px;
}

.table tbody tr:hover{
    background:#f1f1f1;
}

.badge{
    padding:8px 12px;
}

.search-box{
    border-radius:10px;
}

.alert{
    border:none;
    border-radius:15px;
}

.kode-valid{
    background:#e8fff1 !important;
}

.kode-tidak-valid{
    background:#ffe5e5 !important;
}

@media(max-width:768px){

    .sidebar{
        width:100%;
        height:auto;
        position:relative;
    }

    .content{
        margin-left:0;
    }
}

</style>

</head>

<body>

<!-- ======================
SIDEBAR
====================== -->
<div class="sidebar">

    <h3>

        <i class="bi bi-building"></i>
        MITRA AZAM

    </h3>

    <a href="dashboard.php">

        <i class="bi bi-speedometer2"></i>
        Dashboard

    </a>

    <a href="barang.php"
       style="background:rgba(255,255,255,0.2);">

        <i class="bi bi-box-seam"></i>
        Data Barang

        <?php if($total_habis > 0): ?>

            <span class="badge bg-danger float-end">

                <?= $total_habis; ?>

            </span>

        <?php endif; ?>

    </a>

    <a href="tambah_barang.php">

        <i class="bi bi-plus-circle"></i>
        Tambah Barang

    </a>
    
     <a href="barang_masuk.php">

                <i class="bi bi-box-arrow-in-down"></i>
                Barang Masuk
                
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

    <a href="../auth/logout.php">

        <i class="bi bi-box-arrow-right"></i>
        Logout

    </a>

</div>

<!-- ======================
CONTENT
====================== -->
<div class="content">

    <!-- HEADER -->
    <div class="card mb-4">

        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

            <div>

                <h3>Data Barang</h3>

                <p class="mb-0 text-muted">
                    Sistem Informasi Toko Bangunan
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

    <!-- ALERT KODE -->
    <?= $peringatan_kode; ?>

    <!-- ======================
    STATISTIK
    ====================== -->
    <div class="row mb-4">

        <!-- TOTAL BARANG -->
        <div class="col-md-4 mb-3">

            <div class="card bg-primary text-white">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h2>

                            <?= $total_barang; ?>

                        </h2>

                        <p class="mb-0">
                            Total Barang
                        </p>

                    </div>

                    <i class="bi bi-box-seam"
                       style="font-size:50px;"></i>

                </div>

            </div>

        </div>

        <!-- STOK MENIPIS -->
        <div class="col-md-4 mb-3">

            <div class="card bg-warning text-dark">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h2>

                            <?= $stok_menipis; ?>

                        </h2>

                        <p class="mb-0">
                            Stok Menipis
                        </p>

                    </div>

                    <i class="bi bi-exclamation-circle"
                       style="font-size:50px;"></i>

                </div>

            </div>

        </div>

        <!-- STOK HABIS -->
        <div class="col-md-4 mb-3">

            <div class="card bg-danger text-white">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h2>

                            <?= $total_habis; ?>

                        </h2>

                        <p class="mb-0">
                            Stok Habis
                        </p>

                    </div>

                    <i class="bi bi-x-circle"
                       style="font-size:50px;"></i>

                </div>

            </div>

        </div>

    </div>

    <!-- ======================
    TABEL
    ====================== -->
    <div class="card">

        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="bi bi-box"></i>
                Data Barang

            </h5>

            <a href="tambah_barang.php"
               class="btn btn-warning btn-sm">

                <i class="bi bi-plus-circle"></i>
                Tambah Barang

            </a>

        </div>

        <div class="card-body">

            <!-- SEARCH -->
    clear        <form method="GET" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="cari" class="form-control search-box" placeholder="Cari barang..." value="<?= htmlspecialchars($cari); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="sort" class="form-select">
                                <option value="id_barang" <?= ($sort == 'id_barang') ? 'selected' : ''; ?>>Urutkan Berdasarkan</option>
                                <option value="kode_barang" <?= ($sort == 'kode_barang') ? 'selected' : ''; ?>>Kode Barang</option>
                                <option value="nama_barang" <?= ($sort == 'nama_barang') ? 'selected' : ''; ?>>Nama Barang</option>
                                <option value="harga_beli" <?= ($sort == 'harga_beli') ? 'selected' : ''; ?>>Harga Beli</option>
                                <option value="harga_jual" <?= ($sort == 'harga_jual') ? 'selected' : ''; ?>>Harga Jual</option>
                                <option value="stok" <?= ($sort == 'stok') ? 'selected' : ''; ?>>Stok</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="order" class="form-select">
                                <option value="ASC" <?= ($order == 'ASC') ? 'selected' : ''; ?>>A-Z / Terkecil</option>
                                <option value="DESC" <?= ($order == 'DESC') ? 'selected' : ''; ?>>Z-A / Terbesar</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-filter"></i> Terapkan
                            </button>
                        </div>
                    </div>
                </form>

            <!-- TABLE -->
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-warning text-center">
                        <tr>
                            <th>No</th>
                            <th>
                                <a href="?sort=kode_barang&order=<?= ($sort == 'kode_barang' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>" class="text-decoration-none text-dark">Kode</a>
                            </th>
                            <th>
                                <a href="?sort=nama_barang&order=<?= ($sort == 'nama_barang' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>" class="text-decoration-none text-dark">Nama Barang</a>
                            </th>
                            <th>
                                <a href="?sort=harga_beli&order=<?= ($sort == 'harga_beli' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>" class="text-decoration-none text-dark">Harga Beli</a>
                            </th>
                            <th>
                                <a href="?sort=harga_jual&order=<?= ($sort == 'harga_jual' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>" class="text-decoration-none text-dark">Harga Jual</a>
                            </th>
                            <th>
                                <a href="?sort=stok&order=<?= ($sort == 'stok' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cari=<?= urlencode($cari); ?>" class="text-decoration-none text-dark">Stok</a>
                            </th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>    

                    <tbody>

                    <?php if(mysqli_num_rows($data) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while($d = mysqli_fetch_assoc($data)): ?>

                        <?php

                        // =====================================
                        // VALIDASI KODE BARANG
                        // =====================================
                        $kode_barang =
                        mysqli_real_escape_string(
                            $conn,
                            $d['kode_barang']
                        );

                        $nama_barang =
                        mysqli_real_escape_string(
                            $conn,
                            $d['nama_barang']
                        );

                        $cek_valid = mysqli_query(
                            $conn,
                            "
                            SELECT COUNT(DISTINCT nama_barang) AS total
                            FROM barang
                            WHERE kode_barang = '$kode_barang'
                            "
                        );

                        $hasil_valid =
                        mysqli_fetch_assoc($cek_valid);

                        $class_row = "";

                        if($hasil_valid['total'] > 1){

                            $class_row = "kode-tidak-valid";

                        }else{

                            $class_row = "kode-valid";
                        }

                        ?>

                        <tr class="<?= $class_row; ?>">

                            <td class="text-center">

                                <?= $no++; ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($d['kode_barang']); ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($d['nama_barang']); ?>

                            </td>

                            <td>

                                Rp <?= number_format($d['harga_beli'],0,',','.'); ?>

                            </td>

                            <td>

                                Rp <?= number_format($d['harga_jual'],0,',','.'); ?>

                            </td>

                            <td class="text-center">

                                <?= $d['stok']; ?>

                            </td>

                            <!-- STATUS -->
                            <td class="text-center">

                            <?php if($d['stok'] <= 0): ?>

                                <span class="badge bg-danger">

                                    Stok Habis

                                </span>

                            <?php elseif($d['stok'] <= $d['stok_minimum']): ?>

                                <span class="badge bg-warning text-dark">

                                    Menipis

                                </span>

                            <?php else: ?>

                                <span class="badge bg-success">

                                    Aman

                                </span>

                            <?php endif; ?>

                            </td>

                            <!-- AKSI -->
                            <td class="text-center">

                                <a
                                    href="edit_barang.php?id=<?= $d['id_barang']; ?>"
                                    class="btn btn-warning btn-sm">

                                    <i class="bi bi-pencil-square"></i>

                                </a>

                                <a
                                    href="hapus_barang.php?id=<?= $d['id_barang']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin hapus data?')">

                                    <i class="bi bi-trash"></i>

                                </a>

                            </td>

                        </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8"
                                class="text-center text-danger">

                                Data Tidak Ditemukan

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>