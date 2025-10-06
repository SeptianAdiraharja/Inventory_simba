@extends('layouts.index')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Satuan Barang</h5>
    <a href="{{ route('super_admin.units.create') }}" class="btn btn-sm btn-primary">
      <i class="ri ri-add-line me-1"></i> Tambah
    </a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Nama Satuan Barang</th>
          <th>Aksi</th>
        </tr>
      </thead>
      @forelse($units as $unit)
      <tbody class="table-border-bottom-0">
        <tr>
          <td>
            <i class="icon-base ri ri-ruler-line icon-22px text-info me-3"></i>
            <span>{{ $unit->name }}</span>
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                <i class="icon-base ri ri-more-2-line icon-18px"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('super_admin.units.edit', $unit->id) }}">
                  <i class="icon-base ri ri-pencil-line icon-18px me-1"></i> Edit
                </a>
                <form action="{{ route('super_admin.units.destroy', $unit->id) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item" onclick="return confirm('Yakin hapus unit ini?')">
                    <i class="icon-base ri ri-delete-bin-6-line icon-18px me-1"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="2" class="text-center text-muted py-4">
            <i class="ri-information-line me-1"></i> Belum Ada Data
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
