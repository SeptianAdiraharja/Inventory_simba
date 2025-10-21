@extends('layouts.index')
@section('content')

<!-- ======================== -->
<!-- 🔹 HEADER & FILTER -->
<!-- ======================== -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <h4 class="fw-bold text-primary mb-2">
    <i class="bi bi-box-seam me-2"></i>Daftar Permintaan Pegawai & Guest
  </h4>
</div>

<!-- ======================== -->
<!-- 🔹 BAGIAN 1: PEGAWAI -->
<!-- ======================== -->
<div class="section-pegawai">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="mb-0 text-dark fw-semibold">
        <i class="bi bi-person-badge me-2 text-primary"></i>Permintaan dari Pegawai
      </h5>
      <small class="text-muted">Menampilkan data pegawai dengan status approved</small>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-bordered align-middle mb-0">
        <thead class="table-primary">
          <tr class="text-center">
            <th style="width: 50px;">No</th>
            <th>Nama Pengguna</th>
            <th>Status Pemindaian</th>
            <th style="width: 160px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($approvedItems as $i => $cart)
            <tr class="cart-item"
                data-type="pegawai"
                data-scanned="{{ $cart->all_scanned ? 'true' : 'false' }}">
              <td class="text-center">{{ $approvedItems->firstItem() + $i }}</td>
              <td>
                <strong>{{ $cart->user->name ?? 'Guest' }}</strong><br>
                <small class="text-muted d-block">
                  <i class="bi bi-calendar-event me-1"></i>{{ $cart->created_at->format('d M Y H:i') }}
                </small>
               <p class="text-primary fw-semibold">
                <i class="bi bi-box-seam me-1"></i>{{ $cart->cartItems->count() }} Barang Belum Dipindai
              </p>
              </td>
              <td class="text-center">
                @if($cart->all_scanned)
                  <span class="badge bg-success">✅ Sudah dipindai semua</span>
                @else
                  <span class="badge bg-secondary">⏳ Belum dipindai semua</span>
                @endif
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline-primary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ $cart->id }}"
                        aria-expanded="false"
                        aria-controls="collapse{{ $cart->id }}">
                  <i class="bi bi-eye"></i> Detail ({{ $cart->cartItems->count() }})
                </button>
              </td>
            </tr>

            <!-- DETAIL ITEM PEGAWAI -->
            <tr class="collapse bg-light" id="collapse{{ $cart->id }}">
              <td colspan="4">
                <div class="p-3">
                  <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#scanModal{{ $cart->id }}">
                      <i class="bi bi-qr-code-scan me-1"></i> Pindai Barang
                    </button>
                  </div>

                  <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                      <tr class="text-center">
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
                          <td class="item-code">{{ $item->item->code }}</td>
                          <td class="text-center">{{ $item->quantity }}</td>
                          <td class="text-center">
                            @if($item->scanned_at)
                              <span class="badge bg-success">Sudah dipindai</span>
                            @else
                              <span class="badge bg-secondary">Belum dipindai</span>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <!-- ✅ MODAL SCAN BARANG -->
                <div class="modal fade" id="scanModal{{ $cart->id }}" tabindex="-1" aria-labelledby="scanModalLabel{{ $cart->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-centered"> {{-- pakai modal-lg supaya muat tabel --}}
                    <div class="modal-content">
                      <div class="modal-header bg-primary text-white mb-0">
                        <h5 class="modal-title" id="scanModalLabel{{ $cart->id }}">
                          <i class="bi bi-qr-code-scan me-2"></i>Pindai Barang - {{ $cart->user->name ?? 'Guest' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>

                      <form class="scan-form p-3" data-cart-id="{{ $cart->id }}">
                        <div class="row mb-3">
                          <div class="col-md-8">
                            <input type="text" class="form-control barcode-input" placeholder="🔍 Scan atau ketik kode barang lalu tekan Enter">
                          </div>
                          <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-primary save-scan-btn">
                                Simpan Hasil Scan
                            </button>
                          </div>
                        </div>

                        <div class="scan-result small text-muted mb-3"></div>

                        <!-- ✅ Tambahkan tabel daftar barang di modal -->
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
                                <tr>
                                  <td class="text-center">{{ $j+1 }}</td>
                                  <td>{{ $item->item->name }}</td>
                                  <td class="item-code">{{ $item->item->code }}</td>
                                  <td class="text-center">{{ $item->quantity }}</td>
                                  <td class="text-center">
                                    @if($item->scanned_at)
                                      <span class="badge bg-success">Sudah dipindai</span>
                                    @else
                                      <span class="badge bg-secondary">Belum dipindai</span>
                                    @endif
                                  </td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                        <div class="modal-footer">
                          <div class="d-flex justify-content-between w-100">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                              <i class="bi bi-x-circle me-1"></i> Tutup
                            </button>

                            <button type="button" class="btn btn-success save-all-scan-btn" data-cart-id="{{ $cart->id }}">
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


@endsection

@push('styles')
<style>
  td small.fw-semibold {
    display: inline-block;
    margin-top: 4px;
  }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/itemout.js') }}"></script>
@endpush
