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

<!-- ================= THEME SYSTEM ================= -->
<script src="../assets/theme.js"></script>

<style>

/* ================= BASE ================= */
body{
    background:#f1f5f9;
    font-family:Arial, sans-serif;
    transition:0.3s;
}

/* ================= DARK MODE ================= */
body.dark-mode{
    background:#0f172a;
    color:#e2e8f0;
}

/* ================= CARD ================= */
.theme-card{
    border-radius:18px;
    border:none;
    transition:0.3s;
}

/* DARK CARD */
body.dark-mode .theme-card{
    background:#1e293b;
    color:#e2e8f0;
}

/* HEADER */
.theme-header{
    background:linear-gradient(135deg,#f59e0b,#f97316);
    color:white;
    border-radius:18px 18px 0 0;
    padding:20px;
}

/* DARK HEADER */
body.dark-mode .theme-header{
    background:linear-gradient(135deg,#334155,#1e293b);
}

/* BUTTON */
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

        <!-- HEADER -->
        <div class="theme-header">

            <h4 class="mb-0">
                <i class="bi bi-palette-fill"></i>
                Pengaturan Tema Dashboard
            </h4>

        </div>

        <!-- BODY -->
        <div class="card-body p-4">

            <!-- INFO -->
            <div class="alert alert-info">

                <i class="bi bi-info-circle"></i>
                Sistem tema sudah aktif dan terhubung dengan Setting Dashboard.
                Anda dapat mengubah tema dari halaman Setting.

                <ul class="mb-0 mt-2">
                    <li>Dark Mode aktif global</li>
                    <li>Light Mode otomatis</li>
                    <li>Sinkron antar halaman</li>
                </ul>

            </div>

            <!-- STATUS TEMA -->
            <div class="mb-3">

                <strong>Status Tema Saat Ini:</strong>
                <span id="themeStatus" class="badge bg-secondary">Checking...</span>

            </div>

            <!-- ACTION -->
            <div class="d-flex justify-content-between align-items-center mt-4">

                <span class="text-muted">
                    Versi sistem: v1.0
                </span>

                <div>

                    <!-- TOGGLE TEMA -->
                    <button class="btn btn-warning me-2"
                            onclick="toggleTheme()">

                        <i class="bi bi-brush"></i>
                        Toggle Tema

                    </button>

                    <!-- KEMBALI -->
                    <a href="setting.php"
                       class="btn btn-secondary btn-back">

                        <i class="bi bi-arrow-left"></i>
                        Kembali

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================= SYNC STATUS ================= -->
<script>

function updateStatus(){

    const theme = localStorage.getItem("theme");
    const status = document.getElementById("themeStatus");

    if(theme === "dark"){
        status.className = "badge bg-dark";
        status.innerText = "Dark Mode";
    }else{
        status.className = "badge bg-success";
        status.innerText = "Light Mode";
    }
}

// update saat load
document.addEventListener("DOMContentLoaded", function(){

    updateStatus();

    // update setiap toggle
    setInterval(updateStatus, 500);

});

</script>

</body>
</html>