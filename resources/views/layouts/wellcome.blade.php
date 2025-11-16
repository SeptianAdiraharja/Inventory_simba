    <!doctype html>
    <html lang="id" data-assets-path="{{ asset('assets') }}">
    <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Selamat Datang di SIMBA</title>

    <!-- 🧩 Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icons/simba.png') }}" />

    <!-- 🖋️ Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- 🧱 Bootstrap & AOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
        --primary: #ff9900;
        --primary-dark: #ff7a00;
        --text-dark: #1f1f1f;
        --text-light: #6c757d;
        }

        body {
        font-family: 'Inter', sans-serif;
        scroll-behavior: smooth;
        background-color: #fff;
        color: var(--text-dark);
        }

        /* 🌐 NAVBAR */
        .navbar {
        transition: all 0.4s ease-in-out;
        padding: 0.8rem 2rem;
        z-index: 1050;
        backdrop-filter: blur(6px);
        background-color: rgba(255, 255, 255, 0.8);
        }

        .navbar.scrolled {
        background-color: rgba(255, 255, 255, 0.95);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .navbar-brand .navbar-logo {
        height: 48px;
        width: auto;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        transition: transform 0.3s ease;
        }

        .navbar-brand:hover .navbar-logo {
        transform: scale(1.05);
        }

        .brand-text {
        font-family: 'p', sans-serif !important;
        font-weight: 900;
        font-size: 1.7rem;
        letter-spacing: 1px;
        color: var(--primary-dark);
        transition: color 0.3s ease;
        }

        .navbar:hover .brand-text {
        color: var(--primary);
        }

        /* ✨ Menu Link */
        .nav-link {
        color: var(--text-dark);
        font-weight: 500;
        margin: 0 10px;
        position: relative;
        transition: all 0.4s ease;
        }

        /* 🌟 Underline animasi lembut */
        .nav-link::after {
        content: "";
        position: absolute;
        left: 50%;
        bottom: -6px;
        width: 0%;
        height: 2px;
        background-color: var(--primary-dark);
        border-radius: 2px;
        transform: translateX(-50%);
        transition: all 0.35s ease-in-out;
        opacity: 0;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
        width: 60%;
        opacity: 1;
        }

        .nav-link:hover,
        .nav-link.active {
        color: var(--primary-dark);
        transform: translateY(-1px);
        }

        /* 🔐 Tombol Login */
        .btn-login {
        background: linear-gradient(90deg, var(--primary-dark), var(--primary));
        color: #fff !important;
        border-radius: 25px;
        padding: 8px 22px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 3px 6px rgba(255, 153, 0, 0.4);
        }

        .btn-login:hover {
        opacity: 0.9;
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 5px 12px rgba(255, 153, 0, 0.6);
        }

        /* 🌅 Section */
        section {
        padding: 100px 0;
        transition: all 0.5s ease;
        }

    /* 🌑 Footer Hitam Elegan */
    footer {
    background: #000;
    color: #f1f1f1;
    padding: 3rem 0 1.5rem 0;
    border-top: 2px solid rgba(255, 255, 255, 0.1);
    }

    /* 🏛️ Logo & Judul */
    .footer-logo {
    height: 200px;
    width: auto;
    filter: brightness(1.2) drop-shadow(0 2px 4px rgba(255, 255, 255, 0.1));
    transition: transform 0.3s ease;
    }

    .footer-logo:hover {
    transform: scale(1.05);
    }

    /* 🔤 Teks Instansi */
    footer h5 {
    font-size: 1.5rem;       /* Ukuran diperbesar */
    font-weight: 800;        /* Tebal biar menonjol */
    letter-spacing: 0.5px;   /* Sedikit jarak antar huruf */
    color: #fff;
    text-transform: uppercase; /* Biar kesannya resmi */
    }

    footer p {
    font-size: 0.95rem;
    color: #ccc;
    margin-top: 5px;
    line-height: 1.5;
    }

    /* 🔗 Link Sosial */
    footer a {
    color: #f8f8f8;
    transition: all 0.3s ease;
    }

    footer a:hover {
    color: var(--primary);
    transform: scale(1.1);
    }

    hr {
    border-color: rgba(255, 255, 255, 0.15);
    }



        /* 📱 RESPONSIVE */
        @media (max-width: 992px) {
        .navbar {
            padding: 1rem;
        }
        .brand-text {
            font-size: 1.4rem;
        }
        .navbar-logo {
            height: 36px;
        }
        }
    </style>
    </head>

    <body>

    <!-- 🧭 NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container d-flex align-items-center justify-content-between">

        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <img src="{{ asset('assets/img/icons/simba.png') }}" alt="Logo SIMBA" class="navbar-logo">
            <span class="brand-text">SIMBA</span>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list fs-2 text-dark"></i>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse justify-content-end align-items-center" id="navbarNav">
            <ul class="navbar-nav mb-2 mb-lg-0 align-items-lg-center">
            <li class="nav-item"><a class="nav-link active" href="#awal">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="#about">Tentang</a></li>
            <li class="nav-item"><a class="nav-link" href="#stats">Statistik</a></li>
            <li class="nav-item"><a class="nav-link" href="#tutorial">Panduan</a></li>
            <li class="nav-item"><a class="nav-link" href="#lokasi">Lokasi</a></li>
            </ul>
            <a href="{{ route('login') }}" class="btn-login ms-lg-4 mt-3 mt-lg-0">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </a>
        </div>
        </div>
    </nav>

    <!-- MAIN -->
    <main class="pt-5 mt-4">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="footer-section text-white mt-5">
        <div class="container py-4">
        <div class="row justify-content-between align-items-center text-center text-md-start">
            <div class="col-md-6 mb-3 mb-md-0 d-flex flex-column flex-md-row align-items-center gap-3">
            <img src="{{ asset('assets/img/icons/upelkes.png') }}"
                alt="Logo UPTD Pelatihan Kesehatan"
                class="footer-logo img-fluid">
            <div class="text-md-start text-center">
                <h5 class="fw-bold mb-1">UPTD Pelatihan Kesehatan</h5>
                <p class="mb-0 small">Jl. Pasteur No.31, Pasir Kaliki, Kec. Cicendo, Kota Bandung, Jawa Barat 40171</p>
            </div>
            </div>

            <div class="col-md-5 text-md-end">
            <h6 class="fw-semibold mb-2">Ikuti Kami</h6>
            <div class="d-flex justify-content-center justify-content-md-end gap-3 fs-4">
                <a href="https://www.instagram.com/upelkes_jabar/" target="_blank" class="text-white" title="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="https://www.tiktok.com/@upelkes_jabar/" target="_blank" class="text-white" title="TikTok"><i class="bi bi-tiktok"></i></a>
                <a href="https://www.youtube.com/upelkes_jabar" target="_blank" class="text-white" title="YouTube"><i class="bi bi-youtube"></i></a>
            </div>
            </div>
        </div>

        <hr class="border-light opacity-75 mt-4 mb-3">
        <div class="text-center small">
            <p class="mb-0">© {{ date('Y') }} <strong>SIMBA</strong> — Sistem Informasi Manajemen Barang. All rights reserved.</p>
        </div>
        </div>
    </footer>

    <!-- 📜 Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // 🌫️ Scroll blur & shadow navbar
        window.addEventListener('scroll', function () {
        const navbar = document.querySelector('.navbar');
        navbar.classList.toggle('scrolled', window.scrollY > 20);
        });

        // 🎯 Smooth highlight active menu
        const sections = document.querySelectorAll("section[id]");
        const navLinks = document.querySelectorAll(".nav-link");

        function activateMenu() {
        let current = "";
        const scrollY = window.pageYOffset + 150; // offset lebih smooth

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
            current = section.getAttribute("id");
            }
        });

        navLinks.forEach(link => {
            link.classList.remove("active");
            const href = link.getAttribute("href").substring(1);
            if (href === current) {
            link.classList.add("active");
            }
        });
        }

        window.addEventListener("scroll", activateMenu);

        // 🖱️ Smooth scroll saat klik menu
        navLinks.forEach(link => {
        link.addEventListener("click", e => {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute("href"));
            navLinks.forEach(l => l.classList.remove("active"));
            link.classList.add("active");
            window.scrollTo({
            top: target.offsetTop - 100,
            behavior: "smooth"
            });
        });
        });

        AOS.init({ duration: 900, once: true, easing: 'ease-in-out' });
    </script>

    @stack('scripts')
    </body>
    </html>
