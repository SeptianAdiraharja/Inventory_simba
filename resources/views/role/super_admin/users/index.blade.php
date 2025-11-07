@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-people-fill fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('super_admin.dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">List Pengguna</span>
    </div>
    <a href="{{ route('super_admin.users.create') }}"
       class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
       style="background-color:#FF9800;color:white;">
      <i class="ri ri-add-line fs-5"></i> Tambah Akun
    </a>
  </div>

  {{-- 👥 DAFTAR ADMIN --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade mb-4">
    <div class="card-header bg-white border-0 pb-0">
      <h4 class="fw-bold mb-3" style="color:#FF9800;">
        <i class="ri-shield-user-line me-2"></i> Daftar Admin
      </h4>
    </div>

    <div class="card-body pt-2">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead style="background:#FFF3E0;">
            <tr>
              <th>Akun</th>
              <th>Email</th>
              <th>Peran</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users->where('role', 'admin') as $user)
            <tr class="table-row-hover">
              <td class="text-start">
                <div class="d-flex align-items-center">
                  <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}"
                       class="rounded-circle me-3" width="42" height="42" alt="Avatar">
                  <div>
                    <strong>{{ $user->name }}</strong><br>
                    <small class="text-muted">{{ '@' . Str::slug($user->name) }}</small>
                  </div>
                </div>
              </td>
              <td>{{ $user->email }}</td>
              <td><span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">Admin</span></td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                    <i class="ri-more-2-fill text-muted"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 fade show-on-hover">
                    <li>
                      <a href="{{ route('super_admin.users.edit', $user->id) }}" class="dropdown-item d-flex align-items-center">
                        <i class="ri-pencil-line me-2 text-warning"></i> Edit
                      </a>
                    </li>
                    <li>
                      <form action="{{ route('super_admin.users.destroy', $user->id) }}" method="POST"
                            onsubmit="return confirm('Yakin hapus akun ini?')">
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
            <tr>
              <td colspan="4" class="text-center py-4 text-muted"><i class="ri-information-line me-1"></i> Belum ada admin.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- 🧑‍💼 DAFTAR PEGAWAI --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap">
      <h4 class="fw-bold mb-3 mb-md-0" style="color:#FF9800;">
        <i class="ri-user-3-line me-2"></i> Daftar Pegawai
      </h4>
      <form method="GET" action="{{ route('super_admin.users.index') }}">
        <select name="status" class="form-select form-select-sm w-auto border-0 shadow-sm"
                style="border-left:4px solid #FF9800 !important;" onchange="this.form.submit()">
          <option value="">Semua</option>
          <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
          <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Diban</option>
        </select>
      </form>
    </div>

    <div class="card-body pt-2">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead style="background:#FFF3E0;">
            <tr>
              <th>Akun</th>
              <th>Email</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users->where('role', 'pegawai') as $user)
            <tr class="table-row-hover">
              <td class="text-start">
                <div class="d-flex align-items-center">
                  <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}" class="rounded-circle me-3" width="42" height="42">
                  <div>
                    <strong>{{ $user->name }}</strong><br>
                    <small class="text-muted">{{ '@' . Str::slug($user->name) }}</small>
                  </div>
                </div>
              </td>
              <td>{{ $user->email }}</td>
              <td>
                @if($user->is_banned)
                  <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">Banned</span>
                @else
                  <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Aktif</span>
                @endif
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                    <i class="ri-more-2-fill text-muted"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 fade show-on-hover">
                    <li><a href="{{ route('super_admin.users.edit', $user->id) }}" class="dropdown-item"><i class="ri-pencil-line me-2 text-warning"></i> Edit</a></li>
                    <li>
                      <form action="{{ route('super_admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus akun ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger"><i class="ri-delete-bin-6-line me-2"></i> Hapus</button>
                      </form>
                    </li>
                    @if($user->is_banned)
                      <li>
                        <form action="{{ route('users.unban', $user->id) }}" method="POST">@csrf @method('PUT')
                          <button type="submit" class="dropdown-item"><i class="ri-lock-unlock-line me-2 text-success"></i> Unban</button>
                        </form>
                      </li>
                    @else
                      <li>
                        <form action="{{ route('users.ban', $user->id) }}" method="POST">@csrf @method('PUT')
                          <button type="submit" class="dropdown-item"><i class="ri-forbid-line me-2 text-warning"></i> Ban</button>
                        </form>
                      </li>
                    @endif
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center py-4 text-muted"><i class="ri-information-line me-1"></i> Belum ada pegawai.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- 🎨 STYLE ORANGE MODERN --}}
<style>
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
.table-row-hover:hover { background-color: #FFF9E6 !important; transform: translateX(3px); transition: all .2s ease; }
.hover-glow:hover { background-color: #FFC107 !important; color: #fff !important; box-shadow: 0 0 10px rgba(255,152,0,0.4); }
.breadcrumb-link::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: #FF9800; transition: width 0.25s ease; }
.breadcrumb-link:hover::after { width: 100%; }
.form-select:focus { border-color: #FF9800 !important; box-shadow: 0 0 0 3px rgba(255,152,0,0.25); }
</style>
@endsection
