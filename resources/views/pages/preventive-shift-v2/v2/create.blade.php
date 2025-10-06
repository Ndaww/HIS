@extends('layouts.app')
@section('title', 'Tugas PM Saya')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Jadwal Tugas PM Saya ({{ $targetShift }})</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Tugas Saya</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-white shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Tugas Terjadwal</h5>
                    <small>Menampilkan tugas untuk {{ $targetShift }} pada periode {{ $months[$targetMonth] ?? 'N/A' }} {{ $targetYear }}.</small>
                </div>
                <div class="card-body">
                    
                    {{-- Pilihan Bulan, Tahun, dan Shift Filter --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="select_shift">Pilih Shift</label>
                            <select id="select_shift" class="form-control">
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift }}" {{ $shift == $targetShift ? 'selected' : '' }}>{{ $shift }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="select_month">Pilih Bulan</label>
                            <select id="select_month" class="form-control">
                                @foreach ($months as $num => $name)
                                    <option value="{{ $num }}" {{ $num == $targetMonth ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="select_year">Pilih Tahun</label>
                            <select id="select_year" class="form-control">
                                @foreach ($availableYears as $year) 
                                    <option value="{{ $year }}" {{ $year == $targetYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Equipment Type</th>
                                    <th>Nama Tugas</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Waktu Selesai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tasks as $i => $task)
                                    @php
                                        // Tentukan status berdasarkan hasil JOIN dari transaksi
                                        $status = $task->realization_status ?? 'Pending';
                                        $badgeClass = match ($status) {
                                            'Done' => 'bg-success',
                                            'In Progress' => 'bg-warning',
                                            default => 'bg-secondary', // Pending
                                        };
                                        $actionButtonClass = ($status == 'Done') ? 'btn-secondary disabled' : 'btn-primary';
                                        $actionButtonText = ($status == 'Done') ? 'Selesai' : 'Mulai Tugas';
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $task->equipmentType->name ?? 'N/A' }}</td>
                                        <td>{{ $task->task_name }}</td>
                                        <td>
                                            <span class="badge 
                                                {{ match ($task->task_category) {'I' => 'bg-info', 'L' => 'bg-success', 'C' => 'bg-warning', 'T' => 'bg-danger', default => 'bg-secondary', } }} 
                                                text-white">{{ $task->task_category }}</span>
                                        </td>
                                        <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td> 
                                        <td>{{ $task->completion_date ? \Carbon\Carbon::parse($task->completion_date)->format('d M Y H:i') : '-' }}</td>
                                        <td>
                                            {{-- Mengarahkan ke rute form eksekusi (create) --}}
                                            @if ($status != 'Done' || ($task->completion_date == null || \Carbon\Carbon::parse($task->completion_date)->format('d M Y') < \Carbon\Carbon::now()->format('d M Y') ))
                                                <a href="{{ route('pm_shift.create', [
                                                    'task_id' => $task->id, 
                                                    'month' => $targetMonth, 
                                                    'year' => $targetYear,
                                                    'shift' => $targetShift
                                                ]) }}" class="btn btn-sm btn-primary">
                                                    Mulai Tugas
                                                </a>
                                            @else
                                                {{-- Jika Selesai: Tombol Edit --}}
                                                <a href="{{ route('pm_shift.edit', [
                                                    'task_id' => $task->id, 
                                                    'month' => $targetMonth, 
                                                    'year' => $targetYear,
                                                    'shift' => $targetShift
                                                ]) }}" class="btn btn-sm btn-info">
                                                    Edit Realisasi
                                                </a>
                                                <button class="btn btn-sm btn-secondary disabled">Selesai</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada tugas terjadwal untuk {{ $targetShift }} di periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Logika untuk mengubah filter Bulan, Tahun, dan Shift
        $('#select_month, #select_year, #select_shift').on('change', function() {
            let selectedMonth = $('#select_month').val();
            let selectedYear = $('#select_year').val();
            let selectedShift = $('#select_shift').val();
            
            // Redirect ke halaman yang sama dengan parameter query baru
            window.location.href = '{{ route('pm_shift.index') }}' + 
                                     '?month=' + selectedMonth + 
                                     '&year=' + selectedYear +
                                     '&shift=' + selectedShift;
        });
    });
</script>
@endsection
