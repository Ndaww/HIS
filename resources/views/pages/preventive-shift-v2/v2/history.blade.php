@extends('layouts.app')
@section('title', 'Histori Tugas PM Saya')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Histori Tugas PM Saya</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Histori</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-white shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Riwayat Tugas Selesai</h5>
                </div>
                <div class="card-body">
                    
                    {{-- Pilihan Filter --}}
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-3">
                            <label for="filter_start_date">Tanggal Awal</label>
                            <input type="date" id="filter_start_date" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_end_date">Tanggal Akhir</label>
                            <input type="date" id="filter_end_date" class="form-control" value="{{ now()->endOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label for="select_shift">Shift</label>
                            <select id="select_shift" class="form-control">
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift }}" {{ $shift == $targetShift ? 'selected' : '' }}>{{ $shift }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button id="apply_filter" class="btn btn-primary w-100"> <i class="ri-filter-2-fill"></i> Filter</button>
                        </div>
                    </div>

                    {{-- Tabel untuk DataTables --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="history-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Waktu Selesai</th>
                                    <th>Shift</th>
                                    <th>Equipment</th>
                                    <th>Nama Tugas</th>
                                    <th>Kategori</th>
                                    <th>Pelaksana</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
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
        // Inisialisasi DataTables
        var historyTable = $('#history-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('pm_shift.history.data') }}',
                data: function (d) {
                    // Mengirimkan nilai filter ke Controller
                    d.shift = $('#select_shift').val();
                    d.start_date = $('#filter_start_date').val(); // NEW
                    d.end_date = $('#filter_end_date').val();     // NEW
                }
            },
            columns: [
                { data: 'completion_date', name: 'pm_shift_tasks.completion_date' },
                { data: 'assigned_shift', name: 'pm_shift_tasks.assigned_shift' },
                { data: 'equipment_type_name', name: 'equipment_types.name' },
                { data: 'task_name', name: 'master_pm_tasks.task_name' },
                { data: 'task_category', name: 'master_pm_tasks.task_category' },
                { data: 'performer_name', name: 'users.name' },
                { 
                    data: 'notes', 
                    name: 'pm_shift_tasks.notes', 
                    render: function(data) {
                        return data || '-';
                    }
                }
            ]
        });

        // Event handler untuk button Terapkan Filter
        $('#apply_filter').on('click', function() {
            historyTable.ajax.reload(); // Reload Datatables
        });
        
        // Catatan: Karena kita menggunakan tombol, kita tidak perlu event change pada select/input
    });
</script>
@endsection