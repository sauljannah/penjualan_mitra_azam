<?php
session_start();

// =====================================
// SET TIMEZONE WIT (WAKTU INDONESIA TIMUR)
// =====================================
date_default_timezone_set('Asia/Jayapura');

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
// VALIDASI ID PENJUALAN
// =====================================
if (!isset($_GET['id'])) {
    echo "
    <script>
        alert('ID transaksi tidak ditemukan');
        window.location='transaksi.php';
    </script>
    ";
    exit;
}

$id_penjualan = (int) $_GET['id'];

// =====================================
// AMBIL DATA PENJUALAN
// =====================================
$query_penjualan = mysqli_query(
    $conn,
    "SELECT * FROM penjualan WHERE id_penjualan = '$id_penjualan'"
);

// =====================================
// VALIDASI QUERY
// =====================================
if (!$query_penjualan) {
    die("Query Error : " . mysqli_error($conn));
}

// =====================================
// VALIDASI DATA
// =====================================
if (mysqli_num_rows($query_penjualan) === 0) {
    echo "
    <script>
        alert('Data transaksi tidak ditemukan');
        window.location='transaksi.php';
    </script>
    ";
    exit;
}

// Data Penjualan
$penjualan = mysqli_fetch_assoc($query_penjualan);

// =====================================
// AMBIL DETAIL TRANSAKSI
// =====================================
$query_detail = mysqli_query(
    $conn,
    "SELECT detail_penjualan.*, barang.nama_barang
     FROM detail_penjualan
     JOIN barang ON detail_penjualan.id_barang = barang.id_barang
     WHERE detail_penjualan.id_penjualan = '$id_penjualan'
     ORDER BY detail_penjualan.id_detail ASC"
);

// VALIDASI QUERY DETAIL
if (!$query_detail) {
    die("Query Error : " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: #f1f5f9;
            min-height: 100vh;
            padding: 20px;
        }
        .struk {
            width: 340px;
            max-width: 100%;
            background: white;
            margin: auto;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        .content {
            padding: 18px;
        }
        .info-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 5px 0;
            font-size: 12px;
        }
        .label {
            color: #64748b;
        }
        .value {
            text-align: right;
            font-weight: 600;
        }
        .line {
            border-top: 1px dashed #cbd5e1;
            margin: 15px 0;
        }
        .item {
            margin-bottom: 12px;
        }
        .item-name {
            font-weight: 600;
            font-size: 13px;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #475569;
        }
        .total-box {
            background: #eff6ff;
            border-radius: 12px;
            padding: 12px;
        }
        .total-table {
            width: 100%;
        }
        .total-table td {
            padding: 5px 0;
            font-size: 13px;
        }
        .total-final {
            font-size: 15px;
            font-weight: 700;
            color: #2563eb;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e1;
        }
        .footer h6 {
            font-size: 14px;
            font-weight: 600;
        }
        .footer p {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }
        .btn-area {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
        .btn {
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
        }
        .badge-hutang {
            background: #fee2e2;
            color: #dc2626;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .btn-area {
                display: none;
            }
            .struk {
                box-shadow: none;
                width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>

<body>

<div class="struk">
    <div class="header">
        <h2><i class="bi bi-shop"></i> TOKO MITRA AZAM</h2>
        <p>Sistem Informasi Penjualan</p>
        <p>Jl. Hj.Falaq Desa Luhu Dusun Limboro Kecamatan Huamual</p>
    </div>

    <div class="content">
        <div class="info-box">
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal</td>
                    <td class="value">
                        <?php 
                        // Menambahkan 7 jam secara presisi agar sesuai dengan Waktu Indonesia Timur (WIT)
                        $waktu_wit = strtotime($penjualan['tanggal'] . ' +7 hours');
                        echo date('d-m-Y H:i', $waktu_wit); 
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Kasir</td>
                    <td class="value">
                        <?= !empty($penjualan['kasir']) ? htmlspecialchars($penjualan['kasir']) : $_SESSION['nama']; ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">No Transaksi</td>
                    <td class="value">TRX-<?= str_pad($penjualan['id_penjualan'], 5, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td class="label">Pembayaran</td>
                    <td class="value">
                        <?= !empty($penjualan['metode_pembayaran']) ? htmlspecialchars($penjualan['metode_pembayaran']) : 'Tunai'; ?>
                    </td>
                </tr>

                <?php if (!empty($penjualan['metode_pembayaran']) && strtolower($penjualan['metode_pembayaran']) == 'hutang'): ?>
                <tr>
                    <td class="label">Customer</td>
                    <td class="value text-danger">
                        <?= htmlspecialchars($penjualan['nama_customer'] ?? '-'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value"><span class="badge-hutang">Belum Lunas</span></td>
                </tr>
                <tr>
                    <td class="label">Jatuh Tempo</td>
                    <td class="value text-danger">
                        <?= !empty($penjualan['jatuh_tempo']) ? date('d-m-Y', strtotime($penjualan['jatuh_tempo'])) : '-'; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="line"></div>

        <?php while ($detail = mysqli_fetch_assoc($query_detail)): ?>
        <div class="item">
            <div class="item-name">
                <?= htmlspecialchars($detail['nama_barang']); ?>
                <?php
                if (isset($detail['kebutuhan'])) {
                    if ($detail['kebutuhan'] == 0.25) echo " (Potongan Kecil)";
                    elseif ($detail['kebutuhan'] == 0.50) echo " (Potongan Sedang)";
                    elseif ($detail['kebutuhan'] == 0.75) echo " (Potongan Besar)";
                    elseif ($detail['kebutuhan'] == 1) echo " (Full)";
                }
                ?>
            </div>
            <div class="item-detail">
                <span>
                    <?= $detail['jumlah']; ?> x Rp <?= number_format($detail['harga'], 0, ',', '.'); ?>
                </span>
                <strong>
                    Rp <?= number_format($detail['jumlah'] * $detail['harga'], 0, ',', '.'); ?>
                </strong>
            </div>
        </div>
        <?php endwhile; ?>

        <div class="line"></div>

        <div class="total-box">
            <table class="total-table">
                <tr>
                    <td>Total</td>
                    <td class="text-end total-final">
                        Rp <?= number_format($penjualan['total_harga'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <tr>
                    <td>Bayar</td>
                    <td class="text-end">
                        Rp <?= number_format($penjualan['bayar'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <tr>
                    <td>Kembali</td>
                    <td class="text-end text-success fw-bold">
                        Rp <?= number_format($penjualan['kembali'], 0, ',', '.'); ?>
                    </td>
                </tr>

                <?php if (!empty($penjualan['metode_pembayaran']) && strtolower($penjualan['metode_pembayaran']) == 'hutang'): ?>
                <tr>
                    <td class="fw-bold text-danger">Sisa Hutang</td>
                    <td class="text-end fw-bold text-danger">
                        Rp <?= number_format($penjualan['total_harga'] - $penjualan['bayar'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="footer">
            <h6>Terima Kasih 🙏</h6>
            <p>Selamat Berbelanja Kembali</p>
        </div>

        <div class="btn-area">
            <button onclick="window.print()" class="btn btn-success">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="transaksi.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

</body>
</html>