{{-- resources/views/manual_book.blade.php --}}
@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- Breadcrumb --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-book fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="text-muted">Buku Panduan</span>
    </div>

    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1 fw-medium shadow-sm"
         style="border:1px solid #FFC300;color:#FF9800;background:#fff;">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
      </a>
    </div>
  </div>

  {{-- Page Header --}}
  <div class="row g-4 mb-4">
    <div class="col-12">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
          <h4 class="fw-bold mb-1">Buku Panduan Super Admin SIMBA</h4>
          <p class="text-muted mb-0">Panduan penggunaan sistem informasi manajemen barang - penjelasan tiap halaman, fitur lanjutan, dan cara penggunaan.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Two columns: Left content / Right TOC --}}
  <div class="row g-4">
    {{-- LEFT: Manual Content --}}
    <div class="col-xl-9 col-md-12">
      {{-- Section: Dashboard --}}
      <section id="dashboard" class="mb-4">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2" style="color:#FF9800;"></i> Dashboard</h5>
            <small class="text-muted">Ringkasan & grafik</small>
          </div>
          <div class="card-body">
            <p>Halaman utama untuk melihat ringkasan statistik barang, grafik perbandingan barang masuk & keluar, notifikasi kedaluwarsa, dan shortcut ke data penting.</p>

            {{-- Carousel (Slider images) --}}
            <div id="carouselDashboard" class="carousel slide mt-3" data-bs-ride="carousel">
              <div class="carousel-inner rounded-3 shadow-sm">
                <div class="carousel-item active">
                  <img src="{{ asset('assets/img/manual_book/mbbarang1.png') }}" class="d-block w-100" alt="Dashboard screenshot 1">
                </div>
                <div class="carousel-item">
                  <img src="{{ asset('assets/img/manual_book/mbbarang2.png') }}" class="d-block w-100" alt="Dashboard screenshot 2">
                </div>
                <div class="carousel-item">
                  <img src="{{ asset('assets/img/manual_book/mbbarang3.png') }}" class="d-block w-100" alt="Dashboard screenshot 3">
                </div>
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#carouselDashboard" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#carouselDashboard" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      {{-- Section: Kategori / Satuan / Supplier --}}
      <section id="basic-crud" class="mb-4">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-white">
            <h5 class="fw-bold mb-0"><i class="bi bi-tags me-2" style="color:#FFC300;"></i> Kategori / Satuan / Supplier</h5>
          </div>
          <div class="card-body">
            <p>Halaman ini menggunakan operasi CRUD (Create, Read, Update, Delete) standar. Tambah, edit, dan hapus data dapat dilakukan melalui form modal atau halaman terpisah.</p>

            <ul>
              <li>Tambah data: isi form, validasi server-side, lalu simpan.</li>
              <li>Edit data: buka modal atau edit page, ubah lalu submit.</li>
              <li>Hapus data: konfirmasi dengan modal sebelum hapus permanen.</li>
            </ul>

            <div class="mt-3">
              <img src="{{ asset('assets/img/manual_book/mbkategori.gif') }}" class="img-fluid rounded" alt="Kategori example">
            </div>
          </div>
        </div>
      </section>

      {{-- Section: Barang (kompleks) --}}
      <section id="barang" class="mb-4">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-box-seam me-2" style="color:#2ecc71;"></i> Barang (Fitur Lanjutan)</h5>
            <small class="text-muted">Cetak barcode • Import Excel • Filter & monitoring kedaluwarsa</small>
          </div>

          <div class="card-body">
            <p>Halaman Barang merupakan salah satu modul terpenting. Berikut penjelasan fungsi-fungsi yang lebih kompleks:</p>

            {{-- SUB: Cetak Barcode --}}
            <div class="mb-3">
              <h6 class="fw-semibold">Cetak Barcode</h6>
              <p class="text-muted">Fitur ini menghasilkan barcode per item untuk keperluan scan dan label fisik. Alur umum:</p>
              <ol>
                <li>Pilih item yang ingin dicetak (checkbox atau halaman detail item).</li>
                <li>Klik tombol <code>Cetak Barcode</code>.</li>
                <li>Muncul preview label — pilih jumlah label / ukuran kertas (A4 / roll).</li>
                <li>Tekan <code>Print</code> — gunakan print CSS khusus agar hasil rapi.</li>
              </ol>
              <div class="mt-2">
                <img src="{{ asset('assets/img/manual_book/mbbarcode.gif') }}" class="img-fluid rounded" alt="Barcode preview">
              </div>
            </div>

            {{-- SUB: Import Excel --}}
            <div class="mb-3">
              <h6 class="fw-semibold">Import Data Excel</h6>
              <p class="text-muted">Untuk memasukkan data barang massal agar tidak perlu input satu-satu.</p>
              <p><strong>Langkah singkat:</strong></p>
              <ol>
                <li>Download template Excel (kolom: kode, nama, kategori, satuan, stok, tanggal_kedaluwarsa, supplier, harga).</li>
                <li>Isi data sesuai template lalu <code>Upload</code> di halaman Import.</li>
                <li>Server akan memvalidasi baris — tampilkan preview & error jika ada baris invalid.</li>
                <li>Konfirmasi import — data akan disimpan (gunakan DB transaction).</li>
              </ol>

              <div class="mt-2">
                <img src="{{ asset('assets/img/manual_book/mbimport.gif') }}" class="img-fluid rounded" alt="Import excel">
              </div>
            </div>

            {{-- SUB: Filter & Monitoring Expired / Supplier --}}
            <div class="mb-3">
              <h6 class="fw-semibold">Filter & Monitoring</h6>
              <p class="text-muted">Tersedia opsi filter untuk memudahkan pemantauan stok barang, di antaranya:</p>
              <ul>
                <li>Filter berdasarkan rentang tanggal (Dari Tanggal – Sampai Tanggal)</li>
                <li>Urutkan stok barang (Semua, Paling Banyak, Paling Sedikit)</li>
                <li>Pencarian barang berdasarkan nama</li>
              </ul>

              <div class="alert alert-warning">
                Tips: Buat query index pada kolom <code>expired_at</code> dan <code>supplier_id</code> agar filter berjalan cepat.
              </div>
            </div>

            {{-- carousel --}}
            <div class="mt-3">
              <div id="carouselBarang" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded-3 shadow-sm">
                  <div class="carousel-item active">
                    <img src="{{ asset('assets/img/manual_book/mbbarangfilter.png') }}" class="d-block w-100" alt="Barang screen 1">
                  </div>
                  <div class="carousel-item">
                    <img src="{{ asset('assets/img/manual_book/mbbarangfilter1.png') }}" class="d-block w-100" alt="Barang screen 2">
                  </div>
                  <div class="carousel-item">
                    <img src="{{ asset('assets/img/manual_book/mbbarangfilter2.png') }}" class="d-block w-100" alt="Barang screen 2">
                  </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselBarang" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselBarang" data-bs-slide="next">
                  <span class="carousel-control-next-icon"></span>
                </button>
              </div>
            </div>

          </div>
        </div>
      </section>

      {{-- Section: Barang Masuk --}}
      <section id="barang-masuk" class="mb-4">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-white">
            <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down me-2" style="color:#FF9800;"></i> Barang Masuk</h5>
          </div>
          <div class="card-body">
            <p>Form untuk mencatat penerimaan barang dari supplier. Pastikan mencatat Tanggal, Supplier, Item, dan Qty. Data ini akan menambah stok pada item terkait.</p>

            <img src="{{ asset('assets/img/manual_book/mbbarangmasuk.gif') }}" class="img-fluid rounded" alt="Barang masuk">
          </div>
        </div>
      </section>

      {{-- Section: List Pengguna (kompleks) --}}
      <section id="list-user" class="mb-4">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-people me-2" style="color:#FFE000;"></i> List Pengguna</h5>
            <small class="text-muted">Aktif / Nonaktif • Soft Delete</small>
          </div>
          <div class="card-body">
            <p>Halaman pengelolaan pengguna menyediakan fitur berikut:</p>
            <ul>
              <li><strong>Aktifkan / Nonaktifkan</strong> akun pengguna — biasanya toggle yang mengubah kolom <code>is_active</code>.</li>
              <li><strong>Soft Delete</strong> — gunakan trait <code>SoftDeletes</code> di model agar data tidak hilang permanen. Tersedia halaman recovery untuk restore.</li>
              <li>Audit: Simpan log perubahan (siapa menonaktifkan atau menghapus) untuk kepentingan keamanan.</li>
            </ul>

          

            <div class="mt-3">
              <img src="{{ asset('assets/img/manual_book/mblistpegawai.gif') }}" class="img-fluid rounded" alt="List pengguna">
            </div>
          </div>
        </div>
      </section>

      {{-- Section: Export (kompleks) --}}
      <section id="export" class="mb-4">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-arrow-down-fill me-2" style="color:#FF9800;"></i> Export Data</h5>
            <small class="text-muted">Pilih kop surat → Export PDF / Excel</small>
          </div>
          <div class="card-body">
            <p>Fitur export menyediakan opsi untuk memilih kop surat manual sebelum men-generate laporan ke PDF atau Excel.</p>

            <ol>
              <li>Pilih jenis laporan (contoh: Laporan Stok, Laporan Barang Masuk)</li>
              <li>Klik <code>Pilih Kop Surat</code> — tampilkan modal dengan opsi kop surat (default atau custom)</li>
              <li>Pilih format file: <strong>PDF</strong> atau <strong>Excel</strong>, lalu klik <code>Export</code></li>
            </ol>

            {{-- Button contoh untuk modal kop surat --}}
            <div class="d-flex gap-2">
              <button class="btn btn-outline-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#modalKopSurat">Pilih Kop Surat</button>
              <button class="btn btn-warning rounded-pill" id="btnExportPdf">Export PDF</button>
              <button class="btn btn-outline-secondary rounded-pill" id="btnExportExcel">Export Excel</button>
            </div>

            <div class="mt-3">
              <img src="{{ asset('assets/img/manual_book/mbexport.gif') }}" class="img-fluid rounded" alt="Export example">
            </div>

            <p class="mt-3 text-muted">Implementasi: bisa menggunakan package dompdf/laravel-dompdf untuk PDF dan Maatwebsite Excel untuk Excel. Simpan template kop surat di DB agar bisa dipilih sebelum export.</p>
          </div>
        </div>
      </section>

      {{-- Section: Penutup --}}
      <section id="penutup" class="mb-5">
        <div class="card shadow-sm border-0 rounded-3">
          <div class="card-body">
            <h6 class="fw-semibold">Penutup</h6>
            <p class="text-muted">Manual book ini menjelaskan penggunaan dasar hingga fitur lanjutan SIMBA. Untuk tiap fitur kompleks disarankan membuat unit tests dan backup data rutin sebelum import/restore.</p>
          </div>
        </div>
      </section>
    </div>

    {{-- RIGHT: Table of contents / Quick links --}}
    <div class="col-xl-3 col-md-12">
      <div class="card shadow-sm border-0 rounded-3 sticky-top" style="top:90px;">
        <div class="card-body">
          <h6 class="fw-bold">Daftar Isi</h6>
          <nav class="nav flex-column">
            <a class="nav-link py-1" href="#dashboard">Dashboard</a>
            <a class="nav-link py-1" href="#basic-crud">Kategori / Satuan / Supplier</a>
            <a class="nav-link py-1" href="#barang">Barang</a>
            <a class="nav-link py-1" href="#barang-masuk">Barang Masuk</a>
            <a class="nav-link py-1" href="#list-user">List Pengguna</a>
            <a class="nav-link py-1" href="#export">Export</a>
            <a class="nav-link py-1" href="#penutup">Penutup</a>
          </nav>

          <hr>

          <h6 class="fw-semibold">Quick Tips</h6>
          <ul class="small text-muted">
            <li>Gunakan template Excel untuk import.</li>
            <li>Backup DB sebelum proses import massal.</li>
            <li>Manfaatkan soft delete untuk audit dan recovery.</li>
          </ul>

        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal: Pilih Kop Surat --}}
