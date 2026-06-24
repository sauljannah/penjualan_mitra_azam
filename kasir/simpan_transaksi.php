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
    !isset($_POST['bayar']) ||
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

$kebutuhan = isset($_POST['kebutuhan'])
? $_POST['kebutuhan']
: [];

$metode_pembayaran =
mysqli_real_escape_string(
$conn,
$_POST['metode_pembayaran']

);

$nama_customer =
isset($_POST['nama_customer'])
? mysqli_real_escape_string(
    $conn,
    trim($_POST['nama_customer'])
)
: '';

$referensi =
isset($_POST['referensi'])
? mysqli_real_escape_string(
    $conn,
    trim($_POST['referensi'])
)
: '';

// =====================================
// FORMAT BAYAR
// =====================================
$bayar = str_replace(
'.',
'',
$_POST['bayar']
);

$bayar = (int)$bayar;

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
// CEK BARANG
// =====================================
for (

    $i = 0;
    $i < count($id_barang);
    $i++

) {

    $idb = (int)$id_barang[$i];

    $jml = isset($jumlah[$i])
    ? (int)$jumlah[$i]
    : 1;

    $persentase = isset($persen[$i])
    ? (float)$persen[$i]
    : 100;

    $persen =
    isset($_POST['persen'])
    ? $_POST['persen']
    : [];
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

    $harga_jual =
    (int)$barang['harga_jual'];

    $harga_beli =
    (int)$barang['harga_beli'];

    // =====================================
    // HITUNG TOTAL
    // =====================================
    if($barang['jenis_penjualan']=='fleksibel'){

    $subtotal =
    $harga_jual *
    ($persentase / 100);

}else{

    $subtotal =
    $harga_jual * $jml;
}

    $keuntungan =
    ($harga_jual - $harga_beli)
    * $jml;

    $total_harga += $subtotal;

    $total_keuntungan += $keuntungan;
}

// =====================================
// STATUS PEMBAYARAN
// =====================================
$status_pembayaran = 'Lunas';

$kembali = 0;

// =====================================
// HUTANG / BELUM BAYAR
// =====================================
if ($metode_pembayaran == 'Hutang') {

    $status_pembayaran =
    'Belum Lunas';

    $bayar = 0;

    $kembali = 0;

    // =====================================
    // VALIDASI NAMA CUSTOMER
    // =====================================
    if (empty($nama_customer)) {

        echo "

        <script>

            alert('Nama customer wajib diisi untuk transaksi hutang');

            window.location='transaksi.php';

        </script>

        ";

        exit;
    }
}

// =====================================
// PEMBAYARAN NORMAL
// =====================================
else {

    // =====================================
    // VALIDASI PEMBAYARAN
    // =====================================
    if ($bayar < $total_harga) {

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
    $bayar - $total_harga;
}

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
        nama_customer,
        status_pembayaran

    ) VALUES (

        '$tanggal',
        '$total_harga',
        '$bayar',
        '$kembali',
        '$total_keuntungan',
        '$metode_pembayaran',
        '$referensi',
        '$nama_customer',
        '$status_pembayaran'

    )"

);

// =====================================
// VALIDASI SIMPAN
// =====================================
if (!$simpan_penjualan) {

    die(

        'Gagal simpan penjualan : ' .
        mysqli_error($conn)

    );
}

// =====================================
// AMBIL ID PENJUALAN
// =====================================
$id_penjualan =
mysqli_insert_id($conn);

// =====================================
// SIMPAN DETAIL PENJUALAN
// =====================================
for (

    $i = 0;
    $i < count($id_barang);
    $i++

) {

    $idb =
    (int)$id_barang[$i];

    $jml =
    (int)$jumlah[$i];

    // =====================================
    // AMBIL DATA BARANG
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

    // =====================================
    // STOK BARU
    // =====================================
    // Bisa minus jika stok sistem habis
    // tetapi stok offline masih ada

    $stok_lama =
    (int)$barang['stok'];

    $stok_baru =
    $stok_lama - $jml;

    // =====================================
    // SIMPAN DETAIL
    // =====================================
    $simpan_detail = mysqli_query(

        $conn,

        "INSERT INTO detail_penjualan (

            id_penjualan,
            id_barang,
            jumlah,
            harga,
            kebutuhan

        ) VALUES (

            '$id_penjualan',
            '$idb',
            '$jml',
            '$harga_jual',
            '$persen'

        )"

    );

    if (!$simpan_detail) {

        die(

            'Gagal simpan detail : ' .
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

            'Gagal update stok : ' .
            mysqli_error($conn)

        );
    }
}

// =====================================
// LANGSUNG KE STRUK
// =====================================
header(
"Location: struk.php?id=$id_penjualan"
);

exit;

?>