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

?>