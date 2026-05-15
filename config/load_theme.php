<?php
// Pastikan $conn sudah didefinisikan di file koneksi.php
if (isset($conn)) {
    $queryGlobalSetting = mysqli_query($conn, "SELECT tema FROM setting LIMIT 1");
    $globalSetting = mysqli_fetch_assoc($queryGlobalSetting);
    $tema_sistem = $globalSetting['tema'] ?? 'light';
} else {
    // Fallback jika koneksi database gagal
    $tema_sistem = 'light';
}
?>