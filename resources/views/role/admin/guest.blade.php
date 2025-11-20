@extends('layouts.index')
@section('title', 'Daftar Guest')
@section('content')

@if(request('q'))
  <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-3">
    <i class="bi bi-search me-2"></i> Hasil pencarian untuk:
    <strong>{{ request('q') }}</strong>
  </div>
@endif

<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="breadcrumb-icon d-flex align-items-center justify-content-center rounded-circle"
           style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
        <i class="bi bi-house-door-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 align-items-center">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
              Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Daftar Guest
          </li>
        </ol>
      </nav>
    </div>

    <div class="breadcrumb-extra text-end">
      <small class="text-muted">
        <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
      </small>
    </div>
  </div>

  {{-- 🧾 CARD UTAMA --}}
  <div class="card shadow-sm border-0 rounded-4 animate__animated animate__fadeInUp smooth-card">
    <div class="card-header text-white d-flex flex-wrap justify-content-between align-items-center py-3 px-4 rounded-top-4"
         style="background-color:#FF9800;">
      <h5 class="card-title mb-0 fw-semibold d-flex align-items-center">
        <i class="bi bi-people-fill me-2 text-white"></i> Daftar Guest
      </h5>

      {{-- Tombol Tambah Guest --}}
      <button class="btn btn-light btn-sm fw-semibold rounded-pill shadow-sm px-3 smooth-btn"
              x-data
              @click="$dispatch('open-modal', 'createGuestModal')">
        <i class="bi bi-plus-lg me-1"></i> Tambah Guest
      </button>
    </div>

    {{-- 🟠 MODAL TAMBAH GUEST --}}
    <x-modal name="createGuestModal" :show="false">
      <form action="{{ route('admin.guests.store') }}" method="POST" class="p-4">
        @csrf
        <h5 class="fw-bold mb-3" style="color:#FF9800;">Tambah Guest Baru</h5>

        <div class="mb-3">
          <label class="form-label fw-semibold">Nama</label>
          <input type="text" name="name" class="form-control rounded-3 border-2" style="border-color:#FFB74D;" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Nomor Telepon</label>
          <input type="text" name="phone" class="form-control rounded-3 border-2" style="border-color:#FFB74D;" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Deskripsi</label>
          <textarea name="description" class="form-control rounded-3 border-2" rows="3" style="border-color:#FFB74D;"></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
          <button type="button" class="btn btn-light border rounded-pill px-3 smooth-btn"
                  style="border-color:#FF9800;color:#FF9800;"
                  @click="$dispatch('close-modal', 'createGuestModal')">
            Batal
          </button>
          <button type="submit" class="btn text-white rounded-pill px-3 smooth-btn"
                  style="background-color:#FF9800;">
            Simpan
          </button>
        </div>
      </form>
    </x-modal>

    {{-- 📋 TABEL DATA GUEST --}}
    <div class="card-body bg-light p-4">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white rounded-4 overflow-hidden shadow-sm">
          <thead style="background:#FFF3E0;" class="text-center fw-semibold text-secondary">
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Telepon</th>
              <th>Deskripsi</th>
              <th>Dibuat Oleh</th>
              <th>Dibuat Pada</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($guests as $guest)
              <tr class="table-row-smooth text-center">
                <td class="fw-semibold text-secondary">{{ $loop->iteration }}</td>
                <td class="fw-semibold text-dark">{{ $guest->name }}</td>
                <td>{{ $guest->phone }}</td>
                <td>{{ $guest->description ?? '-' }}</td>
                <td>{{ $guest->creator?->name ?? '-' }}</td>
                <td>{{ $guest->created_at->format('d-m-Y H:i') }}</td>
                <td>
                  <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <button class="btn btn-sm rounded-pill fw-semibold shadow-sm smooth-btn"
                            style="background-color:#FFF3E0;color:#FF9800;border:1px solid #FF9800;"
                            data-bs-toggle="modal"
                            data-bs-target="#editGuestModal{{ $guest->id }}">
                      <i class="bi bi-pencil-square me-1"></i> Edit
                    </button>

                    <a href="{{ route('admin.produk.byGuest', $guest->id) }}"
                       class="btn btn-sm text-white fw-semibold shadow-sm rounded-pill px-3 smooth-btn"
                       style="background-color:#FF9800;">
                      🛍️ Pilih Produk
                    </a>
                  </div>
                </td>
              </tr>

              {{-- ✏️ MODAL EDIT GUEST --}}
              <div class="modal fade" id="editGuestModal{{ $guest->id }}" tabindex="-1" aria-labelledby="editGuestLabel{{ $guest->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header text-white rounded-top-4" style="background-color:#FF9800;">
                      <h5 class="modal-title fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i> Edit Data Guest
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="{{ route('admin.guests.update', $guest->id) }}" method="POST">
                      @csrf
                      @method('PUT')
                      <div class="modal-body bg-light p-4">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama</label>
                            <input type="text" name="name" class="form-control rounded-3 border-2"
                                   style="border-color:#FFB74D;" value="{{ $guest->name }}" required>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Telepon</label>
                            <input type="text" name="phone" class="form-control rounded-3 border-2"
                                   style="border-color:#FFB74D;" value="{{ $guest->phone }}" required>
                          </div>
                          <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" class="form-control rounded-3 border-2"
                                      style="border-color:#FFB74D;" rows="3">{{ $guest->description }}</textarea>
                          </div>
                        </div>
                      </div>

                      <div class="modal-footer bg-white border-0 rounded-bottom-4">
                        <button type="button" class="btn btn-light border rounded-pill px-4 smooth-btn"
                                style="border-color:#FF9800;color:#FF9800;"
                                data-bs-dismiss="modal">
                          Batal
                        </button>
                        <button type="submit" class="btn text-white rounded-pill px-4 smooth-btn"
                                style="background-color:#FF9800;">
                          💾 Simpan
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                  <i class="bi bi-info-circle me-1"></i> Belum ada data guest
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4 d-flex justify-content-center">
        {{ $guests->links() }}
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
body {
  background-color: #fffaf4 !important;
}
.breadcrumb-item + .breadcrumb-item::before {
  content: "›";
  color: #ffb74d;
  margin: 0 6px;
}
.breadcrumb-icon:hover {
  transform: scale(1.1);
  background-color: #ffecb3;
  transition: 0.3s ease;
}
.table-hover tbody tr:hover {
  background-color: #fff3e0 !important;
  transition: 0.2s ease;
}
.smooth-fade { animation: smoothFade 0.8s ease; }
@keyframes smoothFade {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.smooth-card { transition: all 0.3s ease; }
.smooth-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(255, 152, 0, 0.25);
}
.smooth-btn {
  transition: all 0.3s ease-in-out;
}
.smooth-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
}
.table-row-smooth {
  transition: all 0.25s ease;
}
.table-row-smooth:hover {
  background-color: #fff8e1 !important;
  transform: scale(1.01);
}
@media (max-width:768px){
  .breadcrumb-extra{display:none;}
  h5{font-size:1rem;}
  .table{font-size:0.9rem;}
}
</style>
@endpush
