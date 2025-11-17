<aside id="layout-menu" class="layout-menu menu-vertical bg-dark d-flex flex-column">
    <!-- 🔶 Logo & Brand -->
    <div class="app-brand demo py-3 d-flex align-items-center">
        <a href="index.html" class="app-brand-link d-flex align-items-center">
            <img src="{{ asset('assets/img/icons/simba.jpg') }}" alt="Logo" class="rounded-circle shadow-glow" width="50" height="50">
            <h4 class="app-brand-text fw-bold ms-3 mt-4 text-white text-glow">SIMBA</h4>
        </a>
    </div>
    <small class="d-block text-center text-light mb-3">Sistem Informasi Manajemen Barang</small>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-2 text-white flex-grow-1">
        <!-- SUPER ADMIN -->
        @if (auth()->user()->role === 'super_admin')
        <li class="menu-item {{ Route::is('super_admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('super_admin.dashboard') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-dashboard-line me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endif

        <!-- ADMIN -->
        @if (auth()->user()->role === 'admin')
        <li class="menu-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link d-flex align-items-center text-white position-relative">
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

        <!-- PEGAWAI -->
        @if (auth()->user()->role === 'pegawai')
        <li class="menu-item {{ Route::is('pegawai.dashboard') ? 'active' : '' }}">
            <a href="{{ route('pegawai.dashboard') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-dashboard-line me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('pegawai.produk') ? 'active' : '' }}">
            <a href="{{ route('pegawai.produk') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-shopping-cart-line me-2"></i>
                <span>Produk</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('pegawai.permintaan.pending') ? 'active' : '' }}">
            <a href="{{ route('pegawai.permintaan.pending') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-time-line me-2"></i>
                <span>Permintaan Pending</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('pegawai.permintaan.history') ? 'active' : '' }}">
            <a href="{{ route('pegawai.permintaan.history') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-history-line me-2"></i>
                <span>Riwayat Permintaan</span>
            </a>
        </li>
        @endif

        <!-- SUPER ADMIN MENU -->
        @if (auth()->user()->role === 'super_admin')
        <li class="menu-header mt-4 text-uppercase small fw-bold text-secondary">Super Admin</li>
        <li class="menu-item {{ Route::is('super_admin.categories.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.categories.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-stack-line me-2"></i>
                <span>Kategori</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.units.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.units.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-price-tag-3-line me-2"></i>
                <span>Satuan Barang</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.suppliers.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.suppliers.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-briefcase-3-line me-2"></i>
                <span>Supplier</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.items.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.items.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-box-3-line me-2"></i>
                <span>Barang</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.item_ins.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.item_ins.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-inbox-archive-line me-2"></i>
                <span>Barang Masuk</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('super_admin.users.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-group-line me-2"></i>
                <span>List Pengguna</span>
            </a>
        </li>
        <li class="menu-item {{ Route::is('super_admin.export.index') ? 'active' : '' }}">
            <a href="{{ route('super_admin.export.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-download-2-line me-2"></i>
                <span>Ekspor Data</span>
            </a>
        </li>
        @endif

        <!-- ADMIN MENU -->
        @if (auth()->user()->role === 'admin')
        <li class="menu-header mt-4 text-uppercase small fw-bold text-secondary">Admin</li>

        <li class="menu-item {{ Route::is('admin.request') ? 'active' : '' }}">
            <a href="{{ route('admin.request') }}" class="menu-link d-flex align-items-center justify-content-between text-white position-relative">
                <div class="d-flex align-items-center">
                    <i class="ri ri-file-list-3-line me-2"></i>
                    <span>Request</span>
                </div>
                @if($pendingCount > 0)
                    <span class="badge rounded-pill bg-danger ms-2">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.itemout.*') ? 'active' : '' }}">
            <a href="{{ route('admin.itemout.index') }}" class="menu-link d-flex align-items-center justify-content-between text-white position-relative">
                <div class="d-flex align-items-center">
                    <i class="ri ri-qr-scan-2-line me-2"></i>
                    <span>ScanQr</span>
                </div>
                @if($approvedCount > 0)
                    <span class="badge rounded-pill bg-success ms-2">{{ $approvedCount }}</span>
                @endif
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.guests.index') ? 'active' : '' }}">
            <a href="{{ route('admin.guests.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-user-line me-2"></i>
                <span>List Guest</span>
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.pegawai.index') ? 'active' : '' }}">
            <a href="{{ route('admin.pegawai.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-user-line me-2"></i>
                <span>List Pegawai</span>
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.export.out') ? 'active' : '' }}">
            <a href="{{ route('admin.export.out') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-download-2-line me-2"></i>
                <span>Export Barang Keluar</span>
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.transaksi.out') ? 'active' : '' }}">
            <a href="{{ route('admin.transaksi.out') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="bi-pencil-square me-2"></i>
                <span>Data Transaksi</span>
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.rejects.scan') ? 'active' : '' }}">
            <a href="{{ route('admin.rejects.scan') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-close-circle-line me-2"></i>
                <span>Barang Rusak / Reject</span>
            </a>
        </li>

        <li class="menu-item {{ Route::is('admin.rejects.index') ? 'active' : '' }}">
            <a href="{{ route('admin.rejects.index') }}" class="menu-link d-flex align-items-center text-white position-relative">
                <i class="ri ri-close-circle-line me-2"></i>
                <span>Data Barang Rusak / Reject</span>
            </a>
        </li>
        @endif
    </ul>

    <!-- 🕒 Waktu Server -->
    <div class="text-center py-3 text-white border-top border-secondary fw-bold">
        <i class="ri ri-time-line me-1"></i>
        <span id="server-time">Memuat waktu...</span>
    </div>
</aside>

<!-- 🧡 CSS Tema Gelap + Efek Gradient Oranye -->
<style>
    .bg-dark {
        background-color: #1a1a1a !important;
    }

    .menu-link {
        color: #f4f4f4 !important;
        padding: 12px 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        margin: 2px 8px;
    }

    /* Gradient efek hover */
    .menu-link:hover {
        background: linear-gradient(90deg, #FF8400 0%, #FFA500 100%) !important;
        color: #1a1a1a !important;
        transform: translateX(5px);
        box-shadow: 0 0 15px rgba(255, 132, 0, 0.5);
    }

    /* Active state dengan gradient */
    .menu-item.active > .menu-link,
    .menu-item.active .menu-link {
        background: linear-gradient(90deg, #FF8400 0%, #FFB84D 100%) !important;
        color: #1a1a1a !important;
        font-weight: 600;
        box-shadow: 0 0 18px rgba(255, 132, 0, 0.6);
        border-left: 4px solid #FFD699;
        transform: translateX(5px);
    }

    .menu-header {
        color: #FFB74D !important;
        letter-spacing: 0.5px;
        padding: 8px 15px;
        margin-top: 10px;
    }

    .shadow-glow {
        box-shadow: 0 0 10px rgba(255, 132, 0, 0.6);
    }

    .text-glow {
        text-shadow: 0 0 6px rgba(255, 132, 0, 0.8);
    }

    .text-light, .text-white {
        color: #f4f4f4 !important;
    }

    .border-secondary {
        border-color: #FF8400 !important;
    }

    /* Style untuk badge notifikasi */
    .menu-link .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
        min-width: 20px;
        text-align: center;
        font-weight: 600;
    }

    /* Pastikan icon dan teks sejajar */
    .menu-link div {
        display: flex;
        align-items: center;
        flex: 1;
    }
</style>

<!-- 🕐 Super Realtime Clock (Anti-Delay, Auto Sync) -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const serverTime = new Date("{{ now()->format('Y-m-d H:i:s') }}");
    const timeDisplay = document.getElementById("server-time");
    let baseTimestamp = serverTime.getTime();
    let baseLocal = Date.now();

    // Fungsi render waktu
    function renderTime() {
        const now = Date.now();
        const diff = now - baseLocal;
        const current = new Date(baseTimestamp + diff);

        const days = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
        const months = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"];

        const d = days[current.getDay()];
        const day = current.getDate().toString().padStart(2,'0');
        const month = months[current.getMonth()];
        const year = current.getFullYear();
        const h = current.getHours().toString().padStart(2,'0');
        const m = current.getMinutes().toString().padStart(2,'0');
        const s = current.getSeconds().toString().padStart(2,'0');

        timeDisplay.textContent = `${d}, ${day} ${month} ${year} - ${h}:${m}:${s}`;
        requestAnimationFrame(renderTime); // jalankan terus secara halus
    }

    // Auto resync jika tab kembali aktif
    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) {
            const newServerTime = new Date("{{ now()->format('Y-m-d H:i:s') }}");
            baseTimestamp = newServerTime.getTime();
            baseLocal = Date.now();
        }
    });

    renderTime(); // mulai
});
</script>
