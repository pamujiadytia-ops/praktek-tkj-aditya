<?php
include "koneksi.php";

/* =====================================================
   FUNGSI ESCAPE
===================================================== */

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


/* =====================================================
   SIMPAN DATA BUKU
===================================================== */

if (isset($_POST['simpan'])) {

    $judul = mysqli_real_escape_string(
        $conn,
        $_POST['judul_buku']
    );

    $pengarang = mysqli_real_escape_string(
        $conn,
        $_POST['pengarang']
    );

    $penerbit = mysqli_real_escape_string(
        $conn,
        $_POST['penerbit']
    );

    $tahun_terbit = mysqli_real_escape_string(
        $conn,
        $_POST['tahun_terbit']
    );

    $stok = mysqli_real_escape_string(
        $conn,
        $_POST['stok']
    );


    $query = mysqli_query(
        $conn,
        "INSERT INTO buku
        (
            judul_buku,
            pengarang,
            penerbit,
            tahun_terbit,
            stok
        )
        VALUES
        (
            '$judul',
            '$pengarang',
            '$penerbit',
            '$tahun_terbit',
            '$stok'
        )"
    );


    if ($query) {

        header("Location: buku.php");
        exit;

    } else {

        $error = "Gagal menyimpan data buku: "
            . mysqli_error($conn);
    }
}


/* =====================================================
   HAPUS DATA BUKU
===================================================== */

if (isset($_GET['hapus'])) {

    $id = filter_var($_GET['hapus'] ?? '', FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $error = "ID buku tidak valid.";

    } else {
        $cek = mysqli_query(
            $conn,
            "SELECT id_buku FROM buku WHERE id_buku='$id' LIMIT 1"
        );

        if (mysqli_num_rows($cek) === 0) {
            $error = "Data buku tidak ditemukan.";

        } else {
            $query = mysqli_query(
                $conn,
                "DELETE FROM buku
                 WHERE id_buku='$id'"
            );

            if ($query) {
                header("Location: buku.php");
                exit;
            } else {
                $error = "Gagal menghapus data buku: "
                    . mysqli_error($conn);
            }
        }
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
         FROM buku
         WHERE id_buku='$id'"
    );


    if (mysqli_num_rows($query) > 0) {

        $dataEdit = mysqli_fetch_assoc($query);

    } else {

        $edit = false;
    }
}


/* =====================================================
   UPDATE DATA BUKU
===================================================== */

