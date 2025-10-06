@auth
  @if(Auth::user()->role === "pegawai" OR Auth::user()->role === "admin")
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
    @endphp

    <!-- Offcanvas Cart -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
      <div class="offcanvas-header justify-content-between">
        <h5 id="offcanvasCartLabel">Keranjang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <h4 class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-primary">Keranjang</span>
          <span class="badge bg-primary rounded-pill">
            {{ $cartsitems ? $cartsitems->cartItems->count() : 0 }}
          </span>
        </h4>

        <ul class="list-group mb-3">
          @if($cartsitems && $cartsitems->cartItems->count() > 0)
            @foreach($cartsitems->cartItems as $item)
              <li class="list-group-item d-flex justify-content-between lh-sm">
                <div>
                  <h6 class="my-0">{{ $item->item->name }}</h6>
                  <small class="text-body-secondary">{{ $item->quantity }}x</small>
                </div>
              </li>
            @endforeach
          @else
            <li class="list-group-item text-center py-3">
              <p class="mb-0">Keranjang Anda kosong</p>
            </li>
          @endif
        </ul>

        @if($cartsitems && $cartsitems->cartItems->count() > 0)
          <a href="{{ route('pegawai.cart.index') }}" class="w-100 btn btn-primary btn-lg">
            Lihat Detail Pesanan
          </a>
        @else
          <a href="{{ route('pegawai.produk') }}" class="w-100 btn btn-outline-primary btn-lg">
            Lanjutkan Belanja
          </a>
        @endif
      </div>
    </div>
  @endif
@endauth

<nav class="layout-navbar container-xxl navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 d-xl-none">
    <a class="nav-item nav-link px-0" href="javascript:void(0)">
      <i class="ri ri-menu-line icon-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
    @if(Auth::user()->role === "pegawai" OR Auth::user()->role === "admin")
    <!-- Search -->
    <div class="navbar-nav align-items-center">
      <div class="nav-item d-flex align-items-center">
      <form action="{{ request()->is('admin/*') ? route('admin.guests.index') : route('pegawai.produk.search') }}"
            method="GET"
            class="d-flex align-items-center">

            <i class="ri ri-search-line icon-lg lh-0"></i>

            <select 
              name="kategori" 
              class="form-select border-0 bg-transparent text-secondary"
              style="width: 150px; font-size: 14px; outline: none; box-shadow: none;"
              onchange="this.form.submit()"
            >
              <option value="none">Pilih Kategori</option>
              @foreach($categories as $category)
              <option value="{{ $category->name }}" {{ request('kategori') == $category->name ? 'selected' : '' }}>{{ $category->name }}</option>
              @endforeach
            </select>

            <!-- Input search -->
            <input 
              type="text"
              name="q"
              class="form-control border-0 bg-transparent shadow-none"
              placeholder="Search..."
              aria-label="Search..."
              style="font-size: 14px; width: 180px;"
              value="{{ request('q') }}"
            />
        </form>
      </div>    
    </div>
    <!-- /Search -->
    @endif

    <ul class="navbar-nav flex-row align-items-center ms-auto">
      @auth
        @if(Auth::user()->role === "pegawai")
          <!-- Cart Icon -->
          <li class="nav-item me-3">
            <a class="nav-link position-relative" data-bs-toggle="offcanvas" href="#offcanvasCart" role="button">
              <i class="ri ri-shopping-cart-2-line icon-lg"></i>
              @if($cartsitems && $cartsitems->cartItems->count() > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  {{ $cartsitems->cartItems->count() }}
                </span>
              @endif
            </a>
          </li>

          <!-- Notification Icon -->
          <li class="nav-item dropdown me-4">
            <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ri ri-notification-3-line icon-lg"></i>
              @if($notifications->count() > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  {{ $notifications->count() }}
                </span>
              @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown">
              @forelse($notifications as $notif)
                <li><a class="dropdown-item" href="#">{{ $notif->message }}</a></li>
              @empty
                <li><span class="dropdown-item-text text-muted">Tidak ada notifikasi</span></li>
              @endforelse
            </ul>
          </li>
        @endif
      @endauth

      <!-- User -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle hide-arrow p-0" href="#" role="button" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="rounded-circle" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="{{ route('profile.edit') }}">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="w-px-40 h-auto rounded-circle" />
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
            <a class="dropdown-item" href="{{ route('logout') }}"
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
  </div>
</nav>
