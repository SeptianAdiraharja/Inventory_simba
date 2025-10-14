@extends('layouts.index')

@section('content')
<div class="container-fluid py-3 animate_animated animate_fadeIn">
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light text-center align-middle">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Jumlah Barang</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $index => $req)
                        <tr id="cart-row-{{ $req->cart_id }}">
                            <td class="text-center">{{ $requests->firstItem() + $index }}</td>

                            <td>
                                <strong>{{ $req->name }}</strong><br>
                                <small class="text-muted">
                                    Diajukan: {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') }}
                                </small>
                            </td>

                            <td>{{ $req->email }}</td>

                            <td>
                                <span class="badge bg-info text-dark">{{ ucfirst($req->role) }}</span>
                            </td>

                            <td class="text-center">
                                <span id="main-status-{{ $req->cart_id }}"
                                    class="badge
                                    @if($req->status == 'pending') bg-warning text-dark
                                    @elseif($req->status == 'rejected') bg-danger
                                    @elseif($req->status == 'approved') bg-success
                                    @elseif($req->status == 'approved_partially') bg-warning text-dark
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                </span>
                            </td>

                            <td class="text-center fw-semibold">{{ $req->total_quantity }}</td>

                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Aksi
                                    </button>
                                    <ul class="dropdown-menu shadow">
                                        <li>
                                            <a class="dropdown-item detail-toggle-btn"
                                               href="#detail-row-{{ $req->cart_id }}"
                                               data-cart-id="{{ $req->cart_id }}"
                                               data-bs-toggle="collapse"
                                               data-bs-target="#detail-row-{{ $req->cart_id }}">
                                                <i class="bi bi-box-seam me-1"></i> Detail (Lihat Barang)
                                            </a>
                                        </li>

                                        <li><hr class="dropdown-divider"></li>

                                        @if($req->status === 'pending')
                                            {{-- SETUJUI SEMUA --}}
                                            <li>
                                                <form action="{{ route('admin.carts.update', $req->cart_id) }}"
                                                      method="POST" class="approve-all-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="dropdown-item text-success" data-trigger="approve-all">
                                                        <i class="bi bi-check-circle me-1"></i> Setujui Semua
                                                    </button>
                                                </form>
                                            </li>

                                            {{-- TOLAK SEMUA --}}
                                            <li>
                                                <form action="{{ route('admin.carts.update', $req->cart_id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Anda yakin ingin menolak SEMUA barang dalam permintaan ini?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-x-circle me-1"></i> Tolak Semua
                                                    </button>
                                                </form>
                                            </li>
                                        @else
                                            <li>
                                                <span class="dropdown-item text-muted">
                                                    Status: {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                                </span>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        {{-- BARIS DETAIL --}}
                        <tr class="collapse" id="detail-row-{{ $req->cart_id }}">
                            <td colspan="7" class="p-0">
                                <div id="detail-content-{{ $req->cart_id }}" class="p-3 bg-light">
                                    <p class="text-center text-muted m-0">Memuat detail...</p>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    <p class="mb-0">Belum ada permintaan dengan status ini.</p>
                                    <small>Coba ubah filter untuk melihat data lain.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- =======================
     BAGIAN MODAL
======================= --}}

{{-- Modal: Tolak Barang --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2"></i> Alasan Penolakan Barang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="rejectItemForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <div class="modal-body">
                    <textarea name="reason" class="form-control" rows="3"
                              placeholder="Tulis alasan penolakan barang ini (Wajib)..." required></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Semua Barang Disetujui --}}
<div class="modal fade" id="cartProcessedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check2-circle me-2"></i> Semua Barang Telah Disetujui
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3 fs-6">
                    Semua barang dalam permintaan ini telah <strong>disetujui</strong>.<br>
                    Silakan lanjut ke halaman <strong>Scan QR</strong> untuk mengeluarkan barang dari gudang.
                </p>
                <i class="bi bi-qr-code display-4 text-success"></i>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('admin.itemout.index') }}" class="btn btn-success">
                    <i class="bi bi-qr-code-scan me-1"></i> Ke Halaman Scan QR
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Barang Disetujui Sebagian --}}
<div class="modal fade" id="cartPartiallyApprovedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i> Barang Disetujui Sebagian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3 fs-6">
                    Beberapa barang dalam permintaan ini telah <strong>disetujui</strong>,<br>
                    sedangkan beberapa lainnya <strong>ditolak</strong>.<br>
                    Silakan lanjut ke halaman <strong>Scan QR</strong> untuk mengeluarkan barang yang disetujui.
                </p>
                <i class="bi bi-qr-code display-4 text-warning"></i>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('admin.itemout.index') }}" class="btn btn-warning text-dark">
                    <i class="bi bi-qr-code-scan me-1"></i> Ke Halaman Scan QR
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- =======================
     BAGIAN SCRIPT
======================= --}}
@push('scripts')
<script src="{{ asset('js/admin-request.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        @if(session('showCartProcessedModal'))
            const modal = new bootstrap.Modal(document.getElementById("cartProcessedModal"));
            modal.show();

            const modalEl = document.getElementById("cartProcessedModal");
            modalEl.addEventListener("hidden.bs.modal", () => location.reload());
            modalEl.querySelector('a[href*="itemout"]').addEventListener("click", () => location.reload());
        @endif
    });
</script>
@endpush
@endsection
