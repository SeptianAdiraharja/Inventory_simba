<aside id="layout-menu" class="layout-menu menu-vertical bg-dark">
    <!-- Logo & Brand -->
    <div class="app-brand demo py-3 d-flex align-items-center">
        <a href="index.html" class="app-brand-link d-flex align-items-center">
            <img src="{{ asset('assets/img/icons/simba.jpg') }}" alt="Logo" class="rounded-circle" width="50" height="50">
            <h4 class="app-brand-text fw-bold ms-3 mt-4 text-white">SIMBA</h4>
        </a>
    </div>
    <small class="d-block text-center text-light mb-3">Sistem Informasi Barang Dan Aset</small>

    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-2 text-white">

        <!-- Dashboard -->
        @if (auth()->user()->role === 'super_admin')
        <li class="menu-item {{ Route::is('super_admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('super_admin.dashboard') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-dashboard-line me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endif

        @if (auth()->user()->role === 'admin')
        <li class="menu-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-dashboard-line me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endif

        {{-- Hitung jumlah permintaan pending (notif real) --}}
        @php
            use App\Models\Cart;

            // 🔸 Jumlah permintaan pending (Request)
            $pendingCount = Cart::where('status', 'pending')->count();

            // 🔸 Jumlah cart "approved" untuk ditampilkan di menu ScanQr (khusus Pegawai)
            $approvedCount = Cart::whereIn('status', ['approved', 'approved_partially'])
            ->whereHas('user', function ($u) {
                $u->where('role', 'pegawai');
            })
            ->whereHas('cartItems', function ($q) {
                $q->whereNull('scanned_at');
            })
            ->whereDoesntHave('cartItems', function ($q) {
                $q->whereNotNull('scanned_at');
            }, '=', 0) // pastikan bukan cart yang semua itemnya sudah discan
            ->count();
        @endphp

        <!-- Pegawai -->
        @if (auth()->user()->role === 'pegawai')
        <li class="menu-item {{ Route::is('pegawai.dashboard') ? 'active' : '' }}">
            <a href="{{ route('pegawai.dashboard') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-dashboard-line me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('pegawai.produk') ? 'active' : '' }} {{ Route::is('pegawai.produk.search') ? 'active' : '' }}">
            <a href="{{ route('pegawai.produk') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-shopping-cart-line me-2"></i>
                <span>Produk</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('pegawai.permintaan.pending') ? 'active' : '' }}">
            <a href="{{ route('pegawai.permintaan.pending') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-time-line me-2"></i>
                <span>Permintaan Pending</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('pegawai.permintaan.history') ? 'active' : '' }}">
            <a href="{{ route('pegawai.permintaan.history') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-time-line me-2"></i>
                <span>Riwayat Permintaan</span>
            </a>
        </li>
        @endif

        <!-- Super Admin -->
        @if (auth()->user()->role === 'super_admin')
        <li class="menu-header mt-4 text-uppercase small fw-bold text-secondary">Super Admin</li>
        <li class="menu-item {{ Route::is('super_admin.categories.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.categories.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-stack-line me-2"></i>
                <span>Kategori</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.units.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.units.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-price-tag-3-line me-2"></i>
                <span>Satuan Barang</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.suppliers.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.suppliers.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-briefcase-3-line me-2"></i>
                <span>Supplier</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.items.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.items.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-box-3-line me-2"></i>
                <span>Barang</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.item_ins.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.item_ins.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-inbox-archive-line me-2"></i>
                <span>Barang Masuk</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.users.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-group-line me-2"></i>
                <span>List Pengguna</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.export.index') ? 'active' : '' }}">
            <a href="{{ route('super_admin.export.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-download-2-line me-2"></i>
                <span>Ekspor Data</span>
            </a>
        </li>
        @endif

        <!-- Admin -->
        @if (auth()->user()->role === 'admin')
        <li class="menu-header mt-4 text-uppercase small fw-bold text-secondary">Admin</li>

        <li class="menu-item {{ Route::is('admin.request') ? 'active' : '' }}">
            <a href="{{ route('admin.request') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-file-list-3-line me-2"></i>
                <span>Request</span>
                @if($pendingCount > 0)
                    <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.itemout.*') ? 'active' : '' }}">
            <a href="{{ route('admin.itemout.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-qr-scan-2-line me-2"></i>
                <span>ScanQr</span>

                {{-- 🔔 Tampilkan badge jika ada cart approved --}}
                @if($approvedCount > 0)
                    <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-success">
                        {{ $approvedCount }}
                    </span>
                @endif
            </a>
        </li>


        <li class="menu-item {{ Route::is('admin.guests.index') ? 'active' : '' }}">
            <a href="{{ route('admin.guests.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-user-line me-2"></i>
                <span>Guest</span>
            </a>
        </li>

         <li class="menu-item {{ Route::is('admin.pegawai.index') ? 'active' : '' }}">
            <a href="{{ route('admin.pegawai.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-user-line me-2"></i>
                <span>Pegawai</span>
            </a>
        </li>

        {{-- ✅ Menu baru: Export Barang Keluar --}}
        <li class="menu-item {{ Route::is('admin.export.out') ? 'active' : '' }}">
            <a href="{{ route('admin.export.out') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-download-2-line me-2"></i>
                <span>Export Barang Keluar</span>
            </a>
        </li>

        {{-- ✅ Menu baru: Transaksi Keluar --}}
        <li class="menu-item {{ Route::is('admin.transaksi.out') ? 'active' : '' }}">
            <a href="{{ route('admin.transaksi.out') }}" class="menu-link d-flex align-items-center text-white">
                <i class="bi-pencil-square me-2"></i>
                <span>Data Transaksi</span>
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.rejects.scan') ? 'active' : '' }}">
            <a href="{{ route('admin.rejects.scan') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-close-circle-line me-2"></i>
                <span>Barang Rusak / Reject</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('admin.rejects.index') ? 'active' : '' }}">
            <a href="{{ route('admin.rejects.index') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-close-circle-line me-2"></i>
                <span>Data Barang Rusak / Reject</span>
            </a>
        </li>
        @endif
    </ul>
</aside>

<!-- Custom CSS -->
<style>
    .menu-link {
        color: #fff !important;
        padding: 10px 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .menu-link:hover {
        background-color: #fff !important;
        color: #000 !important;
    }

    .menu-link:hover i {
        color: #000 !important;
    }

    .menu-item.active > .menu-link {
        background-color: #0d6efd !important;
        color: #fff !important;
    }

    .menu-item.active > .menu-link i {
        color: #fff !important;
    }
</style>