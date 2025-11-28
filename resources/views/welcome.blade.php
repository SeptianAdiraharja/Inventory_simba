@extends('layouts.wellcome')

@section('content')

<!-- 🌇 HERO SECTION -->
<section id="awal" class="hero-section d-flex align-items-center min-vh-100 position-relative"
  style="background: url('{{ asset('assets/img/backgrounds/Login.png') }}') center/cover no-repeat;">
  <div class="hero-overlay-gradient"></div>

  <div class="container position-relative z-3">
    <div class="row align-items-center justify-content-start gy-5">
      <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
        <div class="bg-white bg-opacity-75 rounded-4 p-4 shadow">
          <h1 class="fw-bold text-dark mb-3">Sistem Informasi Manajemen Barang</h1>
          <p class="text-dark mb-4">
            Sistem digital untuk mengelola data barang UPTD Pelatihan Kesehatan agar lebih efisien, transparan, dan terintegrasi.
          </p>
          <a href="#tutorial" class="btn bg-dark bg-opacity-75 text-white fw-semibold px-4 py-2 rounded-3 border-0">
            <i class="bi bi-info-circle me-2"></i> Lihat Panduan Penggunaan
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 🧠 ABOUT SECTION -->
<section id="about" class="py-5 bg-white text-black">
  <div class="container" data-aos="fade-up">
    <div class="section-title text-center mb-5">
      <h2 class="fw-bold">Tentang Sistem</h2>
      <p>Digitalisasi pengelolaan barang untuk efisiensi, akurasi, serta transparansi dalam kinerja UPTD Pelatihan Kesehatan.</p>
    </div>

    <div class="row align-items-center justify-content-center gy-5">
      <!-- 🖼️ Gambar Carousel -->
      <div class="col-lg-5 d-flex justify-content-center" data-aos="fade-right">
        <div id="aboutCarousel"
             class="carousel slide carousel-fade shadow-sm rounded-4 overflow-hidden w-100"
             data-bs-ride="carousel" data-bs-interval="4000"
             style="max-width: 420px;">
          <div class="carousel-inner">
            @for ($i = 1; $i <= 6; $i++)
              <div class="carousel-item {{ $i === 1 ? 'active' : '' }}">
                <img src="{{ asset('assets/img/about/about'.$i.'.png') }}"
                     class="d-block w-100 about-img"
                     alt="Slide {{ $i }}">
              </div>
            @endfor
          </div>

          <button class="carousel-control-prev" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span>
            <span class="visually-hidden">Sebelumnya</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span>
            <span class="visually-hidden">Berikutnya</span>
          </button>
        </div>
      </div>

      <!-- 🧾 Teks Deskripsi -->
      <div class="col-lg-6" data-aos="fade-left">
        <div class="about-text">
          <h4 class="fw-bold text-dark mb-3">Digitalisasi Pengelolaan barang</h4>
          <p>
            Sistem ini membantu proses pendataan, pelaporan, dan pemantauan arus penggunaan barang milik UPTD Pelatihan Kesehatan secara digital,
            sehingga pengelolaan menjadi lebih efisien, akurat, dan transparan.
          </p>
          <ul class="list-unstyled mt-3">
            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Pendataan barang otomatis dan terintegrasi.</li>
            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Mengurangi risiko kehilangan atau duplikasi data.</li>
            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Laporan real-time untuk audit dan evaluasi.</li>
            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Efisiensi administrasi barang berbasis web.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 📊 STATISTIK -->
