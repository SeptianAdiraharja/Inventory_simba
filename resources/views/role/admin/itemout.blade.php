@extends('layouts.index')
@section('content')
<div class="accordion" id="cartAccordion">
    {{-- =============================== --}}
    {{-- Data milik PEGAWAI (approved) --}}
    {{-- =============================== --}}
    @foreach($approvedItems as $cart)
        <div class="accordion-item shadow-sm mb-3">
            <h2 class="accordion-header" id="heading{{ $cart->id }}">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ $cart->id }}"
                        aria-expanded="false"
                        aria-controls="collapse{{ $cart->id }}">
                    <h6 class="mb-0">
                        Permintaan <strong>#{{ $cart->id }}</strong>
                        ({{ $cart->user->name ?? 'Guest' }})
                        @if($cart->all_scanned)
                            <span class="badge bg-success ms-2">Sudah di-scan</span>
                        @else
                            <span class="badge bg-secondary ms-2">Belum scan</span>
                        @endif
                    </h6>
                </button>
            </h2>

            <div id="collapse{{ $cart->id }}" class="accordion-collapse collapse"
                 aria-labelledby="heading{{ $cart->id }}"
                 data-bs-parent="#cartAccordion">
                <div class="accordion-body">
                    <div class="d-flex gap-2 mb-3">
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#scanModal{{ $cart->id }}">
                            Scan Barang
                        </button>

                        <a href="{{ route('admin.itemout.struk', $cart->id) }}"
                           target="_blank"
                           class="btn btn-sm btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
                        </a>
                    </div>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Item</th>
                                <th>Kode</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart->cartItems as $i => $item)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $item->item->name }}</td>
                                    <td>{{ $item->item->code }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>
                                        @if($item->scanned_at)
                                            <span class="badge bg-success">Sudah di-scan</span>
                                        @else
                                            <span class="badge bg-secondary">Belum di-scan</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

        {{-- Modal Scan --}}
        <div class="modal fade" id="scanModal{{ $cart->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Scan Barang - Cart #{{ $cart->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="barcodeInput{{ $cart->id }}"
                               class="form-control mb-3"
                               placeholder="Masukkan barcode & tekan Enter">

                        <table class="table table-bordered" id="scanTable{{ $cart->id }}">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Item</th>
                                    <th>Kode</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <button id="releaseBtn{{ $cart->id }}"
                                class="btn btn-success w-100 mt-3" disabled>
                            Keluarkan Barang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- =============================== --}}
{{-- Data milik GUEST --}}
{{-- =============================== --}}
<h4 class="mt-5 mb-3">Permintaan Tamu (Guest)</h4>
<table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>No</th>
            <th>Nama Tamu</th>
            <th>Nama Barang</th>
            <th>Kode Barang</th>
            <th>Jumlah</th>
            <th>Tanggal Keluar</th>
        </tr>
    </thead>
    <tbody>
        @forelse($guestRequests as $index => $guest)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $guest->guest_name ?? '-' }}</td>
                <td>{{ $guest->item->name ?? '-' }}</td>
                <td>{{ $guest->item->code ?? '-' }}</td>
                <td>{{ $guest->quantity }}</td>
                <td>{{ $guest->released_at ? \Carbon\Carbon::parse($guest->released_at)->format('d/m/Y H:i') : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data guest</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- =============================== --}}
{{-- Script handling Scan + Release --}}
{{-- =============================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    function setupCartScan(cartId) {
        const input = document.getElementById(`barcodeInput${cartId}`);
        const tableBody = document.querySelector(`#scanTable${cartId} tbody`);
        const releaseBtn = document.getElementById(`releaseBtn${cartId}`);
        let scannedItems = [];

        if (!input) return;

        // Scan barcode
        input.addEventListener('keypress', async (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const barcode = e.target.value.trim();
                if (!barcode) return;

                try {
                    const response = await fetch(`/admin/itemout/scan/${cartId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ barcode })
                    });

                    const data = await response.json();
                    if (data.success) {
                        scannedItems.push(data.item);
                        renderTable();
                        e.target.value = '';

                        // update badge di header
                        const header = document.querySelector(`#heading${cartId} h6`);
                        if (header) {
                            header.querySelector('.badge').classList.remove('bg-secondary');
                            header.querySelector('.badge').classList.add('bg-success');
                            header.querySelector('.badge').innerText = 'Sudah di-scan';
                        }
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error(error);
                    alert("Terjadi error saat scanning");
                }
            }
        });

        // Render table hasil scan
        function renderTable() {
            tableBody.innerHTML = '';
            scannedItems.forEach((item, index) => {
                tableBody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.name}</td>
                        <td>${item.code}</td>
                        <td>${item.quantity}</td>
                        <td><span class="badge bg-success">Sudah di-scan</span></td>
                    </tr>`;
            });
            releaseBtn.disabled = scannedItems.length === 0;
        }

        // Release barang
        releaseBtn.addEventListener('click', async () => {
            if (scannedItems.length === 0) {
                alert('Belum ada item yang discan.');
                return;
            }

            try {
                const res = await fetch(`/admin/itemout/release/${cartId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ items: scannedItems })
                });

                const result = await res.json();
                if (result.success) {
                    alert(result.message);
                    releaseBtn.disabled = true;
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error(error);
                alert("Gagal mengeluarkan barang");
            }
        });
    }

    // Jalankan untuk semua cart
    @foreach($approvedItems as $cart)
        setupCartScan({{ $cart->id }});
    @endforeach
});
</script>
@endsection
