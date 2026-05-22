<?php

session_start();

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if (!isset($_SESSION['level'])) {

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// CEK PARAMETER ID
// ======================================
if (!isset($_GET['id']) || empty($_GET['id'])) {

    echo "
    <script>

        alert('ID Barang Tidak Valid');

        window.location='barang.php';

    </script>
    ";

    exit;
}

// ======================================
// AMBIL & VALIDASI ID
// ======================================
$id = (int) $_GET['id'];

// ======================================
// CEK DATA BARANG
// ======================================
$stmt_cek = mysqli_prepare(
    $conn,
    "SELECT id_barang
     FROM barang
     WHERE id_barang = ?"
);

mysqli_stmt_bind_param(
    $stmt_cek,
    "i",
    $id
);

mysqli_stmt_execute($stmt_cek);

$result_cek =
mysqli_stmt_get_result($stmt_cek);

// ======================================
// JIKA DATA ADA
// ======================================
if (mysqli_num_rows($result_cek) > 0) {

    // ======================================
    // HAPUS DATA
    // ======================================
    $stmt_hapus = mysqli_prepare(
        $conn,
        "DELETE FROM barang
         WHERE id_barang = ?"
    );

    mysqli_stmt_bind_param(
        $stmt_hapus,
        "i",
        $id
    );

    $hapus =
    mysqli_stmt_execute($stmt_hapus);

    // ======================================
    // CEK HASIL HAPUS
    // ======================================
    if ($hapus) {

        echo "
        <script>

            alert('Data Barang Berhasil Dihapus');

            window.location='barang.php';

        </script>
        ";

    } else {

        echo "
        <script>

            alert('Gagal Menghapus Data Barang');

            window.location='barang.php';

        </script>
        ";
    }

} else {

    // ======================================
    // DATA TIDAK DITEMUKAN
    // ======================================
    echo "
    <script>

        alert('Data Barang Tidak Ditemukan');

        window.location='barang.php';

    </script>
    ";
}

?>