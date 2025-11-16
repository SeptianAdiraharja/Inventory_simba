@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-people-fill fs-5 text-warning"></i>
      <a href="{{ route('super_admin.dashboard') }}" class="breadcrumb-link fw-semibold text-warning text-decoration-none position-relative">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">List Pengguna</span>
    </div>
    <a href="{{ route('super_admin.users.create') }}"
       class="btn btn-warning text-white rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-2 hover-glow">
      <i class="ri ri-add-line fs-5"></i> Tambah Akun
    </a>
  </div>

  {{-- 👑 ADMIN --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0">
      <h4 class="fw-bold text-warning m-0 py-2 d-flex align-items-center gap-2">
        <i class="ri-shield-user-line"></i> Daftar Admin
      </h4>
    </div>

    <div class="card-body pt-0">
      <div class="table-responsive">
        <table class="table align-middle modern-table">
          <thead>
            <tr>
              <th>Akun</th>
              <th>Email</th>
              <th>Peran</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>

          <tbody>
            @forelse($admins as $user)
              <tr>
                <td class="text-start">
                  <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}"
                         class="rounded-circle shadow-sm avatar-img">
                    <div>
                      <strong class="text-dark">{{ $user->name }}</strong>
                      <div class="text-muted small">{{ '@' . Str::slug($user->name) }}</div>
                    </div>
                  </div>
                </td>

                <td class="text-muted">{{ $user->email }}</td>

                <td>
                  <span class="badge bg-warning text-dark px-3 py-2 rounded-pill text-capitalize">
                    {{ $user->role }}
                  </span>
                </td>

                <td class="text-center position-relative">
                  <div class="dropdown">
                    <button class="btn p-0 text-secondary dropdown-toggle no-caret" data-bs-toggle="dropdown">
                      <i class="ri-more-2-fill fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                      <li>
                        <a href="{{ route('super_admin.users.edit', $user->id) }}" class="dropdown-item">
                          <i class="ri-pencil-line me-2 text-warning"></i> Edit
                        </a>
                      </li>
                      <li>
                        <form action="{{ route('super_admin.users.destroy', $user->id) }}" method="POST"
                              onsubmit="return confirm('Yakin hapus akun ini?')">
                          @csrf @method('DELETE')
                          <button type="submit" class="dropdown-item text-danger">
                            <i class="ri-delete-bin-6-line me-2"></i> Hapus
                          </button>
                        </form>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">Belum ada admin.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">{{ $admins->links('pagination::bootstrap-5') }}</div>
    </div>
  </div>

  {{-- 🧑‍💼 PEGAWAI --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <h4 class="fw-bold text-warning m-0 d-flex align-items-center gap-2">
        <i class="ri-user-3-line"></i> Daftar Pegawai
      </h4>

      <form method="GET" action="{{ route('super_admin.users.index') }}" class="d-flex gap-3 align-items-center flex-wrap">
        <div class="btn-group shadow-sm rounded-pill overflow-hidden filter-group">
          <a href="{{ route('super_admin.users.index') }}"
             class="btn btn-filter {{ request('status') == '' ? 'active' : '' }}">Semua</a>

          <a href="{{ route('super_admin.users.index',['status'=>'active']) }}"
             class="btn btn-filter {{ request('status') == 'active' ? 'active' : '' }}">Aktif</a>

          <a href="{{ route('super_admin.users.index',['status'=>'banned']) }}"
             class="btn btn-filter {{ request('status') == 'banned' ? 'active' : '' }}">Nonaktif</a>
        </div>

        <input type="text" name="search" value="{{ request('search') }}"
               class="form-control rounded-pill shadow-sm search-input"
               placeholder="Cari nama atau email...">
      </form>
    </div>

    <div class="card-body pt-2">
      <div class="table-responsive">
        <table class="table align-middle modern-table">
          <thead>
            <tr>
              <th>Akun</th>
              <th>Email</th>
              <th>Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>

          <tbody>
            @forelse($pegawai as $user)
              <tr>
                <td class="text-start">
                  <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}"
                         class="rounded-circle shadow-sm avatar-img">
                    <div>
                      <strong class="text-dark d-flex align-items-center gap-1">
                        {{ $user->name }}
                        @if($user->trashed())
                           <span class="badge bg-danger small">Terhapus</span>
                        @endif
                      </strong>
                      <div class="text-muted small">{{ '@' . Str::slug($user->name) }}</div>
                    </div>
                  </div>
                </td>

                <td class="text-muted">{{ $user->email }}</td>

                <td>
                  @if($user->trashed())
                    <span class="badge bg-secondary px-3 py-2 text-white rounded-pill">Terhapus</span>
                  @else
                    <span class="badge px-3 py-2 rounded-pill {{ $user->is_banned ? 'bg-danger' : 'bg-success' }} text-white">
                      {{ $user->is_banned ? 'Nonaktif' : 'Aktif' }}
                    </span>
                  @endif
                </td>

                <td class="text-center position-relative">
                  <div class="dropdown">
                    <button class="btn p-0 text-secondary dropdown-toggle no-caret" data-bs-toggle="dropdown">
                      <i class="ri-more-2-fill fs-5"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">

                      {{-- ✏️ EDIT --}}
                      <li>
                        <a href="{{ route('super_admin.users.edit',$user->id) }}" class="dropdown-item">
                          <i class="ri-pencil-line me-2 text-warning"></i> Edit
                        </a>
                      </li>

                      {{-- 🗑 DELETE --}}
                      <li>
                        <form action="{{ route('super_admin.users.destroy',$user->id) }}"
                              method="POST" onsubmit="return confirm('Yakin hapus akun ini?')">
                          @csrf @method('DELETE')
                          <button type="submit" class="dropdown-item text-danger">
                            <i class="ri-delete-bin-6-line me-2"></i> Hapus
                          </button>
                        </form>
                      </li>

                      {{-- 🔄 RESTORE --}}
                      @if($user->trashed())
                        <li>
                          <form action="{{ route('users.restore',$user->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button type="submit" class="dropdown-item text-success">
                              <i class="ri-refresh-line me-2"></i> Pulihkan
                            </button>
                          </form>
                        </li>
                      @endif

                      {{-- 🚫 BAN / UNBAN --}}
                      @if(!$user->trashed())
                        @if($user->is_banned)
                          <li>
                            <form action="{{ route('users.unban',$user->id) }}" method="POST">
                              @csrf @method('PUT')
                              <button type="submit" class="dropdown-item text-success">
                                <i class="ri-lock-unlock-line me-2"></i> Aktifkan
                              </button>
                            </form>
                          </li>
                        @else
                          <li>
                            <form action="{{ route('users.ban',$user->id) }}" method="POST">
                              @csrf @method('PUT')
                              <button type="submit" class="dropdown-item text-warning">
                                <i class="ri-forbid-line me-2"></i> Nonaktifkan
                              </button>
                            </form>
                          </li>
                        @endif
                      @endif
                    </ul>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada pegawai ditemukan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">{{ $pegawai->links('pagination::bootstrap-5') }}</div>
    </div>
  </div>
</div>


<style>
/* FIX TABLE HEADER */
.modern-table thead tr {
    background: #FFF8E1;
}
.modern-table thead th {
    font-weight: 600;
    color: #7a7a7a;
    border-bottom: 2px solid #FFE0A3 !important;
}

/* FIX ROW HOVER TANPA GESER */
.modern-table tbody tr:hover {
    background: #FFF9E6 !important;
}

/* AVATAR STABIL */
.avatar-img {
    width: 45px;
    height: 45px;
}

/* FIX DROPDOWN BUG */
.no-caret::after {
    display: none !important;
}
.dropdown-menu {
    min-width: 160px;
}

/* FILTER BUTTON */
.filter-group .btn-filter {
    background:#fff;
    color:#FF9800;
    border:1px solid #FFCC80;
    font-weight:600;
}
.filter-group .btn-filter.active {
    background:linear-gradient(90deg,#FF9800,#FFB74D);
    color:#fff;
}

/* SEARCH */
.search-input {
    min-width:230px;
    border:1px solid #FFE0B2;
}

/* PAGINATION FIX */
.pagination .page-link {
  color: #FF9800;
  border: 1px solid #FFCC80;
}
.pagination .page-link:hover {
  background-color: #FFE0B2;
  color: #E65100;
}
.pagination .active .page-link {
  background-color: #FF9800 !important;
  border-color: #FF9800 !important;
  color: #fff !important;
}
</style>
@endsection
