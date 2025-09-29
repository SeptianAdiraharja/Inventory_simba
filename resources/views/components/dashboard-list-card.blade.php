@props(['title', 'items', 'type'])

<div class="card h-100">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ $title }}</h5>

    <!-- Tombol View All -->
    <a href="javascript:void(0)"
       class="text-primary small fw-bold open-modal"
       data-type="{{ $type }}">
       View All
    </a>
  </div>

  <div class="card-body">
    <ul class="list-group list-group-flush">
      @forelse($items->take(5) as $item)
        <li class="list-group-item d-flex align-items-center">
          @if($type === 'barang_keluar')
            <img src="{{ asset('storage/' . ($item->item->image ?? 'default.png')) }}"
                 alt="{{ $item->item->name ?? 'No Image' }}"
                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
            <div>
              <strong>{{ $item->item->name ?? '-' }}</strong>
              <div class="text-muted small">Qty: {{ $item->quantity }}</div>
            </div>
          @elseif($type === 'request')
            <img src="{{ asset('storage/' . ($item->items->first()->image ?? 'default.png')) }}"
                 alt="{{ $item->items->first()->name ?? 'No Image' }}"
                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
            <div>
              <strong>{{ $item->items->first()->name ?? '-' }}</strong>
              <div class="text-muted small">By: {{ $item->user->name ?? 'Guest' }}</div>
            </div>
          @endif
        </li>
      @empty
        <li class="list-group-item text-center text-muted">Tidak ada data</li>
      @endforelse
    </ul>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-{{ $type }}" tabindex="-1" aria-labelledby="modalLabel-{{ $type }}" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel-{{ $type }}">Semua Data {{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" id="modal-content-{{ $type }}">
        <!-- Data akan dimuat lewat AJAX -->
        <div class="text-center py-4 text-muted">Memuat data...</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
