@auth
    @if(Auth::user()->role === 'pegawai' || Auth::user()->role === 'admin')
        @php
            $cartsitems = \App\Models\Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->with('cartItems.item')
                ->first();

            $cartexceptactive = \App\Models\Cart::withCount('cartItems')
                ->where('user_id', Auth::id())
                ->where('status', '!=', 'active');
            $notifications = \App\Models\Notification::where('user_id', Auth::id())
                ->where('status', 'unread')
                ->latest()
                ->take(5)
                ->get();

            $notifCount = $notifications->count();

            // 🔥 AMBIL KATEGORI BERDASARKAN KONTEKS HALAMAN
            $assignedCategories = collect();

            if (Auth::user()->role === 'pegawai') {
                // Untuk pegawai: ambil kategori yang di-assign ke user yang login
                $assignedCategories = Auth::user()->categories;
            } elseif (Auth::user()->role === 'admin' && request()->is('admin/pegawai/*/produk')) {
                // Untuk admin di halaman produk pegawai: ambil kategori yang di-assign ke pegawai yang dilihat
                $pegawaiId = request()->segment(3); // Ambil ID pegawai dari URL
                $pegawai = \App\Models\User::find($pegawaiId);
                if ($pegawai) {
                    $assignedCategories = $pegawai->categories;
                }
            } else {
                // Untuk admin di halaman lain: ambil semua kategori
                $assignedCategories = \App\Models\Category::all();
            }
        @endphp

        <!-- ... kode offcanvas cart yang sama ... -->
    @endif
@endauth


