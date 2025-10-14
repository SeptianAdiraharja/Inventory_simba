@extends('layouts.index')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold text-primary mb-3">📋 Detail Permintaan</h4>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <p><strong>Nama Pegawai:</strong> {{ $cart->user->name }}</p>
            <p><strong>Status Permintaan:</strong>
                <span class="badge bg-{{ $cart->status == 'approved' ? 'success' : ($cart->status == 'rejected' ? 'danger' : 'warning') }}">
                    {{ ucfirst($cart->status) }}
                </span>
            </p>
        </div>
    </div>

    <table class="table table-bordered shadow-sm">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Status Item</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cart->cartItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>
                        <span class="badge
                            @if($item->status=='pending') bg-warning
                            @elseif($item->status=='approved') bg-success
                            @else bg-danger @endif">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td>
                        @if($item->status == 'pending')
                            <form action="{{ route('admin.request.item.approve', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form action="{{ route('admin.request.item.reject', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        @else
                            <em>-</em>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('admin.request') }}" class="btn btn-secondary mt-3">⬅ Kembali</a>
</div>
@endsection
