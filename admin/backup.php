<?php
session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// 1. Path Aplikasi mysqldump
$mysqldump = "D:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe";

if (!file_exists($mysqldump)) {
    die("File mysqldump tidak ditemukan di: " . $mysqldump);
}

$database = "penjualan_mitra_azam";

// 2. Mengubah path relatif menjadi absolut menggunakan realpath
$folderRelative = "../backup/";
if(!is_dir($folderRelative)){ 
    mkdir($folderRelative, 0777, true); 
}
$folderAbsolute = realpath($folderRelative) . DIRECTORY_SEPARATOR;

$nama_file = "backup_" . date('Y-m-d_H-i-s') . ".sql";
$path_lengkap = $folderAbsolute . $nama_file;

// 3. Perintah Backup Bersih (Menghapus --skip-column-statistics)
$command = sprintf(
    '""%s" --user=root --host=localhost %s > %s"',
    $mysqldump,
    escapeshellarg($database),
    escapeshellarg($path_lengkap)
);

// 4. Eksekusi menggunakan cmd.exe bawaan Windows
exec("cmd.exe /c " . $command . " 2>&1", $output, $resultCode);

// 5. Validasi Hasil Akhir
if($resultCode === 0 && file_exists($path_lengkap) && filesize($path_lengkap) > 0){
    mysqli_query($conn, "UPDATE setting SET terakhir_backup = NOW() LIMIT 1");
    echo "<script>alert('Backup basis data berhasil disimpan!'); window.location='setting.php';</script>";
} else {
    echo "<h3>Terjadi kesalahan saat melakukan backup!</h3>";
    echo "<strong>Perintah sistem yang dijalankan:</strong> <code>cmd.exe /c $command</code><br><br>";
    echo "<strong>Pesan Error Konsol:</strong> <pre>";
    if (empty($output)) {
        echo "Uraian pesan error kosong dari sistem operasi.";
    } else {
        print_r($output);
    }
    echo "</pre>";
}
?>