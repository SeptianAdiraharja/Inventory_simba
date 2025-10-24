@extends('layouts.index')
@section('content')
<div class="container mt-4">
    {{-- 🔹 Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-envelope-plus"></i> Tambah Kop Surat
        </h4>
        <a href="{{ route('super_admin.kop_surat.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar
        </a>
    </div>

    {{-- 🔹 Form Tambah Kop Surat --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('super_admin.kop_surat.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Nama Instansi --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Instansi / Dinas</label>
                    <input type="text" id="nama_instansi" name="nama_instansi" class="form-control"
                        value="{{ old('nama_instansi', 'PEMERINTAH DAERAH PROVINSI JAWA BARAT') }}">
                </div>

                {{-- Nama Unit --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Unit / Lembaga</label>
                    <input type="text" id="nama_unit" name="nama_unit" class="form-control"
                        value="{{ old('nama_unit', 'UPTD PELATIHAN KESEHATAN') }}">
                </div>

                {{-- Alamat --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="2">{{ old('alamat', 'Jalan Pasteur No. 31') }}</textarea>
                </div>

                {{-- Telepon & Kota --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Telepon</label>
                        <input type="text" id="telepon" name="telepon" class="form-control"
                            value="{{ old('telepon', '(022) 4238422') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Kota</label>
                        <input type="text" id="kota" name="kota" class="form-control"
                            value="{{ old('kota', 'Bandung – 40171') }}">
                    </div>
                </div>

                {{-- Website & Email --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Website</label>
                        <input type="text" id="website" name="website" class="form-control"
                            value="{{ old('website', 'upelkes.jabarprov.go.id') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="{{ old('email', 'upelkes@jabarprov.go.id') }}">
                    </div>
                </div>

                {{-- Logo --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Logo (opsional)</label>
                    <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                </div>

                {{-- Tombol Simpan --}}
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        Simpan Kop Surat
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
@endsection
