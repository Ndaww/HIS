@extends('layouts.app')
@section('title','Dashboard Preventive Maintenance')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Dashboard Preventive Maintenance</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Dashboard</div>
    </div>

    {{-- Filter Bulan dan Tahun --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pm_shift.dashboard') }}" class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label for="month" class="form-label">Bulan</label>
                    <select name="month" id="month" class="form-select">
                        @foreach($nama_bulan as $num => $name)
                            <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label for="year" class="form-label">Tahun</label>
                    <input type="number" name="year" id="year" class="form-control" value="{{ $currentYear }}">
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
            <div class="card bg-primary">
                <div class="card-header border-0">
                    <h5 class="card-title">Pencapaian PM Shift Bulan {{ $nama_bulan[$currentMonth] ?? '' }} {{ $currentYear }}</h5>
                </div>
                <div class="card-body">
                    <h1 class="display-4 fw-bold">{{ $globalSummary['overallPercentage'] }}%</h1>
                    <p class="card-text">
                        {{ number_format($globalSummary['totalCompleted']) }} dari {{ number_format($globalSummary['totalScheduled']) }} Jadwal PM telah Selesai. (Target: {{ number_format($globalSummary['totalTarget']) }})
                    </p>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar fw-bold" role="progressbar"
                             style="width: {{ $globalSummary['overallPercentage'] }}%"
                             aria-valuenow="{{ $globalSummary['overallPercentage'] }}"
                             aria-valuemin="0" aria-valuemax="100">
                             {{ $globalSummary['overallPercentage'] }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- GRAFIK PENCAPAIAN PER TIPE EQUIPMENT --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Grafik Pencapaian Per Tugas PM</div>
                <div class="card-body">
                    <canvas id="equipmentChart"></canvas>
                </div>
            </div>
        </div>

        {{-- GRAFIK BEBAN KERJA SPESIALIS --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Grafik Kinerja Per Shift</div>
                <div class="card-body">
                    <canvas id="specialistChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL PENCAPAIAN PER TIPE EQUIPMENT --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Detail Pencapaian PM Per Tugas (Master ID)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="equipmentTable">
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
                        {{-- Iterasi menggunakan data yang dihitung di Controller --}}
                        @forelse($dashboardData as $data)
                            <tr>
                                <td>{{ $data->task_name }}</td>
                                <td>{{ number_format($data->target_count) }}</td>
                                <td>{{ number_format($data->total_scheduled) }}</td>
                                <td>{{ number_format($data->completed_count) }}</td>
                                <td>
                                    <span class="badge {{ $data->percentage >= 100 ? 'bg-success' : ($data->percentage >= 80 ? 'bg-info' : 'bg-danger') }}">
                                        {{ $data->percentage }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada Tugas PM yang terjadwal untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TABEL BEBAN KERJA SPESIALIS --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Detail Kinerja Shift</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="specialistTable">
                    <thead>
                        <tr>
                            <th>Shift Name</th>
                            <th>Ditugaskan (Beban)</th>
                            <th>Selesai (Completed)</th>
                            <th>Persentase (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Iterasi menggunakan data yang dihitung di Controller --}}
                        @forelse($specialistRealization as $data)
                            <tr>
                                <td>{{ $data->specialist_name }}</td>
                                <td>{{ number_format($data->total_assigned) }}</td>
                                <td>{{ number_format($data->total_completed) }}</td>
                                <td>
                                    <span class="badge {{ $data->percentage >= 100 ? 'bg-success' : ($data->percentage >= 80 ? 'bg-info' : 'bg-warning text-dark') }}">
                                        {{ $data->percentage }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada jadwal yang ditugaskan di periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TABEL KINERJA PER USER --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Detail Kinerja Per User</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small">Kinerja dihitung berdasarkan perbandingan total tugas yang diselesaikan user dengan total tugas yang terjadwal di Shift yang bersangkutan.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="userPerformanceTable">
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
                        {{-- Iterasi menggunakan data yang dihitung di Controller --}}
                        @forelse($userPerformance as $data)
                            <tr>
                                <td>{{ $data->user_name }}</td>
                                <td>{{ $data->assigned_shift }}</td>
                                <td>{{ number_format($data->total_assigned_to_shift) }}</td>
                                <td>{{ number_format($data->total_completed) }}</td>
                                <td>
                                    <span class="badge {{ $data->percentage >= 100 ? 'bg-success' : ($data->percentage >= 80 ? 'bg-info' : 'bg-warning text-dark') }}">
                                        {{ $data->percentage }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada tugas yang diselesaikan oleh user di periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('js')
{{-- Pastikan Anda sudah mengimpor Jquery, DataTables, dan Chart.js di layout Anda, 
     atau tambahkan di sini seperti yang Anda minta: --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // INISIALISASI DATATABLES
        $('#equipmentTable').DataTable({
            "paging": false,
            "searching": false,
            "info": false,
            "order": [[ 4, "desc" ]] // Urut berdasarkan Persentase
        });

        $('#specialistTable').DataTable({
            "paging": true,
            "searching": true,
            "info": true,
            "order": [[ 2, "desc" ]] // Urut berdasarkan Completed
        });

        // =======================================================
        // LOGIC CHART.JS DENGAN DATA DARI PHP
        // =======================================================

        // Data 1: Pencapaian Per Tugas PM (menggunakan master_pm_task_id sebagai label)
        const equipmentData = @json($dashboardData);
        const equipmentLabels = equipmentData.map(d => d.equipment_type);
        const equipmentScheduled = equipmentData.map(d => d.total_scheduled);
        const equipmentCompleted = equipmentData.map(d => d.completed_count);

        new Chart(document.getElementById('equipmentChart'), {
            type: 'bar',
            data: {
                labels: equipmentLabels,
                datasets: [
                    {
                        label: 'Jadwal Dibuat',
                        data: equipmentScheduled,
                        backgroundColor: 'rgba(255, 159, 64, 0.7)', // Orange
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Selesai (Completed)',
                        data: equipmentCompleted,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)', // Green
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Data 2: Kinerja Shift
        const specialistData = @json($specialistRealization);
        const specialistNames = specialistData.map(d => d.specialist_name);
        const specialistCompleted = specialistData.map(d => d.total_completed);
        const specialistAssigned = specialistData.map(d => d.total_assigned);

        new Chart(document.getElementById('specialistChart'), {
            type: 'bar',
            data: {
                labels: specialistNames,
                datasets: [
                    {
                        label: 'Tugas Selesai',
                        data: specialistCompleted,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)', // Blue
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Ditugaskan',
                        data: specialistAssigned,
                        backgroundColor: 'rgba(153, 102, 255, 0.8)', // Purple
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                indexAxis: 'y', // Horizontal Bar Chart
                scales: { x: { beginAtZero: true } },
                plugins: { legend: { display: true, position: 'top' } }
            }
        });
    });
</script>
@endsection