<?php
session_start();

if(!isset($_SESSION['level']) || $_SESSION['level'] != 'admin'){
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pengaturan Tema</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
    font-family:Arial, sans-serif;
    transition:0.3s;
}

/* DARK MODE */
body.dark-mode{
    background:#0f172a;
    color:#e2e8f0;
}

.theme-card{
    border-radius:18px;
    border:none;
    transition:0.3s;
}

body.dark-mode .theme-card{
    background:#1e293b;
    color:#e2e8f0;
}

.theme-header{
    background:linear-gradient(135deg,#f59e0b,#f97316);
    color:white;
    border-radius:18px 18px 0 0;
    padding:20px;
}

body.dark-mode .theme-header{
    background:linear-gradient(135deg,#334155,#1e293b);
}

.btn-back{
    border-radius:12px;
    padding:10px 18px;
    font-weight:600;
}

</style>

</head>

<body>

<div class="container py-5">

    <div class="card shadow theme-card">

        <div class="theme-header">
            <h4 class="mb-0">
                <i class="bi bi-palette-fill"></i>
                Pengaturan Tema Dashboard
            </h4>
        </div>

        <div class="card-body p-4">

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Tema sekarang sudah sinkron antar halaman.
            </div>

            <div class="mb-3">
                <strong>Status:</strong>
                <span id="themeStatus" class="badge bg-secondary">Loading...</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">

                <span class="text-muted">Versi sistem: v1.0</span>

                <div>

                    <button class="btn btn-warning me-2" onclick="toggleTheme()">
                        <i class="bi bi-brush"></i> Toggle Tema
                    </button>

                    <a href="setting.php" class="btn btn-secondary btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================= THEME SCRIPT (FIX UTAMA) ================= -->
<script>

function applyTheme(){
    const theme = localStorage.getItem("theme");

    if(theme === "dark"){
        document.body.classList.add("dark-mode");
    } else {
        document.body.classList.remove("dark-mode");
    }
}

function toggleTheme(){
    let theme = localStorage.getItem("theme");

    if(theme === "dark"){
        localStorage.setItem("theme","light");
    } else {
        localStorage.setItem("theme","dark");
    }

    applyTheme();
    updateStatus();
}

function updateStatus(){
    const theme = localStorage.getItem("theme");
    const status = document.getElementById("themeStatus");

    if(theme === "dark"){
        status.className = "badge bg-dark";
        status.innerText = "Dark Mode";
    } else {
        status.className = "badge bg-success";
        status.innerText = "Light Mode";
    }
}

document.addEventListener("DOMContentLoaded", function(){

    // default theme jika belum ada
    if(!localStorage.getItem("theme")){
        localStorage.setItem("theme","light");
    }

    applyTheme();
    updateStatus();

});

</script>

</body>
</html>