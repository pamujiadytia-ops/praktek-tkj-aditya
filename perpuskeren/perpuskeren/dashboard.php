<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$namaPetugas = $_SESSION['nama_petugas'] ?? 'Petugas';

if (isset($_SESSION['id_petugas'])) {
    $idPetugas = (int) $_SESSION['id_petugas'];
    $queryPetugas = mysqli_query(
        $conn,
        "SELECT nama FROM petugas WHERE idp = '$idPetugas' LIMIT 1"
    );

    if ($queryPetugas && mysqli_num_rows($queryPetugas) > 0) {
        $dataPetugas = mysqli_fetch_assoc($queryPetugas);
        $namaPetugas = $dataPetugas['nama'] ?? $namaPetugas;
    }
} elseif (isset($_SESSION['username'])) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);
    $queryPetugas = mysqli_query(
        $conn,
        "SELECT nama FROM petugas WHERE username = '$username' LIMIT 1"
    );

    if ($queryPetugas && mysqli_num_rows($queryPetugas) > 0) {
        $dataPetugas = mysqli_fetch_assoc($queryPetugas);
        $namaPetugas = $dataPetugas['nama'] ?? $namaPetugas;
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
    <title>Dashboard - Perpustakaan</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> 
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family:
                'Segoe UI',
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;
            background: #f6f1e9;
            color: #432918;
            overflow-x: hidden;
        }


        /* ======================================
           LAYOUT
        ====================================== */
        .layout {
            display: flex;
            min-height: 100vh;
        }


        /* ======================================
           SIDEBAR
        ====================================== */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 270px;
            height: 100vh;
            padding: 25px 18px;
            display: flex:
            flex-direction: column;
            background:
                linear-gradient(
                    180deg,
                    #4b2d19,
                    #6b4426
                );

            box-shadow:
                5px 0 30px
                rgba(60, 35, 18, .15);
            z-index: 1000;
            animation:
                sidebarIn .7s ease;
        }


        /* ======================================
           BRAND
        ====================================== */

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-size: 21px;
            font-weight: 700;
            padding: 8px 10px 22px;
            margin-bottom: 25px;
            border-bottom:
                1px solid
                rgba(255,255,255,.15);
        }


        .brand-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background:
                rgba(255,255,255,.12);
            font-size: 23px;
        }


        .brand-text small {
            display: block;
            margin-top: 2px;
            color: #d9c5af;
            font-size: 10px;
            font-weight: 400;
        }

        /* ======================================
           NAV
        ====================================== */

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 13px 15px;
            border-radius: 12px;
            text-decoration: none;
            color: #eaded2;
            font-size: 14px;
            font-weight: 500;
            transition:
                all .25s ease;
        }

        .nav-link i {
            width: 22px;
            font-size: 18px;
            text-align: center;
        }


        .nav-link:hover {
            color: white;
            background:
                rgba(255,255,255,.10);
            transform:
                translateX(4px);
        }

        .nav-link.active {
            color: #4b2d19;
            background: #f0cc8a;
            font-weight: 700;
            box-shadow:
                0 8px 20px
                rgba(0,0,0,.12);
        }


        .nav-link.active i {
            color: #4b2d19;
        }

        /* ======================================
           SIDEBAR FOOTER
        ====================================== */
        .sidebar-footer {
            margin-top: auto;
        }

        .library-info {
            padding: 13px;
            margin-bottom: 12px;
            border-radius: 12px;
            background:
                rgba(255,255,255,.08);
            color: #dfcfc0;
            font-size: 11px;
            line-height: 1.5;
        }

        .library-info i {
            color: #f0cc8a;
            margin-right: 5px;
        }

        .logout-button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            background:
                rgba(255,255,255,.10);
            color: white;
            font-weight: 600;
            transition: .25s;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #dc3545;
            transform:
                translateY(-2px);
            box-shadow:
                0 8px 20px
                rgba(220,53,69,.25);
        }

        /* ======================================
           CONTENT
        ====================================== */

        .content {
            width:
                calc(100% - 270px);
            margin-left: 270px;
            padding: 30px;
            min-height: 100vh;
            animation:
                contentIn .7s ease;
        }

        /* ======================================
           TOPBAR
        ====================================== */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            margin-bottom: 25px;
            background: white;
            border:
                1px solid #eee4d8;
            border-radius: 18px;
            box-shadow:
                0 5px 25px
                rgba(67,41,24,.06);
        }

        .page-title {
            margin: 0;
            color: #432918;
            font-size: 24px;
            font-weight: 800;
        }

        .page-title i {
            margin-right: 8px;
            color: #6b4426;
        }

        .page-subtitle {
            margin: 4px 0 0;
            color: #978777;
            font-size: 12px;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 13px;
            border-radius: 12px;
            background: #f7f1e8;
            color: #5d432e;
            font-size: 13px;
            font-weight: 600;
        }

        .user-badge i {
            font-size: 22px;
            color: #6b4426;
        }

        /* ======================================
           WELCOME BANNER
        ====================================== */
        .welcome {
            position: relative;
            overflow: hidden;
            padding: 28px 30px;
            margin-bottom: 25px;
            border-radius: 20px;
            color: white;
            background:
                linear-gradient(
                    135deg,
                    #6b4426,
                    #8a5a34
                );
            box-shadow:
                0 12px 30px
                rgba(107,68,38,.20);
        }

        .welcome::before {
            content: "";
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            right: -60px;
            top: -80px;
            background:
                rgba(255,255,255,.08);
        }

        .welcome::after {
            content: "📚";
            position: absolute;
            right: 45px;
            bottom: 15px;
            font-size: 75px;
            opacity: .18;
            transform:
                rotate(-10deg);
        }

        .welcome h3 {
            position: relative;
            z-index: 2;
            margin: 0 0 7px;
            font-size: 21px;
            font-weight: 700;
        }

        .welcome p {
            position: relative;
            z-index: 2;
            margin: 0;
            color: #eadfd3;
            font-size: 13px;
        }

        /* ======================================
           RECAP CARDS
        ===================================== */
        .section-title {
            margin-bottom: 14px;
            color: #513522;
            font-size: 15px;
            font-weight: 700;
            }

        .recap {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .card {
            position: relative;
            overflow: hidden;
            min-height: 145px;
            padding: 24px;
            border: 1px solid #eee4d8;
            border-radius: 18px;
            background: white;
            box-shadow:
                0 7px 25px
                rgba(67,41,24,.06);
            transition:
                all .3s ease;
            animation:
                cardUp .7s ease;
        }

        .card:hover {
            transform:
                translateY(-5px);
            box-shadow:
                0 15px 35px
                rgba(67,41,24,.12);
        }

        .card-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            color: #6b4426;
            background: #f8ead5;
            font-size: 23px;
        }

        .value {
            margin-top: 25px;
            color: #5b351d;
            font-size: 32px;
            font-weight: 800;
        }

        .label {
            color: #78695c;
            font-size: 13px;
            font-weight: 600;
        }

        .card small {
            display: block;
            margin-top: 8px;
            color: #aa9a8a;
            font-size: 10px;
        }

        /* ======================================
           EXTRA DASHBOARD CONTENT
        ====================================== */
        .dashboard-showcase {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .feature-panel,
        .status-panel {
            position: relative;
            overflow: hidden;
            padding: 22px 22px 18px;
            border: 1px solid #eee4d8;
            border-radius: 18px;
            background: linear-gradient(180deg, #fffdfb 0%, #f9f2ea 100%);
            box-shadow: 0 7px 25px rgba(67,41,24,.06);
        }

        .feature-panel::before,
        .status-panel::before {
            content: "";
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #6b4426, #d8b981, #f0cc8a);
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            gap: 12px;
        }

        .panel-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            color: #432918;
            font-size: 15px;
            font-weight: 800;
        }

        .panel-title i {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #f7ead5;
            color: #6b4426;
        }

        .badge-soft {
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(107,68,38,.08);
            color: #6b4426;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .mini-stat {
            padding: 16px 12px 14px;
            border: 1px solid #f0e6dc;
            border-radius: 14px;
            background: rgba(255,255,255,.75);
        }

        .mini-stat .mini-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            border-radius: 12px;
            background: #f4e6cf;
            color: #6b4426;
            font-size: 18px;
        }

        .mini-stat strong {
            display: block;
            color: #432918;
            font-size: 24px;
            font-weight: 800;
            line-height: 1.2;
        }

        .mini-stat span {
            display: block;
            margin-top: 4px;
            color: #7a685d;
            font-size: 11px;
            font-weight: 600;
        }

        .status-list {
            display: grid;
            gap: 12px;
        }

        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid #f0e7df;
            border-radius: 12px;
            background: rgba(255,255,255,.75);
        }

        .status-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #513522;
            font-size: 12px;
            font-weight: 700;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #8a5a34;
            box-shadow: 0 0 0 4px rgba(138,90,52,.12);
        }

        .status-value {
            color: #432918;
            font-size: 16px;
            font-weight: 800;
        }

        .progress-wrap {
            margin-top: 18px;
        }

        .progress-head {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #5d4538;
            font-size: 11px;
            font-weight: 700;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            overflow: hidden;
            border-radius: 999px;
            background: #f3e7d9;
        }

        .progress-fill {
            width: 82%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #6b4426, #d5a85d);
        }

        .stock-alert {
            display: none;
            margin: 0 0 20px;
            padding: 16px 18px;
            border: 1px solid #f4c7c7;
            border-left: 5px solid #dc3545;
            border-radius: 14px;
            background: linear-gradient(180deg, #fff4f4 0%, #ffeaea 100%);
            color: #7a1f1f;
            box-shadow: 0 8px 20px rgba(220,53,69,.08);
        }

        .stock-alert.show {
            display: block;
        }

        .stock-alert-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .stock-alert-body {
            display: grid;
            gap: 8px;
        }

        .stock-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(255,255,255,.35);
            color: #a12626;
            font-size: 12px;
            font-weight: 700;
        }

        .stock-item .stock-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            padding: 5px 8px;
            border-radius: 999px;
            background: rgba(220,53,69,.12);
            color: #b42318;
            font-weight: 800;
        }

        .stock-alert.empty {
            display: none;
        }

        /* ======================================
           CONTENT FRAME
        ====================================== */
        .frame-wrapper {
            overflow: hidden;
            min-height: 600px;
            border:
                1px solid #eee4d8;
            border-radius: 18px;
            background: white;
            box-shadow:
                0 7px 25px
                rgba(67,41,24,.06);
        }

        .content-frame {
            width: 100%;
            min-height: 600px;
            display: block;
            border: none;
            background: white;
        }

        /* ======================================
           MODAL
        ====================================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                rgba(30,20,10,.55);
            backdrop-filter:
                blur(5px);
            opacity: 0;
            visibility: hidden;
            transition: .25s;
            z-index: 9999;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .logout-modal {
            width: 90%;
            max-width: 420px;
            padding: 30px;
            border-radius: 20px;
            background: white;
            text-align: center;
            box-shadow:
                0 25px 60px
                rgba(0,0,0,.25);
            transform:
                translateY(20px)
                scale(.95);
            transition: .3s;
        }


        .modal-overlay.show .logout-modal {
            transform:
                translateY(0)
                scale(1);
        }


        .modal-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #fff0f1;
            color: #dc3545;
            font-size: 28px;
        }


        .logout-modal h3 {
            margin-bottom: 8px;
            color: #432918;
            font-size: 20px;
        }


        .logout-modal p {
            margin-bottom: 23px;
            color: #88796c;
            font-size: 13px;
        }


        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            }

        .btn-modal {
            padding: 10px 20px;
            border: none;
            border-radius: 9px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .btn-cancel {
            color: #5d4b3b;
            background: #eee8e0;
        }

        .btn-confirm {
            color: white;
            background: #dc3545;
        }

        .btn-confirm:hover {
            background: #bb2d3b;
            transform:
                translateY(-2px);
            }

        /* ======================================
           ANIMATION
        ====================================== */
        @keyframes sidebarIn {
            from {
                opacity: 0;
                transform:
                    translateX(-30px);
            }
            to {
                opacity: 1;
                transform:
                    translateX(0);
            }
        }
        @keyframes contentIn {
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
        @keyframes cardUp {
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

        /* ======================================
           TABLET
        ====================================== */
        @media (max-width: 900px) {
            .sidebar {
                width: 220px;
            }

            .content {
                width:
                    calc(100% - 220px);
                margin-left: 220px;
                padding: 20px;
                }
            .user-badge {
                display: none;
            }
        }

        /* ======================================
           MOBILE
        ====================================== */
        @media (max-width: 700px) {
            .layout {
                display: block;
            }
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                min-height: auto;
                padding: 15px;
            }

            .brand {
                margin-bottom: 15px;
                padding-bottom: 15px;
                }

            .nav-menu {
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 5px;
            }

            .nav-link {
                flex:
                    0 0 auto;
                white-space: nowrap;
            }


            .sidebar-footer {
                margin-top: 15px;
            }

            .library-info {
                display: none;
            }

            .content {
                width: 100%;
                margin-left: 0;
                padding: 15px;
            }

            .topbar {
                padding: 17px;
                border-radius: 15px;
            }

            .page-title {
                font-size: 20px;
            }

            .welcome {
                padding: 22px;
                border-radius: 16px;
            }

            .welcome::after {
                right: 20px;
                font-size: 55px;
            }


            .recap {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 15px;
            }

            .dashboard-showcase {
                grid-template-columns: 1fr;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .frame-wrapper {
                border-radius: 15px;
            }

            .content-frame {
                min-height: 700px;
            }
        }

        /* ======================================
           SMALL MOBILE
        ====================================== */
        @media (max-width: 450px) {
            .content {
                padding: 10px;
            }

            .topbar {
                padding: 15px;
            }

            .page-subtitle {
                font-size: 10px;
            }
            .welcome h3 {
                font-size: 18px;
            }
            .welcome p {
                font-size: 11px;
                max-width: 80%;
                }
            .card {
                min-height: 125px;
                padding: 20px;
            }

            .value {
                font-size: 27px;
            }

            .actions {
                flex-direction: column;
            }


            .btn-modal {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="layout">

    <!-- ======================================
         SIDEBAR
    ======================================= -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-book-half"></i>
            </div>
            <div class="brand-text">
                Perpustakaan
                <small> Sistem Informasi </small>
            </div>
        </div>


        <nav class="nav-menu">
            <a href="#"class="nav-link active"data-src="home.php">
                 <i class="bi bi-grid-1x2-fill"></i>Home</a>

                 <a href="#"class="nav-link"data-src="buku.php">
                    <i class="bi bi-book"></i> Data Buku</a>

            <a href="#"class="nav-link" data-src="anggota.php">
                <i class="bi bi-people-fill"></i> Data Anggota  </a>

            <a href="#"class="nav-link"data-src="peminjaman.php">
                <i class="bi bi-journal-arrow-up"></i> Data Peminjaman </a>

             <a href="#"class="nav-link"data-src="petugas.php">
                <i class="bi bi-person-badge-fill"></i>Data Petugas </a>
        </nav>
        </nav>
        <div class="sidebar-footer">
            <div class="library-info">
                <i class="bi bi-info-circle-fill"></i>
                Kelola data perpustakaan
                dengan mudah dan terorganisir.

                </div>


            <button
                id="logoutBtn"
                class="logout-button"
            >

                <i class="bi bi-box-arrow-right me-2"></i>

                Logout

            </button>


        </div>


    </aside>



    <!-- ======================================
         MAIN CONTENT
    ======================================= -->

    <main class="content">


        <!-- TOPBAR -->

        <div class="topbar">


            <div>

                <h2 class="page-title">

                    <i class="bi bi-house-door-fill"></i>

                    Dashboard

                </h2>


                <p class="page-subtitle">

                    Selamat datang di Sistem Informasi
                    Perpustakaan

                </p>

            </div>


            <div class="user-badge">

                <i class="bi bi-person-circle"></i>

                <span>
                    <?= htmlspecialchars($namaPetugas, ENT_QUOTES, 'UTF-8'); ?>
                </span>

            </div>


        </div>



        <!-- WELCOME -->

        <div class="welcome">

            <h3>

                Selamat Datang, <?= htmlspecialchars($namaPetugas, ENT_QUOTES, 'UTF-8'); ?> 👋

            </h3>


            <p>

                Kelola buku dan anggota perpustakaan
                melalui dashboard ini.

            </p>

        </div>



        <!-- RECAP -->

        <div class="section-title">

            <i class="bi bi-bar-chart-fill me-2"></i>

            Ringkasan Perpustakaan

        </div>


        <div
            class="recap"
            id="recap"
        >

            <div class="card">
                <div class="card-icon">
                    <i class="bi bi-book-fill"></i>
                </div>
                <div class="value" id="totalBooks">--</div>
                <div class="label">Jumlah Buku</div>
                <small>Data total koleksi buku</small>
            </div>

            <div class="card">
                <div class="card-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="value" id="totalAnggota">--</div>
                <div class="label">Jumlah Anggota</div>
                <small>Data total anggota perpustakaan</small>
            </div>

            <div class="card">
                <div class="card-icon">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div class="value" id="totalPetugas">--</div>
                <div class="label">Jumlah Petugas</div>
                <small>Data akun petugas aktif</small>
            </div>

            <div class="card">
                <div class="card-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="value" id="totalDenda">--</div>
                <div class="label">Total Denda</div>
                <small id="lastUpdated">--</small>
            </div>
        </div>

        <div id="stockAlert" class="stock-alert" aria-live="polite">
            <div class="stock-alert-header">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Peringatan stok menipis</span>
            </div>
            <div class="stock-alert-body" id="stockAlertBody"></div>
        </div>

        <div class="dashboard-showcase">
            <div class="feature-panel">
                <div class="panel-header">
                    <h4 class="panel-title">
                        <i class="bi bi-stars"></i>
                        Highlight Dashboard
                    </h4>
                    <span class="badge-soft">Live</span>
                </div>

                <div class="feature-grid">
                    <div class="mini-stat">
                        <div class="mini-icon">
                            <i class="bi bi-clipboard-data-fill"></i>
                        </div>
                        <strong>98%</strong>
                        <span>Kinerja sistem</span>
                    </div>

                    <div class="mini-stat">
                        <div class="mini-icon">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <strong>24</strong>
                        <span>Peminjaman aktif</span>
                    </div>

                    <div class="mini-stat">
                        <div class="mini-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <strong>12</strong>
                        <span>Update hari ini</span>
                    </div>
                </div>
            </div>

            <div class="status-panel">
                <div class="panel-header">
                    <h4 class="panel-title">
                        <i class="bi bi-speedometer2"></i>
                        Status Cepat
                    </h4>
                </div>

                <div class="status-list">
                    <div class="status-item">
                        <div class="status-label">
                            <span class="status-dot"></span>
                            Koleksi buku
                        </div>
                        <div class="status-value" id="statusBooks">--</div>
                    </div>

                    <div class="status-item">
                        <div class="status-label">
                            <span class="status-dot"></span>
                            Anggota aktif
                        </div>
                        <div class="status-value" id="statusAnggota">--</div>
                    </div>

                    <div class="status-item">
                        <div class="status-label">
                            <span class="status-dot"></span>
                            Pengembalian
                        </div>
                        <div class="status-value">On track</div>
                    </div>
                </div>

                <div class="progress-wrap">
                    <div class="progress-head">
                        <span>Target layanan</span>
                        <span>82%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
            </div>
        </div>



        <!-- IFRAME -->

        <div class="frame-wrapper">

            <iframe
                id="contentFrame"
                src="home.php"
                class="content-frame"
                title="Konten Perpustakaan"
            ></iframe>

        </div>


    </main>


</div>



<!-- ======================================
     MODAL LOGOUT
======================================= -->

<div
    id="modalOverlay"
    class="modal-overlay"
    aria-hidden="true"
>


    <div
        class="logout-modal"
        role="dialog"
        aria-modal="true"
    >


        <div class="modal-icon">

            <i class="bi bi-box-arrow-right"></i>

        </div>


        <h3>

            Konfirmasi Logout

        </h3>


        <p>

            Apakah Anda yakin ingin keluar
            dari sistem perpustakaan?

        </p>


        <div class="actions">


            <button
                class="btn-modal btn-cancel"
                id="modalCancel"
            >

                <i class="bi bi-x-lg me-1"></i>

                Batal

            </button>


            <button
                class="btn-modal btn-confirm"
                id="modalConfirm"
            >

                <i class="bi bi-box-arrow-right me-1"></i>

                Logout

            </button>


        </div>


    </div>

</div>



<!-- ======================================
     JAVASCRIPT
======================================= -->

<script>


/* ======================================
   NAVIGATION
====================================== */

var recapEl =
    document.getElementById('recap');


document
    .querySelectorAll('.nav-link')
    .forEach(function (el) {


        el.addEventListener(
            'click',
            function (e) {


                e.preventDefault();


                document
                    .querySelectorAll('.nav-link')
                    .forEach(
                        n =>
                        n.classList.remove('active')
                    );


                el.classList.add('active');


                var src =
                    el.getAttribute('data-src');


                var frame =
                    document.getElementById(
                        'contentFrame'
                    );


                if (frame && src) {

                    frame.src = src;

                }


                /*
                    Sembunyikan recap
                    ketika membuka data
                    buku / anggota
                */

                if (recapEl) {


                    if (
                        src &&
                        (
                            src.indexOf(
                                'buku.php'
                            ) !== -1
                            ||
                            src.indexOf(
                                'anggota.php'
                            ) !== -1
                        )
                    ) {


                        recapEl.style.display =
                            'none';


                    } else {


                        recapEl.style.display =
                            '';


                        fetchRecap();

                    }

                }

            }
        );

    });



/* ======================================
   MODAL LOGOUT
====================================== */

var logoutBtn =
    document.getElementById(
        'logoutBtn'
    );


var overlay =
    document.getElementById(
        'modalOverlay'
    );


var cancelBtn =
    document.getElementById(
        'modalCancel'
    );


var confirmBtn =
    document.getElementById(
        'modalConfirm'
    );



function openModal() {

    overlay.classList.add('show');

    overlay.setAttribute(
        'aria-hidden',
        'false'
    );

}


function closeModal() {

    overlay.classList.remove('show');

    overlay.setAttribute(
        'aria-hidden',
        'true'
    );

}


logoutBtn.addEventListener(
    'click',
    function () {

        openModal();

    }
);


cancelBtn.addEventListener(
    'click',
    function () {

        closeModal();

    }
);


overlay.addEventListener(
    'click',
    function (e) {

        if (
            e.target === overlay
        ) {

            closeModal();

        }

    }
);


confirmBtn.addEventListener(
    'click',
    function () {

        window.location.href =
            'logout.php';

    }
);



/* ======================================
   FETCH RECAP
====================================== */

function fetchRecap() {


    fetch('recap.php')

        .then(
            function (res) {

                return res.json();

            }
        )

        .then(
            function (data) {


                document
                    .getElementById(
                        'totalBooks'
                    )
                    .textContent =
                    data.total_books ?? '0';

                document
                    .getElementById(
                        'totalAnggota'
                    )
                    .textContent =
                    data.total_anggota ?? '0';

                document
                    .getElementById(
                        'totalPetugas'
                    )
                    .textContent =
                    data.total_petugas ?? '0';

                document
                    .getElementById(
                        'totalDenda'
                    )
                    .textContent =
                    'Rp ' +
                    (
                        data.total_denda ||
                        '0'
                    );

                document
                    .getElementById(
                        'statusBooks'
                    )
                    .textContent =
                    data.total_books ?? '0';

                document
                    .getElementById(
                        'statusAnggota'
                    )
                    .textContent =
                    data.total_anggota ?? '0';

                var stockAlert = document.getElementById('stockAlert');
                var stockAlertBody = document.getElementById('stockAlertBody');
                var lowStockBooks = Array.isArray(data.low_stock_books) ? data.low_stock_books : [];

                if (lowStockBooks.length > 0) {
                    if (stockAlert) {
                        stockAlert.classList.add('show');
                        stockAlert.classList.remove('empty');
                    }

                    if (stockAlertBody) {
                        stockAlertBody.innerHTML = lowStockBooks
                            .map(function (book) {
                                return '<div class="stock-item" style="background: rgba(220,53,69,0.07); border: 1px solid rgba(220,53,69,0.16); color: #9b1c1c;">'
                                    + '<span>' + (book.judul_buku || 'Judul tidak tersedia') + '</span>'
                                    + '<span class="stock-count">' + (book.stok ?? 0) + '</span>'
                                    + '</div>';
                            })
                            .join('');
                    }
                } else {
                    if (stockAlert) {
                        stockAlert.classList.remove('show');
                        stockAlert.classList.add('empty');
                    }

                    if (stockAlertBody) {
                        stockAlertBody.innerHTML = '';
                    }
                }

                document
                    .getElementById(
                        'lastUpdated'
                    )
                    .textContent =
                    'Terakhir: ' +
                    (
                        data.last_updated ||
                        '-'
                    );

            }
        )

        .catch(
            function (e) {

                console.error(
                    'Gagal mengambil recap',
                    e
                );

            }
        );

}



/* ======================================
   REFRESH DARI IFRAME
====================================== */

window.addEventListener(
    'message',
    function (e) {


        try {


            var d = e.data;


            if (
                d &&
                d.type ===
                'recap-refresh'
            ) {

                fetchRecap();

            }


        } catch (err) {

        }

    }
);



/* ======================================
   IFRAME LOAD
====================================== */

var frame =
    document.getElementById(
        'contentFrame'
    );


if (frame) {


    frame.addEventListener(
        'load',
        function () {


            try {


                var src =
                    frame.getAttribute(
                        'src'
                    ) || '';


                if (recapEl) {


                    if (
                        src.indexOf(
                            'index.php'
                        ) !== -1
                        ||
                        src.indexOf(
                            'anggota.php'
                        ) !== -1
                        ||
                        src.indexOf(
                            'buku.php'
                        ) !== -1
                    ) {
                        recapEl.style.display =
                            'none';
                    } else {
                        recapEl.style.display =
                            '';
                        fetchRecap();
                    }
                }
            } catch (e) {
                fetchRecap();
            }
        }
    );
}
fetchRecap();
/* Refrsh setiap 5 detik */
setInterval(
    fetchRecap,
    5000
);
</script>
</body>
</html>