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
{{-- Ticketing --}}
    <section id="ticketing">
        <div class="row g-4 mb-4">
            <h4 class="fw-bold text-success mb-0">Ticketing <hr></h4>
            <div class="col-sm-6 col-md-3">
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
    </section>

{{-- KWH --}}
    <section>
        <div class="row g-4 mb-4">
            <h4 class="fw-bold text-success mb-0">
                KWH Meter
                <hr>
            </h4>
        </div>

        <!-- FILTER -->
        <div class="row g-4 mb-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Bulan</label>
                <select id="bulanSelect" class="form-select">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Tahun</label>
                <select id="tahunSelect" class="form-select">
                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                    <option value="{{ now()->year - 1 }}">{{ now()->year - 1 }}</option>
                </select>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-1 border-primary">
                    <div class="card-body">
                        <small class="text-muted">Total Pemakaian</small>
                        <h3 class="fw-bold text-primary" id="card-total">-</h3>
                        <small class="text-muted">kWh</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-1 border-success">
                    <div class="card-body">
                        <small class="text-muted">Rata-rata Harian</small>
                        <h3 class="fw-bold text-success" id="card-rata">-</h3>
                        <small class="text-muted">kWh / hari</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-1 border-secondary">
                    <div class="card-body">
                        <small class="text-muted">Hari Tercatat</small>
                        <h3 class="fw-bold text-secondary" id="card-hari">-</h3>
                        <small class="text-muted">hari</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFIK -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Grafik Pemakaian kWh</h6>
                <canvas id="kwhChart" height="90"></canvas>
            </div>
        </div>
    </section>

