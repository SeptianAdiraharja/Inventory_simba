@extends('layouts.index')
@section('content')

@if(request('q'))
    <div class="alert alert-info rounded-3 mb-3">
        Hasil pencarian untuk: <strong>{{ request('q') }}</strong>
    </div>
@endif


<div class="container-fluid py-4 animate__animated animate__fadeIn">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary rounded-top-4 py-3 px-4">
            <h4 class="card-title mb-0 fw-semibold">
                👥 Daftar Pegawai
            </h4>
        </div>

        <div class="card-body bg-light p-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle bg-white rounded-3 shadow-sm overflow-hidden mb-0">
                    <thead class="table-primary text-center text-dark fw-semibold">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Dibuat Pada</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawai as $index => $p)
                            <tr class="text-center">
                                <td class="fw-medium text-secondary">{{ $index + 1 }}</td>
                                <td class="fw-semibold text-dark">{{ $p->name }}</td>
                                <td class="text-muted">{{ $p->email }}</td>
                                <td class="text-secondary">{{ $p->created_at->format('d-m-Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.pegawai.produk', $p->id) }}"
                                       class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-semibold">
                                        🛍️ Pilih Produk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted fs-5">
                                        <i class="ri-information-line fs-4 text-primary"></i>
                                        <br>Belum ada data pegawai
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
