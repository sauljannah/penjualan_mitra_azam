<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

if (!isset($_SESSION['level']) || $_SESSION['level'] != "kasir") {
    header("Location: ../auth/login.php");
    exit;
}

// =====================================
// VALIDASI INPUT DASAR
// =====================================
if (!isset($_POST['id_barang'], $_POST['jumlah'], $_POST['metode_pembayaran'])) {
    die("<script>alert('Data tidak lengkap!'); window.location='transaksi.php';</script>");
}

// =====================================
// AMBIL DATA & SANITASI
// =====================================
$id_barang = $_POST['id_barang'];
$jumlah = $_POST['jumlah'];
$persen_array = $_POST['persen'] ?? [];
$kebutuhan_array = $_POST['kebutuhan'] ?? [];
$metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
$nama_customer = mysqli_real_escape_string($conn, trim($_POST['nama_customer'] ?? ''));
$jatuh_tempo = !empty($_POST['jatuh_tempo']) ? mysqli_real_escape_string($conn, $_POST['jatuh_tempo']) : NULL;
$id_user = $_SESSION['id_user'] ?? 0;
$tanggal = date('Y-m-d H:i:s');

// Format bayar
$bayar = (int)str_replace('.', '', $_POST['bayar'] ?? 0);

// =====================================
// HITUNG TOTAL (Lakukan sebelum insert)
// =====================================
$total_harga = 0;
$total_keuntungan = 0;

for ($i = 0; $i < count($id_barang); $i++) {
    $idb = (int)$id_barang[$i];
    $jml = (int)($jumlah[$i] ?? 1);
    $persentase = (float)($persen_array[$i] ?? 100);
    $kali_kaca = (float)($kebutuhan_array[$i] ?? 1.0);

    $query_barang = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang = '$idb'");
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
// LOGIKA STATUS PEMBAYARAN
// =====================================
$status_pembayaran = ($metode_pembayaran == 'Hutang') ? 'Belum Lunas' : 'Lunas';
$kembali = ($metode_pembayaran == 'Hutang') ? 0 : max(0, $bayar - $total_harga);

if ($metode_pembayaran == 'Hutang' && empty($nama_customer)) {
    die("<script>alert('Nama customer wajib diisi untuk hutang!'); window.location='transaksi.php';</script>");
}

// =====================================
// SIMPAN KE DATABASE
// =====================================
$sql_penjualan = "INSERT INTO penjualan (tanggal, total_harga, bayar, kembali, keuntungan, metode_pembayaran, nama_customer, status_pembayaran, id_user, jatuh_tempo) 
                  VALUES ('$tanggal', '$total_harga', '$bayar', '$kembali', '$total_keuntungan', '$metode_pembayaran', '$nama_customer', '$status_pembayaran', '$id_user', " . ($jatuh_tempo ? "'$jatuh_tempo'" : "NULL") . ")";

if (!mysqli_query($conn, $sql_penjualan)) die('Error: ' . mysqli_error($conn));
$id_penjualan = mysqli_insert_id($conn);

// Simpan Detail & Update Stok
for ($i = 0; $i < count($id_barang); $i++) {
    $idb = (int)$id_barang[$i];
    $jml = (int)$jumlah[$i];
    $kebutuhan_val = mysqli_real_escape_string($conn, $kebutuhan_array[$i] ?? '1');
    
    // Update stok
    mysqli_query($conn, "UPDATE barang SET stok = stok - $jml WHERE id_barang = '$idb'");
    
    // Simpan detail
    mysqli_query($conn, "INSERT INTO detail_penjualan (id_penjualan, id_barang, jumlah, harga, kebutuhan) 
                         VALUES ('$id_penjualan', '$idb', '$jml', (SELECT harga_jual FROM barang WHERE id_barang='$idb'), '$kebutuhan_val')");
}

header("Location: struk.php?id=$id_penjualan");
exit;
?>