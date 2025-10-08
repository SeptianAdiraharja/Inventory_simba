@extends('layouts.index')
@section('content')

<!-- ======================== -->
<!-- 🔹 HEADER & FILTER -->
<!-- ======================== -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <h4 class="fw-bold text-primary mb-2">
    <i class="bi bi-box-seam me-2"></i>Daftar Permintaan Pegawai & Guest
  </h4>
  <div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-funnel me-1"></i> Filter Data
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
      <li><a class="dropdown-item filter-btn" data-filter="all" href="#">📋 Semua Data</a></li>
      <li><a class="dropdown-item filter-btn" data-filter="pegawai" href="#">👨‍💼 Pegawai</a></li>
      <li><a class="dropdown-item filter-btn" data-filter="guest" href="#">👤 Guest</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item filter-btn" data-filter="scanned" href="#">✅ Sudah di-scan semua</a></li>
      <li><a class="dropdown-item filter-btn" data-filter="not-scanned" href="#">🚫 Belum di-scan semua</a></li>
    </ul>
  </div>
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
                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ $cart->created_at->format('d M Y H:i') }}</small>
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
                  <i class="bi bi-eye"></i> Detail
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
                        <tr>
                          <td class="text-center">{{ $j+1 }}</td>
                          <td>{{ $item->item->name }}</td>
                          <td>{{ $item->item->code }}</td>
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

<!-- ======================== -->
<!-- 🔹 BAGIAN 2: TAMU -->
<!-- ======================== -->
<hr class="my-5">

<div class="section-guest">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="mb-0 text-dark fw-semibold">
        <i class="bi bi-person-lines-fill me-2 text-warning"></i>Daftar Barang Keluar Tamu
      </h5>
      <small class="text-muted">Data untuk tamu yang telah melakukan transaksi</small>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-bordered align-middle mb-0">
        <thead class="table-warning">
          <tr class="text-center">
            <th style="width: 50px;">No</th>
            <th>Nama Tamu</th>
            <th>Tanggal Keluar</th>
            <th style="width: 160px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($guestItemOuts as $i => $guest)
            <tr class="cart-item" data-type="guest" data-scanned="true">
              <td class="text-center">{{ $guestItemOuts->firstItem() + $i }}</td>
              <td>
                <strong>{{ $guest->name }}</strong><br>
                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $guest->phone }}</small>
              </td>
              <td class="text-center">
                {{ optional($guest->guestCart?->updated_at)->format('d M Y H:i') ?? '-' }}
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline-warning" type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapseGuest{{ $guest->id }}"
                  aria-expanded="false"
                  aria-controls="collapseGuest{{ $guest->id }}">
                  <i class="bi bi-eye"></i> Detail
                </button>
              </td>
            </tr>

            <!-- DETAIL TAMU -->
            <tr class="collapse bg-light" id="collapseGuest{{ $guest->id }}">
              <td colspan="4">
                <div class="p-3">
                  <h6 class="fw-bold mb-3">Detail Barang Keluar</h6>
                  @if($guest->guestCart && $guest->guestCart->items->count() > 0)
                    <table class="table table-sm table-bordered mb-0">
                      <thead class="table-light">
                        <tr class="text-center">
                          <th style="width:50px;">No</th>
                          <th>Nama Barang</th>
                          <th>Kode</th>
                          <th style="width:80px;">Jumlah</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($guest->guestCart->items as $j => $item)
                          <tr>
                            <td class="text-center">{{ $j+1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->code }}</td>
                            <td class="text-center">{{ $item->pivot->quantity }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  @else
                    <div class="text-center text-muted">Tidak ada item untuk tamu ini.</div>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted py-3">
              <i class="bi bi-info-circle me-1"></i> Tidak ada data barang keluar tamu.
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="d-flex justify-content-center mt-3">
    {{ $guestItemOuts->links() }}
  </div>
</div>

@endsection

<!-- ======================== -->
<!-- 🔹 SCRIPT FILTER -->
<!-- ======================== -->
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
  const filterButtons = document.querySelectorAll(".filter-btn");
  const rows = document.querySelectorAll(".cart-item");
  const sectionPegawai = document.querySelectorAll(".section-pegawai");
  const sectionGuest = document.querySelectorAll(".section-guest");

  filterButtons.forEach(btn => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const filter = this.getAttribute("data-filter");

      // Reset semua
      rows.forEach(row => row.style.display = "");
      sectionPegawai.forEach(sec => sec.style.display = "");
      sectionGuest.forEach(sec => sec.style.display = "");

      // Filter jenis pengguna
      if (filter === "pegawai") sectionGuest.forEach(sec => sec.style.display = "none");
      else if (filter === "guest") sectionPegawai.forEach(sec => sec.style.display = "none");

      // Filter status pemindaian
      else if (filter === "scanned")
        rows.forEach(row => { if (row.dataset.scanned !== "true") row.style.display = "none"; });
      else if (filter === "not-scanned")
        rows.forEach(row => { if (row.dataset.scanned !== "false") row.style.display = "none"; });
    });
  });
});
</script>
@endpush
