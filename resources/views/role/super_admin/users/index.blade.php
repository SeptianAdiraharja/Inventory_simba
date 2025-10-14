@extends('layouts.index')
@section('content')
<div class="col-12">
  <div class="card overflow-hidden">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="fw-bold text-primary mb-0">Daftar Akun</h4>
      <a href="{{ route('super_admin.users.create') }}" class="btn btn-sm btn-primary">
        <i class="ri ri-add-line me-1"></i> Tambah
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light text-center">
          <tr>
            <th class="text-truncate">Akun</th>
            <th class="text-truncate">Email</th>
            <th class="text-truncate">Peran</th>
            <th class="text-truncate">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
          <tr>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-4">
                  <img src="{{ asset('assets/img/avatars/' . ($loop->iteration % 7 + 1) . '.png') }}" 
                       alt="Avatar" 
                       class="rounded-circle" />
                </div>
                <div>
                  <h6 class="mb-0 text-truncate">{{ $user->name }}</h6>
                  <small class="text-truncate">{{ '@' . Str::slug($user->name) }}</small>
                </div>
              </div>
            </td>
            <td class="text-truncate">{{ $user->email }}</td>
            <td class="text-truncate">
              <div class="d-flex align-items-center">
                @if($user->role == 'super_admin')
                  <i class="icon-base ri ri-vip-crown-line icon-22px text-danger me-2"></i>
                  <span>Super Admin</span>
                @elseif($user->role == 'admin')
                  <i class="icon-base ri ri-edit-box-line text-primary icon-22px me-2"></i>
                  <span>Admin</span>
                @else
                  <i class="icon-base ri ri-user-3-line icon-22px text-success me-2"></i>
                  <span>Pegawai</span>
                @endif
              </div>
            </td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="ri ri-more-2-line"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('super_admin.users.edit', $user->id) }}">
                    <i class="ri ri-pencil-line me-1"></i> Edit
                  </a>
                  <form action="{{ route('super_admin.users.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item" onclick="return confirm('Yakin hapus akun ini?')">
                      <i class="ri ri-delete-bin-6-line me-1"></i> Delete
                    </button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="text-center text-muted py-4">
              <i class="ri-information-line me-1"></i> Belum ada akun
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
