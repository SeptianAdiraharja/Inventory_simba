@extends('layouts.index')
@section('content')
<div class="container-fluid py-3">

  {{-- 🔹 Header Utama --}}
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <h3 class="fw-bold text-primary mb-0">
      <i class="ri ri-group-line me-2"></i> List Pengguna
    </h3>
    <a href="{{ route('super_admin.users.create') }}" class="btn btn-primary shadow-sm">
      <i class="ri ri-add-line me-1"></i> Tambah Akun
    </a>
  </div>

  {{-- 🔹 Table Admin --}}
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light">
      <h5 class="fw-bold text-primary mb-0"><i class="ri ri-shield-user-line me-2"></i>Daftar Admin</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-primary text-center">
          <tr>
            <th>Akun</th>
            <th>Email</th>
            <th>Peran</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users->where('role', 'admin') as $user)
          <tr>
            {{-- Nama & Avatar --}}
            <td>
              <div class="d-flex align-items-center">
                <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}"
                     alt="Avatar" class="rounded-circle me-3" width="42" height="42">
                <div>
                  <strong>{{ $user->name }}</strong><br>
                  <small class="text-muted">{{ '@' . Str::slug($user->name) }}</small>
                </div>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td class="text-center"><span class="badge bg-primary">Admin</span></td>

            {{-- Aksi titik 3 --}}
            <td class="text-center">
              <div class="dropdown">
                <button type="button" class="btn p-0 btn-sm" data-bs-toggle="dropdown">
                  <i class="ri-more-2-line"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a href="{{ route('super_admin.users.edit', $user->id) }}"
                     class="dropdown-item d-flex align-items-center">
                    <i class="ri-pencil-line me-1"></i> Edit
                  </a>
                  <form action="{{ route('super_admin.users.destroy', $user->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="dropdown-item d-flex align-items-center"
                            onclick="return confirm('Yakin hapus akun ini?')">
                      <i class="ri-delete-bin-6-line me-1"></i> Hapus
                    </button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="text-center text-muted py-3">
              <i class="ri-information-line me-1"></i> Belum ada admin
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- 🔹 Table Pegawai --}}
  <div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="fw-bold text-success mb-0"><i class="ri ri-user-3-line me-2"></i>Daftar Pegawai</h5>
      <form method="GET" action="{{ route('super_admin.users.index') }}">
        <select name="status" class="form-select form-select-sm btn btn-primary" onchange="this.form.submit()">
          <option value="">Semua</option>
          <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
          <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Diban</option>
        </select>
      </form>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-success text-center">
          <tr>
            <th>Akun</th>
            <th>Email</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users->where('role', 'pegawai') as $user)
          <tr>
            {{-- Nama & Avatar --}}
            <td>
              <div class="d-flex align-items-center">
                <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}"
                     alt="Avatar" class="rounded-circle me-3" width="42" height="42">
                <div>
                  <strong>{{ $user->name }}</strong><br>
                  <small class="text-muted">{{ '@' . Str::slug($user->name) }}</small>
                </div>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td class="text-center">
              @if($user->is_banned)
                <span class="badge bg-danger">Banned</span>
              @else
                <span class="badge bg-success">Aktif</span>
              @endif
            </td>

            {{-- Aksi titik 3 --}}
            <td class="text-center">
              <div class="dropdown">
                <button type="button" class="btn p-0 btn-sm" data-bs-toggle="dropdown">
                  <i class="ri-more-2-line"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a href="{{ route('super_admin.users.edit', $user->id) }}"
                     class="dropdown-item d-flex align-items-center">
                    <i class="ri-pencil-line me-1"></i> Edit
                  </a>
                  <form action="{{ route('super_admin.users.destroy', $user->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="dropdown-item d-flex align-items-center"
                            onclick="return confirm('Yakin hapus akun ini?')">
                      <i class="ri-delete-bin-6-line me-1"></i> Hapus
                    </button>
                  </form>
                  @if($user->is_banned)
                    <form action="{{ route('users.unban', $user->id) }}" method="POST">
                      @csrf @method('PUT')
                      <button type="submit" class="dropdown-item d-flex align-items-center"
                              onclick="return confirm('Aktifkan kembali akun ini?')">
                        <i class="ri-lock-unlock-line me-1"></i> Unban
                      </button>
                    </form>
                  @else
                    <form action="{{ route('users.ban', $user->id) }}" method="POST">
                      @csrf @method('PUT')
                      <button type="submit" class="dropdown-item d-flex align-items-center"
                              onclick="return confirm('Nonaktifkan akun ini?')">
                        <i class="ri-forbid-line me-1"></i> Ban
                      </button>
                    </form>
                  @endif
                </div>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="text-center text-muted py-3">
              <i class="ri-information-line me-1"></i> Belum ada pegawai
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection