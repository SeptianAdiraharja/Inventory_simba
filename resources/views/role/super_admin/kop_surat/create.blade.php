@extends('layouts.index')
@section('title', 'Buat Kop Surat')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
      <i class="bi bi-envelope-plus" style="color:#FF9800;"></i> Tambah Kop Surat
    </h4>
    <a href="{{ route('super_admin.kop_surat.index') }}" class="btn btn-light rounded-pill border shadow-sm">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>

  {{-- 🧾 FORM --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
      <form action="{{ route('super_admin.kop_surat.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Nama Instansi</label>
            <input type="text" name="nama_instansi" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old ('nama_instansi',  'PEMERINTAH DAERAH PROVINSI JAWA BARAT') }}"
                   required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Nama Unit / Lembaga</label>
            <input type="text" name="nama_unit" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old ('nama_unit',  'UPTD PELATIHAN KESEHATAN') }}"
                   required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Alamat</label>
          <textarea name="alamat" rows="2" class="form-control border-0 shadow-sm"
                    style="border-left:4px solid #FF9800;">{{ old ('alamat',  'Jalan Pasteur No. 31') }}</textarea>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Telepon</label>
            <input type="text" name="telepon" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old ('telepon',  '(022) 4238422') }}"
                   >
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Kota</label>
            <input type="text" name="kota" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old ('kota',  'Bandung - 40171') }}"
                   >
          </div>
        </div>

        {{-- website & email --}}
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Website</label>
            <input type="text" name="website" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old ('website',  'upelkes.jabarprov.go.id') }}"
                   >
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old ('email',  'upelkes@jabarprov.go.id') }}"
                   >
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Logo (opsional)</label>
          <input type="file" name="logo" class="form-control border-0 shadow-sm"
                 accept="image/*" style="border-left:4px solid #FF9800;">
        </div>

        <div class="text-end">
          <button type="submit" class="btn rounded-pill hover-glow px-4"
                  style="background-color:#FF9800;color:white;">
            <i class="bi bi-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
{{-- 🔹 Preview Kop Surat --}}
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h5 class="fw-bold text-center mb-3">Preview Kop Surat</h5>

            <div class="border p-4 bg-white" id="kop-preview">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="me-3">
                        <img id="logo-preview" src="{{ asset('images/default-logo.png') }}" alt="Logo" width="90">
                    </div>
                    <div class="text-center flex-grow-1">
                        <h6 class="mb-0 fw-semibold text-uppercase" id="instansi-text">PEMERINTAH DAERAH PROVINSI JAWA BARAT</h6>
                        <h5 class="fw-bold mb-1 text-uppercase" id="unit-text">UPTD PELATIHAN KESEHATAN</h5>
                        <p class="mb-0 small" id="alamat-text">Jalan Pasteur No. 31 Telepon (022) 4238422</p>
                        <p class="mb-0 small" id="kontak-text">
                            Website: upelkes.jabarprov.go.id &nbsp; | &nbsp; Email: upelkes@jabarprov.go.id
                        </p>
                        <p class="small mb-0" id="kota-text">Bandung – 40171</p>
                    </div>
                </div>
                <hr class="mt-3 mb-0 border-dark border-2">
            </div>
        </div>
    </div>
</div>

@push('script')
{{-- 🔹 Script Sinkronisasi Preview --}}
<script>
    const fields = ['nama_instansi', 'nama_unit', 'alamat', 'telepon', 'email', 'website', 'kota'];

    fields.forEach(id => {
        document.querySelector(`#${id}`).addEventListener('input', updatePreview);
    });

    function updatePreview() {
        const instansi = document.querySelector('#nama_instansi').value || 'PEMERINTAH DAERAH PROVINSI JAWA BARAT';
        const unit = document.querySelector('#nama_unit').value || 'UPTD PELATIHAN KESEHATAN';
        const alamat = document.querySelector('#alamat').value || 'Jalan Pasteur No. 31';
        const telepon = document.querySelector('#telepon').value || '(022) 4238422';
        const email = document.querySelector('#email').value || 'upelkes@jabarprov.go.id';
        const website = document.querySelector('#website').value || 'upelkes.jabarprov.go.id';
        const kota = document.querySelector('#kota').value || 'Bandung – 40171';

        document.querySelector('#instansi-text').textContent = instansi.toUpperCase();
        document.querySelector('#unit-text').textContent = unit.toUpperCase();
        document.querySelector('#alamat-text').textContent = `${alamat} Telepon ${telepon}`;
        document.querySelector('#kontak-text').innerHTML = `Website: ${website} &nbsp; | &nbsp; Email: ${email}`;
        document.querySelector('#kota-text').textContent = kota;
    }

    // Preview Logo
    document.querySelector('#logo').addEventListener('change', event => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => document.querySelector('#logo-preview').src = e.target.result;
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush

<style>
.hover-glow:hover { background:#FFC107 !important; box-shadow:0 0 10px rgba(255,152,0,0.4); }
.form-control:focus { border-color:#FF9800 !important; box-shadow:0 0 0 3px rgba(255,152,0,0.25); }
</style>
@endsection