{{-- Preventive Spesialist --}}
    <section>
        <div class="row g-4 my-4">
            <h4 class="fw-bold text-success mb-0">
                Preventive Spesialist
                <hr>
            </h4>
        </div>
        {{-- FILTER BULAN & TAHUN --}}
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="month" class="form-label">Bulan</label>
                        <select name="month" id="month" class="form-select">
                            @foreach($nama_bulan as $num => $name)
                                <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="year" class="form-label">Tahun</label>
                        <select id="year" class="form-select" name="year">
                            <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                            <option value="{{ now()->year - 1 }}">{{ now()->year - 1 }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Tampilkan Data</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RINGKASAN GLOBAL --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title" id="card-title-preventive">Pencapaian PM Global Bulan {{ $nama_bulan[$currentMonth] ?? '' }} {{ $currentYear }}</h5>
                    </div>
                    <div class="card-body">
                        <h1 class="display-4 fw-bold" id="overallPercentage">0%</h1>
                        <p class="card-text" id="globalSummaryText">Memuat data...</p>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar text-white fw-bold" role="progressbar" id="globalProgress" 
                                style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="row mb-4">
            {{-- Chart Equipment --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Grafik Pencapaian Per Tipe Equipment</div>
                    <div class="card-body">
                        <canvas id="equipmentChart"></canvas>
                    </div>
                </div>
            </div>
            {{-- Chart Specialist --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Grafik Beban Kerja Spesialis</div>
                    <div class="card-body">
                        <canvas id="specialistChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL EQUIPMENT --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>Detail Pencapaian PM Per Tipe Equipment</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="equipmentTable">
                        <thead>
                            <tr>
                                <th>Tipe Equipment</th>
                                <th>Target Unit</th>
                                <th>Jadwal Dibuat</th>
                                <th>Realisasi (Completed)</th>
                                <th>Persentase (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABEL SPECIALIST --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>Detail Beban Kerja Spesialis</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="specialistTable">
                        <thead>
                            <tr>
                                <th>Spesialis</th>
                                <th>Ditugaskan (Beban)</th>
                                <th>Selesai (Completed)</th>
                                <th>Persentase (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="4" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

{{-- Preventive Shift --}}
    <section>
        <div class="row g-4 my-4">
            <h4 class="fw-bold text-success mb-0">
                Preventive Shift
                <hr>
            </h4>
        </div>
        {{-- FILTER BULAN & TAHUN --}}
        <div class="card mb-4">
            <div class="card-body">
                <form id="shiftFilterForm" class="row g-3 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label for="shiftMonth" class="form-label">Bulan</label>
                        <select name="month" id="shiftMonth" class="form-select">
                            @foreach($nama_bulan as $num => $name)
                                <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label for="shiftYear" class="form-label">Tahun</label>
                        <input type="number" name="year" id="shiftYear" class="form-control" value="{{ $currentYear }}">
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <button type="submit" class="btn btn-primary w-100">Tampilkan Data</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RINGKASAN GLOBAL --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h5 class="card-title">Pencapaian PM Shift Bulan <span id="shiftCurrentMonthText">{{ $nama_bulan[$currentMonth] }}</span> {{ $currentYear }}</h5>
                    </div>
                    <div class="card-body">
                        <h1 class="display-4 fw-bold" id="shiftOverallPercentage">0%</h1>
                        <p id="shiftGlobalSummaryText">Memuat data...</p>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar fw-bold" role="progressbar" style="width:0%" id="shiftGlobalProgressBar" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Grafik Pencapaian Per Tugas PM</div>
                    <div class="card-body">
                        <canvas id="shiftEquipmentChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Grafik Kinerja Per Shift</div>
                    <div class="card-body">
                        <canvas id="shiftSpecialistChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE EQUIPMENT --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>Detail Pencapaian PM Per Tugas 
                    {{-- (Master ID) --}}
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="shiftEquipmentTable">
                        <thead>
                            <tr>
                                <th>Master PM Task</th>
                                <th>Target Unit</th>
                                <th>Jadwal Dibuat</th>
                                <th>Realisasi (Completed)</th>
                                <th>Persentase (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABLE SPECIALIST --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>Detail Kinerja Shift</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="shiftSpecialistTable">
                        <thead>
                            <tr>
                                <th>Shift Name</th>
                                <th>Ditugaskan (Beban)</th>
                                <th>Selesai (Completed)</th>
                                <th>Persentase (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="4" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABLE USER PERFORMANCE --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>Detail Kinerja Per User</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Kinerja dihitung berdasarkan perbandingan total tugas yang diselesaikan user dengan total tugas yang terjadwal di Shift yang bersangkutan.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="shiftUserPerformanceTable">
                        <thead>
                            <tr>
                                <th>User ID / Nama</th>
                                <th>Assigned Shift</th>
                                <th>Total Tugas Shift</th>
                                <th>Selesai Oleh User (Completed)</th>
                                <th>Persentase Kinerja (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
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
                    backgroundColor: chartColors.lineChartColor + '40', 
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
                        },
                        ticks: {
                            stepSize: 1
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

{{-- kwh meter --}}
<script>
function animateNumber(el, start, end, duration = 800, decimals = 2) {
    const range = end - start;
    const startTime = performance.now();

    function step(currentTime) {
        const progress = Math.min((currentTime - startTime) / duration, 1);
        const value = start + range * progress;

        el.innerText = decimals === 0
            ? Math.round(value)
            : value.toFixed(decimals);

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    }

    requestAnimationFrame(step);
}
</script>


<script>
let kwhChart = null;

function initChart() {
    if (kwhChart) return; 

    const ctx = document.getElementById('kwhChart');
    if (!ctx) return;

    kwhChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: '#34B26A',
                backgroundColor: '#C9EBD7',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#34B26A',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

</script>

<script>
    function updateChart(res) {
        if (!kwhChart) return;

        kwhChart.data.labels = res.labels;
        kwhChart.data.datasets[0].data = res.values;
        kwhChart.update();
    }

function loadDashboard() {
    const bulan = document.getElementById('bulanSelect').value;
    const tahun = document.getElementById('tahunSelect').value;

    // KPI 
    fetch(`/dashboard/kwh/summary?bulan=${bulan}&tahun=${tahun}`)
        .then(res => res.json())
        .then(res => {
            animateNumber(document.getElementById('card-total'), 0, res.total, 800, 2);
            animateNumber(document.getElementById('card-rata'), 0, res.rata_rata, 800, 2);
            animateNumber(document.getElementById('card-hari'), 0, res.jumlah_hari, 600, 0);
        });

    // CHART 
    fetch(`/dashboard/kwh/chart?bulan=${bulan}&tahun=${tahun}`)
        .then(res => res.json())
        .then(res => updateChart(res));
}

document.addEventListener('DOMContentLoaded', () => {
    const now = new Date();
    document.getElementById('bulanSelect').value = now.getMonth() + 1;
    document.getElementById('tahunSelect').value = now.getFullYear();

    initChart();      // chart dibuat sekali
    loadDashboard();  // data masuk animasi

    document.getElementById('bulanSelect').addEventListener('change', loadDashboard);
    document.getElementById('tahunSelect').addEventListener('change', loadDashboard);
});
</script>


{{-- preventive spesialist --}}
<script>
    function fetchJSON(url, params) {
        const query = new URLSearchParams(params).toString();
        return fetch(url + '?' + query).then(res => res.json());
    }

    function loadGlobalSummary(month, year) {
        fetchJSON('{{ route("preventive-v2.data.global-summary") }}', { month, year })
            .then(data => {
                // console.log(data);
                // console.log(document.getElementById('overallPercentage'));
                // console.log(document.getElementById('globalSummaryText'));
                document.getElementById('overallPercentage').innerText = data.overallPercentage + '%';
                document.getElementById('globalSummaryText').innerText = `${data.totalCompleted} dari ${data.totalScheduled} Jadwal PM selesai (Target: ${data.totalTarget})`;
                const progress = document.getElementById('globalProgress');
                progress.style.width = data.overallPercentage + '%';
                progress.ariaValueNow = data.overallPercentage;
                progress.innerText = data.overallPercentage + '%';
            });
    }

    function loadEquipmentChart(month, year) {
        fetchJSON('{{ route("preventive-v2.data.chart-equipment") }}', { month, year })
            .then(data => {
                const labels = data.map(d => d.equipment_type);
                const totalScheduled = data.map(d => d.total_scheduled);
                const totalCompleted = data.map(d => d.completed_count);

                // console.log(data, totalScheduled,totalCompleted);

                // Destroy chart lama jika sudah ada
                if (window.equipmentChart instanceof Chart) {
                    window.equipmentChart.destroy();
                }

                const ctx = document.getElementById('equipmentChart').getContext('2d');
                window.equipmentChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Jadwal Dibuat',
                                data: totalScheduled,
                                backgroundColor: 'rgba(255, 159, 64, 0.7)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                            },
                            {
                                label: 'Completed',
                                data: totalCompleted,
                                backgroundColor: 'rgba(75, 192, 192, 0.7)', // Green
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: 50,     
                                title: { display: true, text: 'Jumlah Jadwal' }
                            }
                        }
                    }
                });
            });
    }


    function loadSpecialistChart(month, year) {
        fetchJSON('{{ route("preventive-v2.data.chart-specialist") }}', { month, year })
            .then(data => {
                const labels = data.map(d => d.specialist_name);
                const totalAssigned = data.map(d => d.total_assigned);
                const totalCompleted = data.map(d => d.total_completed);

                // Destroy chart lama jika sudah ada
                if (window.specialistChart instanceof Chart) {
                    window.specialistChart.destroy();
                }

                const ctx = document.getElementById('specialistChart').getContext('2d');
                window.specialistChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Total Assigned',
                                data: totalAssigned,
                                backgroundColor: 'rgba(54, 162, 235, 0.7)'
                            },
                            {
                                label: 'Completed',
                                data: totalCompleted,
                                backgroundColor: 'rgba(75, 192, 192, 0.7)'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: 10,     
                                ticks: {
                                    stepSize: 1 
                                },
                                title: { display: true, text: 'Jumlah Jadwal' }
                            }
                        }
                    }
                });
            });
    }



    function loadEquipmentTable(month, year) {
        fetchJSON('{{ route("preventive-v2.data.table-equipment") }}', { month, year })
            .then(data => {
                const tbody = document.querySelector('#equipmentTable tbody');
                tbody.innerHTML = '';
                data.forEach(d => {
                    const badgeClass = d.percentage >= 100 ? 'bg-success' : (d.percentage >= 80 ? 'bg-info' : 'bg-danger');
                    tbody.innerHTML += `<tr>
                        <td>${d.equipment_type}</td>
                        <td>${d.target_count}</td>
                        <td>${d.total_scheduled}</td>
                        <td>${d.completed_count}</td>
                        <td><span class="badge ${badgeClass}">${d.percentage}%</span></td>
                    </tr>`;
                });
            });
    }

    function loadSpecialistTable(month, year) {
        fetchJSON('{{ route("preventive-v2.data.table-specialist") }}', { month, year })
            .then(data => {
                const tbody = document.querySelector('#specialistTable tbody');
                tbody.innerHTML = '';
                data.forEach(d => {
                    const badgeClass = d.percentage >= 100 ? 'bg-success' : (d.percentage >= 80 ? 'bg-info' : 'bg-warning text-dark');
                    tbody.innerHTML += `<tr>
                        <td>${d.specialist_name}</td>
                        <td>${d.total_assigned}</td>
                        <td>${d.total_completed}</td>
                        <td><span class="badge ${badgeClass}">${d.percentage}%</span></td>
                    </tr>`;
                });
            });
    }

    // LOAD ALL DATA
    function loadAll() {
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;
        const preventiveCard = document.getElementById('card-title-preventive');
        const nama_bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni','Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];


        loadGlobalSummary(month, year);
        loadEquipmentChart(month, year);
        loadSpecialistChart(month, year);
        loadEquipmentTable(month, year);
        loadSpecialistTable(month, year);
        preventiveCard.innerHTML = 'Pencapaian PM Global Bulan ' + nama_bulan[month] + ' ' + year;
    }

    // Filter form submit
    document.getElementById('filterForm').addEventListener('submit', function(e){
        e.preventDefault();
        loadAll();
    });

    // LOAD awal
    loadAll();
</script>

{{-- Preventive shift --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthInput = document.getElementById('shiftMonth');
    const yearInput = document.getElementById('shiftYear');

    const equipmentCtx = document.getElementById('shiftEquipmentChart').getContext('2d');
    const specialistCtx = document.getElementById('shiftSpecialistChart').getContext('2d');

    let shiftEquipmentChart = new Chart(equipmentCtx, {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Persentase (%)', data: [], backgroundColor: 'rgba(54, 162, 235, 0.6)' }] },
        options: { responsive:true, scales: { y: { beginAtZero: true, max: 100 } } }
    });

    let shiftSpecialistChart = new Chart(specialistCtx, {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Persentase (%)', data: [], backgroundColor: 'rgba(255, 206, 86, 0.6)' }] },
        options: { responsive:true, scales: { y: { beginAtZero: true, max: 100 } } }
    });

    function loadDashboard() {
        const month = monthInput.value;
        const year = yearInput.value;

        // Global Summary
        fetch(`{{ route('pm_shift.globalSummary') }}?month=${month}&year=${year}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('shiftOverallPercentage').innerText = data.overallPercentage + '%';
            document.getElementById('shiftGlobalProgressBar').style.width = data.overallPercentage + '%';
            document.getElementById('shiftGlobalProgressBar').innerText = data.overallPercentage + '%';
            document.getElementById('shiftGlobalSummaryText').innerText = `${data.totalCompleted} dari ${data.totalScheduled} Jadwal PM telah Selesai.`;
        });

        // Chart Data
        fetch(`{{ route('pm_shift.chartData') }}?month=${month}&year=${year}`)
        .then(res => res.json())
        .then(data => {
            // Equipment Chart
            shiftEquipmentChart.data.labels = data.equipmentData.map(d => d.task_name);
            shiftEquipmentChart.data.datasets[0].data = data.equipmentData.map(d => d.percentage);
            shiftEquipmentChart.update();

            // Specialist Chart
            shiftSpecialistChart.data.labels = data.specialistData.map(d => d.shift_name);
            shiftSpecialistChart.data.datasets[0].data = data.specialistData.map(d => d.percentage);
            shiftSpecialistChart.update();
        });

        // Equipment Table
        fetch(`{{ route('pm_shift.table.equipment') }}?month=${month}&year=${year}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#shiftEquipmentTable tbody');
            tbody.innerHTML = '';
            if(data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>`;
            } else {
                data.forEach(d => {
                    let badgeClass = d.percentage >= 100 ? 'bg-success' : (d.percentage >= 80 ? 'bg-info' : 'bg-danger');
                    tbody.innerHTML += `<tr>
                        <td>${d.task_name}</td>
                        <td>1</td>
                        <td>${d.total_scheduled}</td>
                        <td>${d.completed_count}</td>
                        <td><span class="badge ${badgeClass}">${d.percentage}%</span></td>
                    </tr>`;
                });
            }
        });

        // Specialist Table
        fetch(`{{ route('pm_shift.table.specialist') }}?month=${month}&year=${year}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#shiftSpecialistTable tbody');
            tbody.innerHTML = '';
            if(data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center">Tidak ada data.</td></tr>`;
            } else {
                data.forEach(d => {
                    let badgeClass = d.percentage >= 100 ? 'bg-success' : (d.percentage >= 80 ? 'bg-info' : 'bg-warning text-dark');
                    tbody.innerHTML += `<tr>
                        <td>${d.shift_name}</td>
                        <td>${d.total_assigned}</td>
                        <td>${d.total_completed}</td>
                        <td><span class="badge ${badgeClass}">${d.percentage}%</span></td>
                    </tr>`;
                });
            }
        });

        // User Performance Table
        fetch(`{{ route('pm_shift.table.user') }}?month=${month}&year=${year}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#shiftUserPerformanceTable tbody');
            tbody.innerHTML = '';
            if(data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>`;
            } else {
                data.forEach(d => {
                    let badgeClass = d.percentage >= 100 ? 'bg-success' : (d.percentage >= 80 ? 'bg-info' : 'bg-warning text-dark');
                    tbody.innerHTML += `<tr>
                        <td>${d.user_name}</td>
                        <td>${d.assigned_shift}</td>
                        <td>${d.total_assigned_to_shift}</td>
                        <td>${d.total_completed}</td>
                        <td><span class="badge ${badgeClass}">${d.percentage}%</span></td>
                    </tr>`;
                });
            }
        });
    }

    // Load awal
    loadDashboard();

    // Filter form submit
    document.getElementById('shiftFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadDashboard();
        document.getElementById('shiftCurrentMonthText').innerText = monthInput.options[monthInput.selectedIndex].text;
    });
});
</script>

@endsection
