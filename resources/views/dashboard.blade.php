@extends('layouts.app')
@section('title', 'Dashboard')
@section('main-content')
    <div class="header-breadcrumb d-flex justify-content-between align-items-center mb-4">
        <h2 id="page-title" class="fw-bold mb-0">Dashboard</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>

    {{-- Baris 1: Info Cards (lebih clean, border tipis, shadow, warna sesuai permintaan) --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-md-3">
            {{-- Total Tiket: Biru --}}
            <div class="card shadow-sm h-100 border-start border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-uppercase fw-bold text-primary mb-1">Total Tiket</p>
                            <h4 class="mb-0">{{ $total }}</h4>
                        </div>
                        <i class="bi bi-ticket-perforator-fill fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            {{-- Status Open: Abu-abu --}}
            <div class="card shadow-sm h-100 border-start border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-uppercase fw-bold text-secondary mb-1">Status Open</p>
                            <h4 class="mb-0">{{ $open }}</h4>
                        </div>
                        <i class="bi bi-folder-fill fs-2 text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            {{-- Prioritas Tinggi: Merah --}}
            <div class="card shadow-sm h-100 border-start border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-uppercase fw-bold text-danger mb-1">Prioritas Tinggi</p>
                            <h4 class="mb-0">{{ $priority['high'] }}</h4>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            {{-- Ditutup Hari Ini: Hijau Tua --}}
            <div class="card shadow-sm h-100 border-start border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-uppercase fw-bold text-success mb-1">Ditutup Hari Ini</p>
                            <h4 class="mb-0">{{ $closedToday }}</h4>
                        </div>
                        <i class="bi bi-patch-check-fill fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 2: Doughnut Chart dan Line Chart (dengan select option) --}}
    <div class="row g-4 mb-4">
        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Status Tiket Keseluruhan</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="dailyStatusDoughnutChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Tren Tiket Bulanan</span>
                    <select id="monthSelector" class="form-select form-select-sm w-auto">
                        @foreach($monthsForSelect as $month)
                            <option value="{{ $month['value'] }}" {{ (Carbon\Carbon::now()->format('Y-m') == $month['value']) ? 'selected' : '' }}>
                                {{ $month['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="ticketLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 3: Bar Chart Prioritas dan Tabel Tiket Terbaru --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Prioritas Tiket</div>
                <div class="card-body">
                    <canvas id="priorityBarChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Daftar Tiket Terbaru</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Nomor Tiket</th>
                                    <th>Judul</th>
                                    <th>Prioritas</th>
                                    <th>Status</th>
                                    <th>Departemen</th>
                                    <th>Tanggal Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestTickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->ticket_number }}</td>
                                    <td>{{ $ticket->title }}</td>
                                    <td>
                                        @if($ticket->priority == 'high')
                                            <span class="badge bg-danger">Tinggi</span>
                                        @elseif($ticket->priority == 'medium')
                                            <span class="badge bg-warning text-dark">Sedang</span>
                                        @else
                                            <span class="badge bg-secondary">Rendah</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->status == 'open')
                                            <span class="badge bg-primary">Open</span>
                                        @elseif($ticket->status == 'in_progress')
                                            <span class="badge bg-info text-dark">In Progress</span>
                                        @elseif($ticket->status == 'closed')
                                            <span class="badge bg-success">Closed</span>
                                        @elseif($ticket->status == 'pending')
                                            <span class="badge bg-secondary">Pending</span>
                                        @else
                                            <span class="badge bg-light text-dark">Lainnya</span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->dept->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script>
    let ticketLineChart;

    const dailyStatusData = {
        open: {{ $dailyStatus['open'] ?? 0 }},
        solved: {{ $dailyStatus['solved'] ?? 0 }},
        in_progress: {{ $dailyStatus['in_progress'] ?? 0 }},
        closed: {{ $dailyStatus['closed'] ?? 0 }},
        pending: {{ $dailyStatus['pending'] ?? 0 }}
    };

    const priorityData = {
        high: {{ $priority['high'] ?? 0 }},
        medium: {{ $priority['medium'] ?? 0 }},
        low: {{ $priority['low'] ?? 0 }}
    };

    // --- Definisi Warna Chart yang Lebih Berwarna (Tidak Terlalu Pucat) ---
    const chartColors = {
        // Doughnut Chart
        gray: '#95a5a6',     // Open: Abu-abu
        blue: '#3498db',     // In Progress: Biru
        yellow: '#f1c40f',   // Pending: Kuning
        lightGreen: '#9ACD32', // Solved: Hijau Muda
        darkGreen: '#16A085',  // Closed: Hijau Tua

        // Line Chart
        lineChartColor: '#27ae60', // Hijau Tua
    };


    // --- Doughnut Chart (Status Tiket Keseluruhan) ---
    const dailyStatusCtx = document.getElementById('dailyStatusDoughnutChart').getContext('2d');
    const dailyStatusDoughnutChart = new Chart(dailyStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Open', 'In Progress', 'Pending', 'Solved', 'Closed'],
            datasets: [{
                data: [dailyStatusData.open, dailyStatusData.in_progress, dailyStatusData.pending, dailyStatusData.solved, dailyStatusData.closed],
                backgroundColor: [
                    chartColors.gray,
                    chartColors.blue,
                    chartColors.yellow,
                    chartColors.lightGreen,
                    chartColors.darkGreen
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 20,
                        padding: 20
                    }
                },
                title: {
                    display: false,
                }
            },
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 0,
                    bottom: 0
                }
            }
        }
    });

    // --- Bar Chart (Prioritas Tiket) ---
    const priorityBarCtx = document.getElementById('priorityBarChart').getContext('2d');
    const priorityBarChart = new Chart(priorityBarCtx, {
        type: 'bar',
        data: {
            labels: ['Tinggi', 'Sedang', 'Rendah'],
            datasets: [{
                data: [priorityData.high, priorityData.medium, priorityData.low],
                backgroundColor: ['#dc3545', '#ffc107', '#6c757d'],
                borderColor: ['#dc3545', '#ffc107', '#6c757d'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0,
                    grid: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });


    // --- FUNGSI UNTUK MEMBUAT / MEMPERBARUI LINE CHART ---
    function createOrUpdateLineChart(labels, data) {
        const ticketLineCtx = document.getElementById('ticketLineChart').getContext('2d');

        if (ticketLineChart) {
            ticketLineChart.destroy();
        }

        ticketLineChart = new Chart(ticketLineCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Tiket',
                    data: data,
                    backgroundColor: chartColors.lineChartColor + '40', // Tambahkan opasitas
                    borderColor: chartColors.lineChartColor,
                    tension: 0.4,
                    borderWidth: 3,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // --- PANGGIL FUNGSI UNTUK INISIALISASI PERTAMA KALI (Default data dari Controller) ---
    createOrUpdateLineChart({!! json_encode($monthlyDates) !!}, {!! json_encode($monthlyCounts) !!});


    // --- JavaScript untuk Select Option Line Chart ---
    document.getElementById('monthSelector').addEventListener('change', function() {
        const selectedMonth = this.value;
        fetch(`{{ route('dashboard.monthly-tickets') }}?month=${selectedMonth}`)
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server responded with status ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                createOrUpdateLineChart(data.labels, data.data);
            })
            .catch(error => {
                console.error('Error fetching monthly ticket data:', error);
                alert('Gagal memuat data tiket bulanan. Silakan coba lagi. Detail: ' + error.message);
            });
    });

</script>
@endsection
