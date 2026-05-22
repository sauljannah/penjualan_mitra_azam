<?php

session_start();

require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// CEK KONEKSI DATABASE
// ======================================
if (!$conn) {

    die(
        "Koneksi database gagal : " .
        mysqli_connect_error()
    );
}

// ======================================
// CEK METHOD POST
// ======================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: login.php");
    exit;
}

// ======================================
// AMBIL INPUT LOGIN
// ======================================
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// ======================================
// VALIDASI INPUT
// ======================================
if (
    empty($username) ||
    empty($password)
) {

    echo "
    <script>
        alert('Username dan Password wajib diisi!');
        window.location='login.php';
    </script>
    ";

    exit;
}

// ======================================
// QUERY USER
// ======================================
$stmt = mysqli_prepare(

    $conn,

    "SELECT
        id_user,
        nama,
        username,
        password,
        level
     FROM users
     WHERE username = ?
     LIMIT 1"
);

// ======================================
// CEK QUERY
// ======================================
if (!$stmt) {

    die(
        "Prepare gagal : " .
        mysqli_error($conn)
    );
}

// ======================================
// BIND PARAMETER
// ======================================
mysqli_stmt_bind_param(
    $stmt,
    "s",
    $username
);

// ======================================
// EXECUTE QUERY
// ======================================
mysqli_stmt_execute($stmt);

// ======================================
// HASIL QUERY
// ======================================
$result = mysqli_stmt_get_result($stmt);

// ======================================
// CEK USERNAME
// ======================================
if (mysqli_num_rows($result) < 1) {

    echo "
    <script>
        alert('Username tidak ditemukan!');
        window.location='login.php';
    </script>
    ";

    exit;
}

// ======================================
// AMBIL DATA USER
// ======================================
$data = mysqli_fetch_assoc($result);

// ======================================
// STATUS LOGIN
// ======================================
$login_berhasil = false;

// ======================================
// PASSWORD HASH
// ======================================
if (
    password_verify(
        $password,
        $data['password']
    )
) {

    $login_berhasil = true;
}

// ======================================
// PASSWORD LAMA TANPA HASH
// ======================================
elseif (
    $password === $data['password']
) {

    $login_berhasil = true;

    // ==================================
    // UPGRADE PASSWORD KE HASH
    // ==================================
    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $update_password = mysqli_prepare(

        $conn,

        "UPDATE users
         SET password = ?
         WHERE id_user = ?"
    );

    if ($update_password) {

        mysqli_stmt_bind_param(
            $update_password,
            "si",
            $password_hash,
            $data['id_user']
        );

        mysqli_stmt_execute(
            $update_password
        );
    }
}

// ======================================
// LOGIN BERHASIL
// ======================================
if ($login_berhasil) {

    // ==================================
    // BUAT SESSION
    // ==================================
    $_SESSION['id_user'] =
    $data['id_user'];

    $_SESSION['nama'] =
    $data['nama'];

    $_SESSION['username'] =
    $data['username'];

    $_SESSION['level'] =
    $data['level'];

    // ==================================
    // UPDATE LAST LOGIN
    // ======================================
    $update_login = mysqli_prepare(

        $conn,

        "UPDATE users
         SET last_login = NOW()
         WHERE id_user = ?"
    );

    // ======================================
    // CEK QUERY UPDATE
    // ======================================
    if ($update_login) {

        mysqli_stmt_bind_param(
            $update_login,
            "i",
            $data['id_user']
        );

        mysqli_stmt_execute(
            $update_login
        );
    }

    // ==================================
    // REDIRECT
    // ==================================
    if ($data['level'] === 'admin') {

        header(
            "Location: ../admin/dashboard.php"
        );

        exit;
    }

    elseif ($data['level'] === 'kasir') {

        header(
            "Location: ../kasir/dashboard.php"
        );

        exit;
    }

    else {

        session_destroy();

        echo "
        <script>
            alert('Level user tidak dikenali!');
            window.location='login.php';
        </script>
        ";

        exit;
    }
}

// ======================================
// PASSWORD SALAH
// ======================================
else {

    echo "
    <script>
        alert('Password salah!');
        window.location='login.php';
    </script>
    ";

    exit;
}

?>