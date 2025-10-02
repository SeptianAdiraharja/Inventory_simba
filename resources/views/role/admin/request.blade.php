@extends('layouts.index')

@section('content')
<div class="container">
    <!-- Header + Filter -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Daftar Request Pending & Rejected</h3>
        <div class="dropdown">
            <!-- Tombol filter -->
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel"></i> Filter
            </button>

            <!-- Dropdown filter -->
            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                <!-- Pastikan pakai route name yang sesuai di web.php -->
                <li><a class="dropdown-item" href="{{ route('admin.request', ['status' => 'all']) }}">Semua</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.request', ['status' => 'pending']) }}">Pending</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.request', ['status' => 'rejected']) }}">Rejected</a></li>
            </ul>
        </div>
    </div>

    <!-- Table daftar request -->
    <table class="table table-bordered table-hover table-responsive">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Quantity</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $index => $req)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $req->name }}</td>
                    <td>{{ $req->email }}</td>
                    <td>{{ ucfirst($req->role) }}</td>
                    <td>
                        <!-- Badge status -->
                        <span class="badge
                            @if($req->status == 'pending') bg-warning
                            @elseif($req->status == 'rejected') bg-danger
                            @elseif($req->status == 'approved') bg-success
                            @endif">
                            {{ ucfirst($req->status) }}
                        </span>
                    </td>
                    <td class="text-center">{{ $req->total_quantity }}</td>
                    <td>{{ $req->created_at->format('d-m-Y H:i') }}</td>
                    <td>
                        @if($req->status == 'pending')
                            <!-- Tombol approve -->
                            <form action="{{ route('admin.carts.update', $req->cart_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>

                            <!-- Tombol reject -->
                            <form action="{{ route('admin.carts.update', $req->cart_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @elseif($req->status == 'rejected')
                            <span class="text-danger">Sudah Ditolak</span>
                        @elseif($req->status == 'approved')
                            <span class="text-success">Sudah Disetujui</span>
                        @endif
                    </td>
                </tr>
            @empty
                <!-- Jika tidak ada data -->
                <tr>
                    <td colspan="8" class="text-center text-muted">Tidak ada request untuk ditampilkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $requests->links() }}
    </div>
</div>
@endsection
