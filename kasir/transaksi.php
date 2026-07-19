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
if (!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}
// =====================================
// CEK LEVEL KASIR
// =====================================
if ($_SESSION['level'] != "kasir") {
    header("Location: ../auth/login.php");
    exit;
}
// =====================================
// AMBIL DATA BARANG
// =====================================
$query_barang = mysqli_query(
    $conn,
    "SELECT * FROM barang ORDER BY nama_barang ASC"
);
if (!$query_barang) {
    die("Query Error : " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan</title>
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{ font-family:'Poppins',sans-serif; }
        body{ background:#f1f5f9; overflow-x:hidden; padding-top: 70px; }
        .offcanvas { background: linear-gradient(180deg, #2563eb, #1e3a8a) !important; color: #ffffff; width: 290px !important; border-right: none; }
        .sidebar-header-custom { padding: 25px 20px 10px 20px; }
        .logo{ font-size:24px; font-weight:700; color: white; display: flex; align-items: center; gap: 10px; }
        .sidebar-profile { background: rgba(0, 0, 0, 0.15); border-radius: 16px; padding: 15px; margin: 15px; display: flex; align-items: center; gap: 12px; border: 1px solid rgba(255, 255, 255, 0.1); }
        .profile-avatar { width: 45px; height: 45px; background: rgba(255, 255, 255, 0.2); border: 2px solid rgba(255, 255, 255, 0.6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .profile-info h6 { margin: 0; font-size: 14px; font-weight: 600; color: white; }
        .profile-info span { font-size: 12px; color: rgba(255, 255, 255, 0.75); display: flex; align-items: center; gap: 5px; }
        .sidebar-nav-container { padding: 5px 15px 20px 15px; }
        .sidebar-nav-container a { display: flex; align-items: center; color: rgba(255, 255, 255, 0.9); text-decoration: none; padding: 14px 18px; margin-bottom: 10px; border-radius: 14px; transition: 0.2s ease; font-weight: 500; }
        .sidebar-nav-container a:hover, .sidebar-nav-container a.active { background: rgba(255, 255, 255, 0.18); color: #ffffff; transform: translateX(4px); }
        .content{ padding:20px 30px; }
        .card{ border:none; border-radius:22px; overflow:hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .card-header{ border:none; font-weight:600; padding:18px 22px; }
        .card-body{ padding:25px; }
        .header-box{ background:linear-gradient(135deg, #2563eb, #3b82f6); color:white; }
        .user-box { background: rgba(255, 255, 255, 0.15); padding: 10px 18px; border-radius: 14px; }
        .search-input{ border-radius:14px; padding:14px; border:1px solid #dbeafe; transition:0.3s; }
        .search-input:focus{ border-color:#2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
        .table{ vertical-align:middle; }
        .table thead{ background:#eff6ff; }
        .table thead th{ color:#1e3a8a; border:none; font-weight:600; }
        .table tbody tr{ transition:0.2s; }
        .table tbody tr:hover{ background:#f8fafc; }
        .table td{ border-color:#eef2f7; }
        .btn{ border-radius:12px; padding:10px 18px; font-weight:500; }
        .btn-success{ background:#10b981; border:none; }
        .btn-success:hover{ background:#059669; }
        .total-box{ background:linear-gradient(135deg, #2563eb, #1d4ed8); color:white; padding:15px 25px; border-radius:18px; text-align:center; box-shadow: 0 5px 15px rgba(37,99,235,0.25); }
        .total-box h3{ font-size:28px; font-weight:700; margin: 0; }
        .form-control{ border-radius:14px; padding:12px; border:1px solid #dbeafe; }
        .form-control:focus{ border-color:#2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    </style>
</head>
<body>

<!-- Navbar & Sidebar (sama seperti sebelumnya) -->
<nav class="navbar bg-white fixed-top shadow-sm" style="height: 65px;">
  <div class="container-fluid px-4 d-flex align-items-center justify-content-start gap-3">
    <button class="btn btn-primary d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarKasir">
      <i class="bi bi-list fs-5"></i>
    </button>
    <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2 m-0 p-0" href="dashboard.php">
      <i class="bi bi-shop"></i> MITRA AZAM
    </a>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarKasir">
  <div class="sidebar-header-custom d-flex justify-content-between align-items-center">
    <div class="logo"><i class="bi bi-shop"></i> MITRA AZAM</div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="sidebar-profile">
      <div class="profile-avatar"><i class="bi bi-person-fill"></i></div>
      <div class="profile-info">
          <h6><?= htmlspecialchars($_SESSION['nama'] ?? 'Kasir Utama'); ?></h6>
          <span><i class="bi bi-circle-fill text-success" style="font-size: 7px;"></i> <?= htmlspecialchars(ucfirst($_SESSION['level'] ?? 'Kasir')); ?></span>
      </div>
  </div>
  <div class="offcanvas-body p-0">
    <div class="sidebar-nav-container">
        <a href="dashboard.php"><i class="bi bi-house-door-fill"></i> Dashboard</a>
        <a href="transaksi.php" class="active"><i class="bi bi-cart-fill"></i> Transaksi</a>
        <a href="data_hutang.php"><i class="bi bi-people-fill"></i> Data Hutang Customer</a>
        <a href="riwayat_transaksi.php"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a>
        <hr class="text-white-50 my-3">
        <a href="../auth/logout.php"><i class="bi bi-box-arrow-right text-danger"></i> <span class="text-white">Logout</span></a>
    </div>
  </div>
</div>

<div class="content">
    <!-- Header & Search tetap -->
    <div class="card header-box mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Transaksi Penjualan</h2>
                    <p class="mb-0 opacity-75">Sistem Kasir Toko Mitra Azam</p>
                </div>
                <div class="user-box border border-white border-opacity-25">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['nama']); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Barang tetap -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white p-3">
            <i class="bi bi-search me-1"></i> Cari Barang
        </div>
        <div class="card-body">
            <input type="text" id="search" class="form-control search-input" placeholder="Cari nama barang...">
        </div>
    </div>

    <div class="card mb-4 d-none shadow-sm" id="hasil">
        <div class="card-header bg-primary text-white p-3">
            <i class="bi bi-box-seam me-1"></i> Daftar Barang Ditemukan
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="table-light">
                        <th class="ps-4">Nama Barang</th>
                        <th class="text-center" style="width: 100px;">Stok</th>
                        <th class="text-end" style="width: 180px;">Harga Jual</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($barang = mysqli_fetch_assoc($query_barang)): ?>
                <tr class="row-barang">
                    <td class="nama fw-medium ps-4"><?= htmlspecialchars($barang['nama_barang']); ?></td>
                    <td class="text-center"><span class="badge bg-secondary rounded-pill px-3"><?= $barang['stok']; ?></span></td>
                    <td class="text-end fw-semibold">Rp <?= number_format($barang['harga_jual'], 0, ',', '.'); ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-success btn-sm add px-3"
                                data-id="<?= $barang['id_barang']; ?>"
                                data-nama="<?= htmlspecialchars($barang['nama_barang']); ?>"
                                data-harga="<?= $barang['harga_jual']; ?>"
                                data-jenis="<?= $barang['jenis_penjualan']; ?>">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cart-fill me-1"></i> Keranjang Belanja</h5>
                <div class="total-box">
                    <small class="opacity-75 d-block mb-1">TOTAL BELANJA</small>
                    <h3 id="total-header">Rp 0</h3>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <form method="POST" action="simpan_transaksi.php" id="formTransaksi" enctype="multipart/form-data">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-3">Barang</th>
                                <th class="text-end" style="width: 150px;">Harga</th>
                                <th style="width: 160px;">Kebutuhan</th>
                                <th style="width: 150px;">Qty / %</th>
                                <th class="text-end" style="width: 180px;">Subtotal</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cart"></tbody>
                    </table>
                </div>

                <input type="hidden" name="total_harga" id="total_input">

                <!-- Bagian Metode Pembayaran (sudah diperbaiki sebelumnya) -->
                <div class="row mt-4">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-semibold">Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                            <option value="">-- Pilih Pembayaran --</option>
                            <option value="Tunai">Tunai</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Hutang">Hutang</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3 d-none" id="referensi_box">
    <label class="form-label fw-semibold">
        Pilih Nama Bank
    </label>

    <select name="referensi" class="form-control">

        <option value="">-- Pilih Bank --</option>
        <option value="BCA">BCA</option>
        <option value="BRI">BRI</option>
        <option value="BNI">BNI</option>
        <option value="Mandiri">Mandiri</option>
        <option value="BTN">BTN</option>
        <option value="CIMB Niaga">CIMB Niaga</option>
        <option value="Permata">Permata</option>
        <option value="Lainnya">Lainnya</option>

    </select>
</div>

                    <div class="col-md-6 mb-3 d-none" id="bukti_box">
                        <label class="form-label fw-semibold text-primary">Upload Bukti Pembayaran (.jpg, .jpeg, .png)</label>
                        <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" class="form-control" accept="image/*">
                    </div>

                    <div class="col-md-6 mb-3 d-none" id="customer_box">
                        <label class="form-label fw-semibold text-danger">Nama Customer (Pihutang)</label>
                        <input type="text" id="nama_customer" name="nama_customer" class="form-control" placeholder="Masukkan nama lengkap customer">
                    </div>

                    <div class="col-md-6 mb-3 d-none" id="jatuh_tempo_box">
                        <label class="form-label fw-semibold text-danger">Tanggal Jatuh Tempo</label>
                        <input type="date" id="jatuh_tempo" name="jatuh_tempo" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Uang Pembayaran</label>
                        <input type="text" name="bayar" id="bayar" class="form-control" placeholder="Masukkan nominal pembayaran">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Kembalian</label>
                        <input type="text" id="kembalian" class="form-control bg-light text-dark fw-bold" readonly>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-3 mt-2 shadow-sm fs-5">
                    <i class="bi bi-save me-1"></i> Simpan Transaksi
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function formatRupiah(angka){
    return 'Rp ' + angka.toLocaleString('id-ID');
}

// SEARCH BARANG
document.getElementById('search').addEventListener('keyup', function(){
    let value = this.value.toLowerCase();
    let hasil = document.getElementById('hasil');
    if(value === ''){
        hasil.classList.add('d-none');
        return;
    }
    hasil.classList.remove('d-none');
    document.querySelectorAll('.row-barang').forEach(row => {
        let nama = row.querySelector('.nama').innerText.toLowerCase();
        row.style.display = nama.includes(value) ? '' : 'none';
    });
});

// ADD TO CART
document.addEventListener('click', function(e){
    if(e.target.closest('.add')){
        let btn = e.target.closest('.add');
        let id = btn.dataset.id;
       
        if(document.querySelector(`input[name="id_barang[]"][value="${id}"]`)){
            alert('Barang sudah ada di keranjang');
            return;
        }

        let kebutuhanField = '';
        let inputJumlahOrPersen = '';

        if(btn.dataset.jenis === 'kaca' || btn.dataset.jenis === 'Kaca'){
            kebutuhanField = `
            <div class="input-group input-group-sm">
                <input type="number" name="panjang[]" step="0.01" class="form-control pjg" placeholder="P (cm)" required>
                <input type="number" name="lebar[]" step="0.01" class="form-control lbr" placeholder="L (cm)" required>
            </div>`;
            inputJumlahOrPersen = `
            <input type="number" name="jumlah[]" value="1" min="1" class="form-control qty_real">
            <input type="hidden" name="persen[]" value="100">`;
        }
        else if(btn.dataset.jenis === 'fleksibel'){
            kebutuhanField = `<input type="hidden" name="kebutuhan[]" value="1"><span class="badge bg-info text-dark px-2 py-2">Fleksibel</span>`;
            inputJumlahOrPersen = `
            <input type="number" name="persen[]" value="100" min="1" max="100" class="form-control persen" placeholder="%">
            <input type="hidden" name="jumlah[]" value="1">`;
        }
        else{
            kebutuhanField = `<input type="hidden" name="kebutuhan[]" value="1"><span class="text-muted">Normal</span>`;
            inputJumlahOrPersen = `
            <input type="number" name="jumlah[]" value="1" min="1" class="form-control qty_real">
            <input type="hidden" name="persen[]" value="100">`;
        }

        document.getElementById('cart').insertAdjacentHTML('beforeend', `
        <tr class="item" data-jenis="${btn.dataset.jenis}">
            <td class="ps-3 fw-medium">${btn.dataset.nama}<input type="hidden" name="id_barang[]" value="${id}"></td>
            <td class="harga text-end" data-raw-harga="${btn.dataset.harga}">Rp ${parseInt(btn.dataset.harga).toLocaleString('id-ID')}</td>
            <td>${kebutuhanField}</td>
            <td>${inputJumlahOrPersen}</td>
            <td class="sub text-end fw-semibold text-primary">Rp 0</td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm del border-0 fs-5 p-1"><i class="bi bi-x-circle-fill"></i></button>
            </td>
        </tr>`);

        document.getElementById('search').value = '';
        document.getElementById('hasil').classList.add('d-none');
        hitungTotal();
    }
});

// HITUNG TOTAL - PERHITUNGAN KACA DIBAGI DENGAN HARGA ASLI
function hitungTotal(){
    let total = 0;

    document.querySelectorAll('.item').forEach(item => {
        let harga = parseInt(item.querySelector('.harga').dataset.rawHarga) || 0;
        let jenis = item.dataset.jenis;
        let subtotal = 0;

        if (jenis === 'fleksibel') {
            let persen = parseFloat(item.querySelector('.persen').value) || 0;
            subtotal = harga * (persen / 100);
        } 
        else if (jenis === 'kaca' || jenis === 'Kaca') {
            let pjg = parseFloat(item.querySelector('.pjg').value) || 0;
            let lbr = parseFloat(item.querySelector('.lbr').value) || 0;
            let qtyReal = parseInt(item.querySelector('.qty_real').value) || 1;
            
            // RUMUS BARU SESUAI PERMINTAAN: Harga Asli dibagi (Panjang x Lebar)
            let luas = pjg * lbr;
            if(luas > 0){
                subtotal = (harga / luas) * qtyReal;
            } else {
                subtotal = 0;
            }
        } 
        else {
            let qtyReal = parseInt(item.querySelector('.qty_real').value) || 1;
            subtotal = harga * qtyReal;
        }

        item.querySelector('.sub').innerText = formatRupiah(Math.round(subtotal));
        total += subtotal;
    });

    document.getElementById('total-header').innerText = formatRupiah(Math.round(total));
    document.getElementById('total_input').value = Math.round(total);

    // Logika Metode Pembayaran (Tetap)
    let metode = document.getElementById('metode_pembayaran').value;
    let bayarInput = document.getElementById('bayar');
    let kembalianInput = document.getElementById('kembalian');

    if(metode === 'QRIS' || metode === 'Transfer'){
        bayarInput.readOnly = true;
        bayarInput.value = Math.round(total).toLocaleString('id-ID');
        kembalianInput.value = 'Rp 0';
    } else if(metode === 'Hutang'){
        bayarInput.readOnly = true;
        bayarInput.value = '0';
        kembalianInput.value = 'Rp 0';
    } else {
        bayarInput.readOnly = false;
        let bayar = parseInt(bayarInput.value.replace(/\D/g,'')) || 0;
        let kembali = bayar - Math.round(total);
        kembalianInput.value = formatRupiah(kembali > 0 ? kembali : 0);
    }
}

// Event Listeners (tetap sama seperti perbaikan sebelumnya)
document.getElementById('bayar').addEventListener('keyup', function(){
    let metode = document.getElementById('metode_pembayaran').value;
    if(metode === 'QRIS' || metode === 'Transfer' || metode === 'Hutang') return;
    let angka = this.value.replace(/\D/g,'');
    this.value = angka !== '' ? parseInt(angka).toLocaleString('id-ID') : '';
    hitungTotal();
});

document.getElementById('metode_pembayaran').addEventListener('change', function(){

    let metode = this.value;

    let referensiBox = document.getElementById('referensi_box');
    let buktiBox = document.getElementById('bukti_box');
    let customerBox = document.getElementById('customer_box');
    let jatuhTempoBox = document.getElementById('jatuh_tempo_box');

    referensiBox.classList.add('d-none');
    buktiBox.classList.add('d-none');
    customerBox.classList.add('d-none');
    jatuhTempoBox.classList.add('d-none');

    document.getElementById('bukti_transaksi').required = false;
    document.getElementById('nama_customer').required = false;
    document.getElementById('jatuh_tempo').required = false;


    if(metode === 'Transfer'){

        referensiBox.classList.remove('d-none');
        buktiBox.classList.remove('d-none');
HEAD
        document.getElementById('bukti_transaksi').required = true;
    } else if(metode === 'Hutang'){


        document.getElementById('bukti_pembayaran').required = true;

    }

    else if(metode === 'QRIS'){

        buktiBox.classList.remove('d-none');

        document.getElementById('bukti_pembayaran').required = true;

    }

    else if(metode === 'Hutang'){

        customerBox.classList.remove('d-none');
        jatuhTempoBox.classList.remove('d-none');

        document.getElementById('nama_customer').required = true;
        document.getElementById('jatuh_tempo').required = true;

    }

    hitungTotal();

    

});

// HAPUS ITEM DARI KERANJANG
document.addEventListener("click", function(e){

    if(e.target.closest(".del")){

        // cari baris item
        let row = e.target.closest("tr");

        // hapus item
        row.remove();

        // hitung ulang total belanja
        hitungTotal();
    }

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>