<div class="modal fade" id="modalKopSurat" tabindex="-1" aria-labelledby="modalKopSuratLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formKopSurat">
        <div class="modal-header">
          <h5 class="modal-title" id="modalKopSuratLabel">Pilih Kop Surat</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          {{-- contoh pilihan kop surat --}}
          <div class="mb-3">
            <label class="form-label">Pilih Template Kop:</label>
            <select name="kop_template" id="kop_template" class="form-select">
              <option value="default">Kop Default - SIMBA</option>
              <option value="instansi_a">Kop Instansi A</option>
              <option value="custom">Custom (masukan manual)</option>
            </select>
          </div>

          <div id="customKopWrapper" class="d-none">
            <label class="form-label">Isi Kop Surat (HTML / Text):</label>
            <textarea name="kop_custom" class="form-control" rows="4"></textarea>
          </div>

          <div class="mt-3">
            <p class="small text-muted">Preview kop surat akan ditampilkan pada dokumen export sesuai pilihan.</p>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning rounded-pill">Simpan pilihan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.smooth-fade{animation:fadeIn 0.6s ease-in-out;}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.breadcrumb-link{position:relative;transition:all 0.25s ease;}
.breadcrumb-link::after{content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;background:#FF9800;transition:width 0.25s ease;}
.breadcrumb-link:hover::after{width:100%;}
#carouselDashboard .carousel-inner {
  max-height: 450px;
}

