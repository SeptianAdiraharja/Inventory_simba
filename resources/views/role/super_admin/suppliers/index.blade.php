@extends('layouts.index')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Supplier</h5>
    <a href="{{ route('super_admin.suppliers.create') }}" class="btn btn-sm btn-primary">
      <i class="ri ri-add-line me-1"></i> Tambah
    </a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Nama Supplier</th>
          <th>Kontak</th>
          <th>Aksi</th>
        </tr>
      </thead>
      @forelse($suppliers as $supplier)
      <tbody class="table-border-bottom-0">
        <tr>
          <td>
            <i class="ri ri-store-2-line text-info me-2"></i>
            {{ $supplier->name }}
          </td>
          <td>{{ $supplier->contact ?? '-' }}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="ri ri-more-2-line"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('super_admin.suppliers.edit', $supplier->id) }}">
                  <i class="ri ri-pencil-line me-1"></i> Edit
                </a>
                <form action="{{ route('super_admin.suppliers.destroy', $supplier->id) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item" onclick="return confirm('Yakin hapus supplier ini?')">
                    <i class="ri ri-delete-bin-6-line me-1"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="3" class="text-center text-muted py-4">
            <i class="ri-information-line me-1"></i> Belum ada data
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
