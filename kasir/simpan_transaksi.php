<?php
session_start();
// ============================
// SET TIMEZONE WIT (WAKTU INDONESIA TIMUR)
// ============================
date_default_timezone_set('Asia/Jayapura');
require_once '../config/koneksi.php';
/** @var mysqli $conn */

// =====================================
// CEK KONEKSI DATABASE
// =====================================
if (!isset($conn) || $conn === false) {
    die("Koneksi database gagal. Periksa file koneksi.php");
}

// =====================================
// CEK LOGIN KASIR
// =====================================
if (!isset($_SESSION['level']) || $_SESSION['level'] != "kasir") {
    header("Location: ../auth/login.php");
    exit;
}

// =====================================
// VALIDASI INPUT
// =====================================
if (!isset($_POST['id_barang']) || !is_array($_POST['id_barang']) || empty($_POST['id_barang'])) {
    die("<script>alert('Data barang tidak valid!'); window.location='transaksi.php';</script>");
}

if (!isset($_POST['metode_pembayaran']) || empty($_POST['metode_pembayaran'])) {
    die("<script>alert('Metode pembayaran harus dipilih!'); window.location='transaksi.php';</script>");
}

// =====================================
// AMBIL & SANITASI DATA
// =====================================
$id_barang        = $_POST['id_barang'];
$jumlah           = $_POST['jumlah'] ?? [];
$panjangs         = $_POST['panjang'] ?? [];
$lebars           = $_POST['lebar'] ?? [];
$persen_array     = $_POST['persen'] ?? [];

$metode_pembayaran = trim($_POST['metode_pembayaran']);
$nama_customer     = trim($_POST['nama_customer'] ?? '');
$jatuh_tempo       = null;

if ($metode_pembayaran == "Hutang") {
    if (empty($_POST['jatuh_tempo'])) {
        die("<script>alert('Tanggal jatuh tempo wajib diisi!'); window.location='transaksi.php';</script>");
    }
    $jatuh_tempo = date('Y-m-d', strtotime($_POST['jatuh_tempo']));
}

$referensi         = trim($_POST['referensi'] ?? '');
$bayar             = (int) str_replace(['.', ','], '', $_POST['bayar'] ?? 0);

$id_user = $_SESSION['id_user'] ?? 0;
$tanggal = date('Y-m-d H:i:s');

// =====================================
// UPLOAD BUKTI TRANSAKSI
// =====================================
$bukti_pembayaran = '';
if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
    $file = $_FILES['bukti_pembayaran'];
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_size = $file['size'];

    if (in_array($file_ext, $allowed_ext) && $file_size <= 5 * 1024 * 1024) { // Maksimal 5MB
        $new_filename = 'bukti_' . date('YmdHis') . '_' . rand(1000, 9999) . '.' . $file_ext;
        $upload_dir = '../uploads/bukti_pembayaran/';

        // Buat folder jika belum ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $target_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $bukti_pembayaran = $new_filename;
        }
    }
}

// =====================================
// HITUNG TOTAL HARGA & KEUNTUNGAN
// =====================================
$total_harga = 0;
$total_keuntungan = 0;
$items = [];

