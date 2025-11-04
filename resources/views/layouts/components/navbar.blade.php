@auth
    @if(Auth::user()->role === 'pegawai' || Auth::user()->role === 'admin')
        @php
            $cartsitems = \App\Models\Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->with('cartItems.item')
                ->first();

            $categories = \App\Models\Category::all();
            $notifications = \App\Models\Notification::where('user_id', Auth::id())
                ->where('status', 'unread')
                ->latest()
                ->take(5)
                ->get();

            $notifCount = $notifications->count();
        @endphp

        <!-- ===== OFFCANVAS CART ===== -->
        <style>
            #offcanvasCart {
                position: fixed !important;
                right: 25px;
                bottom: 100px;
                width: 400px;
                top: 200px;
                max-height: 85vh;
                border-radius: 20px;
                background-color: #fff;
                border: 1px solid #ddd;
                box-shadow: 0 8px 20px rgba(0,0,0,0.25);
                overflow: hidden;
                transition: all 0.25s ease-in-out;
                z-index: 1055;
                backdrop-filter: blur(6px);
            }
            .offcanvas-backdrop.show { display: none !important; }
            .offcanvas-end { transform: translateX(100%) !important; opacity: 0; }
            .offcanvas-end.show { transform: translateX(0) !important; opacity: 1; }
        </style>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
            <div class="offcanvas-header justify-content-between">
                <h5 id="offcanvasCartLabel">Keranjang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <h6 class="fw-semibold text-primary mb-3">Item di Keranjang</h6>
                <ul class="list-group mb-3">
                    @if($cartsitems && $cartsitems->cartItems->count() > 0)
                        @foreach($cartsitems->cartItems as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $item->item->name }}</strong><br>
                                    <small class="text-muted">Kategori: {{ $item->item->category->name ?? '-' }}</small>
                                </div>
                                <form action="{{ route('pegawai.cart.destroy', $item->id) }}" method="POST"
                                      class="confirm-delete-form" data-item-name="{{ $item->item->name }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    @else
                        <li class="list-group-item text-center text-muted py-3">Keranjang kosong</li>
                    @endif
                </ul>

                @if($cartsitems && $cartsitems->cartItems->count() > 0)
                    <form action="{{ route('pegawai.permintaan.submit', $cartsitems->id) }}" method="POST" class="confirm-form">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">Ajukan Permintaan</button>
                    </form>
                @else
                    <a href="{{ route('pegawai.produk') }}" class="btn btn-outline-primary w-100">Lanjutkan Belanja</a>
                @endif
            </div>
        </div>
    @endif
@endauth

