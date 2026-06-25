<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "penjualan_mitra_azam"
);

if (!$conn) {

    die(
        "Koneksi gagal : " .
        mysqli_connect_error()
    );
}

// Ambil status tema dari database untuk digunakan di semua halaman
$queryGlobalSetting = mysqli_query($conn, "SELECT tema FROM setting LIMIT 1");
$globalSetting = mysqli_fetch_assoc($queryGlobalSetting);
$tema_sistem = $globalSetting['tema'] ?? 'light';

?>

