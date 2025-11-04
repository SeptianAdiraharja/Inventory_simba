@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ===================== --}}
  {{-- 🧭 MODERN BREADCRUMB (SAMA DESAIN SEMUA HALAMAN) --}}
  {{-- ===================== --}}
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
            Daftar Permintaan Barang
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


  {{-- ===================== --}}
  {{-- 📦 DAFTAR PERMINTAAN --}}
  {{-- ===================== --}}
  <div class="card shadow-sm border-0 rounded-4 smooth-card animate__animated animate__fadeInUp">
    <div class="card-header bg-primary border-0 d-flex justify-content-between align-items-center px-4 py-3 rounded-top-4">
      <h5 class="m-0 fw-semibold text-white">
        <i class="bi bi-list-check me-2"></i> Daftar Permintaan Barang
      </h5>
      <button class="btn btn-sm btn-outline-light rounded-pill px-3 text-white" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
      </button>
    </div>

    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-center align-middle border-bottom">
          <tr class="text-secondary small">
            <th style="width: 50px;">No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Status</th>
            <th>Jumlah Barang</th>
            <th style="width: 150px;">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @forelse($requests as $index => $req)
          <tr id="cart-row-{{ $req->cart_id }}">
            <td class="text-center text-muted">{{ $requests->firstItem() + $index }}</td>

            <td>
              <strong class="text-dark">{{ $req->name }}</strong><br>
              <small class="text-muted">
                Diajukan: {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') }}
              </small>
            </td>

            <td class="text-muted">{{ $req->email }}</td>

            <td class="text-center">
              <span id="main-status-{{ $req->cart_id }}"
                class="badge rounded-pill px-3 py-2
                  @if($req->status == 'pending') bg-warning text-dark
                  @elseif($req->status == 'rejected') bg-danger
                  @elseif($req->status == 'approved') bg-success
                  @elseif($req->status == 'approved_partially') bg-warning text-dark
                  @endif">
                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
              </span>
            </td>

            <td class="text-center fw-semibold text-dark">
              {{ $req->total_quantity }}
            </td>

            <td class="text-center">
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary rounded-pill dropdown-toggle"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-gear me-1"></i> Opsi
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                  <li>
                    <a class="dropdown-item detail-toggle-btn" href="#"
                       data-cart-id="{{ $req->cart_id }}">
                      <i class="bi bi-eye me-2 text-primary"></i> Lihat Semua Barang
                    </a>
                  </li>

                  @php
                    $isDisabled = in_array($req->status, ['approved', 'approved_partially', 'rejected']);
                  @endphp

                  <li>
                    <a class="dropdown-item approve-all-btn text-success {{ $isDisabled ? 'disabled opacity-50' : '' }}"
                       href="#"
                       data-cart-id="{{ $req->cart_id }}"
                       @if($isDisabled) tabindex="-1" aria-disabled="true" @endif>
                      <i class="bi bi-check-circle me-2"></i> Setujui Semua
                    </a>
                  </li>

                  <li>
                    <a class="dropdown-item reject-all-btn text-danger {{ $isDisabled ? 'disabled opacity-50' : '' }}"
                       href="#"
                       data-cart-id="{{ $req->cart_id }}"
                       @if($isDisabled) tabindex="-1" aria-disabled="true" @endif>
                      <i class="bi bi-x-octagon me-2"></i> Tolak Semua
                    </a>
                  </li>
                </ul>
              </div>
            </td>
          </tr>

          {{-- DETAIL ROW --}}
          <tr class="collapse-row">
            <td colspan="7" class="p-0">
              <div id="detail-content-{{ $req->cart_id }}"
                   class="detail-content-wrapper collapse bg-light border-top"
                   data-cart-id="{{ $req->cart_id }}" data-loaded="false">
                <p class="text-center text-muted m-0 p-3">
                  Klik “Lihat Semua Barang” untuk membuka detail.
                </p>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center py-5">
              <div class="text-muted">
                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                <p class="mb-1 fw-semibold">Belum ada permintaan dengan status ini.</p>
                <small>Coba ubah filter untuk melihat data lainnya.</small>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4 d-flex justify-content-center">
    {{ $requests->links('pagination::bootstrap-5') }}
  </div>
</div>


{{-- 🟥 MODAL: TOLAK BARANG --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-3">
      <div class="modal-header bg-danger text-white rounded-top-3">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-x-circle me-2"></i> Alasan Penolakan Barang
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="rejectItemForm" method="POST"
            data-is-bulk="false"
            data-cart-id=""
            data-item-id="">
        @csrf

        <div class="modal-body">
          <label class="form-label fw-semibold text-secondary">Tuliskan alasan penolakan:</label>
          <textarea name="reason" class="form-control rounded-3" rows="3"
                    placeholder="Contoh: Barang tidak tersedia, data tidak valid..." required></textarea>
        </div>

        <div class="modal-footer bg-light border-top-0 rounded-bottom-3">
          <button type="button" class="btn btn-secondary rounded-pill px-3 mt-5" data-bs-dismiss="modal">
            Batal
          </button>
          <button type="submit" class="btn btn-danger rounded-pill px-3 mt-5">
            Tolak Barang
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- SCRIPT --}}
@push('scripts')
<script src="{{ asset('js/admin-request.js') }}"></script>
@endpush

{{-- STYLE --}}
@push('styles')
<style>
  body {
    background-color: #f4f6f9;
  }

  .smooth-fade {
    animation: smoothFade 0.8s ease;
  }

  @keyframes smoothFade {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    margin: 0 6px;
  }

  .breadcrumb-icon:hover {
    transform: scale(1.1);
    background-color: #e8f0fe;
    transition: 0.3s ease;
  }

  .smooth-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
  }

  .table-hover tbody tr:hover {
    background-color: #f0f8ff !important;
  }

  .btn-outline-primary:hover {
    background-color: #0d6efd;
    color: #fff;
    transition: 0.3s;
  }

  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    h5 { font-size: 1.05rem; }
    .table { font-size: 0.9rem; }
  }
</style>
@endpush
@endsection
