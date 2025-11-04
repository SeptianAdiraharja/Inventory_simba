@extends('layouts.index')

@section('content')
<div class="container-fluid py-3 animate__animated animate__fadeIn">

  @if(isset($search) && $search)
      <div class="alert alert-info">
          Menampilkan hasil pencarian untuk: "<strong>{{ $search }}</strong>"
          <a href="{{ route('admin.pegawai.produk') }}" class="float-end">Tampilkan semua</a>
      </div>
  @endif

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-primary mb-0">
      <i class="ri-store-2-line me-2"></i> Produk Pegawai: {{ $pegawai->name }}
    </h4>
    <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
      <i class="ri-arrow-left-line me-1"></i> Kembali
    </a>
  </div>

  {{-- Daftar Produk --}}
  <div class="row gy-4">
    @foreach ($items as $item)
    <div class="col-xl-3 col-lg-4 col-md-6">
      <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden hover-scale" data-item-id="{{ $item->id }}">
        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
             class="card-img-top" style="height: 180px; object-fit: cover;">
        <div class="card-body">
          <h5 class="fw-semibold mb-1 text-dark">{{ $item->name }}</h5>
          <p class="text-muted small mb-1">
            <i class="ri-folder-line me-1"></i> Kategori:
            <span class="fw-semibold text-secondary">{{ $item->category->name ?? '-' }}</span>
          </p>
          <p class="text-muted small mb-3">
            <i class="ri-archive-line me-1"></i> Stok:
            <span class="{{ $item->stock > 0 ? 'text-success' : 'text-danger fw-bold' }}">
              {{ $item->stock }}
            </span>
          </p>
          <button class="btn btn-primary w-100 rounded-pill py-2"
                  data-bs-toggle="modal"
                  data-bs-target="#scanModal"
                  data-item-id="{{ $item->id }}"
                  data-item-name="{{ $item->name }}">
            <i class="ri-scan-line me-1"></i> Scan Barang
          </button>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Tombol Cart (draggable) --}}
  <button class="btn btn-primary rounded-circle shadow-lg position-fixed position-relative drag-cart"
      id="cartButton"
      style="bottom: 25px; right: 25px; width: 65px; height: 65px; z-index: 1050; cursor: move;">
      <i class="ri-shopping-cart-2-line fs-3"></i>
      <span class="position-absolute badge rounded-pill bg-danger" id="cartBadge"
          style="top: -8px; right: -8px; display: none;">0</span>
  </button>
</div>

{{-- Modal Scan --}}
<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title">Scan Kode Barang</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="scanForm">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="item_id" id="item_id">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Barang</label>
            <input type="text" class="form-control rounded-3" id="item_name" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Barang</label>
            <input type="text" name="barcode" id="barcode" class="form-control rounded-3"
                   placeholder="Scan kode barang..." required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah</label>
            <input type="number" name="quantity" id="quantity" min="1"
                   class="form-control rounded-3" placeholder="Masukkan jumlah" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="ri-save-3-line me-1"></i> Simpan
          </button>
          <button class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Cart --}}
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title text-white"><i class="ri-shopping-basket-2-line me-2"></i>Keranjang Pegawai</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light">
        <div id="cartContent" class="table-responsive text-center text-muted py-3">
          Memuat data...
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success rounded-pill px-4" id="saveCartButton">
          <i class="ri-save-3-line me-1"></i> Simpan ke Item Out
        </button>
        <button class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

{{-- Config --}}
<script>
window.PegawaiApp = {
  id: {{ $pegawai->id }},
  routes: {
    scan: "{{ url('admin/pegawai/' . $pegawai->id . '/scan') }}",
    cart: "{{ url('admin/pegawai/' . $pegawai->id . '/cart') }}",
    saveCart: "{{ url('admin/pegawai/' . $pegawai->id . '/cart/save') }}",
    deleteItemBase: "{{ url('admin/pegawai/' . $pegawai->id . '/cart/item') }}"
  },
  csrf: "{{ csrf_token() }}"
};
</script>

{{-- Script utama --}}
<script src="{{ asset('js/admin-produk-pegawai.js') }}"></script>

{{-- Script draggable cart --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const cart = document.getElementById('cartButton');
  if (!cart) return;

  // ambil posisi dari localStorage
  const saved = JSON.parse(localStorage.getItem('cartPos'));
  if (saved) {
    cart.style.bottom = 'auto';
    cart.style.right = 'auto';
    cart.style.left = saved.x + 'px';
    cart.style.top = saved.y + 'px';
  }

  let isDragging = false, offsetX = 0, offsetY = 0;

  cart.addEventListener('mousedown', (e) => {
    isDragging = true;
    offsetX = e.clientX - cart.offsetLeft;
    offsetY = e.clientY - cart.offsetTop;
    cart.style.transition = 'none';
  });

  document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    const x = e.clientX - offsetX;
    const y = e.clientY - offsetY;
    cart.style.left = x + 'px';
    cart.style.top = y + 'px';
    cart.style.bottom = 'auto';
    cart.style.right = 'auto';
  });

  document.addEventListener('mouseup', () => {
    if (isDragging) {
      isDragging = false;
      cart.style.transition = 'all 0.3s ease';
      localStorage.setItem('cartPos', JSON.stringify({
        x: cart.offsetLeft,
        y: cart.offsetTop
      }));
    }
  });
});
</script>

@endsection