<!-- ========== Navbar ========== -->
@auth
    @if(Auth::user()->role === 'pegawai' || Auth::user()->role === 'admin')
        @php
            $cartsitems = \App\Models\Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->with('cartItems.item')
                ->first();

            $categories = \App\Models\Category::whereNotIn('name', ['Alat Dapur', 'Alat Kebersihan', 'Perabot', 'Peralatan Keamanan'])->get();
            $cartexceptactive = \App\Models\Cart::withCount('cartItems')
                ->where('user_id', Auth::id())
                ->where('status', '!=', 'active');
            $notifications = \App\Models\Notification::where('user_id', Auth::id())
                ->where('status', 'unread')
                ->latest()
                ->take(5)
                ->get();

            $notifCount = $notifications->count();
        @endphp
        <style>
            /* Override default offcanvas supaya lebih seperti floating panel */
            /* Styling offcanvas biar tampil floating */
            #offcanvasCart {
                position: fixed !important;
                right: 25px;
                bottom: 100px; /* biar ada jarak dari tombol cart */
                width: 400px;
                top: 200px;
                max-height: 85vh;
                border-radius: 20px;
                background-color: #fff;
                border: 1px solid #ddd;
                box-shadow: 0 8px 20px rgba(0,0,0,0.25);
                overflow: hidden;
                transition: all 0.25s ease-in-out;
                z-index: 1055; /* pastikan di atas navbar */
                backdrop-filter: blur(6px);
            }

            /* Hilangkan backdrop biar gak gelapin layar */
            .offcanvas-backdrop.show {
                display: none !important;
            }

            /* Animasi halus */
            .offcanvas-end {
                transform: translateX(100%) !important;
                opacity: 0;
            }
            .offcanvas-end.show {
                transform: translateX(0) !important;
                opacity: 1;
            }

        </style>

        <!-- ========== Offcanvas Cart ========== -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
            <div class="offcanvas-header justify-content-between">
                <h5 id="offcanvasCartLabel">Keranjang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body">
                @php
                    // cek user login dulu
                    $countThisWeek = 0;
                    if (\Illuminate\Support\Facades\Auth::check()) {
                        $now = \Carbon\Carbon::now('Asia/Jakarta');

                        // awal minggu = Senin
                        $daysToSubtract = ($now->dayOfWeek === \Carbon\Carbon::SUNDAY) ? 6 : $now->dayOfWeek - 1;
                        $startOfWeek = $now->copy()->subDays($daysToSubtract)->startOfDay();
                        $endOfWeek   = $startOfWeek->copy()->addDays(6)->endOfDay();

                        $countThisWeek = \App\Models\Cart::where('user_id', \Illuminate\Support\Facades\Auth::id())
                            ->whereIn('status', ['pending', 'approved'])
                            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->count();
                    }

                    $maxLimit = 5;
                    $progress = ($maxLimit > 0) ? ($countThisWeek / $maxLimit) * 100 : 0;
                    $isLimitReached = $countThisWeek >= $maxLimit;
                @endphp

                <div class="p-3 border rounded-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-semibold text-warning">
                            <i class="ri-calendar-line me-2"></i>Pengajuan Minggu Ini
                        </h6>
                        <span class="fw-bold text-warning">{{ $countThisWeek }}/5 kali</span>
                    </div>

                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar {{ $isLimitReached ? 'bg-danger' : 'bg-warning' }}"
                            role="progressbar"
                            style="width: {{ $progress }}%;"
                            aria-valuenow="{{ $progress }}"
                            aria-valuemin="0"
                            aria-valuemax="100">
                        </div>
                    </div>

                    @if($isLimitReached)
                        <div class="alert alert-danger alert-dismissible fade show mt-3 py-2 px-3" role="alert">
                            <i class="ri-error-warning-line me-2"></i>
                            Anda telah mencapai batas maksimal 5 pengajuan minggu ini.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                </div>
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-warning">Keranjang</span>
                    <span class="badge bg-warning rounded-pill">
                        {{ $cartsitems ? $cartsitems->cartItems->count() : 0 }}
                    </span>
                </h4>

                <ul class="list-group mb-3">
                    @if($cartsitems && $cartsitems->cartItems->count() > 0)
                        @foreach($cartsitems->cartItems as $item)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $item->item->name }}</h6>
                                        <small class="text-muted">Kategori: {{ $item->item->category->name ?? '-' }}</small>
                                    </div>

                                    {{-- Edit Quantity --}}
                                    <form action="{{ route('pegawai.permintaan.update', $item->id) }}"
                                        method="POST"
                                        class="d-flex align-items-center ms-2 qty-form" style="gap: 6px;">
                                        @csrf
                                        @method('PUT')
                                        <input
                                            type="number"
                                            name="quantity"
                                            value="{{ $item->quantity }}"
                                            data-original="{{ $item->quantity }}"
                                            min="1"
                                            class="form-control form-control-sm text-center qty-input"
                                            style="width: 80px;" required
                                        >
                                        <button type="submit" class="btn btn-sm btn-outline-success btn-qty-check" style="display:none;">
                                            <i class="ri-check-line"></i>
                                        </button>
                                    </form>



                                    {{-- Hapus Item --}}
                                    <form action="{{ route('pegawai.cart.destroy', $item->id) }}" method="POST" class="ms-2 confirm-delete-form" data-item-name="{{ $item->item->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" >
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    @else
                        <li class="list-group-item text-center py-3 text-muted">
                            Keranjang Anda kosong
                        </li>
                    @endif
                </ul>

                @if($cartsitems && $cartsitems->cartItems->count() > 0)
                    @if($isLimitReached)
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="ri-error-warning-line me-1"></i> Batas Pengajuan Tercapai
                        </button>
                    @else
                        <form action="{{ route('pegawai.permintaan.submit', $cartsitems->id ?? 0) }}" method="POST" class=" confirm-form">
                            @csrf
                            <button type="submit" class="w-100 btn btn-warning btn-lg">
                                Ajukan Permintaan
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('pegawai.produk') }}" class="w-100 btn btn-outline-warnig btn-lg">
                        Lanjutkan Belanja
                    </a>
                @endif
            </div>
        </div>
    @endif
@endauth


