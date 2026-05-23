<?php

session_start();
require_once '../config/koneksi.php';

/** @var mysqli $conn */

// ======================================
// PROTEKSI LOGIN
// ======================================
if(
    !isset($_SESSION['level']) ||
    $_SESSION['level'] != 'admin'
){

    header("Location: ../auth/login.php");
    exit;
}

// ======================================
// BUAT FOLDER LOGO
// ======================================
if(!is_dir("../assets/logo")){

    mkdir("../assets/logo", 0777, true);
}

// ======================================
// AMBIL DATA TOKO
// ======================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM profil_toko LIMIT 1"
);

if(!$query){

    die(
        "Query Error : " .
        mysqli_error($conn)
    );
}

// ======================================
// JIKA DATA BELUM ADA
// ======================================
if(mysqli_num_rows($query) == 0){

    $insert = mysqli_query(
        $conn,
        "INSERT INTO profil_toko
        (
            nama_toko,
            jenis_usaha,
            alamat,
            telepon,
            email,
            deskripsi,
            logo
        )

        VALUES
        (
            'MITRA AZAM',
            'Toko Bangunan',
            'Alamat Toko',
            '08123456789',
            'mitraazam@gmail.com',
            'Sistem Kasir Modern',
            ''
        )"
    );

    if(!$insert){

        die(
            "Insert Error : " .
            mysqli_error($conn)
        );
    }

    $query = mysqli_query(
        $conn,
        "SELECT * FROM profil_toko LIMIT 1"
    );
}

$toko = mysqli_fetch_assoc($query);

// ======================================
// PROSES UPDATE
// ======================================
if(isset($_POST['simpan'])){

    $nama_toko = mysqli_real_escape_string(
        $conn,
        trim($_POST['nama_toko'])
    );

    $jenis_usaha = mysqli_real_escape_string(
        $conn,
        trim($_POST['jenis_usaha'])
    );

    $alamat = mysqli_real_escape_string(
        $conn,
        trim($_POST['alamat'])
    );

    $telepon = mysqli_real_escape_string(
        $conn,
        trim($_POST['telepon'])
    );

    $email = mysqli_real_escape_string(
        $conn,
        trim($_POST['email'])
    );

    $deskripsi = mysqli_real_escape_string(
        $conn,
        trim($_POST['deskripsi'])
    );

    // ======================================
    // LOGO LAMA
    // ======================================
    $logo = isset($toko['logo'])
        ? $toko['logo']
        : '';

    // ======================================
    // UPLOAD LOGO
    // ======================================
    if(
        isset($_FILES['logo']) &&
        $_FILES['logo']['name'] != ""
    ){

        $nama_file = $_FILES['logo']['name'];
        $tmp       = $_FILES['logo']['tmp_name'];
        $size      = $_FILES['logo']['size'];
        $error     = $_FILES['logo']['error'];

        $ext = strtolower(
            pathinfo(
                $nama_file,
                PATHINFO_EXTENSION
            )
        );

        $format = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        // VALIDASI FORMAT
        if(!in_array($ext, $format)){

            echo "
            <script>

                alert('Format logo harus JPG, JPEG, PNG, atau WEBP');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }

        // VALIDASI UKURAN
        if($size > 2000000){

            echo "
            <script>

                alert('Ukuran logo maksimal 2MB');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }

        // VALIDASI ERROR
        if($error !== 0){

            echo "
            <script>

                alert('Terjadi kesalahan upload file');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }

        // NAMA FILE BARU
        $nama_baru =
            "logo_" .
            time() .
            "_" .
            rand(100,999) .
            "." .
            $ext;

        $tujuan =
            "../assets/logo/" .
            $nama_baru;

        // UPLOAD FILE
        if(
            move_uploaded_file(
                $tmp,
                $tujuan
            )
        ){

            // HAPUS LOGO LAMA
            if(
                $logo != "" &&
                file_exists(
                    "../assets/logo/" . $logo
                )
            ){

                unlink(
                    "../assets/logo/" . $logo
                );
            }

            $logo = $nama_baru;

        }else{

            echo "
            <script>

                alert('Gagal upload logo');

                window.location='edit_toko.php';

            </script>
            ";

            exit;
        }
    }

    // ======================================
    // UPDATE DATABASE
    // ======================================
    $update = mysqli_query(
        $conn,
        "UPDATE profil_toko SET

            nama_toko   = '$nama_toko',
            jenis_usaha = '$jenis_usaha',
            alamat      = '$alamat',
            telepon     = '$telepon',
            email       = '$email',
            deskripsi   = '$deskripsi',
            logo        = '$logo'

        WHERE id_toko='".$toko['id_toko']."'
        "
    );

    // ======================================
    // CEK UPDATE
    // ======================================
    if($update){

        echo "
        <script>

            alert('Profil toko berhasil diperbarui');

            window.location='setting.php';

        </script>
        ";

    }else{

        echo "
        <script>

            alert('Gagal update profil toko');

        </script>
        ";

        echo mysqli_error($conn);
    }
}

?>