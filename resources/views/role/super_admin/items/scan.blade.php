@extends('layouts.index')

@section('content')
<div class="card">
    <div class="card-header text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Scan / Input Barcode Barang</h5>
        <a href="{{ route('super_admin.items.index') }}" class="btn btn-sm btn-light">
            <i class="ri ri-arrow-left-line me-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('super_admin.scan.process') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="barcode" class="form-label">Barcode</label>
                <input type="text" class="form-control" id="barcode" name="barcode" placeholder="Scan / ketik barcode" autofocus required>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="ri ri-barcode-line me-1"></i> Proses
            </button>
        </form>
    </div>
</div>
@endsection