if (isset($_POST['update'])) {

    $id = mysqli_real_escape_string(
        $conn,
        $_POST['id_buku']
    );

    $judul = mysqli_real_escape_string(
        $conn,
        $_POST['judul_buku']
    );

    $pengarang = mysqli_real_escape_string(
        $conn,
        $_POST['pengarang']
    );

    $penerbit = mysqli_real_escape_string(
        $conn,
        $_POST['penerbit']
    );

    $tahun_terbit = mysqli_real_escape_string(
        $conn,
        $_POST['tahun_terbit']
    );

    $stok = mysqli_real_escape_string(
        $conn,
        $_POST['stok']
    );
    $query = mysqli_query(
        $conn,
        "UPDATE buku SET judul_buku='$judul',pengarang='$pengarang', penerbit='$penerbit',tahun_terbit='$tahun_terbit',stok='$stok' WHERE id_buku='$id'");


    if ($query) {

        header("Location: buku.php");
        exit;

    } else {

        $error = "Gagal mengupdate data buku: "
            . mysqli_error($conn);
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
        Data Buku | Perpustakaan
    </title>


    <!-- =================================================
         BOOTSTRAP
    ================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =================================================
         BOOTSTRAP ICONS
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
           PAGE
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
        ================================================= */

        .error-box {

            margin-bottom: 20px;

            padding: 13px 16px;

            border-radius: 10px;

            background: #fce8e9;

            color: #b02a37;

            font-size: 13px;
        }


        /* =================================================
           FORM CARD
        ================================================= */

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


        /* =================================================
           LABEL
        ================================================= */

        .form-label {

            color: #5c422f;

            font-size: 13px;

            font-weight: 600;
        }


        /* =================================================
           INPUT
        ================================================= */

        .input-group {

            margin-bottom: 16px;
        }


        .input-group-text {

            border: none;

            background: #f1e9df;

            color: #6b4426;
        }


        .form-control {

            border: none;

            background: #f1e9df;

            padding: 11px 13px;

            color: #432918;

            box-shadow: none !important;

            font-size: 13px;
        }


        .form-control:focus {

            background: #e9ded0;

            box-shadow:
                0 0 0 2px
                rgba(107, 68, 38, .12)
                !important;
        }


        /* =================================================
           BUTTON SIMPAN
        ================================================= */

        .btn-save {

            display: inline-flex;

            align-items: center;

            gap: 5px;

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
        ================================================= */

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

            width: min(420px, 100%);

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
        ================================================= */

        .table-responsive {

            overflow-x: auto;
        }


        table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            min-width: 900px;
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

            transition:
                background .2s ease;
        }


        tbody tr:hover {

            background: #fcf8f2;
        }


        tbody tr:last-child td {

            border-bottom: none;
        }


        /* =================================================
           ID BADGE
        ================================================= */

        .id-badge {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 34px;

            padding: 5px 8px;

            border-radius: 7px;

            background: #f4e2c6;

            color: #6b4426;

            font-weight: 700;

            font-size: 11px;
        }


        /* =================================================
           BOOK TITLE
        ================================================= */

        .book-title {

            display: flex;

            align-items: center;

            gap: 9px;

            color: #513522;

            font-weight: 600;
        }


        .book-icon {

            width: 34px;

            height: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            border-radius: 8px;

            background: #f4e2c6;

            color: #6b4426;

            font-size: 14px;
        }


        /* =================================================
           STOCK
        ================================================= */

        .stock-badge {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 38px;

            padding: 6px 9px;

            border-radius: 7px;

            background: #e8f5ee;

            color: #198754;

            font-size: 11px;

            font-weight: 700;
        }


        .stock-empty {

            background: #fce8e9;

            color: #dc3545;
        }

        .stock-low {
            background: #fff0f0;
            color: #d63384;
            border: 1px solid rgba(214, 51, 132, 0.2);
        }


        /* =================================================
           YEAR
        ================================================= */

        .year-badge {

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
           ACTION
        ================================================= */

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
           EMPTY DATA
        ================================================= */

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
           DELETE MODAL
        ================================================= */

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
        ================================================= */

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
        ================================================= */

        @media (max-width: 700px) {

            body {

                padding: 15px;
            }


            .page-header {

                padding: 16px;
            }


            .header-title h2 {

                font-size: 18px;
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

                font-size: 16px;
            }


            .header-title p {

                font-size: 9px;
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

                <i class="bi bi-book-half"></i>

            </div>


            <div>

                <h2>
                    Data Buku
                </h2>

                <p>
                    Kelola data buku perpustakaan
                </p>

            </div>


        </div>


    </div>



    <!-- =================================================
         ERROR
    ================================================== -->

    <?php if (isset($error)) { ?>

        <div class="error-box">

            <i class="bi bi-exclamation-circle me-1"></i>

            <?= e($error); ?>

        </div>

    <?php } ?>



    <!-- =================================================
         FORM BUKU
    ================================================== -->

    <div class="form-card">


        <div class="form-title">

            <i class="bi
                <?= $edit
                    ? 'bi-pencil-square'
                    : 'bi-bookmark-plus-fill'
                ?>">
            </i>


            <?= $edit
                ? 'Edit Data Buku'
                : 'Tambah Buku Baru'
            ?>

        </div>



        <form method="post">


            <?php if ($edit && $dataEdit) { ?>

                <input
                    type="hidden"
                    name="id_buku"
                    value="<?= e(
                        $dataEdit['id_buku']
                    ); ?>"
                >

            <?php } ?>


            <div class="row">


                <!-- =================================================
                     JUDUL
                ================================================== -->

                <div class="col-md-6">


                    <label class="form-label">

                        Judul Buku

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i class="bi bi-book"></i>

                        </span>


                        <input
                            type="text"
                            name="judul_buku"
                            class="form-control"
                            placeholder="Masukkan judul buku"
                            required
                            value="<?= $edit && $dataEdit
                                ? e(
                                    $dataEdit['judul_buku']
                                )
                                : ''
                            ?>"
                        >


                    </div>

                </div>



                <!-- =================================================
                     PENGARANG
                ================================================== -->

                <div class="col-md-6">


                    <label class="form-label">

                        Pengarang

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i class="bi bi-person"></i>

                        </span>


                        <input
                            type="text"
                            name="pengarang"
                            class="form-control"
                            placeholder="Masukkan nama pengarang"
                            required
                            value="<?= $edit && $dataEdit
                                ? e(
                                    $dataEdit['pengarang']
                                )
                                : ''
                            ?>"
                        >


                    </div>

                </div>



                <!-- =================================================
                     PENERBIT
                ================================================== -->

                <div class="col-md-5">


                    <label class="form-label">

                        Penerbit

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i class="bi bi-building"></i>

                        </span>


                        <input
                            type="text"
                            name="penerbit"
                            class="form-control"
                            placeholder="Masukkan penerbit"
                            required
                            value="<?= $edit && $dataEdit
                                ? e(
                                    $dataEdit['penerbit']
                                )
                                : ''
                            ?>"
                        >


                    </div>

                </div>



                <!-- =================================================
                     TAHUN
                ================================================== -->

                <div class="col-md-3">


                    <label class="form-label">

                        Tahun Terbit

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i class="bi bi-calendar3"></i>

                        </span>


                        <input
                            type="number"
                            name="tahun_terbit"
                            class="form-control"
                            placeholder="2026"
                            min="1000"
                            max="9999"
                            required
                            value="<?= $edit && $dataEdit
                                ? e(
                                    $dataEdit['tahun_terbit']
                                )
                                : ''
                            ?>"
                        >


                    </div>

                </div>



                <!-- =================================================
                     STOK
                ================================================== -->

                <div class="col-md-4">


                    <label class="form-label">

                        Stok

                    </label>


                    <div class="input-group">


                        <span class="input-group-text">

                            <i class="bi bi-box-seam"></i>

                        </span>


                        <input
                            type="number"
                            name="stok"
                            class="form-control"
                            placeholder="Jumlah stok"
                            min="0"
                            required
                            value="<?= $edit && $dataEdit
                                ? e(
                                    $dataEdit['stok']
                                )
                                : ''
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
                        class="btn-save btn-update"
                    >

                        <i
                            class="bi
                            bi-check-circle"
                        ></i>

                        Update Data

                    </button>


                    <a
                        href="buku.php"
                        class="btn-cancel"
                    >

                        <i
                            class="bi
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
                            class="bi
                            bi-bookmark-plus"
                        ></i>

                        Simpan Buku

                    </button>


                <?php } ?>


            </div>


        </form>


    </div>



    <!-- =================================================
         TABEL DATA BUKU
    ================================================== -->

    <div class="table-card">


        <div class="table-header">


            <h3 class="table-title">

                <i class="bi bi-bookshelf"></i>

                Daftar Buku

            </h3>


            <span class="badge-info">

                Data Buku

            </span>


        </div>


        <div class="table-tools">

            <div class="search-box">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    id="searchBuku"
                    placeholder="Cari judul, pengarang, penerbit..."
                    aria-label="Cari buku"
                >

            </div>

        </div>



        <div class="table-responsive">


            <table id="tableBuku">


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Judul
                        </th>

                        <th>
                            Pengarang
                        </th>

                        <th>
                            Penerbit
                        </th>

                        <th>
                            Tahun Terbit
                        </th>

                        <th>
                            Stok
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php

                    $data = mysqli_query(
                        $conn,
                        "SELECT *
                         FROM buku
                         ORDER BY id_buku ASC"
                    );


                    if (
                        mysqli_num_rows($data)
                        > 0
                    ) {


                        while (
                            $d =
                            mysqli_fetch_assoc($data)
                        ) {


                            $id = e(
                                $d['id_buku']
                            );

                            $judul = e(
                                $d['judul_buku']
                            );

                            $pengarang = e(
                                $d['pengarang']
                            );

                            $penerbit = e(
                                $d['penerbit']
                            );

                            $tahun = e(
                                $d['tahun_terbit']
                            );

                            $stok = e(
                                $d['stok']
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



                            <!-- JUDUL -->

                            <td>

                                <div
                                    class="book-title"
                                >


                                    <span
                                        class="book-icon"
                                    >

                                        <i
                                            class="bi
                                            bi-book-fill"
                                        ></i>

                                    </span>


                                    <span>

                                        <?= $judul ?>

                                    </span>


                                </div>

                            </td>



                            <!-- PENGARANG -->

                            <td>

                                <?= $pengarang ?>

                            </td>



                            <!-- PENERBIT -->

                            <td>

                                <?= $penerbit ?>

                            </td>



                            <!-- TAHUN -->

                            <td>

                                <span
                                    class="year-badge"
                                >

                                    <i
                                        class="bi
                                        bi-calendar3"
                                    ></i>

                                    <?= $tahun ?>

                                </span>

                            </td>



                            <!-- STOK -->

                            <td>


                                <?php if ((int)$stok <= 0) { ?>


                                    <span
                                        class="
                                        stock-badge
                                        stock-empty"
                                    >

                                        <i
                                            class="bi
                                            bi-x-circle me-1"
                                        ></i>

                                        Habis

                                    </span>


                                <?php } elseif ((int)$stok < 5) { ?>


                                    <span
                                        class="
                                        stock-badge
                                        stock-low"
                                    >

                                        <i
                                            class="bi
                                            bi-exclamation-triangle me-1"
                                        ></i>

                                        <?= $stok ?>

                                    </span>


                                <?php } else { ?>


                                    <span
                                        class="stock-badge"
                                    >

                                        <i
                                            class="bi
                                            bi-box-seam me-1"
                                        ></i>

                                        <?= $stok ?>

                                    </span>


                                <?php } ?>


                            </td>



                            <!-- AKSI -->

                            <td>


                                <div
                                    class="action-buttons"
                                >


                                    <a
                                        href="buku.php?edit=<?= $id ?>"
                                        class="
                                        btn-action
                                        btn-edit"
                                    >

                                        <i
                                            class="bi
                                            bi-pencil-fill"
                                        ></i>

                                        Edit

                                    </a>


                                    <a
                                        href="buku.php?hapus=<?= $id ?>"
                                        class="
                                        btn-action
                                        btn-delete"
                                        onclick="
                                            event.preventDefault();
                                            return confirmDelete(
                                                '<?= e(
                                                    $judul
                                                ) ?>',
                                                this.href
                                            );
                                        "
                                    >

                                        <i
                                            class="bi
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
                                colspan="7"
                                class="empty-data"
                            >

                                <i
                                    class="
                                    bi
                                    bi-book"
                                ></i>

                                Belum ada data buku.

                            </td>

                        </tr>


                    <?php } ?>


                </tbody>


            </table>

        </div>

    </div>


</div>



<!-- =====================================================
     MODAL KONFIRMASI DELETE
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

                    Hapus Data Buku?

                </h5>


                <p class="text-muted small">

                    Apakah kamu yakin ingin
                    menghapus buku:

                    <br>

                    <strong
                        id="bookName"
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

/* =====================================================
   MODAL DELETE
===================================================== */

function confirmDelete(bookName, deleteUrl)
{
    const isConfirmed = window.confirm(
        'Apakah Anda yakin ingin menghapus buku "' +
        bookName +
        '"?'
    );

    if (!isConfirmed) {
        return false;
    }

    document.getElementById(
        'bookName'
    ).textContent = bookName;

    document.getElementById(
        'deleteLink'
    ).href = deleteUrl;

    const modal =
        new bootstrap.Modal(
            document.getElementById(
                'deleteModal'
            )
        );

    modal.show();

    return false;
}



/* =====================================================
   REFRESH RECAP DASHBOARD
===================================================== */

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


window.addEventListener(
    'DOMContentLoaded',
    function ()
    {

        filterTableBySearch('searchBuku', 'tableBuku');
        refreshParentRecap();

    }
);

</script>


</body>

</html>