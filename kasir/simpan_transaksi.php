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

// =====================================
// VALIDASI DATA
// =====================================
if (

    !isset($_POST['id_barang']) ||
    !isset($_POST['jumlah']) ||
    !isset($_POST['bayar'])

) {

    echo "

    <!DOCTYPE html>
    <html lang='id'>

    <head>

        <meta charset='UTF-8'>

        <title>Gagal</title>

        <link
        href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
        rel='stylesheet'>

        <link
        rel='stylesheet'
        href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'>

        <link
        href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap'
        rel='stylesheet'>

        <style>

        *{
            font-family:'Poppins',sans-serif;
        }

        body{

            background:
            linear-gradient(
            135deg,
            #eff6ff,
            #f8fafc);

            height:100vh;

            display:flex;

            justify-content:center;

            align-items:center;
        }

        .box{

            background:white;

            padding:40px;

            border-radius:24px;

            text-align:center;

            box-shadow:
            0 10px 30px rgba(0,0,0,0.1);

            width:400px;
        }

        .icon{

            font-size:70px;

            color:#ef4444;
        }

        </style>

    </head>

    <body>

        <div class='box'>

            <div class='icon'>

                <i class='bi bi-x-circle-fill'></i>

            </div>

            <h3 class='mt-3'>

                Data transaksi belum lengkap

            </h3>

            <a
            href='transaksi.php'
            class='btn btn-danger mt-3'>

                Kembali

            </a>

        </div>

    </body>

    </html>

    ";

    exit;
}

// =====================================
// AMBIL DATA FORM
// =====================================
$id_barang = $_POST['id_barang'];

$jumlah = $_POST['jumlah'];

// HAPUS FORMAT TITIK RUPIAH
$bayar = str_replace(
'.',
'',
$_POST['bayar']
);

$tanggal = date('Y-m-d H:i:s');

// =====================================
// VALIDASI ARRAY
// =====================================
if (

    count($id_barang) === 0 ||
    count($jumlah) === 0

) {

    echo "

    <script>

        alert('Keranjang masih kosong');

        window.location='transaksi.php';

    </script>

    ";

    exit;
}

// =====================================
// TOTAL
// =====================================
$total_harga = 0;

$total_keuntungan = 0;

// =====================================
// CEK SEMUA BARANG
// =====================================
for (

    $i = 0;
    $i < count($id_barang);
    $i++

) {

    $idb = (int)$id_barang[$i];

    $jml = (int)$jumlah[$i];

    // =====================================
    // VALIDASI JUMLAH
    // =====================================
    if ($jml <= 0) {

        echo "

        <script>

            alert('Jumlah barang tidak valid');

            window.location='transaksi.php';

        </script>

        ";

        exit;
    }

    // =====================================
    // AMBIL DATA BARANG
    // =====================================
    $query_barang = mysqli_query(

        $conn,

        "SELECT *
         FROM barang
         WHERE id_barang = '$idb'"

    );

    // VALIDASI QUERY
    if (!$query_barang) {

        die(

            'Query Error : ' .
            mysqli_error($conn)

        );
    }

    // VALIDASI BARANG
    if (mysqli_num_rows($query_barang) === 0) {

        echo "

        <script>

            alert('Barang tidak ditemukan');

            window.location='transaksi.php';

        </script>

        ";

        exit;
    }

    // =====================================
    // DATA BARANG
    // =====================================
    $barang = mysqli_fetch_assoc(
        $query_barang
    );

    $stok = (int)$barang['stok'];

    $harga_jual =
    (int)$barang['harga_jual'];

    $harga_beli =
    (int)$barang['harga_beli'];

    // =====================================
    // VALIDASI STOK
    // =====================================
  if ($jml > $stok) {

    echo "

    <script>

        alert('Stok barang ".$barang['nama_barang']." tidak mencukupi, transaksi tetap dilanjutkan');

    </script>

    ";

}

    // =====================================
    // HITUNG TOTAL
    // =====================================
    $subtotal =
    $harga_jual * $jml;

    $keuntungan =
    ($harga_jual - $harga_beli)
    * $jml;

    $total_harga += $subtotal;

    $total_keuntungan += $keuntungan;
}

// =====================================
// VALIDASI PEMBAYARAN
// =====================================
if ((int)$bayar < $total_harga) {

    echo "

    <!DOCTYPE html>
    <html lang='id'>

    <head>

        <meta charset='UTF-8'>

        <title>Pembayaran Kurang</title>

        <link
        href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
        rel='stylesheet'>

        <link
        rel='stylesheet'
        href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'>

        <link
        href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap'
        rel='stylesheet'>

        <style>

        *{
            font-family:'Poppins',sans-serif;
        }

        body{

            background:
            linear-gradient(
            135deg,
            #eff6ff,
            #f8fafc);

            height:100vh;

            display:flex;

            justify-content:center;

            align-items:center;
        }

        .box{

            background:white;

            padding:40px;

            border-radius:24px;

            text-align:center;

            box-shadow:
            0 10px 30px rgba(0,0,0,0.1);

            width:420px;
        }

        .icon{

            font-size:70px;

            color:#ef4444;
        }

        </style>

    </head>

    <body>

        <div class='box'>

            <div class='icon'>

                <i class='bi bi-cash-stack'></i>

            </div>

            <h3 class='mt-3'>

                Uang Pembayaran Kurang

            </h3>

            <p>

                Total Belanja :

                <strong>

                    Rp ".number_format(
                    $total_harga,
                    0,
                    ',',
                    '.'
                    )."

                </strong>

            </p>

            <a
            href='transaksi.php'
            class='btn btn-danger mt-3'>

                Kembali

            </a>

        </div>

    </body>

    </html>

    ";

    exit;
}

// =====================================
// HITUNG KEMBALIAN
// =====================================
$kembali =
(int)$bayar - $total_harga;

// =====================================
// SIMPAN PENJUALAN
// =====================================
$simpan_penjualan = mysqli_query(

    $conn,

    "INSERT INTO penjualan (

        tanggal,
        total_harga,
        bayar,
        kembali,
        keuntungan

    ) VALUES (

        '$tanggal',
        '$total_harga',
        '$bayar',
        '$kembali',
        '$total_keuntungan'

    )"

);

