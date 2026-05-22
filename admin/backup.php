<?php

session_start();

if(!isset($_SESSION['level'])){
    header("Location: ../auth/login.php");
    exit;
}

$database = "penjualan_mitra_azam";

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="backup_database.sql"');

echo "-- Backup Database\n";
echo "-- Database : ".$database."\n";
echo "-- Tanggal : ".date('d-m-Y H:i:s');

exit;

?>