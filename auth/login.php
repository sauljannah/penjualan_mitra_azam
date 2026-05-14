<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login Sistem | Toko Mitra Azam</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icon -->
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet">

<style>

*{
    font-family:'Poppins',sans-serif;
}

body{

    min-height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    background:
    linear-gradient(
        135deg,
        #0f172a,
        #1e293b,
        #2563eb
    );

    padding:20px;
}

.login-card{

    width:100%;
    max-width:450px;

    border:none;
    border-radius:30px;

    overflow:hidden;

    background:white;

    box-shadow:0 20px 50px rgba(0,0,0,.25);
}

.card-header{

    background:linear-gradient(135deg,#2563eb,#1e40af);

    color:white;
    text-align:center;

    padding:35px;
    border:none;
}

.logo-icon{
    font-size:60px;
    margin-bottom:10px;
}

.card-header h3{
    font-weight:700;
    margin-bottom:5px;
}

.card-body{
    padding:35px;
}

.form-label{
    font-weight:500;
}

.form-control{
    border-radius:15px;
    padding:12px;
}

.form-control:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 .2rem rgba(37,99,235,.15);
}

.btn-login{

    width:100%;
    padding:13px;

    border:none;
    border-radius:15px;

    font-weight:600;
    color:white;

    background:linear-gradient(135deg,#2563eb,#1e40af);

    transition:.3s;
}

.btn-login:hover{

    transform:translateY(-2px);
    background:linear-gradient(135deg,#1d4ed8,#1e3a8a);
}

.card-footer{

    background:#f8fafc;
    text-align:center;
    border:none;
    padding:15px;
}

.link-custom{
    color:#2563eb;
    text-decoration:none;
    font-weight:600;
}

.link-custom:hover{
    color:#1e40af;
}

</style>

</head>

<body>

<div class="login-card">

    <!-- HEADER -->
    <div class="card-header">

        <div class="logo-icon">
            <i class="bi bi-shop"></i>
        </div>

        <h3>SISTEM PENJUALAN</h3>

        <p class="mb-0">
            Toko Mitra Azam
        </p>

    </div>

    <!-- BODY -->
    <div class="card-body">

        <form action="proses_login.php" method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Username
                </label>

                <input type="text"
                       name="username"
                       class="form-control"
                       placeholder="Masukkan Username"
                       required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Password
                </label>

                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Masukkan Password"
                       required>

            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right"></i>
                LOGIN
            </button>

        </form>

        <div class="text-center mt-3">

            Belum punya akun?
            <a href="registrasi.php" class="link-custom">
                Registrasi
            </a>

        </div>

        <div class="text-center mt-2">

            <a href="lupa_password.php" class="link-custom">
                Lupa Password?
            </a>

        </div>

    </div>

    <!-- FOOTER -->
    <div class="card-footer">
        <small>© 2026 Toko Mitra Azam</small>
    </div>

</div>

</body>
</html>