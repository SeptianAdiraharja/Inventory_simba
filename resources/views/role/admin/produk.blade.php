@extends('layouts.index')
@section('content')

<!-- Icon Cart -->
<div class="d-flex justify-content-end mb-3">
    <a href="javascript:void(0)"
       class="btn btn-outline-primary position-relative"
       id="openCartModal"
       data-guest-id="{{ $guest->id ?? '' }}">
        <i class="ri ri-shopping-cart-2-line fs-3"></i>
        @if(isset($cartItems) && $cartItems->count() > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $cartItems->count() }}
            </span>
        @endif
    </a>
</div>

<!-- Daftar Produk -->
<div class="row gy-4">
    @foreach ($items as $item)
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
        <div class="card h-100 shadow-sm border-0">
            <img src="{{ asset('storage/'. $item->image) }}"
                 class="card-img-top"
                 alt="{{ $item->name }}"
                 style="height: 200px; object-fit: cover;">

            <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-1">{{ $item->name }}</h5>
                <p class="card-text text-muted mb-2">
                    Kategori: <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
                </p>
                <small class="mt-2 text-muted">Stok: {{ $item->stock }}</small>

                <!-- Tombol trigger modal scan -->
                <button type="button"
                        class="btn btn-sm btn-primary mt-2"
                        data-bs-toggle="modal"
                        data-bs-target="#scanModal-{{ $item->id }}"
                        {{ $item->stock == 0 ? 'disabled' : '' }}>
                    <i class="ri-shopping-cart-2-line"></i> Keluarkan
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Scan Item -->
    <div class="modal fade" id="scanModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-{{ $item->id }}"
                    action="{{ route('admin.produk.scan', $guest->id ?? 0) }}"
                    method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Barang Keluar: {{ $item->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="guest_id" value="{{ $guest->id ?? '' }}">
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <input type="hidden" name="quantity" value="1">

                        <div class="mb-3">
                            <label class="form-label">Scan Barcode</label>
                            <input id="barcode-{{ $item->id }}"
                                   type="text"
                                   name="barcode"
                                   class="form-control"
                                   placeholder="Scan barcode di sini"
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Modal Cart -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Keranjang Guest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Kode</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <tr><td colspan="3" class="text-center text-muted">Keranjang kosong</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <form id="releaseForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Keluarkan Semua</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Fokus input barcode setiap buka modal scan
    @foreach ($items as $item)
    const input{{ $item->id }} = document.getElementById("barcode-{{ $item->id }}");
    $('#scanModal-{{ $item->id }}').on('shown.bs.modal', function () {
        input{{ $item->id }}.focus();
    });
    input{{ $item->id }}.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            document.getElementById("form-{{ $item->id }}").submit();
        }
    });
    @endforeach

    // === Modal Cart ===
    const openCartBtn = document.getElementById("openCartModal");
    const cartTableBody = document.getElementById("cartTableBody");
    const releaseForm = document.getElementById("releaseForm");
    const cartModalEl = document.getElementById("cartModal");
    const cartModal = new bootstrap.Modal(cartModalEl);

    if(openCartBtn){
        openCartBtn.addEventListener("click", function(){
            const guestId = this.dataset.guestId;
            if(!guestId) return;

            fetch(`/admin/produk/guest/${guestId}/cart`)
                .then(res => res.json())
                .then(data => {
                    cartTableBody.innerHTML = "";
                    if(data.cartItems.length > 0){
                        data.cartItems.forEach(item => {
                            cartTableBody.innerHTML += `
                                <tr>
                                    <td>${item.name}</td>
                                    <td>${item.code ?? '-'}</td>
                                    <td>${item.quantity}</td>
                                </tr>
                            `;
                        });
                    } else {
                        cartTableBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Keranjang kosong</td></tr>`;
                    }

                    // Update form release
                    releaseForm.action = `/admin/produk/guest/${guestId}/release`;

                    // Buka modal
                    cartModal.show();
                });
        });
    }

    // === Tombol Keluarkan Semua (AJAX) ===
    releaseForm.addEventListener("submit", function(e){
        e.preventDefault(); // cegah reload halaman
        const url = this.action;

        fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": this.querySelector('input[name="_token"]').value
            }
        })
        .then(res => {
            if (!res.ok) throw new Error("Gagal memproses permintaan");
            return res.text(); // server redirect → abaikan isi
        })
        .then(() => {
            // ✅ Kosongkan tabel cart
            cartTableBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Keranjang kosong</td></tr>`;

            // ✅ Hilangkan badge notifikasi cart
            const badge = openCartBtn.querySelector(".badge");
            if (badge) badge.remove();

            // ✅ Tutup modal
            cartModal.hide();

            // ✅ Opsional: tampilkan alert kecil sukses
            const alert = document.createElement('div');
            alert.className = "alert alert-success alert-dismissible fade show mt-3";
            alert.innerHTML = `
                Barang berhasil dikeluarkan semua.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector(".container-fluid")?.prepend(alert);
        })
        .catch(err => {
            alert("Terjadi kesalahan: " + err.message);
        });
    });
});
</script>
@endpush
