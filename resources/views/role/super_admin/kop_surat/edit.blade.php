@extends('layouts.index')
@section('title', 'Edit Kop Surat')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB HEADER --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center smooth-fade">
    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
      <i class="bi bi-envelope-check" style="color:#FF9800;"></i>
      Edit Kop Surat
    </h4>
    <a href="{{ route('super_admin.kop_surat.index') }}" class="btn btn-light border rounded-pill shadow-sm">
      <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
  </div>

  {{-- ✏️ FORM EDIT --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
      <form action="{{ route('super_admin.kop_surat.update', $kopSurat->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold text-dark">Nama Instansi / Dinas</label>
            <input type="text" id="nama_instansi" name="nama_instansi"
                   class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old('nama_instansi', $kopSurat->nama_instansi) }}">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold text-dark">Nama Unit / Lembaga</label>
            <input type="text" id="nama_unit" name="nama_unit"
                   class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old('nama_unit', $kopSurat->nama_unit) }}">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-dark">Alamat</label>
          <textarea id="alamat" name="alamat" rows="2"
                    class="form-control border-0 shadow-sm"
                    style="border-left:4px solid #FF9800;">{{ old('alamat', $kopSurat->alamat) }}</textarea>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold text-dark">Telepon</label>
            <input type="text" id="telepon" name="telepon"
                   class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old('telepon', $kopSurat->telepon) }}">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold text-dark">Kota</label>
            <input type="text" id="kota" name="kota"
                   class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old('kota', $kopSurat->kota) }}">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold text-dark">Website</label>
            <input type="text" id="website" name="website"
                   class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old('website', $kopSurat->website) }}">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold text-dark">Email</label>
            <input type="email" id="email" name="email"
                   class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;"
                   value="{{ old('email', $kopSurat->email) }}">
          </div>
        </div>

        {{-- 🖼️ Logo --}}
        <div class="mb-3">
          <label class="form-label fw-semibold text-dark">Logo (opsional)</label>
          <input type="file" id="logo" name="logo"
                 class="form-control border-0 shadow-sm"
                 accept="image/*"
                 style="border-left:4px solid #FF9800;">
          @if ($kopSurat->logo)
            <div class="mt-2">
              <img src="{{ asset('storage/'.$kopSurat->logo) }}" alt="Logo Lama"
                   width="90" class="rounded shadow-sm border">
              <small class="text-muted d-block mt-1">Logo saat ini</small>
            </div>
          @endif
        </div>

        {{-- 💾 Tombol Simpan --}}
        <div class="text-end mt-4">
          <button type="submit" class="btn rounded-pill px-4 hover-glow shadow-sm"
                  style="background-color:#FF9800;color:white;">
            <i class="bi bi-save2"></i> Perbarui Kop Surat
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- 🧾 PREVIEW KOP SURAT --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
      <h5 class="fw-bold text-center mb-3 text-dark">Preview Kop Surat</h5>
      <div class="border rounded-4 p-4 bg-white" id="kop-preview">
        <div class="d-flex align-items-center justify-content-center">
          <div class="me-3">
            <img id="logo-preview"
                 src="{{ $kopSurat->logo ? asset('storage/'.$kopSurat->logo) : asset('images/default-logo.png') }}"
                 alt="Logo" width="90">
          </div>
          <div class="text-center flex-grow-1">
            <h6 class="mb-0 fw-semibold text-uppercase" id="instansi-text">{{ strtoupper($kopSurat->nama_instansi) }}</h6>
            <h5 class="fw-bold mb-1 text-uppercase" id="unit-text" style="color:#FF9800;">{{ strtoupper($kopSurat->nama_unit) }}</h5>
            <p class="mb-0 small" id="alamat-text">{{ $kopSurat->alamat }} Telepon {{ $kopSurat->telepon }}</p>
            <p class="mb-0 small" id="kontak-text">
              Website: {{ $kopSurat->website }} &nbsp; | &nbsp; Email: {{ $kopSurat->email }}
            </p>
            <p class="small mb-0" id="kota-text">{{ $kopSurat->kota }}</p>
          </div>
        </div>
        <hr class="mt-3 mb-0 border-dark border-2">
      </div>
    </div>
  </div>
</div>

{{-- 💫 STYLE TAMBAHAN --}}
<style>
.hover-glow:hover {
  background-color: #FFC107 !important;
  box-shadow: 0 0 10px rgba(255,152,0,0.4);
}
.form-control:focus {
  border-color: #FF9800 !important;
  box-shadow: 0 0 0 3px rgba(255,152,0,0.25);
}
.smooth-fade {
  animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
  from {opacity:0; transform:translateY(10px);}
  to {opacity:1; transform:translateY(0);}
}
</style>

{{-- 🔁 PREVIEW SCRIPT --}}
<script>
const fields = ['nama_instansi', 'nama_unit', 'alamat', 'telepon', 'email', 'website', 'kota'];

fields.forEach(id => {
  document.querySelector(`#${id}`).addEventListener('input', updatePreview);
});

function updatePreview() {
  const instansi = document.querySelector('#nama_instansi').value || '{{ $kopSurat->nama_instansi }}';
  const unit = document.querySelector('#nama_unit').value || '{{ $kopSurat->nama_unit }}';
  const alamat = document.querySelector('#alamat').value || '{{ $kopSurat->alamat }}';
  const telepon = document.querySelector('#telepon').value || '{{ $kopSurat->telepon }}';
  const email = document.querySelector('#email').value || '{{ $kopSurat->email }}';
  const website = document.querySelector('#website').value || '{{ $kopSurat->website }}';
  const kota = document.querySelector('#kota').value || '{{ $kopSurat->kota }}';

  document.querySelector('#instansi-text').textContent = instansi.toUpperCase();
  document.querySelector('#unit-text').textContent = unit.toUpperCase();
  document.querySelector('#alamat-text').textContent = `${alamat} Telepon ${telepon}`;
  document.querySelector('#kontak-text').innerHTML = `Website: ${website} &nbsp; | &nbsp; Email: ${email}`;
  document.querySelector('#kota-text').textContent = kota;
}

// 🖼️ Preview logo realtime
document.querySelector('#logo').addEventListener('change', event => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => document.querySelector('#logo-preview').src = e.target.result;
    reader.readAsDataURL(file);
  }
});
</script>
@endsection
