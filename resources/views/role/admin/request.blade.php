@extends('layouts.index')

@section('content')
<div class="container-fluid py-3 animate__animated animate__fadeIn">
    <!-- HEADER + FILTER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-primary">
                <i class="bi bi-list-check me-2"></i> Daftar Permintaan Pegawai
            </h3>
        </div>

        <!-- Filter Dropdown -->
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel"></i> Filter Status
            </button>
            <ul class="dropdown-menu shadow" aria-labelledby="filterDropdown">
                <li><a class="dropdown-item {{ ($status ?? '') == 'all' ? 'active' : '' }}" href="{{ route('admin.request', ['status' => 'all']) }}">Semua</a></li>
                <li><a class="dropdown-item {{ ($status ?? '') == 'pending' ? 'active' : '' }}" href="{{ route('admin.request', ['status' => 'pending']) }}">Pending</a></li>
                <li><a class="dropdown-item {{ ($status ?? '') == 'rejected' ? 'active' : '' }}" href="{{ route('admin.request', ['status' => 'rejected']) }}">Rejected</a></li>
            </ul>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-center align-middle">
                        <th style="width: 50px;">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Jumlah Item</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $index => $req)
                        <tr>
                            <td class="text-center">{{ $requests->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $req->name }}</strong><br>
                                <small class="text-muted">
                                    Diajukan: {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') }}
                                </small>
                            </td>
                            <td>{{ $req->email }}</td>
                            <td>
                                <span class="badge bg-info text-dark">{{ ucfirst($req->role) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge
                                    @if($req->status == 'pending') bg-warning text-dark
                                    @elseif($req->status == 'rejected') bg-danger
                                    @elseif($req->status == 'approved') bg-success
                                    @endif">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="text-center fw-semibold">{{ $req->total_quantity }}</td>
                            <td class="text-center">
                                @if($req->status == 'pending')
                                    <form action="{{ route('admin.carts.update', $req->cart_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Setujui permintaan">
                                            <i class="bi bi-check-circle me-1"></i> Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.carts.update', $req->cart_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" title="Tolak permintaan">
                                            <i class="bi bi-x-circle me-1"></i> Reject
                                        </button>
                                    </form>
                                @elseif($req->status == 'rejected')
                                    <span class="text-danger fw-semibold">
                                        <i class="bi bi-x-octagon me-1"></i> Sudah Ditolak
                                    </span>
                                @elseif($req->status == 'approved')
                                    <span class="text-success fw-semibold">
                                        <i class="bi bi-check2-circle me-1"></i> Sudah Disetujui
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    <p class="mb-0">Belum ada permintaan dengan status ini.</p>
                                    <small>Coba ubah filter untuk melihat data lain.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Tooltip activation -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
});
</script>
@endsection
