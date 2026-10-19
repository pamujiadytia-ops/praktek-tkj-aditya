<?php
include "koneksi.php";


/* =========================
   SIMPAN DATA ANGGOTA
========================= */

if (isset($_POST['simpan'])) {

    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];

    $tanggal_daftar = !empty($_POST['tanggal_daftar'])
        ? $_POST['tanggal_daftar']
        : date('Y-m-d');


    mysqli_query($conn, "INSERT INTO anggota
        (nama, alamat, no_telp, tanggal_daftar)
        VALUES
        ('$nama', '$alamat', '$no_telp', '$tanggal_daftar')
    ");


    header("Location: anggota.php");
    exit;
}


/* =========================
   HAPUS DATA
========================= */

if (isset($_GET['hapus'])) {

    $id = filter_var($_GET['hapus'] ?? '', FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        $error = "ID anggota tidak valid.";

    } else {
        $cek = mysqli_query(
            $conn,
            "SELECT id_anggota FROM anggota WHERE id_anggota='$id' LIMIT 1"
        );

        if (mysqli_num_rows($cek) === 0) {
            $error = "Data anggota tidak ditemukan.";

        } else {
            $query = mysqli_query(
                $conn,
                "DELETE FROM anggota
                 WHERE id_anggota='$id'"
            );

            if ($query) {
                header("Location: anggota.php");
                exit;
            } else {
                $error = "Gagal menghapus data anggota: "
                    . mysqli_error($conn);
            }
        }
    }

    if (isset($error)) {
        header("Location: anggota.php?error=" . urlencode($error));
        exit;
    }
}


/* =========================
   AMBIL DATA UNTUK EDIT
========================= */

$edit = false;

if (isset($_GET['edit'])) {

    $edit = true;

    $id = $_GET['edit'];

    $query = mysqli_query(
        $conn,
        "SELECT * FROM anggota
         WHERE id_anggota='$id'"
    );

    $dataEdit = mysqli_fetch_assoc($query);
}


/* =========================
   UPDATE DATA
========================= */

