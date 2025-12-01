@props(['title', 'value', 'icon', 'color' => 'primary', 'link' => '#'])

<div class="col-xl-4 col-md-6">
    <div class="card border-0 shadow-sm rounded-4 h-100 hover-card" style="border-left: 4px solid var(--bs-{{ $color }});">
        <div class="card-body d-flex align-items-center justify-content-between p-4">
            <div class="flex-grow-1">
                <h6 class="card-title text-muted mb-2 fw-semibold small">{{ $title }}</h6>
                <h3 class="fw-bold mb-0" style="color: var(--bs-{{ $color }});">{{ $value }}</h3>
                <small class="text-muted">Total data</small>
            </div>
            <div class="icon-container rounded-circle d-flex align-items-center justify-content-center ms-3"
                 style="width: 60px; height: 60px; background: rgba(var(--bs-{{ $color }}-rgb), 0.1);">
                <i class="{{ $icon }} fs-4" style="color: var(--bs-{{ $color }});"></i>
            </div>
        </div>
        @if($link)
        <div class="card-footer bg-transparent border-0 pt-0">
            <a href="{{ $link }}" class="btn btn-sm w-100 rounded-bottom-4 rounded-top-0 fw-semibold hover-scale"
               style="background: rgba(var(--bs-{{ $color }}-rgb), 0.1); color: var(--bs-{{ $color }});">
                Lihat Detail <i class="ri-arrow-right-line ms-1"></i>
            </a>
        </div>
        @endif
    </div>
</div>