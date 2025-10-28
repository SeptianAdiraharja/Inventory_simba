@extends('layouts.index')

@section('content')
<div class="container mt-4">

    {{-- 🔹 Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded shadow-sm" style="background-color:#f8f9fa;">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-envelope-paper text-primary"></i> Daftar Kop Surat
        </h4>
        <a href="{{ route('super_admin.kop_surat.create') }}" class="btn" 
           style="background-color:#6f42c1; color:white;">
            <i class="bi bi-plus-lg"></i> Tambah Kop Surat
        </a>
    </div>

    {{-- 🔹 Table Container --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 800px;">Preview Kop Surat</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kopSurats as $index => $kop)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="border rounded p-3" style="background-color:white; width:800px; margin:auto;">
                                    <table style="width:100%; border-collapse:collapse;">
                                        <tr>
                                            {{-- 🔹 Logo --}}
                                            <td style="width:140px; text-align:center; vertical-align:middle;">
                                                @if($kop->logo)
                                                    <img src="{{ asset('storage/' . $kop->logo) }}?v={{ $kop->updated_at->timestamp }}" 
                                                        alt="Logo" 
                                                        style="width:140px; height:auto; object-fit:contain; margin-right:8px;">
                                                @else
                                                    <img src="{{ asset('images/default-logo.png') }}" 
                                                        alt="Logo Default" 
                                                        style="width:140px; height:auto; object-fit:contain;">
                                                @endif
                                            </td>

                                            {{-- 🔹 Teks Tengah --}}
                                            <td style="text-align:center; vertical-align:middle;">
                                                <div style="font-size:14px; font-weight:600;">
                                                    {{ strtoupper($kop->nama_instansi ?? 'DINAS / LEMBAGA BELUM DIISI') }}
                                                </div>
                                                <div style="font-size:18px; font-weight:900; margin-top:4px;">
                                                    {{ strtoupper($kop->nama_unit ?? 'UNIT / INSTANSI BELUM DIISI') }}
                                                </div>
                                                <div style="font-size:13px; margin-top:4px;">
                                                    {{ $kop->alamat ?? 'Alamat belum diisi' }}<br>
                                                    @if($kop->telepon || $kop->email)
                                                        Telepon: {{ $kop->telepon ?? '-' }} | Email: {{ $kop->email ?? '-' }}<br>
                                                    @endif
                                                    @if($kop->website)
                                                        Website: {{ $kop->website }}<br>
                                                    @endif
                                                    {{ $kop->kota ?? 'Kota belum diisi' }}
                                                </div>
                                            </td>

                                            <td style="width:140px;"></td>
                                        </tr>
                                    </table>

                                    {{-- 🔹 Garis Bawah --}}
                                    <div style="border-bottom:3px solid #000; margin-top:6px;"></div>
                                    <div style="border-bottom:1px solid #000; margin-top:2px;"></div>
                                </div>
                            </td>

                            {{-- 🔹 Tombol Aksi --}}
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('super_admin.kop_surat.edit', $kop->id) }}" 
                                       class="btn btn-sm text-white" 
                                       style="background-color:#6f42c1;">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('super_admin.kop_surat.destroy', $kop->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Yakin hapus kop surat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm text-white" 
                                                style="background-color:#dc3545;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted py-4">Belum ada data kop surat</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 🔹 Tombol Kembali --}}
            <div class="text-start mt-3">
                <a href="{{ route('super_admin.export.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Export
                </a>
            </div>
        </div>
    </div>
</div>
@endsection