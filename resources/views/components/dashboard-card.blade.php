@props(['title', 'value', 'icon', 'color' => 'primary', 'link' => '#'])

<div class="col-md-4 col-sm-12">
  <a href="{{ $link }}" class="text-decoration-none">
    <div class="d-flex align-items-center justify-content-center p-3 text-white rounded shadow-sm bg-{{ $color }} hover-shadow transition" style="min-height: 80px; border-radius: 8px;">
      <div class="me-3">
        <i class="{{ $icon }}" style="font-size: 32px;"></i>
      </div>
      <div class="text-start">
        <div class="fw-semibold" style="font-size: 15px;">{{ $title }}</div>
        <div style="font-size: 13px;">{{ $value }} {{ Str::plural($title) }}</div>
      </div>
    </div>
  </a>
</div>
