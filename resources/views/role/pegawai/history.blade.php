@extends('layouts.index')

@section('content')
<style>
/* =============================
   🍊 Compact Modern Orange UI (Soft Warm Theme)
   ============================= */
body {
  background-color: #f8f9fc !important;
}

/* Smooth Fade */
.smooth-fade { animation: fadeIn 0.45s ease-in-out; }
@keyframes fadeIn { from {opacity:0;transform:translateY(6px);} to {opacity:1;transform:translateY(0);} }

/* ===== Modern Breadcrumb ===== */
.breadcrumb-modern {
  background: #fff;
  border-radius: 12px;
  padding: 0.85rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.breadcrumb-modern .icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(243, 156, 18, 0.1);
  color: #f39c12;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.breadcrumb {
  background: transparent;
  margin: 0;
  font-size: 0.92rem;
}

.breadcrumb-item + .breadcrumb-item::before {
  content: "/";
  color: #b1b1b1;
  padding: 0 0.45rem;
}

.breadcrumb-item a {
  color: #f39c12;
  font-weight: 500;
  text-decoration: none;
}
.breadcrumb-item a:hover { color: #e67e22; }
.breadcrumb-item.active { color: #333; font-weight: 600; }

.breadcrumb-modern .text-muted {
  font-size: 0.88rem;
}

/* ===== Card Styling ===== */
.card {
  border: none;
  border-radius: 12px !important;
  box-shadow: 0 3px 14px rgba(0, 0, 0, 0.04);
  background: #fff;
}

.card-header {
  background: #fff;
  border-bottom: 1px solid #f1f1f1;
  padding: 0.9rem 1.25rem;
}

.card-header h5 {
  font-size: 1rem;
  font-weight: 600;
  color: #f39c12;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

/* ===== Filter Tabs ===== */
.nav-pills .nav-link {
  border-radius: 50px !important;
  font-weight: 500;
  font-size: 0.9rem;
  border: none;
  background: #fff;
  color: #f39c12;
  padding: 0.45rem 1rem;
  min-height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s ease;
  box-shadow: 0 2px 6px rgba(243, 156, 18, 0.05);
}

.nav-pills .nav-link.active {
  background-color: #f39c12 !important;
  color: #fff !important;
  box-shadow: 0 3px 10px rgba(243, 156, 18, 0.25);
}

.nav-pills .nav-link .badge {
  font-size: 0.75rem;
  font-weight: 600;
  margin-left: 0.4rem;
  border-radius: 50px;
  background: rgba(243, 156, 18, 0.1);
  color: #f39c12;
}

.nav-pills .nav-link.active .badge {
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
}

/* ===== Hover Card ===== */
.hover-card {
  background: #fff;
  border: 1px solid #fff3e0;
  border-radius: 12px;
  padding: 0.9rem 1.1rem;
  transition: all 0.25s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 16px rgba(243, 156, 18, 0.08);
}

.hover-card h6 {
  font-size: 0.95rem;
  font-weight: 600;
  color: #444;
}

.hover-card small {
  font-size: 0.85rem;
  color: #6c757d;
}

/* ===== Buttons & Badges ===== */
.btn {
  border-radius: 8px;
  font-size: 0.85rem;
  transition: all 0.2s ease;
}

.btn-outline-primary {
  border: 1px solid #ffd599;
  color: #f39c12;
}
.btn-outline-primary:hover {
  background: #f39c12;
  color: #fff;
}

.badge {
  font-size: 0.8rem;
  padding: 0.35rem 0.7rem;
  border-radius: 50px;
  font-weight: 600;
}

.badge.bg-success {
  background-color: #2ecc71 !important;
}

.badge.bg-warning {
  background-color: #f8c471 !important;
  color: #664d03 !important;
}

.badge.bg-danger {
  background-color: #e74c3c !important;
}

/* ===== Pagination Custom ===== */
.pagination .page-link {
  border: none;
  color: #f39c12;
  font-weight: 500;
  border-radius: 50px;
  margin: 0 3px;
  padding: 0.45rem 0.75rem;
  box-shadow: 0 2px 5px rgba(243, 156, 18, 0.1);
  background: #fff;
  transition: all 0.2s ease;
}

.pagination .page-item.active .page-link {
  background: #f39c12;
  color: #fff;
  box-shadow: 0 3px 8px rgba(243, 156, 18, 0.25);
}

.pagination .page-link:hover {
  background: rgba(243, 156, 18, 0.1);
  color: #e67e22;
}

.pagination .page-item.disabled .page-link {
  opacity: 0.6;
  cursor: not-allowed;
  box-shadow: none;
}

/* ===== Modal ===== */
.modal-content {
  border-radius: 16px;
  border: none;
  box-shadow: 0 5px 18px rgba(243, 156, 18, 0.25);
}
.modal-body { background: #fffaf2; }

/* ===== Responsive ===== */
@media (max-width:768px){
  .breadcrumb-modern{flex-direction:column;align-items:flex-start;gap:0.5rem;padding:0.8rem 1rem;}
  .card-header h5{font-size:0.95rem;}
  .hover-card{padding:0.8rem 1rem;}
}
</style>

{{-- ======================== --}}
{{-- 🧭 MODERN BREADCRUMB --}}
{{-- ======================== --}}
<div class="breadcrumb-modern smooth-fade mb-4">
  <div class="d-flex align-items-center gap-2">
    <div class="icon">
      <i class="bi bi-clock-history"></i>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('pegawai.dashboard') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Riwayat Permintaan</li>
      </ol>
    </nav>
  </div>
  <div class="text-end small text-muted">
    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ now()->format('d M Y, H:i') }}
  </div>
</div>

{{-- 📦 MAIN CONTENT --}}
<div class="container-fluid smooth-fade">
  <div class="card rounded-4 overflow-hidden">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
      <h5><i class="bi bi-clipboard-data"></i> Daftar Riwayat Permintaan Barang</h5>
    </div>

    {{-- Filter Tabs --}}
    <div class="card-body bg-light py-3 border-bottom">
      @php
        $statuses = [
            'all' => ['label' => 'Semua', 'count' => $statusCounts['all']],
            'pending' => ['label' => 'Pending', 'count' => $statusCounts['pending']],
            'approved' => ['label' => 'Approved', 'count' => $statusCounts['approved']],
            'rejected' => ['label' => 'Rejected', 'count' => $statusCounts['rejected']],
        ];
        $activeStatus = request('status') ?? 'all';
      @endphp
      <ul class="nav nav-pills gap-2 flex-wrap">
        @foreach($statuses as $key => $data)
          <li class="nav-item">
            <a href="{{ route('pegawai.permintaan.history', ['status' => $key]) }}"
               class="nav-link {{ $activeStatus == $key ? 'active' : '' }}">
              {{ $data['label'] }}
              <span class="badge">{{ $data['count'] }}</span>
            </a>
          </li>
        @endforeach
      </ul>
    </div>

    {{-- Riwayat List --}}
    <div class="card-body">
      @if($carts->isEmpty())
        <div class="text-center text-muted py-5">
          <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
          <p class="mb-0">Belum ada permintaan.</p>
        </div>
      @else
        <div class="d-flex flex-column gap-3">
          @foreach($carts as $cart)
            <div class="hover-card">
              <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                <div>
                  <h6>{{ $cart->created_at->format('d M Y') }}</h6>
                  <small><i class="bi bi-clock me-1"></i>{{ $cart->created_at->format('H:i') }} WIB</small>
                </div>
                <span class="badge {{ $cart->status == 'approved' ? 'bg-success' :
                      ($cart->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                  {{ ucfirst($cart->status) }}
                </span>
              </div>

              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted">
                  <i class="bi bi-box-seam me-1"></i>{{ $cart->cart_items_count }} Barang
                </div>
                <div class="mt-2 mt-md-0">
                  <button type="button" class="btn btn-sm btn-outline-primary btn-detail px-3" data-id="{{ $cart->id }}">
                    <i class="bi bi-eye me-1"></i> Detail
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- ✅ Pagination Custom dengan Filter Tetap --}}
        @if ($carts->hasPages())
          <nav class="mt-4 d-flex justify-content-center">
            <ul class="pagination pagination-sm mb-0">
              {{-- Previous --}}
              @if ($carts->onFirstPage())
                <li class="page-item disabled"><span class="page-link">‹</span></li>
              @else
                <li class="page-item">
                  <a class="page-link"
                     href="{{ $carts->previousPageUrl() . '&status=' . request('status', 'all') }}"
                     rel="prev">‹</a>
                </li>
              @endif

              {{-- Numbers --}}
              @foreach ($carts->getUrlRange(1, $carts->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $carts->currentPage() ? 'active' : '' }}">
                  <a class="page-link"
                     href="{{ $url . '&status=' . request('status', 'all') }}">{{ $page }}</a>
                </li>
              @endforeach

              {{-- Next --}}
              @if ($carts->hasMorePages())
                <li class="page-item">
                  <a class="page-link"
                     href="{{ $carts->nextPageUrl() . '&status=' . request('status', 'all') }}"
                     rel="next">›</a>
                </li>
              @else
                <li class="page-item disabled"><span class="page-link">›</span></li>
              @endif
            </ul>
          </nav>
        @endif
      @endif
    </div>
  </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="detailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-body p-0" id="detailContent">
        <div class="text-center py-5 text-muted" id="loadingState" style="display:none;">
          <i class="bi bi-arrow-repeat fs-1 mb-2 d-block opacity-75"></i>
          <p>Memuat detail permintaan...</p>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = new bootstrap.Modal(document.getElementById('detailModal'));
  const detailContent = document.getElementById('detailContent');
  const loadingState = document.getElementById('loadingState');

  document.querySelectorAll('.btn-detail').forEach(btn => {
    btn.addEventListener('click', () => {
      loadingState.style.display = 'block';
      detailContent.innerHTML = '';
      modal.show();
      fetch(`/pegawai/permintaan/${btn.dataset.id}/detail`)
        .then(res => res.text())
        .then(html => {
          loadingState.style.display = 'none';
          detailContent.innerHTML = html;
        })
        .catch(() => {
          loadingState.style.display = 'none';
          detailContent.innerHTML = `
            <div class="text-center text-danger py-5">
              <i class="bi bi-exclamation-triangle fs-1 mb-2 d-block"></i>
              <p>Gagal memuat data.</p>
            </div>`;
        });
    });
  });
});
</script>
@endpush
@endsection
