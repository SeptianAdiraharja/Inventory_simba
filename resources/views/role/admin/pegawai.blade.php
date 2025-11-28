@extends('layouts.index')
@section('title', 'Daftar Pegawai')
@section('content')

{{-- 🎯 PENCARIAN ALERT --}}
@if(request('q'))
<div class="alert alert-warning border-0 shadow-sm rounded-3 mb-3">
    <i class="bi bi-search me-2"></i> Hasil pencarian untuk: <strong>{{ request('q') }}</strong>
</div>
@endif

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🧭 BREADCRUMB NAVIGATION --}}
    <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
        <div class="d-flex align-items-center flex-wrap gap-2">
            {{-- Breadcrumb Icon --}}
            <div class="breadcrumb-icon d-flex align-items-center justify-content-center rounded-circle" style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
                <i class="bi bi-house-door-fill fs-5"></i>
            </div>
            {{-- Breadcrumb Links --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
                            Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
                        Daftar Pegawai
                    </li>
                </ol>
            </nav>
        </div>
        {{-- Timestamp --}}
        <div class="breadcrumb-extra text-end">
            <small class="text-muted">
                <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
            </small>
        </div>
    </div>

    {{-- 📋 MAIN CONTENT CARD --}}
    <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInUp smooth-card">
        {{-- Card Header --}}
        <div class="card-header text-white d-flex justify-content-between align-items-center py-3 px-4 rounded-top-4" style="background-color:#FF9800;">
            <h5 class="card-title mb-0 fw-semibold d-flex align-items-center">
                <i class="bi bi-people-fill me-2 text-white"></i> Daftar Pegawai
            </h5>
        </div>

        {{-- Card Body --}}
        <div class="card-body bg-light p-4">
            <div class="table-responsive rounded-4 overflow-hidden">
                <table class="table table-hover align-middle bg-white shadow-sm mb-0">
                    {{-- Table Header --}}
                    <thead class="text-center fw-semibold" style="background-color:#FFF3E0;color:#5d4037;">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Dibuat Pada</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    {{-- Table Body --}}
                    <tbody>
                        @forelse($pegawai as $index => $p)
                        <tr class="text-center table-row-smooth">
                            <td class="fw-medium text-secondary">{{ $index + 1 }}</td>
                            <td class="fw-semibold text-dark">{{ $p->name }}</td>
                            <td class="text-muted">{{ $p->email }}</td>
                            <td class="text-secondary">{{ $p->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                {{-- Action Button --}}
                                <a href="{{ route('admin.pegawai.produk', $p->id) }}"
                                   class="btn btn-sm text-white px-3 rounded-pill fw-semibold smooth-btn"
                                   style="background-color:#FF9800;">
                                    🛍️ Pilih Produk
                                </a>
                            </td>
                        </tr>
                        @empty
                        {{-- Empty State --}}
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted fs-5">
                                    <i class="bi bi-info-circle fs-4" style="color:#FF9800;"></i>
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

{{-- 🎨 CUSTOM STYLES --}}
@push('styles')
<style>
    /*
    * GLOBAL STYLES
    * Warna tema oranye dengan variasi yang harmonis
    */

    body {
        background-color: #fffaf4 !important;
    }

    /*
    * BREADCRUMB STYLING
    * Navigasi dengan ikon dan timestamp
    */
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #ffb74d;
        margin: 0 6px;
    }

    .breadcrumb-icon {
        transition: 0.3s ease;
    }

    .breadcrumb-icon:hover {
        transform: scale(1.1);
        background-color: #ffecb3;
    }

    /*
    * ANIMATION STYLES
    * Transisi halus untuk elemen UI
    */
    .smooth-fade {
        animation: smoothFade 0.8s ease;
    }

    @keyframes smoothFade {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /*
    * CARD INTERACTIONS
    * Efek hover pada kartu utama
    */
    .smooth-card {
        transition: all 0.3s ease;
    }

    .smooth-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 152, 0, 0.25);
    }

    /*
    * TABLE ROW EFFECTS
    * Highlight baris tabel saat hover
    */
    .table-row-smooth {
        transition: all 0.25s ease;
    }

    .table-row-smooth:hover {
        background-color: #fff3e0 !important;
        transform: scale(1.01);
    }

    /*
    * BUTTON STYLING
    * Tombol dengan efek interaktif
    */
    .smooth-btn {
        transition: all 0.3s ease-in-out;
    }

    .smooth-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
        background-color: #fb8c00 !important;
    }

    /*
    * RESPONSIVE DESIGN
    * Penyesuaian untuk perangkat mobile
    */
    @media (max-width: 768px) {
        .breadcrumb-extra {
            display: none;
        }
        h5, h6 {
            font-size: 1rem;
        }
        .table {
            font-size: 0.9rem;
        }
        .btn-sm {
            padding: 0.4rem 0.75rem;
        }
    }
</style>
@endpush

@endsection