@extends('layouts.index')
@section('title', 'Daftar Permintaan Barang')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🔍 Info Pencarian --}}
  @if(isset($search) && $search)
  <div class="alert alert-warning border-0 shadow-sm rounded-3">
    <i class="bi bi-search me-1"></i> Menampilkan hasil pencarian untuk:
    <strong>"{{ $search }}"</strong>
    <a href="{{ route('admin.request') }}" class="float-end text-decoration-none fw-semibold" style="color:#FF9800;">
      <i class="bi bi-arrow-counterclockwise me-1"></i> Tampilkan semua
    </a>
  </div>
  @endif

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

  {{-- 📦 DAFTAR PERMINTAAN --}}
  <div class="card shadow-sm border-0 rounded-4 smooth-card animate__animated animate__fadeInUp">
    <div class="card-header border-0 d-flex justify-content-between align-items-center px-4 py-3 rounded-top-4"
         style="background-color:#FF9800;">
      <h5 class="m-0 fw-semibold text-white">
        <i class="bi bi-list-check me-2"></i> Daftar Permintaan Barang
      </h5>
      <button class="btn btn-sm btn-outline-light rounded-pill px-3 text-white fw-semibold" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
      </button>
    </div>

    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead style="background:#FFF3E0;" class="text-center align-middle border-bottom">
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
            <td class="text-center text-muted fw-semibold">{{ $requests->firstItem() + $index }}</td>

            <td>
              <strong class="text-dark">{{ $req->name }}</strong><br>
              <small class="text-muted">
                Diajukan: {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') }}
              </small>
            </td>

            <td class="text-muted">{{ $req->email }}</td>

            <td class="text-center">
              <span id="main-status-{{ $req->cart_id }}"
                    class="badge rounded-pill px-3 py-2 fw-semibold
                    @if($req->status == 'pending') bg-warning text-dark
                    @elseif($req->status == 'rejected') bg-danger
                    @elseif($req->status == 'approved') bg-success
                    @elseif($req->status == 'approved_partially') bg-warning text-dark
                    @endif">
                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
              </span>
            </td>

            <td class="text-center fw-semibold text-dark">{{ $req->total_quantity }}</td>

            <td class="text-center">
              <div class="btn-group">
                <button class="btn btn-sm rounded-pill dropdown-toggle fw-semibold text-dark border"
                        style="border-color:#FF9800;"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-gear me-1" style="color:#FF9800;"></i> Opsi
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                  <li>
                    <a class="dropdown-item detail-toggle-btn fw-semibold" href="#"
                       data-cart-id="{{ $req->cart_id }}">
                      <i class="bi bi-eye me-2 text-warning"></i> Lihat Semua Barang
                    </a>
                  </li>
                </ul>
              </div>
            </td>
          </tr>

          {{-- DETAIL --}}
          <tr class="collapse-row">
            <td colspan="7" class="p-0">
              <div id="detail-content-{{ $req->cart_id }}"
                   class="detail-content-wrapper collapse bg-light border-top"
                   data-cart-id="{{ $req->cart_id }}" data-loaded="false">
                <p class="text-center text-muted m-0 p-3">
                  Klik "Lihat Semua Barang" untuk membuka detail.
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

  {{-- PAGINATION --}}
  <div class="mt-4 d-flex justify-content-center">
    {{ $requests->links('pagination::bootstrap-5') }}
  </div>
</div>

{{-- 🟥 MODAL TOLAK --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header text-white rounded-top-4" style="background-color:#FF7043;">
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
          <textarea name="reason" class="form-control rounded-3 shadow-sm border-0"
                    style="border-left:4px solid #FF9800;"
                    rows="3" placeholder="Contoh: Barang tidak tersedia, data tidak valid..." required></textarea>
        </div>

        <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
          <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">
            Batal
          </button>
          <button type="submit" class="btn rounded-pill px-3 text-white" style="background-color:#FF9800;">
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
  background-color: #fffaf4;
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
  color: #ffb74d;
  margin: 0 6px;
}
.breadcrumb-icon:hover {
  transform: scale(1.1);
  background-color: #ffecb3;
  transition: 0.3s ease;
}
.smooth-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(255, 152, 0, 0.2);
  transition: all 0.3s ease;
}
.table-hover tbody tr:hover {
  background-color: #fff3e0 !important;
}
.dropdown-menu .dropdown-item:hover {
  background-color: #fff8e1;
}
.btn-outline-primary:hover,
.btn-outline-light:hover {
  background-color: #FF9800;
  color: #fff !important;
  transition: 0.3s;
}
@media (max-width: 768px) {
  .breadcrumb-extra { display: none; }
  h5 { font-size: 1rem; }
  .table { font-size: 0.9rem; }
}
</style>
@endpush
@endsection
