@extends('layouts.index')

@section('content')
<style>
    /* =============================
       ✨ UI/UX Dashboard Styling ✨
       ============================= */
    body {
        background-color: #f7f9fb !important;
    }

    .page-section {
        animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== Breadcrumb Styling ===== */
    .breadcrumb-wrapper {
        margin-bottom: 1.8rem;
        background: #ffffff;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .breadcrumb {
        background: transparent !important;
        margin-bottom: 0;
        padding: 0;
        font-size: 0.92rem;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: #6c757d;
        content: "/";
        padding: 0 0.5rem;
    }

    .breadcrumb-item a {
        color: #4e73df;
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: #6c757d;
        font-weight: 500;
    }

    .page-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1d3557;
        margin: 0;
    }

    /* ===== Card & Button ===== */
    .card {
        border-radius: 16px !important;
        border: none !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        background-color: #fff;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        border-bottom: 1px solid #eef1f5 !important;
        padding: 1rem 1.5rem !important;
        background: #fff !important;
    }

    .card-header h5 {
        font-size: 1.05rem;
        color: #1d3557;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn {
        font-size: 0.88rem !important;
        border-radius: 8px !important;
        padding: 0.45rem 0.9rem !important;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background-color: #4e73df !important;
        border-color: #4e73df !important;
    }

    .btn-outline-primary {
        border-color: #b0c4ff !important;
        color: #4e73df !important;
    }

    .btn-outline-primary:hover {
        background-color: #4e73df !important;
        color: white !important;
    }

    /* ===== Table ===== */
    .table {
        font-size: 0.95rem;
    }

    .table thead th {
        background-color: #f8f9fc !important;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #e2e6ea !important;
        text-align: left;
    }

    .table tbody tr:hover {
        background-color: #f9fbff !important;
        transition: background 0.2s ease;
    }

    .badge {
        border-radius: 8px !important;
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }

    .avatar img {
        width: 42px;
        height: 42px;
        border: 2px solid #f1f1f1;
        object-fit: cover;
        border-radius: 50%;
    }

    /* ===== Chart Area ===== */
    canvas {
        background: linear-gradient(to bottom, #ffffff, #f9faff);
        border-radius: 12px;
        padding: 10px;
    }

    /* ===== Responsiveness ===== */
    @media (max-width: 992px) {
        .breadcrumb-wrapper {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .btn-sm {
            width: 100%;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1rem !important;
        }

        .card-header h5 {
            font-size: 1rem;
        }
    }
</style>

<div class="page-section">

    <!-- ====== Breadcrumb ====== -->
    <div class="breadcrumb-wrapper">
        <h4 class="page-title"><i class="bx bx-home me-2 text-primary"></i> Dashboard Pegawai</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('pegawai.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Statistik Permintaan Barang</li>
            </ol>
        </nav>
    </div>

    <!-- ========== Statistik Permintaan Barang ========== -->
    <div class="row gy-4 mb-4">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="fw-semibold mb-0">
                        <i class="bx bx-bar-chart-alt-2 text-primary"></i>
                        Statistik Permintaan Barang
                    </h5>

                    <!-- Filter Range -->
                    <form method="GET" action="{{ route('pegawai.dashboard') }}" class="d-flex flex-wrap gap-2 mt-2 mt-sm-0">
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
    </div>

    <!-- ========== Aktivitas Permintaan Pengguna ========== -->
    <div class="row gy-4">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-semibold text-secondary mb-0">
                        <i class="bx bx-user me-2 text-primary"></i> Aktivitas Permintaan
                    </h5>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
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
                                            <div class="avatar me-3">
                                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" />
                                            </div>
                                            <div>
                                                <h6 class="fw-semibold mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">#{{ $user->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ ucfirst($user->role) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary fw-semibold">
                                            {{ $user->total_request }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Belum ada data pengguna dengan request.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                    ticks: { color: '#6c757d', font: { size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f2f4f7' },
                    ticks: {
                        stepSize: 10,
                        color: '#6c757d',
                        font: { size: 12 }
                    }
                }
            }
        }
    });
</script>
@endsection
