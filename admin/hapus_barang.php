<?php
session_start();

// Mengamankan pemanggilan file koneksi jika terjadi error database di awal
try {
    require_once '../config/koneksi.php';
} catch (Exception $e) {
    die("Gagal memuat koneksi database: " . $e->getMessage());
}

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
    "SELECT id_barang FROM barang WHERE id_barang = ?"
);

mysqli_stmt_bind_param($stmt_cek, "i", $id);
mysqli_stmt_execute($stmt_cek);
mysqli_stmt_store_result($stmt_cek); // Lebih aman untuk menghitung baris (num_rows)

// ======================================
// JIKA DATA ADA
// ======================================
if (mysqli_stmt_num_rows($stmt_cek) > 0) {
    // Tutup statement cek terlebih dahulu sebelum membuat statement baru
    mysqli_stmt_close($stmt_cek);

    // ======================================
    // HAPUS DATA
    // ======================================
    $stmt_hapus = mysqli_prepare(
        $conn,
        "DELETE FROM barang WHERE id_barang = ?"
    );

    mysqli_stmt_bind_param($stmt_hapus, "i", $id);
    $hapus = mysqli_stmt_execute($stmt_hapus);

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
    
    mysqli_stmt_close($stmt_hapus);

} else {
    // Tutup statement jika data tidak ditemukan
    mysqli_stmt_close($stmt_cek);

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