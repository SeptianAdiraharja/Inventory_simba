@extends('layouts.index')
@section('content')

@if(request('q'))
    <div class="alert alert-info rounded-3 mb-3">
        Hasil pencarian untuk: <strong>{{ request('q') }}</strong>
    </div>
@endif


<!-- ======================== -->
<!-- 🔹 HEADER & FILTER -->
<!-- ======================== -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <h4 class="fw-bold text-primary mb-2">
    <i class="bi bi-box-seam me-2"></i>Daftar Permintaan Pegawai
  </h4>
</div>

<div class="container-fluid py-4 animate__animated animate__fadeIn">


  <!-- ======================== -->
  <!-- 🧭 BREADCRUMB MODERN (SMOOTH & RESPONSIVE) -->
  <!-- ======================== -->
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="breadcrumb-icon bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded-circle" style="width:38px;height:38px;">
        <i class="bi bi-house-door-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 align-items-center">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary fw-semibold">
              Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Daftar Permintaan Pegawai
          </li>
        </ol>
      </nav>
    </div>
    <div class="breadcrumb-extra text-end">
      <small class="text-muted">
        <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
      </small>
    </div>
  </div>

  <!-- ======================== -->
  <!-- 🔹 BAGIAN 1: PEGAWAI -->
  <!-- ======================== -->
  <div class="section-pegawai">
    <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInUp">
      <div class="card-header bg-primary text-white rounded-top-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-semibold text-white">
          <i class="bi bi-person-badge me-2 text-warning"></i>Permintaan dari Pegawai
        </h5>
        <small class="text-white opacity-75">Menampilkan data pegawai dengan status approved</small>
      </div>

      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0">
          <thead class="bg-light text-primary text-center fw-semibold">
            <tr>
              <th style="width: 50px;">No</th>
              <th>Nama Pengguna</th>
              <th>Status Pemindaian</th>
              <th style="width: 160px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($approvedItems as $i => $cart)
              <tr class="cart-item" data-type="pegawai" data-scanned="{{ $cart->all_scanned ? 'true' : 'false' }}">
                <td class="text-center text-secondary fw-semibold">{{ $approvedItems->firstItem() + $i }}</td>
                <td>
                  <strong class="text-dark fs-6">{{ $cart->user->name ?? 'Guest' }}</strong><br>
                  <small class="text-muted d-block mb-1">
                    <i class="bi bi-calendar-event me-1"></i>{{ $cart->created_at->format('d M Y H:i') }}
                  </small>
                  <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2">
                    <i class="bi bi-box-seam me-1"></i>{{ $cart->cartItems->count() }} Barang Belum Dipindai
                  </span>
                </td>
                <td class="text-center">
                  @if($cart->all_scanned)
                    <span class="badge bg-success fs-6 px-3 py-2">✅ Sudah dipindai semua</span>
                  @else
                    <span class="badge bg-secondary fs-6 px-3 py-2">⏳ Belum dipindai semua</span>
                  @endif
                </td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2"
                          type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#collapse{{ $cart->id }}"
                          aria-expanded="false"
                          aria-controls="collapse{{ $cart->id }}">
                    <i class="bi bi-eye me-1"></i> Detail ({{ $cart->cartItems->count() }})
                  </button>
                </td>
              </tr>

              <!-- DETAIL ITEM PEGAWAI -->
              <tr class="collapse bg-light" id="collapse{{ $cart->id }}">
                <td colspan="4">
                  <div class="p-3">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                      <button class="btn btn-primary rounded-pill px-3 py-2"
                              data-bs-toggle="modal"
                              data-bs-target="#scanModal{{ $cart->id }}">
                        <i class="bi bi-qr-code-scan me-1"></i> Pindai Barang
                      </button>
                    </div>

                    <table class="table table-sm table-bordered mb-0">
                      <thead class="table-light text-center">
                        <tr>
                          <th style="width:50px;">No</th>
                          <th>Nama Barang</th>
                          <th>Kode</th>
                          <th style="width:80px;">Jumlah</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($cart->cartItems as $j => $item)
                          <tr data-item-id="{{ $item->item->id }}">
                            <td class="text-center">{{ $j+1 }}</td>
                            <td>{{ $item->item->name }}</td>
                            <td class="item-code text-primary fw-semibold">{{ $item->item->code }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center">
                              @if($item->scanned_at)
                                <span class="badge bg-success px-3 py-2">Sudah dipindai</span>
                              @else
                                <span class="badge bg-secondary px-3 py-2">Belum dipindai</span>
                              @endif
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>

                  <!-- ✅ MODAL SCAN BARANG -->
                  <div class="modal fade" id="scanModal{{ $cart->id }}" tabindex="-1" aria-labelledby="scanModalLabel{{ $cart->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                      <div class="modal-content border-0 shadow-lg rounded-3">
                        <div class="modal-header bg-primary text-white rounded-top-3">
                          <h5 class="modal-title" id="scanModalLabel{{ $cart->id }}">
                            <i class="bi bi-qr-code-scan me-2"></i>Pindai Barang - {{ $cart->user->name ?? 'Guest' }}
                          </h5>
                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form class="scan-form p-3" data-cart-id="{{ $cart->id }}">
                          <div class="row mb-3 align-items-center">
                            <div class="col-md-8 mb-2 mb-md-0">
                              <input type="text" class="form-control barcode-input rounded-pill px-3 py-2"
                                     placeholder="🔍 Scan atau ketik kode barang lalu tekan Enter">
                            </div>
                            <div class="col-md-4 text-end">
                              <button type="submit" class="btn btn-primary save-scan-btn rounded-pill px-4 py-2">
                                Simpan Hasil Scan
                              </button>
                            </div>
                          </div>

                          <div class="scan-result small text-muted mb-3"></div>

                          <div class="table-responsive border rounded">
                            <table class="table table-sm table-hover align-middle mb-0">
                              <thead class="table-light text-center">
                                <tr>
                                  <th style="width:50px;">No</th>
                                  <th>Nama Barang</th>
                                  <th>Kode</th>
                                  <th style="width:80px;">Jumlah</th>
                                  <th>Status</th>
                                </tr>
                              </thead>
                              <tbody>
                                @foreach($cart->cartItems as $j => $item)
                                  <tr data-item-id="{{ $item->item->id }}">
                                    <td class="text-center">{{ $j+1 }}</td>
                                    <td>{{ $item->item->name }}</td>
                                    <td class="item-code text-primary fw-semibold">{{ $item->item->code }}</td>
                                    <td class="item-qty text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">
                                      @if($item->scanned_at)
                                        <span class="badge bg-success px-3 py-2">Sudah dipindai</span>
                                      @else
                                        <span class="badge bg-secondary px-3 py-2">Belum dipindai</span>
                                      @endif
                                    </td>
                                  </tr>
                                @endforeach
                              </tbody>
                            </table>
                          </div>
                          <div class="modal-footer mt-3 border-0">
                            <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                              <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i> Tutup
                              </button>

                              <button type="button"
                                      class="btn btn-success rounded-pill save-all-scan-btn px-4 py-2 disabled"
                                      data-cart-id="{{ $cart->id }}"
                                      disabled>
                                Simpan Semua Hasil Scan
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-3">
                  <i class="bi bi-info-circle me-1"></i> Tidak ada data permintaan pegawai.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
      {{ $approvedItems->links() }}
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
  body {
    background-color: #f4f6f9;
  }

  .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    margin: 0 6px;
  }

  .smooth-fade {
    animation: smoothFade 0.8s ease;
  }

  @keyframes smoothFade {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .breadcrumb-icon {
    transition: 0.3s ease;
  }

  .breadcrumb-icon:hover {
    transform: scale(1.1);
    background-color: #e8f0fe;
  }

  .table-hover tbody tr:hover {
    background-color: #f0f8ff;
    transition: 0.2s ease;
  }

  .btn {
    transition: 0.2s ease;
  }

  .btn:hover {
    opacity: 0.9;
  }

  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    h4.fw-bold { font-size: 1.1rem; }
    .table { font-size: 0.9rem; }
    .btn-sm { padding: 0.4rem 0.75rem; }
  }
</style>
@endpush

<script src="{{ asset('js/itemout.js') }}"></script>
