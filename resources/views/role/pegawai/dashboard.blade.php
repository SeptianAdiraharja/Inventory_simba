@extends('layouts.index')

@section('content')
<div class="row gy-4">

    <!-- Request Overview Chart -->
    <div class="col-xl-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold text-primary">
                    <i class="bx bx-bar-chart-alt-2 me-2"></i> Statistik Permintaan Barang
                </h5>

                <!-- Range Filter -->
                <form method="GET" action="{{ route('pegawai.dashboard') }}" class="d-flex gap-2">
                    <input type="hidden" name="range" id="rangeInput">

                    <button type="submit" onclick="setRange('week')"
                        class="btn btn-sm {{ $range == 'week' ? 'btn-primary' : 'btn-outline-primary' }}">
                        1 Minggu
                    </button>
                    <button type="submit" onclick="setRange('month')"
                        class="btn btn-sm {{ $range == 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                        1 Bulan
                    </button>
                    <button type="submit" onclick="setRange('year')"
                        class="btn btn-sm {{ $range == 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                        1 Tahun
                    </button>
                </form>
            </div>

            <div class="card-body" style="height: 360px;">
                <canvas id="weeklyOverviewChart"></canvas>
            </div>
        </div>
    </div>
    <!-- /Request Overview Chart -->

    <!-- User Request Data Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold text-secondary">
                    <i class="bx bx-user me-2"></i>Aktivitas Permintaan
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-center">Total Request</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted">#{{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2">
                                        {{ $user->total_request }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle me-1"></i> Belum ada data pengguna dengan request.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- /User Request Data Table -->

</div>

{{-- ChartJS Script --}}
<script>
    const ctx = document.getElementById('weeklyOverviewChart');

    function setRange(value) {
        document.getElementById('rangeInput').value = value;
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($history['labels']),
            datasets: [{
                label: 'Total Request',
                data: @json($history['data']),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.15)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4e73df',
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Grafik Permintaan Barang',
                    color: '#4e73df',
                    font: { size: 16, weight: 'bold' },
                    padding: { top: 10, bottom: 20 }
                },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#333',
                    bodyColor: '#333',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    callbacks: {
                        label: (context) => `${context.formattedValue} request`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#6c757d' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f2f4f7' },
                    ticks: {
                        stepSize: 10,
                        color: '#6c757d'
                    }
                }
            }
        }
    });
</script>
@endsection
