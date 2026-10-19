<?php
include "koneksi.php";
/* =====================================================
   FUNGSI ESCAPE
===================================================== */
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
function updateStokBuku($conn, $idBuku, $delta)
{
    $idBuku = (int) $idBuku;
    $delta = (int) $delta;

    if ($idBuku <= 0 || $delta === 0) {
        return true;
    }
    return mysqli_query(
        $conn,
        "UPDATE buku SET stok = stok + $delta WHERE id_buku='$idBuku'"
    );
}

function hitungDendaPeminjaman($tanggalPinjam, $tanggalKembali)
{
    if (empty($tanggalPinjam) || empty($tanggalKembali)) {
        return 0;
    }
    try {
        $pinjam = new DateTime($tanggalPinjam);
        $kembali = new DateTime($tanggalKembali);
    } catch (Exception $e) {
        return 0;
    }
    $batasWaktu = (clone $pinjam)->modify('+7 days');
    if ($kembali <= $batasWaktu) {
        return 0;
    }
    $selisihHari = (int) $kembali->diff($batasWaktu)->format('%r%a');
    if ($selisihHari <= 0) {
        return 0;
    }
    return $selisihHari * 10000;
}

$checkBookColumn = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM peminjaman LIKE 'id_buku'"
);
if (mysqli_num_rows($checkBookColumn) === 0) {
    mysqli_query(
        $conn,
        "ALTER TABLE peminjaman ADD COLUMN id_buku INT NULL AFTER id_anggota"
    );
}

/* =====================================================
   SIMPAN DATA PEMINJAMAN
===================================================== */

if (isset($_POST['simpan'])) {

    $id_anggota = mysqli_real_escape_string(
        $conn,
        $_POST['id_anggota']
    );
    $id_buku = filter_var($_POST['id_buku'] ?? '', FILTER_VALIDATE_INT);
    if ($id_buku === false || $id_buku <= 0) {
        $error = "Pilih buku yang akan dipinjam terlebih dahulu.";
    } else {
        $cekBuku = mysqli_query(
            $conn,
            "SELECT stok FROM buku WHERE id_buku='$id_buku' LIMIT 1"
        );
        if (mysqli_num_rows($cekBuku) === 0) {
            $error = "Buku yang dipilih tidak ditemukan.";
        } else {
            $dataBuku = mysqli_fetch_assoc($cekBuku);

            if ((int) $dataBuku['stok'] <= 0) {
                $error = "Stok buku tidak tersedia saat ini.";
            }
        }
    }
    if (!isset($error)) {
        $tanggal_pinjam = mysqli_real_escape_string(
            $conn,
            $_POST['tanggal_pinjam']
        );
        $tanggal_kembali = !empty($_POST['tanggal_kembali'])
            ? mysqli_real_escape_string(
                $conn,
                $_POST['tanggal_kembali']
            )
            : NULL;
        $status = !empty($tanggal_kembali)
            ? 'Dikembalikan'
            : 'Dipinjam';
        $denda = (int) ($_POST['denda'] ?? 0);
        if ($denda <= 0) {
            $denda = hitungDendaPeminjaman(
                $tanggal_pinjam,
                $tanggal_kembali
            );
        }
        if ($tanggal_kembali === NULL) {
        $tanggalKembaliSQL = "NULL";
    } else {
        $tanggalKembaliSQL = "'$tanggal_kembali'";
    }
        $query = mysqli_query(
            $conn,
            "INSERT INTO peminjaman
            (id_anggota, id_buku, tanggal_pinjam, tanggal_kembali, denda)
            VALUES
            (
                '$id_anggota',
                '$id_buku',
                '$tanggal_pinjam',
                $tanggalKembaliSQL,
                '$denda'
            )"
        );
        if ($query) {

            if (strtolower(trim($status)) === 'dipinjam') {
                mysqli_query(
                    $conn,
                    "UPDATE buku SET stok = stok - 1 WHERE id_buku='$id_buku' AND stok > 0"
                );
            }

            header("Location: peminjaman.php");
            exit;

        } else {

            $error =
                "Gagal menyimpan data peminjaman: "
                . mysqli_error($conn);
        }
    }
}


/* =====================================================
   HAPUS DATA
===================================================== */

