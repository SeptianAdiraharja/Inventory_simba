@props(['title', 'value', 'icon', 'color' => 'primary', 'link' => '#'])

<div class="col-md-4 col-sm-12 mb-3">
  <a href="{{ $link }}" class="text-decoration-none text-dark">
    <div class="card border-0 shadow-sm hover-scale p-3 rounded-3" style="background: #f9fafb;">
      <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
             style="width: 55px; height: 55px; background-color: var(--bs-{{ $color }}); color: white;">
          <i class="{{ $icon }}" style="font-size: 28px;"></i>
        </div>
        <div>
          <div class="fw-semibold text-secondary small">{{ $title }}</div>
          <div class="fw-bold fs-5 text-dark">{{ $value }}</div>
        </div>
      </div>
    </div>
  </a>
</div>
