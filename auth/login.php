<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Sistem | Toko Mitra Azam</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            background: linear-gradient(to right, #0d6efd, #0dcaf0);
            height: 100vh;
        }

        .login-card{
            border-radius: 15px;
            overflow: hidden;
        }

        .card-header{
            background: #0d6efd;
            color: white;
        }

        .btn-login{
            background: #0d6efd;
            color: white;
            font-weight: bold;
        }

        .btn-login:hover{
            background: #0b5ed7;
            color: white;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="row justify-content-center align-items-center vh-100">

        <div class="col-md-4">

            <div class="card shadow-lg login-card">

                <div class="card-header text-center p-4">

                    <h3>
                        SISTEM PENJUALAN
                    </h3>

                    <p class="mb-0">
                        Toko Mitra Azam
                    </p>

                </div>

                <div class="card-body p-4">

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

                        <button type="submit"
                                class="btn btn-login w-100">

                            LOGIN

                        </button>

                    </form>

                    <div class="text-center mt-3">

                        Belum punya akun?

                        <a href="registrasi.php">
                            Registrasi
                        </a>

                    </div>

                    <div class="text-center mt-2">

                        <a href="lupa_password.php"
                        class="text-danger text-decoration-none">

                            Lupa Password?

                        </a>

                    </div>

                </div>

                <div class="card-footer text-center">

                    <small>
                        © 2026 Toko Mitra Azam
                    </small>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>