// VALIDASI SIMPAN
if (!$simpan_penjualan) {

    die(

        "Gagal simpan penjualan : " .
        mysqli_error($conn)

    );
}

// =====================================
// AMBIL ID PENJUALAN
// =====================================
$id_penjualan =
mysqli_insert_id($conn);

// =====================================
// SIMPAN DETAIL
// =====================================
for (

    $i = 0;
    $i < count($id_barang);
    $i++

) {

    $idb = (int)$id_barang[$i];

    $jml = (int)$jumlah[$i];

    // AMBIL BARANG
    $query_barang = mysqli_query(

        $conn,

        "SELECT *
         FROM barang
         WHERE id_barang = '$idb'"

    );

    $barang =
    mysqli_fetch_assoc(
    $query_barang
    );

    $harga_jual =
    (int)$barang['harga_jual'];

    $stok_baru =
    (int)$barang['stok']
    - $jml;

    // =====================================
    // SIMPAN DETAIL
    // =====================================
    $simpan_detail = mysqli_query(

        $conn,

        "INSERT INTO detail_penjualan (

            id_penjualan,
            id_barang,
            jumlah,
            harga

        ) VALUES (

            '$id_penjualan',
            '$idb',
            '$jml',
            '$harga_jual'

        )"

    );

    if (!$simpan_detail) {

        die(

            "Gagal simpan detail : " .
            mysqli_error($conn)

        );
    }

    // =====================================
    // UPDATE STOK
    // =====================================
    $update_stok = mysqli_query(

        $conn,

        "UPDATE barang
         SET stok = '$stok_baru'
         WHERE id_barang = '$idb'"

    );

    if (!$update_stok) {

        die(

            "Gagal update stok : " .
            mysqli_error($conn)

        );
    }
}

// =====================================
// REDIRECT
// =====================================
header(
"Location: struk.php?id=$id_penjualan"
);

exit;

?>