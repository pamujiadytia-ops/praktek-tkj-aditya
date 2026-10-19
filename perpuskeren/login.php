
<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek username dan password dari tabel petugas
    $query = mysqli_query($conn, "SELECT * FROM petugas 
        WHERE username='$username' 
        AND password='$password'");

    if (mysqli_num_rows($query) > 0) {

        $data = mysqli_fetch_assoc($query);

        // Simpan data petugas ke session
        $_SESSION['login'] = true;
        $_SESSION['id_petugas'] = $data['idp'];
        $_SESSION['nama_petugas'] = $data['nama'];
        $_SESSION['username'] = $data['username'];

        header("Location: dashboard.php");
        exit;

    } else {

        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Petugas | Perpustakaan</title>

    <!-- Bootstrap 5 -->
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
            min-height: 100vh;

            font-family: 'Segoe UI', sans-serif;

            background: #f5efe6;
        }

        /* =========================
           CONTAINER UTAMA
        ========================= */

        .login-page {
            min-height: 100vh;
            display: flex;
        }


        /* =========================
           BAGIAN KIRI
        ========================= */

        .library-section {

            width: 50%;
            min-height: 100vh;

            position: relative;

            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 50px;

            color: white;

            background:
                linear-gradient(
                    rgba(73, 45, 25, 0.90),
                    rgba(73, 45, 25, 0.95)
                ),
                url("https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1200&q=80");

            background-size: cover;
            background-position: center;
        }


        /* Overlay dekorasi */

        .library-section::before {

            content: "";

            position: absolute;

            width: 350px;
            height: 350px;

            border-radius: 50%;

            background: rgba(218, 165, 84, 0.15);

            top: -150px;
            left: -150px;
        }


        .library-section::after {

            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            border-radius: 50%;

            background: rgba(218, 165, 84, 0.10);

            bottom: -150px;
            right: -120px;
        }


        /* Isi kiri */

        .library-content {

            position: relative;

            z-index: 2;

            max-width: 520px;

            text-align: center;

            animation: slideLeft 1s ease;
        }


        /* Icon buku */

        .book-icon {

            width: 110px;
            height: 110px;

            margin: 0 auto 25px;

            border-radius: 25px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 55px;

            color: #fff;

            background: rgba(255, 255, 255, 0.12);

            border: 1px solid rgba(255, 255, 255, 0.25);

            backdrop-filter: blur(8px);

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.25);

            animation: floating 3s ease-in-out infinite;
        }


        .library-content h1 {

            font-size: 45px;

            font-weight: 700;

            margin-bottom: 15px;
        }


        .library-content h1 span {

            color: #e0ad63;
        }


        .library-content p {

            font-size: 17px;

            line-height: 1.7;

            color: #eadfd1;

            margin-bottom: 30px;
        }


        /* Dekorasi buku */

        .books-decoration {

            display: flex;

            justify-content: center;

            gap: 10px;

            margin-top: 30px;
        }


        .book {

            width: 45px;
            height: 100px;

            border-radius: 5px;

            box-shadow:
                0 10px 20px rgba(0, 0, 0, 0.25);

            animation: bookFloat 3s ease-in-out infinite;
        }


        .book:nth-child(1) {
            height: 90px;
        }

        .book:nth-child(2) {
            height: 120px;
            animation-delay: .3s;
        }

        .book:nth-child(3) {
            height: 105px;
            animation-delay: .6s;
        }

        .book:nth-child(4) {
            height: 125px;
            animation-delay: .9s;
        }

        .book:nth-child(5) {
            height: 95px;
            animation-delay: 1.2s;
        }


        /* =========================
           BAGIAN KANAN
        ========================= */

        .login-section {

            width: 50%;
            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 50px;

            background: #f8f5ef;
        }


        .login-box {

            width: 100%;

            max-width: 430px;

            animation: slideRight 1s ease;
        }


        /* Logo */

        .login-logo {

            width: 70px;
            height: 70px;

            display: flex;

            align-items: center;
            justify-content: center;

            margin-bottom: 20px;

            border-radius: 18px;

            background: #6b4426;

            color: #fff;

            font-size: 32px;

            box-shadow:
                0 10px 25px rgba(107, 68, 38, 0.25);
        }


        .login-box h2 {

            color: #432918;

            font-weight: 700;

            margin-bottom: 5px;
        }


        .login-subtitle {

            color: #8a7766;

            margin-bottom: 30px;
        }


        /* Input */

        .form-label {

            color: #513522;

            font-weight: 600;

        }


        .input-group {

            margin-bottom: 20px;

        }


        .input-group-text {

            border: none;

            background: #eee7dc;

            color: #6b4426;

        }


        .form-control {

            border: none;

            background: #eee7dc;

            padding: 13px;

            box-shadow: none !important;

        }


        .form-control:focus {

            background: #e8ded0;

        }


        /* Button */

        .btn-login {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 10px;

            background: #6b4426;

            color: white;

            font-weight: 600;

            transition: .3s;
        }


        .btn-login:hover {

            background: #50311c;

            color: white;

            transform: translateY(-3px);

            box-shadow:
                0 10px 20px rgba(107, 68, 38, 0.25);
        }


        .btn-login:active {

            transform: scale(.98);

        }


        /* Error */

        .error {

            border-radius: 10px;

            animation: shake .4s ease;

        }


        /* Footer */

        .login-footer {

            margin-top: 25px;

            text-align: center;

            font-size: 13px;

            color: #9a8978;
        }


        /* =========================
           ANIMASI
        ========================= */

        @keyframes slideLeft {

            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }


        @keyframes slideRight {

            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }


        @keyframes floating {

            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }


        @keyframes bookFloat {

            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }


        @keyframes shake {

            0%, 100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .login-page {
                flex-direction: column;
            }

            .library-section,
            .login-section {

                width: 100%;

            }

            .library-section {

                min-height: 45vh;

                padding: 40px 25px;
            }

            .login-section {

                min-height: 55vh;

                padding: 40px 25px;
            }

            .library-content h1 {

                font-size: 35px;
            }

            .library-content p {

                font-size: 15px;
            }

            .book-icon {

                width: 80px;
                height: 80px;

                font-size: 40px;
            }
        }


        @media (max-width: 500px) {

            .library-section {

                min-height: 40vh;

            }

            .login-section {

                min-height: 60vh;

            }

            .library-content h1 {

                font-size: 28px;
            }

            .library-content p {

                font-size: 14px;

                margin-bottom: 15px;
            }

            .books-decoration {

                margin-top: 15px;
            }

            .book {

                width: 30px;

                height: 70px !important;
            }

            .login-box {

                max-width: 100%;
            }
        }

    </style>

