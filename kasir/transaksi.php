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

    "SELECT *
     FROM barang
     ORDER BY nama_barang ASC"

);

if (!$query_barang) {

    die(

        "Query Error : " .
        mysqli_error($conn)

    );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Transaksi Penjualan</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link
href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

<style>

*{
    font-family:'Poppins',sans-serif;
}

body{
    background:#f1f5f9;
    overflow-x:hidden;
}

.sidebar{

    width:260px;

    height:100vh;

    position:fixed;

    top:0;
    left:0;

    background:linear-gradient(
    180deg,
    #2563eb,
    #1e3a8a);

    padding:25px 15px;

    color:white;

    box-shadow:4px 0 15px rgba(0,0,0,0.1);

    z-index:1000;
}

.logo{

    text-align:center;

    font-size:28px;

    font-weight:700;

    margin-bottom:30px;
}

.logo i{
    margin-right:5px;
}

.sidebar a{

    display:flex;

    align-items:center;

    gap:12px;

    color:white;

    text-decoration:none;

    padding:14px 16px;

    border-radius:14px;

    margin-bottom:12px;

    transition:0.3s;
}

.sidebar a:hover{

    background:rgba(255,255,255,0.15);

    transform:translateX(5px);
}

.sidebar i{
    font-size:18px;
}

.content{

    margin-left:260px;

    padding:30px;
}

.card{

    border:none;

    border-radius:22px;

    overflow:hidden;

    box-shadow:
    0 5px 20px rgba(0,0,0,0.08);
}

.card-header{

    border:none;

    font-weight:600;

    padding:18px 22px;
}

.card-body{
    padding:25px;
}

.header-box{

    background:linear-gradient(
    135deg,
    #2563eb,
    #3b82f6);

    color:white;
}

.user-box{

    background:rgba(255,255,255,0.15);

    padding:10px 18px;

    border-radius:14px;
}

.search-input{

    border-radius:14px;

    padding:14px;

    border:1px solid #dbeafe;

    transition:0.3s;
}

.search-input:focus{

    border-color:#2563eb;

    box-shadow:
    0 0 0 4px rgba(37,99,235,0.1);
}

.table{

    vertical-align:middle;
}

.table thead{

    background:#eff6ff;
}

.table thead th{

    color:#1e3a8a;

    border:none;

    font-weight:600;
}

.table tbody tr{

    transition:0.2s;
}

.table tbody tr:hover{

    background:#f8fafc;
}

.table td{

    border-color:#eef2f7;
}

.btn{

    border-radius:12px;

    padding:10px 18px;

    font-weight:500;
}

.btn-success{

    background:#10b981;

    border:none;
}

.btn-success:hover{

    background:#059669;
}

.btn-danger{

    border:none;
}

.total-box{

    background:linear-gradient(
    135deg,
    #2563eb,
    #1d4ed8);

    color:white;

    padding:20px;

    border-radius:18px;

    text-align:center;

    box-shadow:
    0 5px 15px rgba(37,99,235,0.25);
}

.total-box h3{

    font-size:32px;

    font-weight:700;
}

.form-control{

    border-radius:14px;

    padding:12px;

    border:1px solid #dbeafe;
}

.form-control:focus{

    border-color:#2563eb;

    box-shadow:
    0 0 0 4px rgba(37,99,235,0.1);
}

@media(max-width:768px){

    .sidebar{

        position:relative;

        width:100%;

        height:auto;
    }

    .content{

        margin-left:0;

        padding:20px;
    }
}

</style>

</head>

<body>

<div class="sidebar">

    <div class="logo">

        <i class="bi bi-shop"></i>

        KASIR

    </div>

    <a href="dashboard.php">

        <i class="bi bi-house-door-fill"></i>

        Dashboard

    </a>

    <a href="transaksi.php">

        <i class="bi bi-cart-fill"></i>

        Transaksi

    </a>

    <a href="riwayat_transaksi.php">

        <i class="bi bi-clock-history"></i>

        Riwayat Transaksi

    </a>

    <a href="../auth/logout.php">

        <i class="bi bi-box-arrow-right"></i>

        Logout

    </a>

</div>

<div class="content">

    <!-- HEADER -->
    <div class="card header-box mb-4">

        <div class="card-body">

            <div class="d-flex
            justify-content-between
            align-items-center
            flex-wrap gap-3">

                <div>

                    <h2 class="fw-bold">

                        Transaksi Penjualan

                    </h2>

                    <p class="mb-0">

                        Sistem Kasir Toko Mitra Azam

                    </p>

                </div>

                <div class="user-box">

                    <i class="bi bi-person-circle"></i>

                    <?= htmlspecialchars($_SESSION['nama']); ?>

                </div>

            </div>

        </div>

    </div>

    <!-- SEARCH -->
    <div class="card mb-4">

        <div class="card-header bg-primary text-white">

            <i class="bi bi-search"></i>

            Cari Barang

        </div>

        <div class="card-body">

            <input
            type="text"
            id="search"
            class="form-control search-input"
            placeholder="Cari nama barang...">

        </div>

    </div>

    <!-- HASIL -->
    <div class="card mb-4 d-none"
    id="hasil">

        <div class="card-header bg-primary text-white">

            <i class="bi bi-box-seam"></i>

            Daftar Barang

        </div>

        <div class="card-body table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr class="text-center">

                        <th>Nama Barang</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody>

                <?php while($barang = mysqli_fetch_assoc($query_barang)): ?>

                <tr class="row-barang">

                    <td class="nama fw-medium">

                        <?= htmlspecialchars($barang['nama_barang']); ?>

                    </td>

                    <td class="text-center">

                        <?= $barang['stok']; ?>

                    </td>

                    <td>

                        Rp <?= number_format(
                        $barang['harga_jual'],
                        0,
                        ',',
                        '.'
                        ); ?>

                    </td>

                    <td class="text-center">

                        <button
                        type="button"

                        class="btn btn-success btn-sm add"

                        data-id="<?= $barang['id_barang']; ?>"

                        data-nama="<?= htmlspecialchars($barang['nama_barang']); ?>"

                        data-harga="<?= $barang['harga_jual']; ?>">

                        <i class="bi bi-cart-plus"></i>

                        Tambah

                        </button>

                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- KERANJANG -->
    <div class="card">

        <div class="card-header bg-primary text-white">

            <div class="d-flex
            justify-content-between
            align-items-center
            flex-wrap gap-3">

                <h5 class="mb-0">

                    <i class="bi bi-cart-fill"></i>

                    Keranjang Belanja

                </h5>

                <div class="total-box">

                    <small>Total Belanja</small>

                    <h3 id="total-header">

                        Rp 0

                    </h3>

                </div>

            </div>

        </div>

        <div class="card-body">

            <form
            method="POST"
            action="simpan_transaksi.php"
            id="formTransaksi">

                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr class="text-center">

                                <th>Barang</th>
                                <th>Harga</th>
                                <th width="120">Qty</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>

                            </tr>

                        </thead>

                        <tbody id="cart"></tbody>

                    </table>

                </div>

                <input
                type="hidden"
                name="total_harga"
                id="total_input">

                <!-- FORM PEMBAYARAN -->
                <div class="row mt-4">

                    <!-- METODE -->
                    <div class="col-md-12 mb-3">

                        <label class="form-label fw-semibold">

                            Metode Pembayaran

                        </label>

                        <select
                        name="metode_pembayaran"
                        id="metode_pembayaran"
                        class="form-control"
                        required>

                            <option value="">
                                -- Pilih Pembayaran --
                            </option>

                            <option value="Tunai">
                                Tunai
                            </option>

                            <option value="QRIS">
                                QRIS
                            </option>

                            <option value="Transfer">
                                Transfer
                            </option>

                            <option value="Hutang">
                                Hutang / Kredit
                            </option>

                        </select>

                    </div>

                    <!-- REFERENSI -->
                    <div
                    class="col-md-12 mb-3 d-none"
                    id="referensi_box">

                        <label class="form-label fw-semibold">

                            Nama Bank / E-Wallet

                        </label>

                        <input
                        type="text"
                        name="referensi"
                        class="form-control"
                        placeholder="Contoh : BCA / DANA">

                    </div>

                    <!-- CUSTOMER -->
                    <div
                    class="col-md-6 mb-3 d-none"
                    id="customer_box">

                        <label class="form-label fw-semibold">

                            Nama Customer

                        </label>

                        <input
                        type="text"
                        id="nama_customer"
                        name="nama_customer"
                        class="form-control"
                        placeholder="Masukkan nama customer">

                    </div>

                    <!-- JATUH TEMPO -->
                    <div
                    class="col-md-6 mb-3 d-none"
                    id="jatuh_tempo_box">

                        <label class="form-label fw-semibold">

                            Jatuh Tempo

                        </label>

                        <input
                        type="date"
                        id="jatuh_tempo"
                        name="jatuh_tempo"
                        class="form-control">

                    </div>

                    <!-- BAYAR -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Uang Pembayaran

                        </label>

                        <input
                        type="text"
                        name="bayar"
                        id="bayar"
                        class="form-control"
                        placeholder="Masukkan pembayaran">

                    </div>

                    <!-- KEMBALIAN -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">

                            Kembalian

                        </label>

                        <input
                        type="text"
                        id="kembalian"
                        class="form-control"
                        readonly>

                    </div>

                </div>

                <button
                type="submit"
                class="btn btn-success w-100 py-3">

                    <i class="bi bi-save"></i>

                    Simpan Transaksi

                </button>

            </form>

        </div>

    </div>

</div>

<script>

// FORMAT RUPIAH
function formatRupiah(angka){

    return 'Rp ' +
    angka.toLocaleString('id-ID');
}

// SEARCH
document.getElementById('search')
.addEventListener('keyup', function(){

    let value =
    this.value.toLowerCase();

    let hasil =
    document.getElementById('hasil');

    if(value === ''){

        hasil.classList.add('d-none');
        return;
    }

    hasil.classList.remove('d-none');

    document.querySelectorAll('.row-barang')
    .forEach(row => {

        let nama =
        row.querySelector('.nama')
        .innerText.toLowerCase();

        row.style.display =
        nama.includes(value)
        ? ''
        : 'none';
    });
});

// ADD CART
document.addEventListener('click', function(e){

    if(e.target.closest('.add')){

        let btn =
        e.target.closest('.add');

        let id =
        btn.dataset.id;

        let cek =
        document.querySelector(
        `input[name="id_barang[]"][value="${id}"]`
        );

        if(cek){

            alert('Barang sudah ada');
            return;
        }

        document.getElementById('cart')
        .insertAdjacentHTML('beforeend', `

        <tr class="item">

            <td>

                ${btn.dataset.nama}

                <input
                type="hidden"
                name="id_barang[]"
                value="${id}">

            </td>

            <td class="harga text-end">

                ${btn.dataset.harga}

            </td>

            <td>

                <input
                type="number"
                name="jumlah[]"
                value="1"
                min="1"
                class="form-control qty">

            </td>

            <td class="sub text-end fw-semibold">

                Rp 0

            </td>

            <td class="text-center">

                <button
                type="button"
                class="btn btn-danger btn-sm del">

                    <i class="bi bi-trash"></i>

                </button>

            </td>

        </tr>

        `);

        hitungTotal();
    }
});

// HITUNG TOTAL
function hitungTotal(){

    let total = 0;

    document.querySelectorAll('.item')
    .forEach(item => {

        let harga =
        parseInt(
        item.querySelector('.harga')
        .innerText
        ) || 0;

        let qty =
        parseInt(
        item.querySelector('.qty')
        .value
        ) || 0;

        let subtotal =
        harga * qty;

        item.querySelector('.sub')
        .innerText =
        formatRupiah(subtotal);

        total += subtotal;
    });

    document.getElementById(
    'total-header'
    ).innerText =
    formatRupiah(total);

    document.getElementById(
    'total_input'
    ).value = total;

    let metode =
    document.getElementById(
    'metode_pembayaran'
    ).value;

    let bayarInput =
    document.getElementById('bayar');

    // QRIS / TRANSFER
    if(
        metode === 'QRIS' ||
        metode === 'Transfer'
    ){

        bayarInput.readOnly = true;

        bayarInput.value =
        total.toLocaleString('id-ID');

        document.getElementById(
        'kembalian'
        ).value = 'Rp 0';
    }

    // HUTANG
    else if(metode === 'Hutang'){

        bayarInput.readOnly = true;

        bayarInput.value = '0';

        document.getElementById(
        'kembalian'
        ).value = 'Rp 0';
    }

    // TUNAI
    else{

        bayarInput.readOnly = false;

        let bayar =
        parseInt(
        bayarInput.value
        .replace(/\./g,'')
        ) || 0;

        let kembali =
        bayar - total;

        document.getElementById(
        'kembalian'
        ).value =
        formatRupiah(
        kembali > 0 ? kembali : 0
        );
    }
}

// FORMAT INPUT BAYAR
document.getElementById('bayar')
.addEventListener('keyup', function(){

    let metode =
    document.getElementById(
    'metode_pembayaran'
    ).value;

    if(
        metode === 'QRIS' ||
        metode === 'Transfer' ||
        metode === 'Hutang'
    ){
        return;
    }

    let angka =
    this.value.replace(/\D/g,'');

    if(angka !== ''){

        this.value =
        parseInt(angka)
        .toLocaleString('id-ID');

    }else{

        this.value = '';
    }

    hitungTotal();
});

// METODE PEMBAYARAN
document.getElementById('metode_pembayaran')
.addEventListener('change', function(){

    let metode = this.value;

    let referensiBox =
    document.getElementById('referensi_box');

    let customerBox =
    document.getElementById('customer_box');

    let jatuhTempoBox =
    document.getElementById('jatuh_tempo_box');

    let customerInput =
    document.getElementById('nama_customer');

    let jatuhTempo =
    document.getElementById('jatuh_tempo');

    // QRIS / TRANSFER
    if(
        metode === 'QRIS' ||
        metode === 'Transfer'
    ){

        referensiBox
        .classList.remove('d-none');

        customerBox
        .classList.add('d-none');

        jatuhTempoBox
        .classList.add('d-none');

        customerInput.required = false;

        jatuhTempo.required = false;
    }

    // HUTANG
    else if(metode === 'Hutang'){

        referensiBox
        .classList.add('d-none');

        customerBox
        .classList.remove('d-none');

        jatuhTempoBox
        .classList.remove('d-none');

        customerInput.required = true;

        jatuhTempo.required = true;
    }

    // TUNAI
    else{

        referensiBox
        .classList.add('d-none');

        customerBox
        .classList.add('d-none');

        jatuhTempoBox
        .classList.add('d-none');

        customerInput.required = false;

        jatuhTempo.required = false;
    }

    hitungTotal();
});

// DELETE ITEM
document.addEventListener('click', function(e){

    if(e.target.closest('.del')){

        e.target.closest('tr')
        .remove();

        hitungTotal();
    }
});

// UPDATE TOTAL
document.addEventListener(
'change',
function(e){

    if(e.target.classList.contains('qty')){

        hitungTotal();
    }
});

// VALIDASI FORM
document.getElementById('formTransaksi')
.addEventListener('submit', function(e){

    let metode =
    document.getElementById(
    'metode_pembayaran'
    ).value;

    let customer =
    document.getElementById(
    'nama_customer'
    ).value;

    let jatuhTempo =
    document.getElementById(
    'jatuh_tempo'
    ).value;

    let cart =
    document.querySelectorAll('.item');

    if(cart.length < 1){

        e.preventDefault();

        alert(
        'Keranjang masih kosong'
        );

        return;
    }

    if(
        metode === 'Hutang' &&
        customer.trim() === ''
    ){

        e.preventDefault();

        alert(
        'Nama customer wajib diisi'
        );

        return;
    }

    if(
        metode === 'Hutang' &&
        jatuhTempo === ''
    ){

        e.preventDefault();

        alert(
        'Jatuh tempo wajib diisi'
        );

        return;
    }
});

</script>

</body>
</html>