if (isset($_GET['hapus'])) {

    $id = filter_var($_GET['hapus'] ?? '', FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $error = "ID peminjaman tidak valid.";

    } else {
        $cek = mysqli_query(
            $conn,
            "SELECT id_buku, tanggal_kembali FROM peminjaman WHERE id_peminjaman='$id' LIMIT 1"
        );

        if (mysqli_num_rows($cek) === 0) {
            $error = "Data peminjaman tidak ditemukan.";

        } else {
            $rowDelete = mysqli_fetch_assoc($cek);
            $idBukuDelete = (int) ($rowDelete['id_buku'] ?? 0);
            $tanggalKembaliDelete = trim((string) ($rowDelete['tanggal_kembali'] ?? ''));
            $statusDelete = empty($tanggalKembaliDelete) ? 'dipinjam' : 'dikembalikan';

            $query = mysqli_query(
                $conn,
                "DELETE FROM peminjaman
                 WHERE id_peminjaman='$id'"
            );

            if ($query) {
                if ($statusDelete === 'dipinjam' && $idBukuDelete > 0) {
                    updateStokBuku($conn, $idBukuDelete, 1);
                }

                header("Location: peminjaman.php");
                exit;
            } else {
                $error = "Gagal menghapus data peminjaman: "
                    . mysqli_error($conn);
            }
        }
    }

    if (isset($error)) {
        header("Location: peminjaman.php?error=" . urlencode($error));
        exit;
    }
}


/* =====================================================
   AMBIL DATA UNTUK EDIT
===================================================== */

$edit = false;
$dataEdit = null;


if (isset($_GET['edit'])) {

    $edit = true;

    $id = mysqli_real_escape_string(
        $conn,
        $_GET['edit']
    );


    $query = mysqli_query(
        $conn,
        "SELECT *
         FROM peminjaman
         WHERE id_peminjaman='$id'"
    );


    if (mysqli_num_rows($query) > 0) {

        $dataEdit = mysqli_fetch_assoc($query);

    } else {

        $edit = false;
    }
}


/* =====================================================
   UPDATE DATA
===================================================== */

if (isset($_POST['update'])) {

    $id = mysqli_real_escape_string(
        $conn,
        $_POST['id_peminjaman']
    );

    $id_anggota = mysqli_real_escape_string(
        $conn,
        $_POST['id_anggota']
    );

    $id_buku = filter_var($_POST['id_buku'] ?? '', FILTER_VALIDATE_INT);

    if ($id_buku === false || $id_buku <= 0) {
        $error = "Pilih buku yang akan dipinjam terlebih dahulu.";
    } else {
        $dataSebelum = mysqli_query(
            $conn,
            "SELECT id_buku, tanggal_kembali FROM peminjaman WHERE id_peminjaman='$id' LIMIT 1"
        );

        $idBukuLama = null;
        $statusLama = null;

        if (mysqli_num_rows($dataSebelum) > 0) {
            $rowSebelum = mysqli_fetch_assoc($dataSebelum);
            $idBukuLama = $rowSebelum['id_buku'];
            $statusLama = empty($rowSebelum['tanggal_kembali']) ? 'dipinjam' : 'dikembalikan';
        }

        $tanggal_pinjam = mysqli_real_escape_string(
            $conn,
            $_POST['tanggal_pinjam']
        );

        $tanggal_kembali = !empty($_POST['tanggal_kembali'])
            ? mysqli_real_escape_string(
                $conn,
                $_POST['tanggal_kembali']
            )
            : NULL;

        $status = !empty($tanggal_kembali)
            ? 'Dikembalikan'
            : 'Dipinjam';

        $denda = (int) ($_POST['denda'] ?? 0);
        if ($denda <= 0) {
            $denda = hitungDendaPeminjaman(
                $tanggal_pinjam,
                $tanggal_kembali
            );
        }


    if ($tanggal_kembali === NULL) {

        $tanggalKembaliSQL = "NULL";

    } else {

        $tanggalKembaliSQL = "'$tanggal_kembali'";
    }


        $query = mysqli_query(
            $conn,
            "UPDATE peminjaman SET

                id_anggota='$id_anggota',
                id_buku='$id_buku',
                tanggal_pinjam='$tanggal_pinjam',
                tanggal_kembali=$tanggalKembaliSQL,
                denda='$denda'

             WHERE id_peminjaman='$id'"
        );


        if ($query) {
            $statusBaru = strtolower(trim($status));
            $statusLamaLower = strtolower(trim((string) $statusLama));

            if ($statusLamaLower === 'dipinjam' && $statusBaru === 'dikembalikan') {
                if ($idBukuLama) {
                    mysqli_query(
                        $conn,
                        "UPDATE buku SET stok = stok + 1 WHERE id_buku='$idBukuLama'"
                    );
                }
            }

            if ($statusLamaLower === 'dikembalikan' && $statusBaru === 'dipinjam') {
                $cekBuku = mysqli_query(
                    $conn,
                    "SELECT stok FROM buku WHERE id_buku='$id_buku' LIMIT 1"
                );

                if (mysqli_num_rows($cekBuku) > 0) {
                    $rowBuku = mysqli_fetch_assoc($cekBuku);
                    if ((int) $rowBuku['stok'] > 0) {
                        mysqli_query(
                            $conn,
                            "UPDATE buku SET stok = stok - 1 WHERE id_buku='$id_buku'"
                        );
                    }
                }
            }

            if ($idBukuLama && $idBukuLama != $id_buku && $statusBaru === 'dipinjam') {
                mysqli_query(
                    $conn,
                    "UPDATE buku SET stok = stok + 1 WHERE id_buku='$idBukuLama'"
                );
                mysqli_query(
                    $conn,
                    "UPDATE buku SET stok = stok - 1 WHERE id_buku='$id_buku' AND stok > 0"
                );
            }

            header("Location: peminjaman.php");
            exit;

        } else {

            $error =
                "Gagal mengupdate data peminjaman: "
                . mysqli_error($conn);
        }
    }
}

