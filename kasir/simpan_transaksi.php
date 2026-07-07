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

$persen_array = isset($_POST['persen']) ? $_POST['persen'] : [];
$kebutuhan_array = isset($_POST['kebutuhan']) ? $_POST['kebutuhan'] : [];

$metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
$nama_customer = isset($_POST['nama_customer']) ? mysqli_real_escape_string($conn, trim($_POST['nama_customer'])) : '';
$referensi = isset($_POST['referensi']) ? mysqli_real_escape_string($conn, trim($_POST['referensi'])) : '';

// SOLUSI ERROR: Jika jatuh_tempo kosong, isi otomatis dengan tanggal hari ini agar database tidak menolak (Mencegah NOT NULL error)
$jatuh_tempo = isset($_POST['jatuh_tempo']) && !empty($_POST['jatuh_tempo']) 
    ? mysqli_real_escape_string($conn, $_POST['jatuh_tempo']) 
    : date('Y-m-d'); 

$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0; 

// =====================================
// FORMAT BAYAR
// =====================================
$bayar = str_replace('.', '', $_POST['bayar']);
$bayar = (int)$bayar;

$tanggal = date('Y-m-d H:i:s');

// =====================================
// VALIDASI KERANJANG
// =====================================
if (count($id_barang) === 0 || count($jumlah) === 0) {
    echo "
    <script>
        alert('Keranjang masih kosong');
        window.location='transaksi.php';
    </script>
    ";
    exit;
}

// =====================================
// HITUNG TOTAL & KEUNTUNGAN SECARA KESELURUHAN
// =====================================
$total_harga = 0;
$total_keuntungan = 0;

for ($i = 0; $i < count($id_barang); $i++) {
    $idb = (int)$id_barang[$i];
    $jml = isset($jumlah[$i]) ? (int)$jumlah[$i] : 1;
    
    $persentase = isset($persen_array[$i]) ? (float)$persen_array[$i] : 100;
    $kali_kaca = isset($kebutuhan_array[$i]) ? (float)$kebutuhan_array[$i] : 1.0;

    if ($jml <= 0) {
        echo "
        <script>
            alert('Jumlah barang tidak valid');
            window.location='transaksi.php';
        </script>
        ";
        exit;
    }

    $query_barang = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang = '$idb'");
    if (mysqli_num_rows($query_barang) === 0) {
        echo "
        <script>
            alert('Barang tidak ditemukan');
            window.location='transaksi.php';
        </script>
        ";
        exit;
    }

    $barang = mysqli_fetch_assoc($query_barang);
    $harga_jual = (int)$barang['harga_jual'];
    $harga_beli = (int)$barang['harga_beli'];

    if ($barang['jenis_penjualan'] == 'fleksibel') {
        $subtotal = $harga_jual * ($persentase / 100);
        $keuntungan = ($harga_jual - $harga_beli) * ($persentase / 100); 
    } elseif (strtolower($barang['jenis_penjualan']) == 'kaca') {
        $subtotal = ($harga_jual * $kali_kaca) * $jml;
        $keuntungan = (($harga_jual - $harga_beli) * $kali_kaca) * $jml;
    } else {
        $subtotal = $harga_jual * $jml;
        $keuntungan = ($harga_jual - $harga_beli) * $jml;
    }

    $total_harga += $subtotal;
    $total_keuntungan += $keuntungan;
}

// =====================================
// STATUS PEMBAYARAN
// =====================================
$status_pembayaran = 'Lunas';
$kembali = 0;

if ($metode_pembayaran == 'Hutang') {
    $status_pembayaran = 'Belum Lunas';
    $bayar = 0;
    $kembali = 0;

    if (empty($nama_customer)) {
        echo "
        <script>
            alert('Nama customer wajib diisi untuk transaksi hutang');
            window.location='transaksi.php';
        </script>
        ";
        exit;
    }
} else {
    if ($bayar < $total_harga) {
        echo "
        <script>
            alert('Uang pembayaran kurang');
            window.location='transaksi.php';
        </script>
        ";
        exit;
    }
    $kembali = $bayar - $total_harga;
}