<!-- ========== Navbar ========== -->
<nav class="layout-navbar container-xxl navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <!-- Mobile Menu Toggle -->
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 d-xl-none">
        <a class="nav-item nav-link px-0" href="javascript:void(0)">
            <i class="ri ri-menu-line icon-md"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

    @auth
        @if(Auth::user()->role === 'pegawai' || Auth::user()->role === 'admin')
            @php
                // Logika untuk menentukan tampilan search dan kategori
                $showSearch = false;
                $showCategoryDropdown = false;

                if (Auth::user()->role === 'pegawai') {
                    // Pegawai: search hanya di halaman produk
                    $showSearch = request()->is('pegawai/produk*');
                    $showCategoryDropdown = $showSearch;
                } elseif (Auth::user()->role === 'admin') {
                    // Admin: search hanya di halaman tertentu
                    $showSearchPages = [
                        'admin/request*',
                        'admin/itemout*',
                        'admin/guests*',
                        'admin/pegawai*',
                        'admin/rejects*',
                        'admin/produk*',
                        'admin/transaksi*'

                    ];

                    $showSearch = false;
                    foreach ($showSearchPages as $pattern) {
                        if (request()->is($pattern)) {
                            $showSearch = true;
                            break;
                        }
                    }

                    // Admin: dropdown kategori hanya di halaman produk guest, produk pegawai, dan produk
                    $showCategoryDropdown = request()->is('admin/produk*') || request()->is('admin/pegawai/*/produk');
                }
            @endphp

             @if($showSearch)
                <!-- ============================= -->
                <!-- 🔍 SEARCH BAR (CENTERED) -->
                <!-- ============================= -->
                <div class="flex-grow-1 d-flex justify-content-center align-items-center">
                    @php
                        // 1. Tentukan URL Aksi default
                        $actionUrl = route('pegawai.produk.search');

                        // 2. Jika user adalah 'admin', cek rute spesifik
                        if (Auth::user()->role === 'admin') {
                            if (request()->is('admin/guests*')) {
                                $actionUrl = route('admin.guests.index');
                            } elseif (request()->is('admin/produk*')) {
                                if (request()->is('admin/produk/guest/*')) {
                                    $guestId = request()->segment(4);
                                    $actionUrl = route('admin.produk.byGuest', $guestId);
                                } else {
                                    $actionUrl = route('admin.produk.index');
                                }
                            } elseif (request()->is('admin/pegawai/*/produk')) {
                                $pegawaiId = request()->segment(3);
                                $actionUrl = url("admin/pegawai/{$pegawaiId}/produk");
                            } elseif (request()->is('admin/request*')) {
                                $actionUrl = route('admin.request.search');
                            } elseif (request()->is('admin/itemout*')) {
                                $actionUrl = route('admin.itemout.search');
                            } elseif (request()->is('admin/pegawai*')) {
                                $actionUrl = route('admin.pegawai.index');
                            } elseif (request()->is('admin/rejects*')) {
                                $actionUrl = route('admin.rejects.search');
                             } elseif (request()->is('admin/transaksi*')) {
                                $actionUrl = route('admin.transaksi.search');
                            } else {
                                $actionUrl = '#';
                            }
                        }

                        // Check if there are no search results
                        $hasSearchResults = isset($items) && $items->count() > 0;
                        $hasSearchQuery = request()->has('q') && !empty(request('q'));
                        $hasCategoryFilter = request()->has('kategori') && request('kategori') != 'none';
                    @endphp

                    {{-- NOTIFICATION ALERT --}}
                    @if($hasSearchQuery && !$hasSearchResults)
                        <div class="position-absolute top-0 start-50 translate-middle-x mt-5" style="z-index: 9999;">
                            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ri-alert-line me-2"></i>
                                    <div>
                                        <strong>Pencarian tidak ditemukan!</strong>
                                        <div class="small">
                                            Barang dengan nama "<strong>{{ request('q') }}</strong>"
                                            @if($hasCategoryFilter)
                                                dan kategori "<strong>{{ request('kategori') }}</strong>"
                                            @endif
                                            tidak ditemukan.
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                                </div>
                            </div>
                        </div>
                    @elseif($hasCategoryFilter && !$hasSearchResults && !$hasSearchQuery)
                        <div class="position-absolute top-0 start-50 translate-middle-x mt-5" style="z-index: 9999;">
                            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="ri-information-line me-2"></i>
                                    <div>
                                        <strong>Kategori kosong!</strong>
                                        <div class="small">
                                            Tidak ada barang dalam kategori "<strong>{{ request('kategori') }}</strong>".
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form
                        action="{{ $actionUrl }}"
                        method="GET"
                        class="d-flex align-items-center px-3 py-1 border border-secondary-subtle bg-white bg-opacity-75 rounded-pill shadow-sm"
                        style="max-width: 600px; width: 100%; transition: all 0.2s ease;"
                        id="search-form"
                    >
                        {{-- ... kategori dropdown dan input search ... --}}
                        @if($showCategoryDropdown && $assignedCategories->count() > 0)
                            <select name="kategori"
                                    class="form-select border-0 bg-transparent text-secondary fw-medium"
                                    style="width: 150px; font-size: 14px; outline: none; box-shadow: none;"
                                    onchange="this.form.submit()">
                                <option value="none">Pilih Kategori</option>
                                @foreach($assignedCategories as $category)
                                    <option value="{{ $category->name }}" {{ request('kategori') == $category->name ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($showCategoryDropdown && $assignedCategories->count() === 0)
                            @php
                                $allCategories = \App\Models\Category::all();
                            @endphp
                            @if($allCategories->count() > 0)
                                <select name="kategori"
                                        class="form-select border-0 bg-transparent text-secondary fw-medium"
                                        style="width: 150px; font-size: 14px; outline: none; box-shadow: none;"
                                        onchange="this.form.submit()">
                                    <option value="none">Pilih Kategori</option>
                                    @foreach($allCategories as $category)
                                        <option value="{{ $category->name }}" {{ request('kategori') == $category->name ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        @endif

                        <i class="ri ri-search-line icon-lg lh-0 me-2 text-secondary opacity-75"></i>

                        <input type="text"
                            name="q"
                            class="form-control border-0 bg-transparent shadow-none text-secondary"
                            placeholder="Cari..."
                            aria-label="Search..."
                            style="font-size: 14px;"
                            value="{{ request('q') }}" />

                        <button type="submit" style="display: none;"></button>
                    </form>
                </div>
            @endif
        @endif
    @endauth


        <!-- Right Side Navbar -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">

            @auth
                @if(Auth::user()->role === 'pegawai')

                    <!-- Notification Icon -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri ri-notification-3-line fs-4"></i>
                            @if($notifCount > 0)
                                <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $notifCount }}
                                </span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="notifDropdown" style="min-width: 280px;">
                            @forelse($notifications as $notif)
                                <li class="px-3 py-2 border-bottom small">
                                    <div class="fw-semibold text-dark">{{ $notif->title ?? 'Notifikasi Baru' }}</div>
                                    <div class="text-muted">{{ $notif->message ?? '-' }}</div>
                                </li>
                            @empty
                                <li class="text-center text-muted py-3 small">Tidak ada notifikasi baru</li>
                            @endforelse
                        </ul>
                    </li>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const notifDropdown = document.getElementById('notifDropdown');
                            if (notifDropdown) {
                                notifDropdown.addEventListener('click', function() {
                                    fetch('{{ route('pegawai.notifications.read') }}')
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.success) {
                                                const badge = document.getElementById('notif-badge');
                                                if (badge) badge.remove();
                                            }
                                        })
                                        .catch(err => console.error(err));
                                });
                            }
                        });
                    </script>
                @endif
            @endauth

            <!-- User Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="#" role="button" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="rounded-circle" />
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}"
                                             alt="user-avatar"
                                             class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                    <small class="text-muted">{{ Auth::user()->email }}</small>
                                </div>
                            </div>
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item"
                           href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- /Right Side Navbar -->
    </div>
