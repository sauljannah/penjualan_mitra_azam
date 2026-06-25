<?php
// File ini akan dipanggil di setiap halaman admin
if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    $q = mysqli_query($conn, "SELECT tema FROM users WHERE id_user = '$id_user'");
    $data = mysqli_fetch_assoc($q);
    
    // Jika kolom belum ada atau error, default ke 'light'
    $theme = $data['tema'] ?? 'light';
    $_SESSION['tema'] = $theme;
} else {
    $theme = 'light';
}
?>