// =====================================
// SIMPAN PENJUALAN
// =====================================

// =====================================
// UPLOAD BUKTI PEMBAYARAN
// =====================================

$bukti_pembayaran = "";

if (
    ($metode_pembayaran == "Transfer" || $metode_pembayaran == "QRIS") &&
    isset($_FILES['bukti_transaksi']) &&
    $_FILES['bukti_transaksi']['error'] == 0
) {

    $folder = "../uploads/bukti_pembayaran/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['bukti_transaksi']['name'], PATHINFO_EXTENSION));

    $nama_file = "bukti_" . time() . "." . $ext;

    move_uploaded_file(
        $_FILES['bukti_transaksi']['tmp_name'],
        $folder . $nama_file
    );

    $bukti_pembayaran = $nama_file;
}

$sql_jatuh_tempo = (!empty($jatuh_tempo))
    ? "'$jatuh_tempo'"
    : "NULL";

$query_insert = "
INSERT INTO penjualan (
    tanggal,
    total_harga,
    bayar,
    kembali,
    keuntungan,
    metode_pembayaran,
    referensi,
    bukti_pembayaran,
    nama_customer,
    status_pembayaran,
    id_user,
    jatuh_tempo
) VALUES (
    '$tanggal',
    '$total_harga',
    '$bayar',
    '$kembali',
    '$total_keuntungan',
    '$metode_pembayaran',
    '$referensi',
    '$bukti_pembayaran',
    '$nama_customer',
    '$status_pembayaran',
    '$id_user',
    $sql_jatuh_tempo
)";

$simpan_penjualan = mysqli_query($conn, $query_insert);

if (!$simpan_penjualan) {
    die("Gagal menyimpan transaksi : " . mysqli_error($conn));
}

// Ambil ID transaksi yang baru dibuat
$id_penjualan = mysqli_insert_id($conn);

// =====================================
// SIMPAN DETAIL PENJUALAN & UPDATE STOK
// =====================================
for ($i = 0; $i < count($id_barang); $i++) {
    $idb = (int)$id_barang[$i];
    $jml = (int)$jumlah[$i];
    
    $persentase = isset($persen_array[$i]) ? (float)$persen_array[$i] : 100;
    $kebutuhan_val = isset($kebutuhan_array[$i]) ? mysqli_real_escape_string($conn, $kebutuhan_array[$i]) : '1';

    $query_barang = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang = '$idb'");
    $barang = mysqli_fetch_assoc($query_barang);
    $harga_jual = (int)$barang['harga_jual'];

    if ($barang['jenis_penjualan'] == 'fleksibel') {
        $nilai_kebutuhan = $persentase . "%";
    } else {
        $nilai_kebutuhan = $kebutuhan_val;
    }

    $stok_lama = (int)$barang['stok'];
    $stok_baru = $stok_lama - $jml;

    // SIMPAN DETAIL
    // Hitung subtotal sesuai jenis penjualan
if ($barang['jenis_penjualan'] == 'fleksibel') {

    $subtotal = $harga_jual * ($persentase / 100);

} elseif (strtolower($barang['jenis_penjualan']) == 'kaca') {

    $subtotal = ($harga_jual * (float)$kebutuhan_val) * $jml;

} else {

    $subtotal = $harga_jual * $jml;

}

$simpan_detail = mysqli_query($conn, "
INSERT INTO detail_penjualan (
    id_penjualan,
    id_barang,
    jumlah,
    harga,
    subtotal
) VALUES (
    '$id_penjualan',
    '$idb',
    '$jml',
    '$harga_jual',
    '$subtotal'
)");
    if (!$simpan_detail) {
        die('Gagal simpan detail : ' . mysqli_error($conn));
    }

    // UPDATE STOK BARANG
    $update_stok = mysqli_query($conn, "UPDATE barang SET stok = '$stok_baru' WHERE id_barang = '$idb'");
    if (!$update_stok) {
        die('Gagal update stok : ' . mysqli_error($conn));
    }
}

// =====================================
// ALALIHKAN KE STRUK
// =====================================
header("Location: struk.php?id=$id_penjualan");
exit;
?>