</nav>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.qty-input').forEach(input => {
        const form = input.closest('form');
        const btn = form.querySelector('.btn-qty-check');
        const original = input.dataset.original;

        input.addEventListener('input', () => {
            if (input.value !== original) {
                // animasi masuk
                if (!btn.classList.contains('fade-up')) {
                    btn.style.display = 'inline-block';
                    btn.classList.remove('fade-down');
                    void btn.offsetWidth; // reset animasi
                    btn.classList.add('fade-up');
                }
            } else {
                // animasi keluar
                btn.classList.remove('fade-up');
                btn.classList.add('fade-down');
                btn.addEventListener('animationend', function hideAfter() {
                    if (btn.classList.contains('fade-down')) {
                        btn.style.display = 'none';
                    }
                    btn.removeEventListener('animationend', hideAfter);
                });
            }
        });

    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.confirm-form');
    const deleteForms = document.querySelectorAll('.confirm-delete-form');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // cegah submit langsung

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin mengajukan permintaan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, yakin!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // submit form kalau user setuju
                }
            });
        });
    });

    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const itemName = form.dataset.itemName;

            Swal.fire({
                title: 'Konfirmasi!',
                text: Yakin ingin menghapus item "${itemName}" dari keranjang?,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
<style>
.swal2-container {
  z-index: 999999 !important;
}
.swal2-overflow-fix {
  overflow: visible !important;
}

.fade-up {
  animation: fadeUp 0.3s ease forwards;
}

.fade-down {
  animation: fadeDown 0.3s ease forwards;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeDown {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(6px);
  }
}

.breadcrumb {
  background: transparent;
  font-size: 0.9rem;
  margin-left: 5px;
}
.breadcrumb-item + .breadcrumb-item::before {
  content: "›";
  color: #aaa;
}
.breadcrumb-item a {
  color: #FFA500;
  transition: color 0.2s;
}
.breadcrumb-item a:hover {
  color: #FF7F00;
}
.breadcrumb-item.active {
  color: #000000;
}


</style>
@endpush
