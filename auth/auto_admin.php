<?php
require_once '../config/koneksi.php';

echo "SCRIPT AUTO ADMIN JALAN <br>";

if (!isset($conn) || !$conn) {
    die("Koneksi database gagal");
}

echo "KONEKSI BERHASIL <br>";

/* CEK ADMIN */
$stmt = mysqli_prepare($conn, "SELECT id_user FROM users WHERE level = ?");
$level = "admin";
mysqli_stmt_bind_param($stmt, "s", $level);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo "JUMLAH ADMIN: " . mysqli_num_rows($result) . "<br>";

if (mysqli_num_rows($result) == 0) {

    $nama     = "Administrator";
    $username = "admin";
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    $level    = "admin";
    $status   = "aktif";

    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (id_user,nama, username, password, level, status)
         VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "sssss",
        $nama,
        $username,
        $password,
        $level,
        $status
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "✅ ADMIN BERHASIL DIBUAT";
    } else {
        die("Insert gagal: " . mysqli_error($conn));
    }

} else {
    echo "ℹ ADMIN SUDAH ADA";
}
?>