#carouselDashboard .carousel-item img {
  object-fit: contain;
  width: 100%;
  height: 450px;
  background-color: #ffffff;
  padding: 10px;
}
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){

  // Toggle custom kop wrapper
  const kopSelect = document.getElementById('kop_template');
  const kopWrapper = document.getElementById('customKopWrapper');

  if(kopSelect){
    kopSelect.addEventListener('change', function(){
      if(this.value === 'custom') kopWrapper.classList.remove('d-none');
      else kopWrapper.classList.add('d-none');
    });
  }

  // Simulasi simpan kop surat
  const formKop = document.getElementById('formKopSurat');
  if(formKop){
    formKop.addEventListener('submit', function(e){
      e.preventDefault();
      const chosen = kopSelect.value;
      // TODO: kirim via AJAX ke backend untuk simpan pilihan kop
      alert('Kop surat dipilih: ' + chosen);
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalKopSurat'));
      modal.hide();
    });
  }

  // Export buttons (contoh trigger)
  document.getElementById('btnExportPdf')?.addEventListener('click', function(){
    // TODO: panggil endpoint export PDF, sertakan pilihan kop surat
    alert('Men-trigger export PDF (implementasikan endpoint di backend).');
  });
  document.getElementById('btnExportExcel')?.addEventListener('click', function(){
    // TODO: panggil endpoint export Excel
    alert('Men-trigger export Excel (implementasikan endpoint di backend).');
  });

});
</script>
@endpush
