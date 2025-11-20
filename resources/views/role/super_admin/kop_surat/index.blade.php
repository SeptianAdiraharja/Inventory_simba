@extends('layouts.index')
@section('title', 'Kop Surat')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 HEADER --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center smooth-fade">
    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
      <i class="bi bi-envelope-paper" style="color:#FF9800;"></i>
      Daftar Kop Surat
    </h4>
    <a href="{{ route('super_admin.kop_surat.create') }}"
       class="btn rounded-pill d-flex align-items-center gap-2 hover-glow"
       style="background-color:#FF9800;color:white;">
      <i class="bi bi-plus-lg"></i> Tambah Kop Surat
    </a>
  </div>

  {{-- 🧾 TABEL --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      <div class="table-responsive">
        <table class="table align-middle text-center table-hover">
          <thead style="background:#FFF3E0;">
            <tr>
              <th style="width:50px;">No</th>
              <th>Preview Kop Surat</th>
              <th style="width:150px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($kopSurats as $index => $kop)
            <tr class="table-row-hover">
              <td>{{ $index + 1 }}</td>
              <td>
                <div class="border rounded-4 p-3 bg-white mx-auto" style="max-width:850px;">
                  <table class="w-100">
                    <tr>
                      {{-- Logo --}}
                      <td style="width:140px; text-align:center;">
                        @if($kop->logo)
                          <img src="{{ asset('storage/'.$kop->logo) }}?v={{ $kop->updated_at->timestamp }}"
                               alt="Logo" width="120" style="object-fit:contain;">
                        @else
                          <img src="{{ asset('images/default-logo.png') }}" alt="Logo" width="120">
                        @endif
                      </td>
                      {{-- Text --}}
                      <td class="text-center align-middle">
                        <div style="font-size:14px;font-weight:600;">{{ strtoupper($kop->nama_instansi ?? '-') }}</div>
                        <div style="font-size:18px;font-weight:900;margin-top:4px;">
                          {{ strtoupper($kop->nama_unit ?? '-') }}
                        </div>
                        <div style="font-size:13px;margin-top:4px;">
                          {{ $kop->alamat ?? '-' }}<br>
                          @if($kop->telepon || $kop->email)
                            Telepon: {{ $kop->telepon ?? '-' }} | Email: {{ $kop->email ?? '-' }}<br>
                          @endif
                          @if($kop->website)
                            Website: {{ $kop->website }}<br>
                          @endif
                          {{ $kop->kota ?? '-' }}
                        </div>
                      </td>
                      <td style="width:140px;"></td>
                    </tr>
                  </table>
                  <div style="border-bottom:3px solid #000;margin-top:6px;"></div>
                  <div style="border-bottom:1px solid #000;margin-top:2px;"></div>
                </div>
              </td>
              <td>
                <div class="d-flex justify-content-center gap-2">
                  <a href="{{ route('super_admin.kop_surat.edit', $kop->id) }}"
                     class="btn btn-sm rounded-circle text-white" style="background:#FF9800;">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <form action="{{ route('super_admin.kop_surat.destroy', $kop->id) }}" method="POST"
                        onsubmit="return confirm('Yakin hapus kop surat ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm rounded-circle text-white" style="background:#dc3545;">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-muted py-4">Belum ada data kop surat</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <a href="{{ route('super_admin.export.index') }}"
           class="btn btn-light border rounded-pill shadow-sm">
          <i class="bi bi-arrow-left"></i> Kembali ke Export
        </a>
      </div>
    </div>
  </div>
</div>

<style>
.table-row-hover:hover { background-color:#FFF9E6 !important; transform:translateX(3px); transition:all .2s ease; }
.hover-glow:hover { background-color:#FFC107 !important; color:white !important; box-shadow:0 0 10px rgba(255,152,0,0.4); }
.smooth-fade { animation:fadeIn .5s ease-in-out; }
@keyframes fadeIn { from {opacity:0;transform:translateY(10px);} to {opacity:1;transform:translateY(0);} }
</style>
@endsection
