@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  <!-- ======================== -->
  <!-- 🧭 BREADCRUMB MODERN (SMOOTH & RESPONSIVE) -->
  <!-- ======================== -->
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="breadcrumb-icon bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded-circle" style="width:38px;height:38px;">
        <i class="bi bi-house-door-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 align-items-center">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary fw-semibold">
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

  <!-- === 📋 DAFTAR GUEST === -->
  <div class="card shadow-sm border-0 rounded-4 animate__animated animate__fadeInUp">
    <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center py-3 px-4 rounded-top-3">
      <h4 class="card-title mb-0 fw-semibold d-flex align-items-center">
        <i class="bi bi-people-fill me-2 text-warning"></i> Daftar Guest
      </h4>

      <!-- Tombol Tambah Guest -->
      <button
        class="btn btn-light btn-sm fw-semibold shadow-sm px-3"
        x-data
        @click="$dispatch('open-modal', 'createGuestModal')">
        + Tambah Guest
      </button>
    </div>

    <!-- Modal Tambah Guest -->
    <x-modal name="createGuestModal" :show="false">
      <form action="{{ route('admin.guests.store') }}" method="POST" class="p-3">
        @csrf
        <h5 class="fw-semibold mb-3 text-primary">Tambah Guest Baru</h5>

        <div class="mb-3">
          <label class="form-label fw-semibold">Nama</label>
          <input type="text" name="name" class="form-control rounded-3" placeholder="Masukkan nama guest..." required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Phone</label>
          <input type="text" name="phone" class="form-control rounded-3" placeholder="Masukkan nomor telepon..." required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
          <button type="button" class="btn btn-outline-secondary px-3" @click="$dispatch('close-modal', 'createGuestModal')">Batal</button>
          <button type="submit" class="btn btn-primary px-3">Simpan</button>
        </div>
      </form>
    </x-modal>

    <!-- === TABEL DATA GUEST === -->
    <div class="card-body bg-light p-4">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white shadow-sm rounded-3">
          <thead class="bg-primary text-white text-center">
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Phone</th>
              <th>Description</th>
              <th>Dibuat Oleh</th>
              <th>Dibuat Pada</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($guests as $guest)
              <tr>
                <td class="text-center fw-semibold">{{ $loop->iteration }}</td>
                <td class="fw-semibold text-dark">{{ $guest->name }}</td>
                <td>{{ $guest->phone }}</td>
                <td>{{ $guest->description ?? '-' }}</td>
                <td>{{ $guest->creator?->name ?? '-' }}</td>
                <td>{{ $guest->created_at->format('d-m-Y H:i') }}</td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <!-- Tombol Edit -->
                    <button
                      class="btn btn-sm btn-outline-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#editGuestModal{{ $guest->id }}">
                      <i class="ri-edit-2-line"></i> Edit
                    </button>

                    <!-- Tombol Pilih Produk -->
                    <a href="{{ route('admin.produk.byGuest', $guest->id) }}"
                       class="btn btn-sm btn-info px-3 fw-semibold text-white">
                      Pilih Produk
                    </a>
                  </div>
                </td>
              </tr>

              <!-- Modal Edit Guest -->
              <div class="modal fade" id="editGuestModal{{ $guest->id }}" tabindex="-1" aria-labelledby="editGuestLabel{{ $guest->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content border-0 shadow-lg rounded-3">

                    <div class="modal-header bg-primary text-white">
                      <h5 class="modal-title fw-semibold" id="editGuestLabel{{ $guest->id }}">
                        ✏️ Edit Data Guest
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('admin.guests.update', $guest->id) }}" method="POST">
                      @csrf
                      @method('PUT')

                      <div class="modal-body bg-light">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama</label>
                            <input type="text" name="name" class="form-control form-control-lg rounded-3" value="{{ $guest->name }}" required>
                          </div>

                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control form-control-lg rounded-3" value="{{ $guest->phone }}" required>
                          </div>

                          <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Keterangan tambahan...">{{ $guest->description }}</textarea>
                          </div>
                        </div>
                      </div>

                      <div class="modal-footer bg-white mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">💾 Simpan Perubahan</button>
                      </div>
                    </form>

                  </div>
                </div>
              </div>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">Belum ada data guest</td>
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
  /* 🌐 Background Halus */
  body {
    background-color: #f5f7fb !important;
  }

  /* 🌟 Smooth Fade Effect */
  .smooth-fade {
    animation: smoothFade 0.8s ease;
  }

  @keyframes smoothFade {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* 🧭 Breadcrumb */
  .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    margin: 0 6px;
  }

  .breadcrumb-icon {
    transition: all 0.3s ease;
  }
  .breadcrumb-icon:hover {
    transform: scale(1.1);
    background-color: #e9efff;
  }

  /* 🧾 Table */
  .table-hover tbody tr:hover {
    background-color: #f0f8ff !important;
    transition: 0.2s ease;
  }

  /* ✨ Buttons */
  .btn {
    transition: all 0.25s ease;
  }
  .btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
  }

  /* 📱 Responsiveness */
  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    h4, h5 { font-size: 1.1rem; }
    .table { font-size: 0.9rem; }
    .btn-sm { padding: 0.4rem 0.75rem; }
    .modal-dialog { max-width: 95%; }
  }
</style>
@endpush
