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
    !isset($_POST['metode_pembayaran'])

) {

    echo "

    <script>

        alert('Data transaksi belum lengkap');

        window.location='transaksi.php';

    </script>

    ";

    exit;
}

// =====================================
// AMBIL DATA FORM
// =====================================
$id_barang = $_POST['id_barang'];

$jumlah = $_POST['jumlah'];

$metode_pembayaran =
mysqli_real_escape_string(
$conn,
$_POST['metode_pembayaran']
);

$referensi = '';

if(isset($_POST['referensi'])){

    $referensi =
    mysqli_real_escape_string(
    $conn,
    $_POST['referensi']
    );
}

// =====================================
// FORMAT BAYAR
// =====================================
$bayar = 0;

if(isset($_POST['bayar'])){

    $bayar = str_replace(
    '.',
    '',
    $_POST['bayar']
    );
}

$tanggal = date('Y-m-d H:i:s');

// =====================================
// VALIDASI KERANJANG
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

    if (!$query_barang) {

        die(

            'Query Error : ' .
            mysqli_error($conn)

        );
    }

    // =====================================
    // VALIDASI BARANG
    // =====================================
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

            alert('Stok barang ".$barang['nama_barang']." tidak mencukupi');

            window.location='transaksi.php';

        </script>

        ";

        exit;
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
// PEMBAYARAN NON TUNAI
// =====================================
if (

    $metode_pembayaran == 'QRIS' ||
    $metode_pembayaran == 'Transfer'

) {

    $bayar = $total_harga;
}

// =====================================
// VALIDASI PEMBAYARAN
// =====================================
if ((int)$bayar < $total_harga) {

    echo "

    <script>

        alert('Uang pembayaran kurang');

        window.location='transaksi.php';

    </script>

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
        keuntungan,
        metode_pembayaran,
        referensi,
        kasir

    ) VALUES (

        '$tanggal',
        '$total_harga',
        '$bayar',
        '$kembali',
        '$total_keuntungan',
        '$metode_pembayaran',
        '$referensi',
        '".$_SESSION['nama']."'

    )"

);

// =====================================
// VALIDASI SIMPAN
// =====================================
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

    // =====================================
    // AMBIL BARANG
    // =====================================
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
// REDIRECT KE STRUK
// =====================================
header(
"Location: struk.php?id=$id_penjualan"
);

exit;

?>