</head>


<body>

<div class="login-page">


    <!-- =====================================
         BAGIAN KIRI - PERPUSTAKAAN
    ====================================== -->

    <section class="library-section">

        <div class="library-content">

            <div class="book-icon">

                <i class="bi bi-book-half"></i>

            </div>


            <h1>

                Perpustakaan
                <span>Digital</span>

            </h1>


            <p>

                Selamat datang di Sistem Informasi
                Perpustakaan. Kelola data buku,
                anggota, dan peminjaman dengan
                lebih mudah dan terorganisir.

            </p>


            <!-- Dekorasi Buku -->

            <div class="books-decoration">

                <div class="book bg-warning"></div>

                <div class="book bg-danger"></div>

                <div class="book bg-success"></div>

                <div class="book bg-primary"></div>

                <div class="book bg-info"></div>

            </div>

        </div>

    </section>



    <!-- =====================================
         BAGIAN KANAN - LOGIN
    ====================================== -->

    <section class="login-section">

        <div class="login-box">


            <!-- Logo -->

            <div class="login-logo">

                <i class="bi bi-person-lock"></i>

            </div>


            <h2>
                Login Petugas
            </h2>


            <p class="login-subtitle">

                Silakan masuk untuk mengakses
                sistem perpustakaan.

            </p>


            <!-- ERROR -->

            <?php if (isset($error)) { ?>

                <div class="alert alert-danger error">

                    <i class="bi bi-exclamation-circle me-2"></i>

                    <?= $error; ?>

                </div>

            <?php } ?>


            <!-- FORM -->

            <form method="POST">


                <!-- Username -->

                <label class="form-label">Username</label>
                <div class="input-group"> 
                    <span class="input-group-text"><i class="bi bi-person-fill"></i>

                    </span>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        required
                    >

                </div>


                <!-- Password -->

                <label class="form-label">

                    Password

                </label>

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="bi bi-lock-fill"></i>

                    </span>


                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >


                    <button
                        type="button"
                        class="input-group-text"
                        onclick="togglePassword()"
                        style="border: none;"
                    >

                        <i
                            class="bi bi-eye-fill"
                            id="eyeIcon"
                        ></i>

                    </button>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    name="login"
                    class="btn btn-login mt-2"
                >

                    <i class="bi bi-box-arrow-in-right me-2"></i>

                    Masuk ke Sistem

                </button>


            </form>


            <div class="login-footer">

                <i class="bi bi-shield-check me-1"></i>

                Sistem Informasi Perpustakaan

            </div>


        </div>

    </section>
</div>
<!-- =====================================
     JAVASCRIPT
====================================== -->
<script>
function togglePassword() {
    const password =
        document.getElementById("password");
    const eyeIcon =
        document.getElementById("eyeIcon");
    if (password.type === "password") {
        password.type = "text";
        eyeIcon.classList.remove(
            "bi-eye-fill"
        );
        eyeIcon.classList.add(
            "bi-eye-slash-fill"
        );
    } else {
        password.type = "password";
        eyeIcon.classList.remove(
            "bi-eye-slash-fill"
        );
        eyeIcon.classList.add(
            "bi-eye-fill"
        );
    }
}
</script>
</body>
</html>