<!-- ===== NAVBAR ===== -->
<nav class="layout-navbar container-xxl navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 d-xl-none">
        <a class="nav-item nav-link px-0" href="javascript:void(0)">
            <i class="ri ri-menu-line icon-md"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-end w-100" id="navbar-collapse">
        @auth
            @php
                $showSearch = false;
                $showCategoryDropdown = false;
                $actionUrl = '#';

                if (Auth::user()->role === 'pegawai') {
                    $showSearch = request()->is('pegawai/produk*');
                    $showCategoryDropdown = $showSearch;
                    $actionUrl = route('pegawai.produk.search');
                } elseif (Auth::user()->role === 'admin') {
                    $isHidden = request()->is('admin/dashboard*') || request()->is('admin/export*') || request()->is('admin/rejects*');
                    $showSearch = !$isHidden;
                    $showCategoryDropdown = request()->is('admin/produk*') || request()->is('admin/pegawai/*/produk');
                    if (request()->is('admin/produk*')) $actionUrl = route('admin.produk.index');
                    elseif (request()->is('admin/pegawai/*/produk')) $actionUrl = '#';
                    elseif (request()->is('admin/request*')) $actionUrl = route('admin.request.search');
                    elseif (request()->is('admin/itemout*')) $actionUrl = route('admin.itemout.search');
                    elseif (request()->is('admin/pegawai*')) $actionUrl = route('admin.pegawai.index');
                }
            @endphp

            @if($showSearch)
                <!-- ===== SEARCH BAR MODERN ===== -->
                <div class="flex-grow-1 d-flex justify-content-center align-items-center px-3">
                    <form action="{{ $actionUrl }}" method="GET" id="search-form"
                          class="search-bar d-flex align-items-center shadow-sm rounded-pill bg-white bg-opacity-75 px-3 py-2 border border-secondary-subtle">
                        @if($showCategoryDropdown)
                            <div class="dropdown-wrapper position-relative me-2">
                                <select name="kategori" class="form-select custom-select text-secondary fw-medium"
                                        onchange="this.form.submit()" onclick="toggleArrow(this)">
                                    <option value="none" {{ request('kategori') == 'none' ? 'selected' : '' }}>Pilih Kategori</option>
                                    <option value="none">Semua</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->name }}" {{ request('kategori') == $category->name ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="ri-arrow-down-s-line dropdown-icon"></i>
                            </div>
                            <div class="vr mx-2 opacity-25"></div>
                        @endif

                        <i class="ri-search-line fs-5 text-secondary me-2"></i>
                        <input type="text" name="q"
                               class="form-control border-0 bg-transparent shadow-none text-secondary search-input"
                               placeholder="Cari produk, kategori, atau item..." value="{{ request('q') }}">
                    </form>
                </div>

                @if(Auth::user()->role === 'admin')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const searchForm = document.getElementById('search-form');
                            if (searchForm) {
                                searchForm.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    const currentUrl = window.location.href;
                                    const params = new URLSearchParams(new FormData(searchForm));
                                    window.location.href = currentUrl.split('?')[0] + '?' + params.toString();
                                });
                            }
                        });
                    </script>
                @endif
            @endif
        @endauth

        <!-- ===== RIGHT SIDE ===== -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            @auth
                @if(Auth::user()->role === 'pegawai')
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="notifDropdown" data-bs-toggle="dropdown">
                            <i class="ri ri-notification-3-line fs-4"></i>
                            @if($notifCount > 0)
                                <span id="notif-badge"
                                      class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $notifCount }}</span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:280px;">
                            @forelse($notifications as $notif)
                                <li class="px-3 py-2 border-bottom small">
                                    <div class="fw-semibold text-dark">{{ $notif->title ?? 'Notifikasi' }}</div>
                                    <div class="text-muted">{{ $notif->message ?? '-' }}</div>
                                </li>
                            @empty
                                <li class="text-center text-muted py-3 small">Tidak ada notifikasi baru</li>
                            @endforelse
                        </ul>
                    </li>
                    <script>
                        document.addEventListener('DOMContentLoaded',()=>{
                            const notif=document.getElementById('notifDropdown');
                            if(notif){
                                notif.addEventListener('click',()=>{
                                    fetch('{{ route('pegawai.notifications.read') }}')
                                        .then(r=>r.json()).then(d=>{
                                            if(d.success) document.getElementById('notif-badge')?.remove();
                                        });
                                });
                            }
                        });
                    </script>
                @endif
            @endauth

            <!-- 👤 USER -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="#" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img src="{{ asset('assets/img/avatars/1.png') }}"
                                         class="w-px-40 h-auto rounded-circle" />
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                    <small class="text-muted">{{ Auth::user()->email }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<!-- ===== STYLE FIX PANAH & SEARCH BAR ===== -->
<style>
.search-bar {
    max-width: 600px;
    width: 100%;
    transition: all 0.35s ease;
    border: 1px solid rgba(180, 180, 180, 0.4);
}
.search-bar:hover,
.search-bar:focus-within {
    background-color: #fff;
    box-shadow: 0 0 14px rgba(108, 99, 255, 0.25);
    transform: scale(1.05);
    border-color: #6c63ff;
}

/* --- Dropdown Styling --- */
.dropdown-wrapper { position: relative; display: inline-block; }
.custom-select {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background: transparent !important;
    background-image: none !important;
    border: none !important;
    outline: none !important;
    padding-right: 28px;
    font-size: 14px;
    font-weight: 500;
    color: #555;
    cursor: pointer;
    transition: color 0.2s ease;
}
.custom-select:hover { color: #6c63ff; }
.custom-select:focus { color: #333; }

.dropdown-icon {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%) rotate(0deg);
    font-size: 16px;
    color: #777;
    pointer-events: none;
    transition: transform 0.3s ease, color 0.3s ease;
}
.dropdown-wrapper.open .dropdown-icon {
    transform: translateY(-50%) rotate(180deg);
    color: #6c63ff;
}

/* --- Input --- */
.search-input {
    font-size: 14px;
    padding-left: 0.3rem;
}
.search-input::placeholder { color: #aaa; font-style: italic; }
.search-input:focus { outline: none !important; color: #333; }

@media (max-width: 768px) {
    .search-bar { max-width: 90%; transform: scale(1); }
    .custom-select { display: none; }
}
</style>

<!-- ===== JS: ANIMASI PANAH ===== -->
<script>
function toggleArrow(select) {
    const wrapper = select.closest('.dropdown-wrapper');
    wrapper.classList.add('open');
    select.addEventListener('blur', () => {
        wrapper.classList.remove('open');
    });
}
</script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const deleteForms=document.querySelectorAll('.confirm-delete-form');
    deleteForms.forEach(form=>{
        form.addEventListener('submit',e=>{
            e.preventDefault();
            Swal.fire({
                title:'Konfirmasi!',
                text:`Hapus "${form.dataset.itemName}" dari keranjang?`,
                icon:'warning',showCancelButton:true,
                confirmButtonText:'Ya, hapus',cancelButtonText:'Batal'
            }).then(r=>{if(r.isConfirmed) form.submit();});
        });
    });
});
</script>
@endpush
