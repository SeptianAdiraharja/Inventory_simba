<ul class="list-group list-group-flush">
  @forelse($items as $item)
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

@if(method_exists($items, 'links'))
  <div class="mt-3">
    {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
  </div>
@endif
