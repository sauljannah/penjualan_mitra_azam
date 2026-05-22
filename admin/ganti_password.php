<?php

session_start();
require_once '../config/koneksi.php';

if(!isset($_SESSION['level'])){
    header("Location: ../auth/login.php");
    exit;
}

if(isset($_POST['ganti'])){

    $password_lama = md5($_POST['password_lama']);
    $password_baru = md5($_POST['password_baru']);

    $id = $_SESSION['id_user'];

    $cek = mysqli_query($conn,
    "SELECT * FROM user
    WHERE id_user='$id'
    AND password='$password_lama'");

    if(mysqli_num_rows($cek) > 0){

        mysqli_query($conn,
        "UPDATE user SET
        password='$password_baru'
        WHERE id_user='$id'");

        echo "
        <script>
            alert('Password berhasil diganti');
            window.location='setting.php';
        </script>
        ";

    }else{

        echo "
        <script>
            alert('Password lama salah');
        </script>
        ";
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Ganti Password</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-warning">

            <h4>Ganti Password</h4>

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="mb-3">

                    <label>Password Lama</label>

                    <input
                    type="password"
                    name="password_lama"
                    class="form-control"
                    required>

                </div>

                <div class="mb-3">

                    <label>Password Baru</label>

                    <input
                    type="password"
                    name="password_baru"
                    class="form-control"
                    required>

                </div>

                <button
                type="submit"
                name="ganti"
                class="btn btn-warning">

                    Ganti Password

                </button>

                <a href="setting.php"
                class="btn btn-secondary">

                    Kembali

                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>