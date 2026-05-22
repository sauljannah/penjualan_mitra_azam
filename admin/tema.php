<?php
session_start();

if(!isset($_SESSION['level'])){
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Tema Dashboard</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-warning">

            <h4>Tema Dashboard</h4>

        </div>

        <div class="card-body">

            <div class="alert alert-info">

                Fitur tema dashboard masih dalam pengembangan.

            </div>

            <a href="setting.php"
            class="btn btn-secondary">

                Kembali

            </a>

        </div>

    </div>

</div>

</body>
</html>