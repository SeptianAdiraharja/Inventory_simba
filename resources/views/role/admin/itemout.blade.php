@extends('layouts.index')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Daftar Permintaan Pegawai & Guest</h4>
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
            <li><a class="dropdown-item filter-btn" data-filter="all" href="#">Semua</a></li>
            <li><a class="dropdown-item filter-btn" data-filter="pegawai" href="#">Pegawai</a></li>
            <li><a class="dropdown-item filter-btn" data-filter="guest" href="#">Guest</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item filter-btn" data-filter="scanned" href="#">Sudah di-scan semua</a></li>
            <li><a class="dropdown-item filter-btn" data-filter="not-scanned" href="#">Belum di-scan semua</a></li>
        </ul>
    </div>
</div>

{{-- ======================== --}}
{{-- BAGIAN 1: PEGAWAI --}}
{{-- ======================== --}}
<div class="table-responsive section-pegawai">
    <table class="table table-hover table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 50px;" class="text-center">No</th>
                <th>Nama Pengguna</th>
                <th class="text-center">Status Pemindaian</th>
                <th style="width: 160px;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($approvedItems as $i => $cart)
                <tr class="cart-item align-middle"
                    data-type="pegawai"
                    data-scanned="{{ $cart->all_scanned ? 'true' : 'false' }}">
                    <td class="text-center">{{ $approvedItems->firstItem() + $i }}</td>
                    <td>
                        {{ $cart->user->name ?? 'Guest' }}<br>
                        <small class="text-muted">{{ $cart->created_at->format('d M Y H:i') }}</small>
                    </td>
                    <td class="text-center">
                        @if($cart->all_scanned)
                            <span class="badge bg-success small">Sudah dipindai semua</span>
                        @else
                            <span class="badge bg-secondary small">Belum dipindai semua</span>
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

                {{-- DETAIL --}}
                <tr class="collapse" id="collapse{{ $cart->id }}">
                    <td colspan="4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3">
                                    <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#scanModal{{ $cart->id }}">
                                        <i class="bi bi-qr-code-scan"></i> Pindai Barang
                                    </button>
                                </div>

                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:50px;" class="text-center">No</th>
                                            <th>Nama Barang</th>
                                            <th>Kode</th>
                                            <th style="width:80px;" class="text-center">Jumlah</th>
                                            <th class="text-center">Status</th>
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
                                                        <span class="badge bg-success small">Sudah dipindai</span>
                                                    @else
                                                        <span class="badge bg-secondary small">Belum dipindai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-3 section-pegawai">
    {{ $approvedItems->links() }}
</div>

{{-- ======================== --}}
{{-- BAGIAN 2: TAMU --}}
{{-- ======================== --}}
<hr class="my-4">

<div class="table-responsive section-guest">
    <h4 class="mb-3">Daftar Barang Keluar Tamu</h4>
    <table class="table table-hover table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th>Nama Tamu</th>
                <th class="text-center">Tanggal Keluar</th>
                <th class="text-center" style="width: 160px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($guestItemOuts as $i => $guest)
            <tr class="align-middle cart-item"
                data-type="guest"
                data-scanned="true">
                <td class="text-center">{{ $guestItemOuts->firstItem() + $i }}</td>
                <td>
                    {{ $guest->name }}<br>
                    <small class="text-muted">Telp: {{ $guest->phone }}</small>
                </td>
                <td class="text-center">
                    {{ optional($guest->guestCart?->updated_at)->format('d M Y H:i') ?? '-' }}
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseGuest{{ $guest->id }}"
                        aria-expanded="false"
                        aria-controls="collapseGuest{{ $guest->id }}">
                        <i class="bi bi-eye"></i> Detail
                    </button>
                </td>
            </tr>

            {{-- DETAIL TAMU --}}
            <tr class="collapse" id="collapseGuest{{ $guest->id }}">
                <td colspan="4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5>Detail Barang Keluar</h5>
                            @if($guest->guestCart && $guest->guestCart->items->count() > 0)
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:50px;">No</th>
                                        <th>Nama Barang</th>
                                        <th>Kode</th>
                                        <th class="text-center" style="width:80px;">Jumlah</th>
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
                            <div class="text-muted text-center">Tidak ada item untuk tamu ini.</div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">Tidak ada data barang keluar tamu.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-3 section-guest">
    {{ $guestItemOuts->links() }}
</div>

@endsection

{{-- ======================== --}}
{{-- SCRIPT FILTER --}}
{{-- ======================== --}}
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

            // Default tampilkan semua
            rows.forEach(row => row.style.display = "");
            sectionPegawai.forEach(sec => sec.style.display = "");
            sectionGuest.forEach(sec => sec.style.display = "");

            if (filter === "pegawai") {
                sectionGuest.forEach(sec => sec.style.display = "none");
            }
            else if (filter === "guest") {
                sectionPegawai.forEach(sec => sec.style.display = "none");
            }
            else if (filter === "scanned") {
                rows.forEach(row => {
                    if (row.getAttribute("data-scanned") !== "true") {
                        row.style.display = "none";
                    }
                });
            }
            else if (filter === "not-scanned") {
                rows.forEach(row => {
                    if (row.getAttribute("data-scanned") !== "false") {
                        row.style.display = "none";
                    }
                });
            }
        });
    });
});
</script>
