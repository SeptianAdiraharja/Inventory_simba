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
       class="btn btn-warning text-white rounded-pill px-3 py-2 d-flex align-items-center gap-2 shadow-sm hover-glow">
      <i class="ri ri-add-line fs-5"></i> Tambah Akun
    </a>
  </div>

  {{-- 👑 DAFTAR ADMIN --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pb-0">
      <h4 class="fw-bold mb-3 text-warning">
        <i class="ri-shield-user-line me-2"></i> Daftar Admin
      </h4>
    </div>
    <div class="card-body pt-2">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center mb-0">
          <thead style="background:#FFF8E1;">
            <tr class="text-secondary">
              <th>Akun</th>
              <th>Email</th>
              <th>Peran</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($admins as $user)
              <tr class="table-row-hover">
                <td class="text-start">
                  <div class="d-flex align-items-center">
                    <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}"
                         class="rounded-circle me-3 shadow-sm" width="45" height="45" alt="Avatar">
                    <div>
                      <strong class="text-dark">{{ $user->name }}</strong><br>
                      <small class="text-muted">{{ '@' . Str::slug($user->name) }}</small>
                    </div>
                  </div>
                </td>
                <td class="text-muted">{{ $user->email }}</td>
                <td><span class="badge bg-warning text-dark px-3 py-2 rounded-pill text-capitalize">{{ $user->role }}</span></td>
                <td>
                  <div class="dropdown">
                    <button class="btn p-0 text-secondary dropdown-toggle" data-bs-toggle="dropdown">
                      <i class="ri-more-2-fill fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                      <li><a href="{{ route('super_admin.users.edit', $user->id) }}" class="dropdown-item d-flex align-items-center text-secondary">
                        <i class="ri-pencil-line me-2 text-warning"></i> Edit</a></li>
                      <li>
                        <form action="{{ route('super_admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus akun ini?')">
                          @csrf @method('DELETE')
                          <button type="submit" class="dropdown-item text-danger d-flex align-items-center">
                            <i class="ri-delete-bin-6-line me-2"></i> Hapus
                          </button>
                        </form>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center py-4 text-muted"><i class="ri-information-line me-1"></i> Belum ada admin.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- 🧑‍💼 DAFTAR PEGAWAI --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <h4 class="fw-bold text-warning mb-0">
        <i class="ri-user-3-line me-2"></i> Daftar Pegawai
      </h4>

      {{-- FILTER + SEARCH --}}
      <form method="GET" action="{{ route('super_admin.users.index') }}" class="d-flex flex-wrap align-items-center justify-content-end gap-3">

        {{-- Tombol Filter --}}
        <div class="btn-group shadow-sm rounded-pill overflow-hidden" role="group">
          <a href="{{ route('super_admin.users.index') }}"
             class="btn btn-filter {{ request('status') == '' ? 'active' : '' }}">Semua</a>
          <a href="{{ route('super_admin.users.index', ['status' => 'active']) }}"
             class="btn btn-filter {{ request('status') == 'active' ? 'active' : '' }}">Aktif</a>
          <a href="{{ route('super_admin.users.index', ['status' => 'banned']) }}"
             class="btn btn-filter {{ request('status') == 'banned' ? 'active' : '' }}">Nonaktif</a>
        </div>

        {{-- Search --}}
        <div class="position-relative">
          <input type="text" name="search" value="{{ request('search') }}"
                 class="form-control rounded-pill ps-5 shadow-sm border-0"
                 placeholder="Cari nama atau email..." style="min-width: 230px;">
        </div>
      </form>
    </div>

    <div class="card-body pt-3">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center mb-0">
          <thead style="background:#FFF8E1;">
            <tr class="text-secondary">
              <th>Akun</th>
              <th>Email</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pegawai as $user)
              <tr class="table-row-hover">
                <td class="text-start">
                  <div class="d-flex align-items-center">
                    <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}"
                         class="rounded-circle me-3 shadow-sm" width="45" height="45">
                    <div>
                      <strong class="text-dark">{{ $user->name }}</strong><br>
                      <small class="text-muted">{{ '@' . Str::slug($user->name) }}</small>
                    </div>
                  </div>
                </td>
                <td class="text-muted">{{ $user->email }}</td>
                <td>
                  @if($user->is_banned)
                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill">Nonaktif</span>
                  @else
                    <span class="badge bg-success text-white px-3 py-2 rounded-pill">Aktif</span>
                  @endif
                </td>
                <td>
                  <div class="dropdown">
                    <button class="btn p-0 text-secondary dropdown-toggle" data-bs-toggle="dropdown">
                      <i class="ri-more-2-fill fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                      <li><a href="{{ route('super_admin.users.edit', $user->id) }}" class="dropdown-item">
                        <i class="ri-pencil-line me-2 text-warning"></i> Edit</a></li>
                      <li>
                        <form action="{{ route('super_admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus akun ini?')">
                          @csrf @method('DELETE')
                          <button type="submit" class="dropdown-item text-danger">
                            <i class="ri-delete-bin-6-line me-2"></i> Hapus
                          </button>
                        </form>
                      </li>
                      @if($user->is_banned)
                        <li><form action="{{ route('users.unban', $user->id) }}" method="POST">@csrf @method('PUT')
                          <button type="submit" class="dropdown-item text-success">
                            <i class="ri-lock-unlock-line me-2"></i> Aktifkan
                          </button></form></li>
                      @else
                        <li><form action="{{ route('users.ban', $user->id) }}" method="POST">@csrf @method('PUT')
                          <button type="submit" class="dropdown-item text-warning">
                            <i class="ri-forbid-line me-2"></i> Nonaktifkan
                          </button></form></li>
                      @endif
                    </ul>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center py-4 text-muted"><i class="ri-information-line me-1"></i> Tidak ada pegawai ditemukan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- 🎨 STYLE TAMBAHAN --}}
<style>
.btn-filter {
  background: #fff;
  color: #ff9800;
  border: 1px solid #ffcc80;
  font-weight: 600;
  transition: all 0.3s ease;
}
.btn-filter:hover { background: #fff8e1; color: #e65100; }
.btn-filter.active {
  background: linear-gradient(90deg, #ff9800, #ffb74d);
  color: #fff;
  border-color: #ffa726;
  box-shadow: 0 0 10px rgba(255, 152, 0, 0.4);
}
.table-row-hover:hover { background-color: #FFF9E6 !important; transform: translateX(4px); transition: all 0.2s ease; }
.form-control:focus { box-shadow: 0 0 8px rgba(255, 152, 0, 0.3) !important; }
.hover-glow:hover { background-color: #FFA000 !important; box-shadow: 0 0 12px rgba(255,152,0,0.45); transform: translateY(-2px); }
</style>
@endsection
