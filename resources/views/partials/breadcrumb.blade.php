{{-- partials/breadcrumb.blade.php --}}
<nav aria-label="breadcrumb" class="ps-4 py-2">
    <ol class="breadcrumb mb-0" style="background: transparent;">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}"><i class="ri-home-4-line"></i> Home</a>
        </li>

        @php
            $segments = '';
        @endphp

        @foreach(Request::segments() as $segment)
            @php $segments .= '/'.$segment; @endphp

            @if ($loop->last)
                <li class="breadcrumb-item active text-capitalize" aria-current="page">
                    {{ str_replace('-', ' ', $segment) }}
                </li>
            @else
                <li class="breadcrumb-item text-capitalize">
                    <a href="{{ url($segments) }}">{{ str_replace('-', ' ', $segment) }}</a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
