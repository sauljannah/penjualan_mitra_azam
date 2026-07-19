<?php
session_start();
require_once '../config/koneksi.php';

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
$id_barang       = $_POST['id_barang'];
$jumlah          = $_POST['jumlah'] ?? [];
$panjangs        = $_POST['panjang'] ?? [];
$lebars          = $_POST['lebar'] ?? [];
$persen_array    = $_POST['persen'] ?? [];

$metode_pembayaran = trim($_POST['metode_pembayaran']);
$nama_customer     = trim($_POST['nama_customer'] ?? '');
$jatuh_tempo       = !empty($_POST['jatuh_tempo']) ? trim($_POST['jatuh_tempo']) : null;
$referensi         = trim($_POST['referensi'] ?? '');
$bayar             = (int) str_replace(['.', ','], '', $_POST['bayar'] ?? 0);

$id_user = $_SESSION['id_user'] ?? 0;
$tanggal = date('Y-m-d H:i:s');

// =====================================
// UPLOAD BUKTI PEMBAYARAN (SUDAH DIPERBAIKI + DEBUG)
// =====================================
$bukti_pembayaran = '';
$upload_error = '';

if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
    $file = $_FILES['bukti_pembayaran'];
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_size = $file['size'];

    if (in_array($file_ext, $allowed_ext) && $file_size <= 5 * 1024 * 1024) {
        $new_filename = 'bukti_' . date('YmdHis') . '_' . rand(1000, 9999) . '.' . $file_ext;
        $upload_dir = '../uploads/bukti_pembayaran/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $target_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $bukti_pembayaran = $new_filename;
        } else {
            $upload_error = "Gagal memindahkan file ke folder uploads.";
        }
    } else {
        $upload_error = "File tidak valid (hanya jpg, jpeg, png & maksimal 5MB).";
    }
} elseif (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] != 4) {
    // Error upload selain "no file uploaded"
    $upload_error = "Error upload file: " . $_FILES['bukti_pembayaran']['error'];
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
    $p      = (float)($panjangs[$i] ?? 0);
    $l      = (float)($lebars[$i] ?? 0);
    $persen = (float)($persen_array[$i] ?? 100);

    $query_barang = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang = $idb");
    $barang = mysqli_fetch_assoc($query_barang);

    if (!$barang) continue;

    $harga_jual = (int)$barang['harga_jual'];
    $harga_beli = (int)$barang['harga_beli'];
    $jenis      = strtolower($barang['jenis_penjualan'] ?? '');

    if ($jenis == 'kaca') {
        $luas = $p * $l;
        if ($luas > 0) {
            $subtotal   = ($harga_jual / $luas) * $jml;
            $keuntungan = (($harga_jual - $harga_beli) / $luas) * $jml;
        } else {
            $subtotal   = 0;
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

    $items[] = [
        'id_barang'  => $idb,
        'jumlah'     => $jml,
        'panjang'    => $p,
        'lebar'      => $l,
        'persen'     => $persen,
        'harga_jual' => $harga_jual,
        'jenis'      => $jenis,
        'subtotal'   => $subtotal
    ];
}

// =====================================
// SIMPAN PENJUALAN
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

mysqli_stmt_bind_param($stmt, "siiiisssssis", 
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
    $stmt_detail = mysqli_prepare($conn, "INSERT INTO detail_penjualan 
        (id_penjualan, id_barang, jumlah, panjang, lebar, harga, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    mysqli_stmt_bind_param($stmt_detail, "iiiddid", 
        $id_penjualan, 
        $item['id_barang'], 
        $item['jumlah'], 
        $item['panjang'], 
        $item['lebar'], 
        $item['harga_jual'], 
        $item['subtotal']
    );

    if (!mysqli_stmt_execute($stmt_detail)) {
        die('Gagal menyimpan detail penjualan: ' . mysqli_stmt_error($stmt_detail));
    }
    mysqli_stmt_close($stmt_detail);

    mysqli_query($conn, "UPDATE barang SET stok = stok - " . $item['jumlah'] . " 
                        WHERE id_barang = " . $item['id_barang']);
}

// =====================================
// REDIRECT KE STRUK
// =====================================
header("Location: struk.php?id=$id_penjualan");
exit;
?>