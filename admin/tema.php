<?php
session_start();
require_once '../config/koneksi.php';

if(!isset($_SESSION['level']) || $_SESSION['level'] != 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

// Ambil tema saat ini dari database untuk status awal
$id_user = $_SESSION['id_user'];
$res = mysqli_query($conn, "SELECT tema FROM user WHERE id_user = '$id_user'");
$row = mysqli_fetch_assoc($res);
$theme = $row['tema'] ?? 'light';
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Tema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">

<div class="container py-5">
    <div class="card shadow" style="border-radius:18px;">
        <div class="card-header text-white" style="background:linear-gradient(135deg,#f59e0b,#f97316); border-radius:18px 18px 0 0; padding:20px;">
            <h4><i class="bi bi-palette-fill"></i> Pengaturan Tema</h4>
        </div>
        <div class="card-body p-4">
            <p>Status: <span id="themeStatus" class="badge bg-secondary"><?php echo ucfirst($theme); ?> Mode</span></p>
            <button class="btn btn-warning" onclick="toggleTheme()">
                <i class="bi bi-brush"></i> Toggle Tema
            </button>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>

<script>
function toggleTheme() {
    const htmlEl = document.documentElement;
    let newTheme = htmlEl.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    
    // 1. Update UI
    htmlEl.setAttribute('data-bs-theme', newTheme);
    document.getElementById('themeStatus').innerText = newTheme.charAt(0).toUpperCase() + newTheme.slice(1) + " Mode";
    
    // 2. Kirim ke server (PENTING: Pastikan file update_tema.php ada di folder yang sama)
    fetch('update_tema.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tema: newTheme })
    });
}
</script>
</body>
</html>