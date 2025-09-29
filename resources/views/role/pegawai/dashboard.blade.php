@extends('layouts.index')
@section('content')
<div class="row gy-6">
    <!-- Weekly Overview Chart -->
    <div class="col-xl-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h5 class="mb-1">Request Overview ({{ ucfirst($range) }})</h5>

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
            </div>
            <div class="card-body pt-lg-2">
                <canvas id="weeklyOverviewChart"></canvas>
            </div>
        </div>
    </div>
    <!--/ Weekly Overview Chart -->

    <!-- Data Tables -->
    <div class="col-12">
        <div class="card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Total Request</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-4">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-truncate">{{ $user->name }}</h6>
                                        <small class="text-truncate">#{{ $user->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td><span class="badge bg-label-primary rounded-pill">{{ $user->total_request }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--/ Data Tables -->
</div>

{{-- ChartJS --}}
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
            backgroundColor: 'rgba(78, 115, 223, 0.15)', // area fill lebih lembut
            fill: true,
            tension: 0.4, // garis smooth
            pointBackgroundColor: '#fff',
            pointBorderColor: '#4e73df',
            pointRadius: 3,
            pointHoverRadius: 6,
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false // legend ga perlu karena cuma 1 dataset
            },
            title: {
                display: true,
                text: 'Total Request Overview',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                padding: { top: 10, bottom: 20 }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        return context.formattedValue + ' request';
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f0f0f0' // grid lebih soft
                },
                ticks: {
                    stepSize: 10
                }
            }
        }
    }
});
</script>


@endsection
