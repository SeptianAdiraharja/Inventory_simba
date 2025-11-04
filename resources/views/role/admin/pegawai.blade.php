@extends('layouts.index')
@section('content')

<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB MODERN (SAMA SEPERTI HALAMAN LAIN) --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="breadcrumb-icon bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded-circle"
           style="width:38px;height:38px;">
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
            Daftar Pegawai
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

  {{-- 📋 CARD DAFTAR PEGAWAI --}}
  <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInUp smooth-card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3 px-4 rounded-top-4">
      <h5 class="card-title mb-0 fw-semibold d-flex align-items-center">
        <i class="bi bi-people-fill me-2 text-warning"></i> Daftar Pegawai
      </h5>
    </div>

    <div class="card-body bg-light p-4">
      <div class="table-responsive rounded-4 overflow-hidden">
        <table class="table table-hover align-middle bg-white shadow-sm mb-0">
          <thead class="text-center fw-semibold" style="background-color: #e9f2ff; color: #374151;">
            <tr>
              <th style="width: 60px;">No</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Dibuat Pada</th>
              <th style="width: 180px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pegawai as $index => $p)
              <tr class="text-center table-row-smooth">
                <td class="fw-medium text-secondary">{{ $index + 1 }}</td>
                <td class="fw-semibold text-dark">{{ $p->name }}</td>
                <td class="text-muted">{{ $p->email }}</td>
                <td class="text-secondary">{{ $p->created_at->format('d-m-Y H:i') }}</td>
                <td>
                  <a href="{{ route('admin.pegawai.produk', $p->id) }}"
                     class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-semibold smooth-btn">
                    🛍️ Pilih Produk
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4">
                  <div class="text-muted fs-5">
                    <i class="bi bi-info-circle fs-4 text-primary"></i>
                    <br>Belum ada data pegawai
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- 🎨 STYLE (DISESUAIKAN DENGAN HALAMAN LAIN) --}}
@push('styles')
<style>
  body {
    background-color: #f4f6f9 !important;
  }

  /* 🔹 Breadcrumb Consistent Style */
  .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    margin: 0 6px;
  }

  .breadcrumb-icon {
    transition: 0.3s ease;
  }

  .breadcrumb-icon:hover {
    transform: scale(1.1);
    background-color: #e8f0fe;
  }

  .smooth-fade {
    animation: smoothFade 0.8s ease;
  }

  @keyframes smoothFade {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* ✨ Card Hover */
  .smooth-card {
    transition: all 0.3s ease;
  }

  .smooth-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  }

  /* 📊 Table Hover */
  .table-row-smooth {
    transition: all 0.25s ease;
  }

  .table-row-smooth:hover {
    background-color: #f0f8ff !important;
    transform: scale(1.01);
  }

  /* 🔘 Button Smooth */
  .smooth-btn {
    transition: all 0.3s ease;
    border-color: #0d6efd;
    color: #0d6efd;
  }

  .smooth-btn:hover {
    background-color: #0d6efd;
    color: white !important;
    transform: scale(1.05);
    box-shadow: 0 3px 10px rgba(13, 110, 253, 0.3);
  }

  /* 📱 Responsiveness */
  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    h5, h6 { font-size: 1rem; }
    .table { font-size: 0.9rem; }
    .btn-sm { padding: 0.4rem 0.75rem; }
  }
</style>
@endpush

@endsection