if (isset($_POST['update'])) {

    $id = $_POST['id_anggota'];

    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];

    $tanggal_daftar = !empty($_POST['tanggal_daftar'])
        ? $_POST['tanggal_daftar']
        : date('Y-m-d');


    mysqli_query($conn, "UPDATE anggota SET

        nama='$nama',
        alamat='$alamat',
        no_telp='$no_telp',
        tanggal_daftar='$tanggal_daftar'

        WHERE id_anggota='$id'
    ");


    header("Location: anggota.php");
    exit;
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
        Data Anggota | Perpustakaan
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

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


        /* =====================================
           CONTAINER
        ===================================== */

        .page-container {

            width: 100%;

            max-width: 1250px;

            margin: auto;

            animation:
                pageIn .6s ease;
        }


        /* =====================================
           HEADER
        ===================================== */

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
                rgba(67,41,24,.06);
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


        /* =====================================
           FORM CARD
        ===================================== */

        .form-card {

            padding: 25px;

            margin-bottom: 25px;

            border-radius: 18px;

            background: white;

            border:
                1px solid #eee4d8;

            box-shadow:
                0 6px 25px
                rgba(67,41,24,.06);

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


        /* =====================================
           INPUT
        ===================================== */

        .form-label {

            color: #5c422f;

            font-size: 13px;

            font-weight: 600;
        }


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
                rgba(107,68,38,.12) !important;
        }


        /* =====================================
           BUTTON
        ===================================== */

        .btn-save {

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
                rgba(107,68,38,.22);
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


        /* =====================================
           TABLE CARD
        ===================================== */

        .table-card {

            overflow: hidden;

            border-radius: 18px;

            background: white;

            border:
                1px solid #eee4d8;

            box-shadow:
                0 6px 25px
                rgba(67,41,24,.06);

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


        /* =====================================
           TABLE
        ===================================== */

        .table-responsive {

            overflow-x: auto;
        }


        table {

            width: 100%;

            margin: 0;

            border-collapse: collapse;

            min-width: 850px;

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


        /* =====================================
           ID
        ===================================== */

        .id-badge {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 32px;

            padding: 5px 8px;

            border-radius: 7px;

            background: #f4e2c6;

            color: #6b4426;

            font-weight: 700;

            font-size: 11px;
        }


        /* =====================================
           MEMBER ICON
        ===================================== */

        .member-name {

            display: flex;

            align-items: center;

            gap: 9px;

            color: #513522;

            font-weight: 600;
        }


        .member-avatar {

            width: 34px;

            height: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            border-radius: 50%;

            background: #f4e2c6;

            color: #6b4426;

            font-size: 14px;
        }


        /* =====================================
           PHONE
        ===================================== */

        .phone-number {

            display: inline-flex;

            align-items: center;

            gap: 6px;
        }


        .phone-number i {

            color: #8a5a34;
        }


        /* =====================================
           DATE
        ===================================== */

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


        /* =====================================
           ACTION
        ===================================== */

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


        /* =====================================
           EMPTY DATA
        ===================================== */

        .empty-data {

            padding: 45px 20px;

            text-align: center;

            color: #9b8b7b;
        }


        .empty-data i {

            display: block;

            margin-bottom: 10px;

            color: #c5b5a5;

            font-size: 45px;
        }


        /* =====================================
           MODAL
        ===================================== */

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


        /* =====================================
           ANIMATION
        ===================================== */

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


        /* =====================================
           RESPONSIVE
        ===================================== */

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

        }

    </style>

</head>


<body>


<div class="page-container">


    <!-- =================================
         HEADER
    ================================== -->

    <div class="page-header">


        <div class="header-title">


            <div class="header-icon">

                <i class="bi bi-people-fill"></i>

            </div>


            <div>

                <h2>

                    Data Anggota

                </h2>


                <p>

                    Kelola data anggota
                    perpustakaan

                </p>

            </div>


        </div>


    </div>



    <!-- =================================
         FORM
    ================================== -->

    <div class="form-card">


        <div class="form-title">

            <i class="bi
                <?= $edit
                    ? 'bi-pencil-square'
                    : 'bi-person-plus-fill'
                ?>">
            </i>


            <?= $edit
                ? 'Edit Data Anggota'
                : 'Tambah Anggota Baru'
            ?>

        </div>



        <form method="post">


            <?php if ($edit) { ?>

                <input
                    type="hidden"
                    name="id_anggota"
                    value="<?= htmlspecialchars(
                        $dataEdit['id_anggota'] ?? ''
                    ); ?>"
                >

            <?php } ?>


            <div class="row">


                <!-- NAMA -->

                <div class="col-md-6">

                    <label class="form-label">

                        Nama Anggota

                    </label>


                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-person"></i>

                        </span>


                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Masukkan nama anggota"
                            required
                            value="<?= $edit
                                ? htmlspecialchars(
                                    $dataEdit['Nama'] ?? ''
                                )
                                : ''
                            ?>"
                        >

                    </div>

                </div>



                <!-- NO TELP -->

                <div class="col-md-6">

                    <label class="form-label">

                        Nomor Telepon

                    </label>


                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-telephone"></i>

                        </span>


                        <input
                            type="text"
                            name="no_telp"
                            class="form-control"
                            placeholder="Contoh: 081234567890"
                            required
                            value="<?= $edit
                                ? htmlspecialchars(
                                    $dataEdit['No_Telp'] ?? ''
                                )
                                : ''
                            ?>"
                        >

                    </div>

                </div>



                <!-- ALAMAT -->

                <div class="col-md-8">

                    <label class="form-label">

                        Alamat

                    </label>


                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-geo-alt"></i>

                        </span>


                        <input
                            type="text"
                            name="alamat"
                            class="form-control"
                            placeholder="Masukkan alamat lengkap"
                            required
                            value="<?= $edit
                                ? htmlspecialchars(
                                    $dataEdit['Alamat'] ?? ''
                                )
                                : ''
                            ?>"
                        >

                    </div>

                </div>



                <!-- TANGGAL -->

                <div class="col-md-4">

                    <label class="form-label">

                        Tanggal Daftar

                    </label>


                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-calendar3"></i>

                        </span>


                        <input
                            type="date"
                            name="tanggal_daftar"
                            class="form-control"
                            required
                            value="<?= $edit
                                ? htmlspecialchars(
                                    $dataEdit['Tanggal_daftar']
                                    ?? date('Y-m-d')
                                )
                                : date('Y-m-d')
                            ?>"
                        >

                    </div>

                </div>


            </div>



            <!-- BUTTON -->

            <div class="mt-2">


                <?php if ($edit) { ?>


                    <button
                        type="submit"
                        name="update"
                        class="btn-save btn-update"
                    >

                        <i
                            class="bi
                            bi-check-circle me-1"
                        ></i>

                        Update Data

                    </button>


                    <a
                        href="anggota.php"
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
                            bi-person-plus me-1"
                        ></i>

                        Simpan Anggota

                    </button>


                <?php } ?>


            </div>


        </form>

    </div>



    <!-- =================================
         TABLE
    ================================== -->

    <div class="table-card">


        <div class="table-header">


            <h3 class="table-title">

                <i
                    class="bi
                    bi-people-fill"
                ></i>

                Daftar Anggota

            </h3>


            <span class="badge-info">

                Data Anggota

            </span>


        </div>


        <div class="table-tools">

            <div class="search-box">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    id="searchAnggota"
                    placeholder="Cari nama, alamat, telepon..."
                    aria-label="Cari anggota"
                >

            </div>

        </div>



        <div class="table-responsive">


            <table id="tableAnggota">


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Nama
                        </th>

                        <th>
                            Alamat
                        </th>

                        <th>
                            No. Telepon
                        </th>

                        <th>
                            Tanggal Daftar
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php

                    $data =
                        mysqli_query(
                            $conn,
                            "SELECT * FROM anggota"
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
                                htmlspecialchars(
                                    $d['id_anggota']
                                    ?? ''
                                );


                            $nama =
                                htmlspecialchars(
                                    $d['Nama']
                                    ?? ''
                                );


                            $alamat =
                                htmlspecialchars(
                                    $d['Alamat']
                                    ?? ''
                                );


                            $no_telp =
                                htmlspecialchars(
                                    $d['No_Telp']
                                    ?? ''
                                );


                            $tanggal =
                                htmlspecialchars(
                                    $d['Tanggal_daftar']
                                    ?? ''
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



                            <!-- NAMA -->

                            <td>

                                <div
                                    class="member-name"
                                >


                                    <span
                                        class="member-avatar"
                                    >

                                        <i
                                            class="bi
                                            bi-person-fill"
                                        ></i>

                                    </span>


                                    <span>

                                        <?= $nama ?>

                                    </span>


                                </div>

                            </td>



                            <!-- ALAMAT -->

                            <td>

                                <span>

                                    <?= $alamat ?>

                                </span>

                            </td>



                            <!-- TELEPON -->

                            <td>

                                <span
                                    class="phone-number"
                                >

                                    <i
                                        class="bi
                                        bi-telephone-fill"
                                    ></i>

                                    <?= $no_telp ?>

                                </span>

                            </td>



                            <!-- TANGGAL -->

                            <td>

                                <span
                                    class="date-badge"
                                >

                                    <i
                                        class="bi
                                        bi-calendar3"
                                    ></i>

                                    <?= $tanggal ?>

                                </span>

                            </td>



                            <!-- AKSI -->

                            <td>


                                <div
                                    class="action-buttons"
                                >


                                    <a
                                        href="anggota.php?edit=<?= $id ?>"
                                        class="btn-action btn-edit"
                                    >

                                        <i
                                            class="bi
                                            bi-pencil-fill"
                                        ></i>

                                        Edit

                                    </a>


                                    <a
                                        href="anggota.php?hapus=<?= $id ?>"
                                        class="btn-action btn-delete"
                                        onclick="
                                            return confirmDelete(
                                                '<?= htmlspecialchars(
                                                    $nama,
                                                    ENT_QUOTES
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
                                colspan="6"
                                class="empty-data"
                            >

                                <i
                                    class="bi
                                    bi-people"
                                ></i>

                                Belum ada data anggota.

                            </td>

                        </tr>


                    <?php } ?>


                </tbody>


            </table>

        </div>

    </div>


</div>



<!-- =================================
     MODAL DELETE
================================== -->

<div
    class="modal fade"
    id="deleteModal"
    tabindex="-1"
>


    <div
        class="modal-dialog modal-dialog-centered"
    >


        <div
            class="modal-content delete-modal"
        >


            <div
                class="modal-body text-center p-4"
            >


                <div class="delete-icon">

                    <i
                        class="bi
                        bi-trash3-fill"
                    ></i>

                </div>


                <h5
                    class="fw-bold"
                    style="color:#432918;"
                >

                    Hapus Data Anggota?

                </h5>


                <p class="text-muted small">

                    Apakah kamu yakin ingin
                    menghapus anggota:

                    <br>

                    <strong
                        id="memberName"
                        style="color:#6b4426;"
                    ></strong>

                    ?

                </p>


                <div
                    class="d-flex
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
                            class="bi
                            bi-trash-fill me-1"
                        ></i>

                        Hapus

                    </a>


                </div>


            </div>


        </div>

    </div>

</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>



<script>

/* =====================================
   MODAL DELETE
===================================== */

function confirmDelete(memberName, deleteUrl) {

    const modal =
        new bootstrap.Modal(
            document.getElementById(
                'deleteModal'
            )
        );


    document.getElementById(
        'memberName'
    ).textContent = memberName;


    document.getElementById(
        'deleteLink'
    ).href = deleteUrl;


    modal.show();


    return false;
}



/* =====================================
   REFRESH RECAP DASHBOARD
===================================== */

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

if (
    window.top !== window.self
) {

    window.addEventListener(
        'DOMContentLoaded',
        function () {

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

            filterTableBySearch('searchAnggota', 'tableAnggota');

        }
    );

} else {
    window.addEventListener('DOMContentLoaded', function () {
        filterTableBySearch('searchAnggota', 'tableAnggota');
    });
}

</script>


</body>

</html>