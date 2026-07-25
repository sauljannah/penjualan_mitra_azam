<?php
session_start();
// ============================
// SET TIMEZONE WIT (WAKTU INDONESIA TIMUR)
// ============================
date_default_timezone_set('Asia/Jayapura');
require_once '../config/koneksi.php';
/** @var mysqli $conn */

// =====================================
// PROTEKSI LOGIN
// =====================================
if (!isset($_SESSION['level']) || $_SESSION['level'] != "kasir") {
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
if ($id_penjualan <= 0) {
    die("ID Transaksi tidak valid.");
}

// Cek mode: Apakah mode 'internal' (untuk kasir/admin) atau kosong (untuk pelanggan)
$is_internal = isset($_GET['mode']) && $_GET['mode'] === 'internal';

// =====================================
// AMBIL DATA PENJUALAN & KASIR
// =====================================
$query_penjualan = mysqli_query(
    $conn,
    "SELECT p.*, u.nama AS nama_kasir 
     FROM penjualan p 
     LEFT JOIN users u ON p.id_user = u.id_user 
     WHERE p.id_penjualan = $id_penjualan"
);

if (!$query_penjualan) {
    die("Query Error : " . mysqli_error($conn));
}

if (mysqli_num_rows($query_penjualan) === 0) {
    echo "
    <script>
        alert('Data transaksi tidak ditemukan');
        window.location='transaksi.php';
    </script>
    ";
    exit;
}

$penjualan = mysqli_fetch_assoc($query_penjualan);

// =====================================
// AMBIL DETAIL TRANSAKSI
// =====================================
$query_detail = mysqli_query(
    $conn,
    "SELECT detail_penjualan.*, barang.nama_barang, barang.jenis_penjualan 
     FROM detail_penjualan 
     JOIN barang ON detail_penjualan.id_barang = barang.id_barang 
     WHERE detail_penjualan.id_penjualan = $id_penjualan 
     ORDER BY detail_penjualan.id_detail ASC"
);

if (!$query_detail) {
    die("Query Error : " . mysqli_error($conn));
}

// Ambil metode pembayaran
$metode_bayar = trim($penjualan['metode_pembayaran'] ?? 'Tunai');
$metode_clean = strtolower($metode_bayar);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - TRX-<?= str_pad($penjualan['id_penjualan'], 5, '0', STR_PAD_LEFT); ?> <?= $is_internal ? '(Internal)' : ''; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f1f5f9; min-height: 100vh; padding: 20px; }
        .struk { width: 380px; max-width: 100%; background: white; margin: auto; border-radius: 18px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 20px; text-align: center; }
        .header h2 { margin: 0; font-size: 18px; font-weight: 700; }
        .header p { margin: 3px 0; font-size: 11px; }
        .content { padding: 18px; }
        .info-box { background: #f8fafc; border-radius: 12px; padding: 12px; margin-bottom: 15px; }
        .info-table { width: 100%; }
        .info-table td { padding: 4px 0; font-size: 11px; }
        .label { color: #64748b; }
        .value { text-align: right; font-weight: 600; }
        .line { border-top: 1px dashed #cbd5e1; margin: 12px 0; }
        .item { margin-bottom: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
        .item-name { font-weight: 600; font-size: 12px; color: #0f172a; margin-bottom: 2px; }
        .item-detail { display: flex; justify-content: space-between; font-size: 11px; color: #475569; }
        .total-box { background: #eff6ff; border-radius: 12px; padding: 12px; }
        .total-table { width: 100%; }
        .total-table td { padding: 4px 0; font-size: 12px; }
        .total-final { font-size: 14px; font-weight: 700; color: #2563eb; }
        .footer { text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1; }
        .footer h6 { font-size: 13px; font-weight: 600; }
        .footer p { font-size: 11px; color: #64748b; margin: 0; }
        .btn-area { display: flex; flex-direction: column; gap: 8px; justify-content: center; margin-top: 20px; }
        .btn { border-radius: 10px; padding: 7px 12px; font-size: 12px; font-weight: 500; }
        .badge-hutang { background: #fee2e2; color: #dc2626; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; }
        .badge-lunas { background: #dcfce7; color: #16a34a; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; }
        .badge-mode { font-size: 10px; padding: 4px 8px; border-radius: 6px; }
        
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .struk { box-shadow: none; width: 100%; border-radius: 0; }
        }
    </style>
</head>

<body>

<div class="struk">
    <div class="header">
        <h2><i class="bi bi-shop"></i> TOKO MITRA AZAM</h2>
        <p>Sistem Informasi Penjualan</p>
        <p>Jl. Hj.Falaq Desa Luhu Dusun Limboro Kecamatan Huamual</p>
        <div class="mt-2">
            <span class="badge badge-mode <?= $is_internal ? 'bg-warning text-dark' : 'bg-light text-primary'; ?>">
                Nota Transaksi #<?= $id_penjualan; ?> <?= $is_internal ? '[INTERNAL/KASIR]' : ''; ?>
            </span>
        </div>
    </div>

    <div class="content">
        <div class="info-box">
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal</td>
                    <td class="value"><?= date('d-m-Y H:i', strtotime($penjualan['tanggal'])); ?></td>
                </tr>
                <tr>
                    <td class="label">Kasir</td>
                    <td class="value"><?= htmlspecialchars($penjualan['nama_kasir'] ?? 'Kasir'); ?></td>
                </tr>
                <tr>
                    <td class="label">No Transaksi</td>
                    <td class="value">TRX-<?= str_pad($penjualan['id_penjualan'], 5, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td class="label">Metode Bayar</td>
                    <td class="value">
                        <?php 
                        if ($metode_clean === 'qris') {
                            echo "QRIS " . (!empty($penjualan['referensi']) ? "(" . htmlspecialchars($penjualan['referensi']) . ")" : "");
                        } elseif ($metode_clean === 'transfer') {
                            echo "Transfer " . (!empty($penjualan['referensi']) ? "(" . htmlspecialchars($penjualan['referensi']) . ")" : "");
                        } elseif ($metode_clean === 'hutang') {
                            echo "Hutang";
                        } else {
                            echo "Tunai";
                        }
                        ?>
                    </td>
                </tr>

                <?php if ($metode_clean === 'hutang'): ?>
                <tr>
                    <td class="label">Customer</td>
                    <td class="value text-danger fw-bold"><?= htmlspecialchars($penjualan['nama_customer'] ?? '-'); ?></td>
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
                <?php else: ?>
                <tr>
                    <td class="label">Status</td>
                    <td class="value"><span class="badge-lunas">Lunas</span></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="line"></div>

        <?php while ($detail = mysqli_fetch_assoc($query_detail)): 
            $nama = htmlspecialchars($detail['nama_barang']);
            $jenis = strtolower($detail['jenis_penjualan'] ?? '');
            $subtotal = $detail['subtotal'];
            $harga = $detail['harga'];
            $qty = $detail['jumlah'];
            $panjang = $detail['panjang'];
            $lebar = $detail['lebar'];
            $modal_satuan = $detail['harga_satuan_beli'] ?? 0;
            $laba_item = $detail['keuntungan_item'] ?? 0;

            $keterangan = "";
            if ($jenis === 'kaca' && $panjang > 0 && $lebar > 0) {
                $keterangan = " ({$panjang}×{$lebar} cm)";
            } elseif ($jenis === 'fleksibel' && $detail['persen'] > 0) {
                $keterangan = " ({$detail['persen']}%)";
            }
        ?>
        <div class="item">
            <div class="item-name"><?= $nama . $keterangan; ?></div>
            <div class="item-detail">
                <span><?= $qty; ?> × Rp <?= number_format($harga, 0, ',', '.'); ?></span>
                <strong>Rp <?= number_format($subtotal, 0, ',', '.'); ?></strong>
            </div>
            
            <!-- Tambahan rincian khusus Mode Internal -->
            <?php if ($is_internal): ?>
            <div class="d-flex justify-content-between mt-1 pt-1 border-top border-light text-muted" style="font-size: 10px;">
                <span>Modal: Rp <?= number_format($modal_satuan, 0, ',', '.'); ?></span>
                <span class="text-success fw-semibold">Laba: Rp <?= number_format($laba_item, 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>

        <div class="line"></div>

        <div class="total-box">
            <table class="total-table">
                <tr>
                    <td class="fw-medium">Total Belanja</td>
                    <td class="text-end total-final">
                        Rp <?= number_format($penjualan['total_harga'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Uang Dibayar</td>
                    <td class="text-end">
                        Rp <?= number_format($penjualan['bayar'], 0, ',', '.'); ?>
                    </td>
                </tr>
                
                <?php if ($metode_clean === 'hutang'): ?>
                <tr>
                    <td class="fw-bold text-danger">Total Hutang</td>
                    <td class="text-end fw-bold text-danger">
                        Rp <?= number_format($penjualan['total_harga'] - $penjualan['bayar'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <?php else: ?>
                <tr>
                    <td class="text-muted">Kembalian</td>
                    <td class="text-end text-success fw-bold">
                        Rp <?= number_format($penjualan['kembali'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <?php endif; ?>

                <!-- Tambahan total laba bersih untuk Mode Internal -->
                <?php if ($is_internal): ?>
                <tr>
                    <td colspan="2"><hr class="my-1"></td>
                </tr>
                <tr>
                    <td class="fw-bold text-success">Total Laba Bersih</td>
                    <td class="text-end fw-bold text-success">
                        Rp <?= number_format($penjualan['keuntungan'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="footer">
            <h6>Terima Kasih 🙏</h6>
            <p>Selamat Berbelanja Kembali</p>
        </div>

        <div class="btn-area no-print">
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-success flex-grow-1">
                    <i class="bi bi-printer"></i> Print Struk
                </button>
                <a href="transaksi.php" class="btn btn-secondary flex-grow-1">
                    <i class="bi bi-arrow-left"></i> Transaksi Baru
                </a>
            </div>
            
            <!-- Tombol Switch Mode Internal / Pelanggan -->
            <div>
                <?php if ($is_internal): ?>
                    <a href="struk.php?id=<?= $id_penjualan; ?>" class="btn btn-outline-secondary w-100 btn-sm">
                        <i class="bi bi-eye"></i> Beralih ke Mode Pelanggan
                    </a>
                <?php else: ?>
                    <a href="struk.php?id=<?= $id_penjualan; ?>&mode=internal" class="btn btn-outline-warning text-dark w-100 btn-sm">
                        <i class="bi bi-shield-lock"></i> Beralih ke Mode Internal / Laba
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>