<?php
session_start();
require_once '../config/koneksi.php';
global $conn;

$mysql_folder = "mysql-8.0.30-winx64"; // <--- SESUAIKAN DENGAN FOLDER DI D:\laragon\bin\mysql\
$mysqldump = "D://bin/mysql/$mysql_folder/bin/mysqldump.exe";

// CEK APAKAH FILE ADA
if (!file_exists($mysqldump)) {
    die("File mysqldump tidak ditemukan di: " . $mysqldump . "<br>Silakan cek apakah nama folder mysql sudah benar!");
}

$database = "penjualan_mitra_azam";
$folderBackup = "../backup/";
if(!is_dir($folderBackup)){ mkdir($folderBackup, 0777, true); }

$nama_file = "backup_" . date('Y-m-d_H-i-s') . ".sql";
$path = $folderBackup . $nama_file;

// Perintah Backup
$command = "\"$mysqldump\" --user=root --host=localhost --skip-column-statistics " . escapeshellarg($database) . " > " . escapeshellarg($path);

exec($command . " 2>&1", $output, $resultCode);

if($resultCode === 0 && file_exists($path)){
    mysqli_query($conn, "UPDATE setting SET terakhir_backup = NOW() LIMIT 1");
    echo "<script>alert('Backup berhasil!'); window.location='setting.php';</script>";
} else {
    echo "Terjadi kesalahan. <br> Perintah: $command <br><br> Output Error: <pre>" . print_r($output, true) . "</pre>";
}
?>