?>


<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Peminjaman | Perpustakaan
    </title>


    <!-- =================================================
         BOOTSTRAP
    ================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =================================================
         BOOTSTRAP ICON
    ================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 25px;

            font-family:
                'Segoe UI',
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;

            background: #f7f3ed;

            color: #432918;
        }


        /* =================================================
           CONTAINER
        ================================================= */

        .page-container {

            width: 100%;

            max-width: 1250px;

            margin: auto;

            animation:
                pageIn .6s ease;
        }


        /* =================================================
           HEADER
        ================================================= */

        .page-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 22px;

            padding: 20px 24px;

            border-radius: 18px;

            background: white;

            border:
                1px solid #eee4d8;

            box-shadow:
                0 6px 25px
                rgba(67, 41, 24, .06);
        }


        .header-title {

            display: flex;

            align-items: center;

            gap: 12px;
        }


        .header-icon {

            width: 48px;

            height: 48px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 13px;

            background: #f4e2c6;

            color: #6b4426;

            font-size: 22px;
        }


        .header-title h2 {

            margin: 0;

            color: #432918;

            font-size: 21px;

            font-weight: 800;
        }


        .header-title p {

            margin: 3px 0 0;

            color: #99897b;

            font-size: 11px;
        }


        /* =================================================
           ERROR
        ================================================== */

        .error-box {

            margin-bottom: 20px;

            padding: 13px 16px;

            border-radius: 10px;

            background: #fce8e9;

            color: #b02a37;

            font-size: 13px;
        }


        /* =================================================
           FORM
        ================================================== */

        .form-card {

            padding: 25px;

            margin-bottom: 25px;

            border-radius: 18px;

            background: white;

            border:
                1px solid #eee4d8;

            box-shadow:
                0 6px 25px
                rgba(67, 41, 24, .06);

            animation:
                cardIn .7s ease;
        }


        .form-title {

            display: flex;

            align-items: center;

            gap: 9px;

            margin-bottom: 20px;

            color: #56361f;

            font-size: 16px;

            font-weight: 700;
        }


        .form-title i {

            color: #8a5a34;
        }


        .form-label {

            color: #5c422f;

            font-size: 13px;

            font-weight: 600;
        }


        /* =================================================
           INPUT
        ================================================== */

        .input-group {

            margin-bottom: 16px;
        }


        .input-group-text {

            border: none;

            background: #f1e9df;

            color: #6b4426;
        }


        .form-control,
        .form-select {

            border: none;

            background: #f1e9df;

            padding: 11px 13px;

            color: #432918;

            box-shadow: none !important;

            font-size: 13px;
        }


        .form-control:focus,
        .form-select:focus {

            background: #e9ded0;

            box-shadow:
                0 0 0 2px
                rgba(107, 68, 38, .12)
                !important;
        }


        /* =================================================
           BUTTON SIMPAN
        ================================================== */

        .btn-save {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 11px 22px;

            border: none;

            border-radius: 10px;

            background: #6b4426;

            color: white;

            font-size: 13px;

            font-weight: 600;

            transition: .25s;
        }


        .btn-save:hover {

            background: #51311c;

            color: white;

            transform:
                translateY(-2px);

            box-shadow:
                0 8px 18px
                rgba(107, 68, 38, .22);
        }


        .btn-update {

            background: #198754;
        }


        .btn-update:hover {

            background: #157347;
        }


        .btn-cancel {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            margin-left: 7px;

            padding: 10px 20px;

            border-radius: 10px;

            background: #eee8e0;

            color: #5c4939;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            transition: .2s;
        }


        .btn-cancel:hover {

            background: #ded5ca;

            color: #432918;
        }


        /* =================================================
           TABLE CARD
        ================================================== */

        .table-card {

            overflow: hidden;

            border-radius: 18px;

            background: white;

            border:
                1px solid #eee4d8;

            box-shadow:
                0 6px 25px
                rgba(67, 41, 24, .06);

            animation:
                cardIn .8s ease;
        }


        .table-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 20px 24px;

            border-bottom:
                1px solid #eee4d8;
        }


        .table-tools {

            padding: 18px 24px 14px;

            margin-bottom: 0;
        }


        .search-box {

            display: flex;

            align-items: center;

            gap: 10px;

            width: min(430px, 100%);

            padding: 10px 14px;

            border-radius: 12px;

            background: #fffdfb;

            border: 1px solid #eadcc7;

            box-shadow: 0 4px 12px rgba(67, 41, 24, .04);

            transition: border-color .2s ease, box-shadow .2s ease;
        }


        .search-box:focus-within {

            border-color: #c89f63;

            box-shadow: 0 0 0 3px rgba(107, 68, 38, .08);
        }


        .search-box i {

            color: #6b4426;

            font-size: 15px;

            flex-shrink: 0;
        }


        .search-box input {

            width: 100%;

            border: none;

            outline: none;

            background: transparent;

            color: #432918;

            font-size: 13px;

            appearance: none;

            -webkit-appearance: none;

            box-shadow: none !important;
        }


        .search-box input::placeholder {

            color: #947d68;
        }


        .table-title {

            margin: 0;

            color: #432918;

            font-size: 16px;

            font-weight: 700;
        }


        .table-title i {

            margin-right: 7px;

            color: #6b4426;
        }


        .badge-info {

            padding: 6px 10px;

            border-radius: 20px;

            background: #f4e2c6;

            color: #6b4426;

            font-size: 10px;

            font-weight: 600;
        }


        /* =================================================
           TABLE
        ================================================== */

        .table-responsive {

            overflow-x: auto;
        }


        table {

            width: 100%;

            min-width: 1050px;

            margin: 0;

            border-collapse: collapse;

            table-layout: fixed;
        }


        thead th {

            padding: 16px 18px;

            background: #6b4426;

            color: white;

            border: none;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;

            line-height: 1.5;
        }


        tbody td {

            padding: 16px 18px;

            border-bottom:
                1px solid #eee8e0;

            color: #5e5045;

            font-size: 12px;

            vertical-align: middle;

            line-height: 1.6;
        }


        tbody tr {

            transition: .2s;
        }


        tbody tr:hover {

            background: #fcf8f2;
        }


        tbody tr:last-child td {

            border-bottom: none;
        }


        /* =================================================
           ID
        ================================================== */

        .id-badge {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 38px;

            padding: 6px 9px;

            border-radius: 7px;

            background: #f4e2c6;

            color: #6b4426;

            font-weight: 700;

            font-size: 11px;
        }


        /* =================================================
           MEMBER
        ================================================== */

        .member-box {

            display: flex;

            align-items: center;

            gap: 9px;
        }


        .member-icon {

            width: 34px;

            height: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            border-radius: 50%;

            background: #f4e2c6;

            color: #6b4426;
        }


        .member-name {

            color: #513522;

            font-weight: 600;
        }


        /* =================================================
           DATE
        ================================================== */

        .date-badge {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 6px 9px;

            border-radius: 7px;

            background: #f5f1ec;

            color: #705d4c;

            font-size: 11px;

            font-weight: 600;
        }


        /* =================================================
           STATUS
        ================================================== */

        .status-badge {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 7px 10px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: 700;
        }


        .status-dipinjam {

            background: #fff3cd;

            color: #997404;
        }


        .status-dikembalikan {

            background: #d1e7dd;

            color: #146c43;
        }


        /* =================================================
           DENDA
        ================================================== */

        .denda {

            font-weight: 700;

            color: #dc3545;
        }


        .denda-free {

            color: #198754;

            font-weight: 600;
        }


        /* =================================================
           ACTION
        ================================================== */

        .action-buttons {

            display: flex;

            gap: 6px;
        }


        .btn-action {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 5px;

            padding: 7px 10px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 11px;

            font-weight: 600;

            transition: .2s;
        }


        .btn-edit {

            background: #fff3cd;

            color: #997404;
        }


        .btn-edit:hover {

            background: #ffdf7e;

            color: #664d03;

            transform:
                translateY(-2px);
        }


        .btn-delete {

            background: #fce8e9;

            color: #dc3545;
        }


        .btn-delete:hover {

            background: #f5c2c7;

            color: #b02a37;

            transform:
                translateY(-2px);
        }


        /* =================================================
           EMPTY
        ================================================== */

        .empty-data {

            padding: 50px 20px;

            text-align: center;

            color: #9b8b7b;
        }


        .empty-data i {

            display: block;

            margin-bottom: 10px;

            color: #c5b5a5;

            font-size: 45px;
        }


        /* =================================================
           MODAL
        ================================================== */

        .delete-modal {

            border: none;

            border-radius: 18px;

            overflow: hidden;
        }


        .delete-icon {

            width: 65px;

            height: 65px;

            margin: 0 auto 15px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 50%;

            background: #fff0f1;

            color: #dc3545;

            font-size: 27px;
        }


        /* =================================================
           ANIMATION
        ================================================== */

        @keyframes pageIn {

            from {

                opacity: 0;

                transform:
                    translateY(15px);
            }

            to {

                opacity: 1;

                transform:
                    translateY(0);
            }
        }


        @keyframes cardIn {

            from {

                opacity: 0;

                transform:
                    translateY(20px);
            }

            to {

                opacity: 1;

                transform:
                    translateY(0);
            }
        }


        /* =================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 700px) {

            body {

                padding: 15px;
            }


            .page-header {

                padding: 16px;
            }


            .form-card {

                padding: 18px;
            }


            .table-header {

                padding: 17px;
            }
        }


        @media (max-width: 500px) {

            body {

                padding: 10px;
            }


            .header-icon {

                width: 40px;

                height: 40px;
            }


            .header-title h2 {

                font-size: 17px;
            }


            .form-card,
            .table-card {

                border-radius: 14px;
            }


            .btn-save,
            .btn-cancel {

                width: 100%;

                justify-content: center;

                margin: 5px 0 0;
            }


            .btn-cancel {

                margin-left: 0;
            }
        }

    </style>

</head>


<body>


<div class="page-container">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="page-header">


        <div class="header-title">


            <div class="header-icon">

                <i class="bi bi-arrow-left-right"></i>

            </div>


            <div>

                <h2>
                    Data Peminjaman
                </h2>

                <p>
                    Kelola data peminjaman perpustakaan
                </p>

            </div>


        </div>


    </div>



    <!-- =================================================
         ERROR
    ================================================== -->

    <?php if (isset($error)) { ?>

        <div class="error-box">

            <i
                class="bi
                bi-exclamation-circle me-1"
            ></i>

            <?= e($error); ?>

        </div>

    <?php } ?>



    <!-- =================================================
         FORM PEMINJAMAN
    ================================================== -->

    <div class="form-card">


        <div class="form-title">

            <i class="bi
                <?= $edit
                    ? 'bi-pencil-square'
                    : 'bi-journal-plus'
                ?>">
            </i>


            <?= $edit
                ? 'Edit Data Peminjaman'
                : 'Tambah Peminjaman'
            ?>

        </div>



        <form method="post" onsubmit="hitungDendaOtomatis();">


            <?php if ($edit && $dataEdit) { ?>

                <input
                    type="hidden"
                    name="id_peminjaman"
                    value="<?= e(
                        $dataEdit['id_peminjaman']
                    ); ?>"
                >

            <?php } ?>


            <div class="row">


                <!-- =================================================
                     ANGGOTA
                ================================================== -->

                <div class="col-md-6">


                    <label class="form-label">

                        Anggota

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i
                                class="bi
                                bi-person-fill"
                            ></i>

                        </span>


                        <select
                            name="id_anggota"
                            class="form-select"
                            required
                        >


                            <option value="">

                                Pilih Anggota

                            </option>


                            <?php

                            $anggota =
                                mysqli_query(
                                    $conn,
                                    "SELECT *
                                     FROM anggota
                                     ORDER BY id_anggota ASC"
                                );


                            while (
                                $a =
                                mysqli_fetch_assoc(
                                    $anggota
                                )
                            ) {


                                $selected = '';


                                if (
                                    $edit &&
                                    $dataEdit &&
                                    $dataEdit[
                                        'id_anggota'
                                    ]
                                    ==
                                    $a[
                                        'id_anggota'
                                    ]
                                ) {

                                    $selected =
                                        'selected';
                                }


                                $namaAnggota =
                                    $a['Nama']
                                    ??
                                    $a['nama']
                                    ??
                                    '';

                            ?>


                                <option
                                    value="<?= e(
                                        $a['id_anggota']
                                    ); ?>"
                                    <?= $selected; ?>
                                >

                                    <?= e(
                                        $a['id_anggota']
                                    ); ?>

                                    -
                                    
                                    <?= e(
                                        $namaAnggota
                                    ); ?>

                                </option>


                            <?php } ?>


                        </select>


                    </div>

                </div>



                <!-- =================================================
                     BUKU YANG DIPINJAM
                ================================================== -->

                <div class="col-md-6">


                    <label class="form-label">

                        Buku yang Dipinjam

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i
                                class="bi
                                bi-book-fill"
                            ></i>

                        </span>


                        <select
                            name="id_buku"
                            class="form-select"
                            required
                        >


                            <option value="">

                                Pilih Buku

                            </option>


                            <?php

                            $bukuList = mysqli_query(
                                $conn,
                                "SELECT * FROM buku ORDER BY judul_buku ASC"
                            );


                            while ($b = mysqli_fetch_assoc($bukuList)) {
                                $selected = '';

                                if (
                                    $edit &&
                                    $dataEdit &&
                                    $dataEdit['id_buku'] == $b['id_buku']
                                ) {
                                    $selected = 'selected';
                                }
                            ?>


                                <option
                                    value="<?= e($b['id_buku']); ?>"
                                    <?= $selected; ?>
                                >

                                    <?= e($b['judul_buku']); ?>
                                    (Stok: <?= e($b['stok']); ?>)

                                </option>


                            <?php } ?>


                        </select>


                    </div>

                </div>



                <!-- =================================================
                     TANGGAL PINJAM
                ================================================== -->

                <div class="col-md-4">


                    <label class="form-label">

                        Tanggal Pinjam

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i
                                class="bi
                                bi-calendar-plus"
                            ></i>

                        </span>


                        <input
                            type="date"
                            name="tanggal_pinjam"
                            class="form-control"
                            required
                            value="<?= $edit && $dataEdit
                                ? e(
                                    $dataEdit[
                                        'tanggal_pinjam'
                                    ]
                                )
                                : date('Y-m-d')
                            ?>"
                        >


                    </div>

                </div>



                <!-- =================================================
                     TANGGAL KEMBALI
                ================================================== -->

                <div class="col-md-4">


                    <label class="form-label">

                        Tanggal Kembali

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i
                                class="bi
                                bi-calendar-check"
                            ></i>

                        </span>


                        <input
                            type="date"
                            name="tanggal_kembali"
                            class="form-control"
                            value="<?= $edit && $dataEdit
                                ? e(
                                    $dataEdit[
                                        'tanggal_kembali'
                                    ]
                                )
                                : ''
                            ?>"
                        >


                    </div>

                </div>



                <!-- =================================================
                     DENDA
                ================================================== -->

                <div class="col-md-4">


                    <label class="form-label">

                        Denda

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <strong>
                                Rp
                            </strong>

                        </span>


                        <input
                            type="number"
                            name="denda"
                            class="form-control"
                            min="0"
                            placeholder="0"
                            readonly
                            value="<?= $edit && $dataEdit
                                ? e(
                                    $dataEdit['denda']
                                )
                                : '0'
                            ?>"
                        >


                    </div>

                </div>


            </div>



            <!-- =================================================
                 BUTTON
            ================================================== -->

            <div class="mt-2">


                <?php if ($edit) { ?>


                    <button
                        type="submit"
                        name="update"
                        class="
                        btn-save
                        btn-update"
                    >

                        <i
                            class="
                            bi
                            bi-check-circle"
                        ></i>

                        Update Peminjaman

                    </button>


                    <a
                        href="peminjaman.php"
                        class="btn-cancel"
                    >

                        <i
                            class="
                            bi
                            bi-x-circle"
                        ></i>

                        Batal

                    </a>


                <?php } else { ?>


                    <button
                        type="submit"
                        name="simpan"
                        class="btn-save"
                    >

                        <i
                            class="
                            bi
                            bi-journal-plus"
                        ></i>

                        Simpan Peminjaman

                    </button>


                <?php } ?>


            </div>


        </form>


    </div>



    <!-- =================================================
         TABEL PEMINJAMAN
    ================================================== -->

    <div class="table-card">


        <div class="table-header">


            <h3 class="table-title">

                <i
                    class="
                    bi
                    bi-journal-text"
                ></i>

                Daftar Peminjaman

            </h3>


            <span class="badge-info">

                Data Peminjaman

            </span>


        </div>


        <div class="table-tools">

            <div class="search-box">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    id="searchPeminjaman"
                    placeholder="Cari anggota, buku, tanggal, denda..."
                    aria-label="Cari peminjaman"
                >

            </div>

        </div>



        <div class="table-responsive">


            <table id="tablePeminjaman">


                <thead>

                    <tr>

                        <th>
                            ID Peminjaman
                        </th>

                        <th>
                            Anggota
                        </th>

                        <th>
                            Buku
                        </th>

                        <th>
                            Tanggal Pinjam
                        </th>

                        <th>
                            Tanggal Kembali
                        </th>

                        <th>
                            Denda
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php

                    /*
                     * LEFT JOIN digunakan agar
                     * data peminjaman tetap muncul
                     * walaupun data anggota bermasalah.
                     */

                    $data = mysqli_query(
                        $conn,
                        "SELECT
                            peminjaman.*,

                            anggota.Nama AS nama_anggota,
                            buku.judul_buku

                         FROM peminjaman

                         LEFT JOIN anggota
                         ON
                            peminjaman.id_anggota
                            =
                            anggota.id_anggota

                         LEFT JOIN buku
                         ON
                            peminjaman.id_buku
                            =
                            buku.id_buku

                         ORDER BY
                            peminjaman.id_peminjaman ASC"
                    );


                    if (
                        mysqli_num_rows($data)
                        > 0
                    ) {


                        while (
                            $d =
                            mysqli_fetch_assoc($data)
                        ) {


                            $id =
                                e(
                                    $d[
                                        'id_peminjaman'
                                    ]
                                );


                            $idAnggota =
                                e(
                                    $d[
                                        'id_anggota'
                                    ]
                                );


                            $namaAnggota =
                                e(
                                    $d[
                                        'nama_anggota'
                                    ]
                                    ??
                                    'Anggota'
                                );

                            $judulBuku =
                                e(
                                    $d[
                                        'judul_buku'
                                    ]
                                    ??
                                    'Buku tidak tersedia'
                                );


                            $tanggalPinjam =
                                e(
                                    $d[
                                        'tanggal_pinjam'
                                    ]
                                );


                            $tanggalKembali =
                                e(
                                    $d[
                                        'tanggal_kembali'
                                    ]
                                    ??
                                    ''
                                );


                            $status = !empty($d['tanggal_kembali'])
                                ? 'Dikembalikan'
                                : 'Dipinjam';

                            $denda =
                                (int)(
                                    $d['denda']
                                    ?? 0
                                );

                    ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <span
                                    class="id-badge"
                                >

                                    <?= $id ?>

                                </span>

                            </td>



                            <!-- ANGGOTA -->

                            <td>

                                <div
                                    class="member-box"
                                >


                                    <span
                                        class="
                                        member-icon"
                                    >

                                        <i
                                            class="
                                            bi
                                            bi-person-fill"
                                        ></i>

                                    </span>


                                    <div>

                                        <div
                                            class="
                                            member-name"
                                        >

                                            <?= $namaAnggota ?>

                                        </div>


                                        <small
                                            class="text-muted"
                                        >

                                            ID:
                                            <?= $idAnggota ?>

                                        </small>

                                    </div>


                                </div>

                            </td>



                            <!-- BUKU -->

                            <td>

                                <div
                                    class="member-box"
                                >

                                    <span
                                        class="
                                        member-icon"
                                    >

                                        <i
                                            class="
                                            bi
                                            bi-book-half"
                                        ></i>

                                    </span>

                                    <div>

                                        <div
                                            class="
                                            member-name"
                                        >

                                            <?= $judulBuku ?>

                                        </div>

                                        <small
                                            class="text-muted"
                                        >

                                            ID Buku:
                                            <?= e(
                                                $d['id_buku'] ?? ''
                                            ); ?>

                                        </small>

                                    </div>

                                </div>

                            </td>



                            <!-- TANGGAL PINJAM -->

                            <td>

                                <span
                                    class="date-badge"
                                >

                                    <i
                                        class="
                                        bi
                                        bi-calendar3"
                                    ></i>

                                    <?= $tanggalPinjam ?>

                                </span>

                            </td>



                            <!-- TANGGAL KEMBALI -->

                            <td>


                                <?php if (
                                    !empty(
                                        $tanggalKembali
                                    )
                                ) { ?>


                                    <span
                                        class="date-badge"
                                    >

                                        <i
                                            class="
                                            bi
                                            bi-calendar-check"
                                        ></i>

                                        <?= $tanggalKembali ?>

                                    </span>


                                <?php } else { ?>


                                    <span
                                        class="text-muted"
                                    >

                                        Belum kembali

                                    </span>


                                <?php } ?>


                            </td>



                            <!-- DENDA -->

                            <td>


                                <?php if (
                                    $denda > 0
                                ) { ?>


                                    <span
                                        class="denda"
                                    >

                                        Rp
                                        <?= number_format(
                                            $denda,
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                    </span>


                                <?php } else { ?>


                                    <span
                                        class="
                                        denda-free"
                                    >

                                        Rp 0

                                    </span>


                                <?php } ?>


                            </td>



                            <!-- AKSI -->

                            <td>


                                <div
                                    class="
                                    action-buttons"
                                >


                                    <a
                                        href="
                                        peminjaman.php?edit=
                                        <?= $id ?>"
                                        class="
                                        btn-action
                                        btn-edit"
                                    >

                                        <i
                                            class="
                                            bi
                                            bi-pencil-fill"
                                        ></i>

                                        Edit

                                    </a>


                                    <a
                                        href="peminjaman.php?hapus=<?= $id ?>"
                                        class="btn-action btn-delete delete-row-link"
                                        data-delete-name="Peminjaman #<?= $id ?>"
                                        data-delete-url="peminjaman.php?hapus=<?= $id ?>"
                                    >

                                        <i
                                            class="
                                            bi
                                            bi-trash-fill"
                                        ></i>

                                        Hapus

                                    </a>


                                </div>


                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="8"
                                class="empty-data"
                            >

                                <i
                                    class="
                                    bi
                                    bi-journal-x"
                                ></i>

                                Belum ada data
                                peminjaman.

                            </td>

                        </tr>


                    <?php } ?>


                </tbody>


            </table>

        </div>

    </div>


</div>



<!-- =====================================================
     MODAL DELETE
====================================================== -->

<div
    class="modal fade"
    id="deleteModal"
    tabindex="-1"
    aria-hidden="true"
>


    <div
        class="
        modal-dialog
        modal-dialog-centered"
    >


        <div
            class="
            modal-content
            delete-modal"
        >


            <div
                class="
                modal-body
                text-center
                p-4"
            >


                <div class="delete-icon">

                    <i
                        class="
                        bi
                        bi-trash3-fill"
                    ></i>

                </div>


                <h5
                    class="fw-bold"
                    style="color:#432918;"
                >

                    Hapus Data Peminjaman?

                </h5>


                <p class="text-muted small">

                    Apakah kamu yakin ingin
                    menghapus:

                    <br>

                    <strong
                        id="loanName"
                        style="color:#6b4426;"
                    ></strong>

                    ?

                </p>


                <div
                    class="
                    d-flex
                    justify-content-center
                    gap-2"
                >


                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >

                        Batal

                    </button>


                    <a
                        href="#"
                        id="deleteLink"
                        class="btn btn-danger"
                    >

                        <i
                            class="
                            bi
                            bi-trash-fill
                            me-1"
                        ></i>

                        Hapus

                    </a>


                </div>


            </div>


        </div>

    </div>

</div>



<!-- =====================================================
     BOOTSTRAP JS
====================================================== -->

<script
    src="
    https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>

function hitungDendaOtomatis() {
    const pinjamEl = document.querySelector('input[name="tanggal_pinjam"]');
    const kembaliEl = document.querySelector('input[name="tanggal_kembali"]');
    const dendaInput = document.querySelector('input[name="denda"]');

    if (!pinjamEl || !kembaliEl || !dendaInput) {
        return;
    }

    const tanggalPinjam = pinjamEl.value;
    const tanggalKembali = kembaliEl.value;

    if (!tanggalPinjam || !tanggalKembali) {
        dendaInput.value = '0';
        return;
    }

    const tPinjam = new Date(tanggalPinjam + 'T00:00:00');
    const tKembali = new Date(tanggalKembali + 'T00:00:00');
    const batasWaktu = new Date(tPinjam);
    batasWaktu.setDate(batasWaktu.getDate() + 7);

    if (tKembali <= batasWaktu) {
        dendaInput.value = '0';
        return;
    }

    const diffMs = tKembali - batasWaktu;
    const terlambatHari = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
    const totalDenda = terlambatHari * 10000;

    dendaInput.value = totalDenda;
}

const pinjamElForm = document.querySelector('input[name="tanggal_pinjam"]');
const kembaliElForm = document.querySelector('input[name="tanggal_kembali"]');

document.addEventListener('DOMContentLoaded', function () {
    hitungDendaOtomatis();

    if (pinjamElForm) {
        pinjamElForm.addEventListener('change', hitungDendaOtomatis);
        pinjamElForm.addEventListener('input', hitungDendaOtomatis);
    }

    if (kembaliElForm) {
        kembaliElForm.addEventListener('change', hitungDendaOtomatis);
        kembaliElForm.addEventListener('input', hitungDendaOtomatis);
    }
});

/* =====================================================
   MODAL DELETE
===================================================== */

function confirmDelete(
    name,
    deleteUrl
) {
    document.getElementById(
        'loanName'
    ).textContent = name;

    const deleteLink = document.getElementById(
        'deleteLink'
    );

    deleteLink.href = deleteUrl;

    deleteLink.onclick = function (e) {
        e.preventDefault();
        window.location.href = deleteUrl;
    };

    const modal =
        new bootstrap.Modal(
            document.getElementById(
                'deleteModal'
            )
        );
    modal.show();
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-row-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const name = link.dataset.deleteName || 'Data';
            const url = link.dataset.deleteUrl || link.href;
            confirmDelete(name, url);
        });
    });
});

function filterTableBySearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);

    if (!input || !table) {
        return;
    }

    input.addEventListener('keyup', function () {
        const keyword = input.value.toLowerCase().trim();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(function (row) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
        });
    });
}

function refreshParentRecap()
{
    if (
        window.top !== window.self
    ) {
        try {
            window.parent.postMessage(
                {
                    type:
                        'recap-refresh'
                },
                '*'
            );
        } catch (e) {
            // silent
        }
    }
}
window.addEventListener(
    'DOMContentLoaded',
    function ()
    {
        filterTableBySearch('searchPeminjaman', 'tablePeminjaman');
        refreshParentRecap();
    }
);
</script>
</body>
</html>