for ($i = 0; $i < count($id_barang); $i++) {
    $idb = (int)($id_barang[$i] ?? 0);
    if ($idb <= 0) continue;

    $jml    = (int)($jumlah[$i] ?? 1);
    $p      = (float)($panjangs[$i] ?? 0);   // cm
    $l      = (float)($lebars[$i] ?? 0);    // cm
    $persen = (float)($persen_array[$i] ?? 100);

    // Ambil data barang (Termasuk harga beli/modal)
    $query_barang = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang = $idb");
    $barang = mysqli_fetch_assoc($query_barang);

    if (!$barang) continue;

    $harga_jual = (float)$barang['harga_jual'];
    $harga_beli = (float)$barang['harga_beli'];
    $jenis      = strtolower($barang['jenis_penjualan'] ?? '');

    if ($jenis == 'kaca') {
        $luasPelanggan = $p * $l;                // cm²
        $luasStandar   = 200 * 200;              // 200cm x 200cm = standar 1 lembar
        if ($luasPelanggan > 0) {
            $subtotal   = ($luasPelanggan / $luasStandar) * $harga_jual * $jml;
            $keuntungan = ($luasPelanggan / $luasStandar) * ($harga_jual - $harga_beli) * $jml;
        } else {
            $subtotal = 0;
            $keuntungan = 0;
        }
    } elseif ($jenis == 'fleksibel') {
        $subtotal   = $harga_jual * ($persen / 100);
        $keuntungan = ($harga_jual - $harga_beli) * ($persen / 100);
    } else {
        $subtotal   = $harga_jual * $jml;
        $keuntungan = ($harga_jual - $harga_beli) * $jml;
    }

    $total_harga      += $subtotal;
    $total_keuntungan += $keuntungan;

    // Simpan ke array sementara untuk dieksekusi di database
    $items[] = [
        'id_barang'        => $idb,
        'jumlah'           => $jml,
        'panjang'          => $p,
        'lebar'            => $l,
        'persen'           => $persen,
        'harga_jual'       => $harga_jual,
        'harga_beli'       => $harga_beli, // Harga modal historis
        'subtotal'         => $subtotal,
        'keuntungan_item'  => $keuntungan  // Keuntungan bersih per item
    ];
}

// =====================================
// SIMPAN PENJUALAN UTAMA (Prepared Statement)
// =====================================
$status_pembayaran = ($metode_pembayaran == 'Hutang') ? 'Belum Lunas' : 'Lunas';
$kembali = ($metode_pembayaran == 'Hutang') ? 0 : max(0, $bayar - $total_harga);

$stmt = mysqli_prepare($conn, "INSERT INTO penjualan 
    (tanggal, total_harga, bayar, kembali, keuntungan, metode_pembayaran, 
     referensi, nama_customer, status_pembayaran, id_user, jatuh_tempo, bukti_pembayaran) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die("Prepare gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "siiiissssiss",
    $tanggal, 
    $total_harga, 
    $bayar, 
    $kembali, 
    $total_keuntungan, 
    $metode_pembayaran, 
    $referensi, 
    $nama_customer, 
    $status_pembayaran, 
    $id_user, 
    $jatuh_tempo,
    $bukti_pembayaran
);

if (!mysqli_stmt_execute($stmt)) {
    die("Gagal menyimpan penjualan: " . mysqli_stmt_error($stmt));
}

$id_penjualan = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// =====================================
// SIMPAN DETAIL PENJUALAN + UPDATE STOK
// =====================================
foreach ($items as $item) {
    // Pastikan kolom 'harga_satuan_beli' dan 'keuntungan_item' sudah ada di tabel 'detail_penjualan' Anda
    $stmt_detail = mysqli_prepare($conn, "INSERT INTO detail_penjualan 
        (id_penjualan, id_barang, jumlah, panjang, lebar, harga, harga_satuan_beli, subtotal, keuntungan_item) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt_detail) {
        die("Prepare detail gagal: " . mysqli_error($conn));
    }

    // Tipe data binding: i=integer, d=double/float
    mysqli_stmt_bind_param($stmt_detail, "iiiddiddd", 
        $id_penjualan, 
        $item['id_barang'], 
        $item['jumlah'], 
        $item['panjang'], 
        $item['lebar'], 
        $item['harga_jual'], 
        $item['harga_beli'],       // Menyimpan modal saat transaksi terjadi
        $item['subtotal'], 
        $item['keuntungan_item']   // Menyimpan keuntungan bersih item ini
    );

    if (!mysqli_stmt_execute($stmt_detail)) {
        die('Gagal menyimpan detail penjualan: ' . mysqli_stmt_error($stmt_detail));
    }
    mysqli_stmt_close($stmt_detail);

    // Update kurangi stok barang
    mysqli_query($conn, "UPDATE barang SET stok = stok - " . $item['jumlah'] . " 
                         WHERE id_barang = " . $item['id_barang']);
}

// =====================================
// REDIRECT KE STRUK
// =====================================
header("Location: struk.php?id=$id_penjualan");
exit;
?>