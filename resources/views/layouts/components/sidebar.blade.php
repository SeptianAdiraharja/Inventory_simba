<aside id="layout-menu" class="layout-menu menu-vertical bg-dark d-flex flex-column">
    <!-- Logo & Brand -->
    <div class="app-brand demo py-3 d-flex align-items-center">
        <a href="index.html" class="app-brand-link d-flex align-items-center">
            <img src="{{ asset('assets/img/icons/simba.jpg') }}" alt="Logo" class="rounded-circle shadow-glow" width="50" height="50">
            <h4 class="app-brand-text fw-bold ms-3 mt-4 text-white text-glow">SIMBA</h4>
        </a>
    </div>
    <small class="d-block text-center text-light mb-3">Sistem Informasi Barang Dan Aset</small>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-2 text-white flex-grow-1">
        <!-- Semua menu kamu tetap sama -->
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

        @php
            use App\Models\Cart;
            $pendingCount = Cart::where('status', 'pending')->count();
            $approvedCount = Cart::whereIn('status', ['approved', 'approved_partially'])
                ->whereHas('user', function ($u) { $u->where('role', 'pegawai'); })
                ->whereHas('cartItems', function ($q) { $q->whereNull('scanned_at'); })
                ->whereDoesntHave('cartItems', function ($q) { $q->whereNotNull('scanned_at'); }, '=', 0)
                ->count();
        @endphp

        @if (auth()->user()->role === 'pegawai')
        <li class="menu-item {{ Route::is('pegawai.dashboard') ? 'active' : '' }}">
            <a href="{{ route('pegawai.dashboard') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-dashboard-line me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('pegawai.produk') ? 'active' : '' }}">
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
                <i class="ri ri-history-line me-2"></i>
                <span>Riwayat Permintaan</span>
            </a>
        </li>
        @endif

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

        @if (auth()->user()->role === 'admin')
        <li class="menu-header mt-4 text-uppercase small fw-bold text-secondary">Admin</li>
        <li class="menu-item {{ Route::is('admin.request') ? 'active' : '' }}">
            <a href="{{ route('admin.request') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-file-list-3-line me-2"></i>
                <span>Request</span>
                @if($pendingCount > 0)
                    <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.itemout.*') ? 'active' : '' }}">
            <a href="{{ route('admin.itemout.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-qr-scan-2-line me-2"></i>
                <span>ScanQr</span>
                @if($approvedCount > 0)
                    <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-success">{{ $approvedCount }}</span>
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

        <li class="menu-item {{ Route::is('admin.export.out') ? 'active' : '' }}">
            <a href="{{ route('admin.export.out') }}" class="menu-link d-flex align-items-center text-white">
                <i class="ri ri-download-2-line me-2"></i>
                <span>Export Barang Keluar</span>
            </a>
        </li>

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

    <!-- Waktu Server -->
    <div class="text-center py-3 text-white border-top border-secondary fw-bold">
        <i class="ri ri-time-line me-1"></i>
        <span id="server-time">Memuat waktu...</span>
    </div>
</aside>

<!-- CSS Glow & Animasi -->
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
        box-shadow: 0 0 10px #0d6efd;
    }
    .text-glow {
        text-shadow: 0 0 8px rgba(0, 136, 255, 0.8);
    }
    .shadow-glow {
        box-shadow: 0 0 15px rgba(0, 136, 255, 0.6);
    }
</style>

<!-- JS Waktu Server Real-Time -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const serverTime = new Date("{{ now()->format('Y-m-d H:i:s') }}");
        const timeDisplay = document.getElementById("server-time");

        setInterval(() => {
            serverTime.setSeconds(serverTime.getSeconds() + 1);
            const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

            const dayName = days[serverTime.getDay()];
            const day = serverTime.getDate().toString().padStart(2, '0');
            const month = months[serverTime.getMonth()];
            const year = serverTime.getFullYear();
            const hours = serverTime.getHours().toString().padStart(2, '0');
            const minutes = serverTime.getMinutes().toString().padStart(2, '0');
            const seconds = serverTime.getSeconds().toString().padStart(2, '0');

            timeDisplay.textContent = `${dayName}, ${day} ${month} ${year} - ${hours}:${minutes}:${seconds}`;
        }, 1000);
    });
</script>