<section id="stats" class="bg-light py-5 text-black">
  <div class="container text-center" data-aos="fade-up">
    <h2 class="fw-bold mb-5">Statistik Sistem</h2>
    <div class="row g-4 justify-content-center">
      <div class="col-6 col-md-4">
        <div class="stats-item py-5 px-4 rounded-4 bg-white shadow-sm">
          <i class="bi bi-bar-chart-line fs-1 text-warning mb-3"></i>
          <h3 class="fw-bold mb-1">{{ $totalPengunjung ?? 0 }}</h3>
          <p class="fw-semibold mb-0">Pengunjung Website</p>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="stats-item py-5 px-4 rounded-4 bg-white shadow-sm">
          <i class="bi bi-person-check fs-1 text-warning mb-3"></i>
          <h3 class="fw-bold mb-1">{{ $pegawaiAktif ?? 0 }}</h3>
          <p class="fw-semibold mb-0">Pegawai Aktif</p>
        </div>
      </div>
    </div>
  </div>
</section>

 <!-- 🎥 TUTORIAL -->
<section id="tutorial" class="bg-white py-5 text-black">
    <div class="container" data-aos="fade-up">

        <div class="section-title text-center mb-5">
            <h2 class="fw-bold">Tata Cara Penggunaan Bagi Pegawai</h2>
            <p>Panduan penggunaan sistem SIMBA melalui video interaktif.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="ratio ratio-16x9 shadow-lg rounded-4 overflow-hidden">
                    <iframe 
                        src="https://www.youtube.com/embed/fSorx0s5jG0" 
                        title="Tutorial SIMBA"
                        allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        class="border-0">
                    </iframe>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-muted">Jika video tidak muncul, pastikan koneksi internet stabil atau hubungi administrator.</p>
        </div>

    </div>
</section>


<!-- 📍 LOKASI -->
<section id="lokasi" class="py-5 bg-white text-black">
  <div class="container" data-aos="fade-up">
    <div class="section-title text-center mb-4">
      <h2 class="fw-bold">Lokasi Kami</h2>
      <p>Temukan lokasi UPTD Pelatihan Kesehatan di Bandung melalui peta berikut.</p>
    </div>

    <div class="map-container shadow rounded-4 overflow-hidden mx-auto" style="max-width: 900px;">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.962437417771!2d107.59425387499323!3d-6.895909993098043!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e63ec3b486df%3A0x485a589d63cf53a5!2sJl.%20Pasteur%20No.31%2C%20Pasir%20Kaliki%2C%20Kec.%20Cicendo%2C%20Kota%20Bandung!5e0!3m2!1sid!2sid!4v1731305900000!5m2!1sid!2sid"
        width="100%" height="360" style="border:0;" allowfullscreen="" loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>

    <div class="text-center mt-3">
      <a href="https://maps.app.goo.gl/hULoeSpmtF2GydgU9" target="_blank" class="btn btn-warning px-4">
        <i class="bi bi-geo-alt me-2"></i> Buka di Google Maps
      </a>
      <p class="mt-2 text-muted small">
        Jl. Pasteur No.31, Pasir Kaliki, Kec. Cicendo, Kota Bandung, Jawa Barat 40171
      </p>
    </div>
  </div>
</section>

@endsection

@section('scripts')
<script>
  AOS.init({ duration: 900, once: true });
</script>

<style>
/* 🌇 Overlay gradient */
.hero-overlay-gradient {
  position: absolute; inset: 0;
  background: linear-gradient(120deg, rgba(255,122,0,0.85), rgba(255,190,30,0.65), rgba(255,240,120,0.4));
  z-index: 1;
  mix-blend-mode: multiply;
}

/* 🧠 Gambar Carousel (About) */
.about-img {
  max-height: 280px;
  object-fit: contain;
  border-radius: 0.75rem;
  display: block;
  margin: 0 auto;
}

/* 📘 Gambar Tutorial */
.tutorial-card {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.tutorial-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}
.tutorial-img {
  height: 240px;
  width: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.tutorial-card:hover .tutorial-img {
  transform: scale(1.08);
}

/* 📱 Responsif */
@media (max-width: 992px) {
  .about-img { max-height: 240px; }
  .tutorial-img { height: 210px; }
}
@media (max-width: 768px) {
  .about-img { max-height: 200px; }
  .tutorial-img { height: 180px; }
}
</style>
@endsection
