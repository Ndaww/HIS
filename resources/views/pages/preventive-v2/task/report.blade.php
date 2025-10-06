@extends('layouts.app') 

@section('title', 'Laporan Preventive Maintenance')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Laporan Preventive Maintenance</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive V2</span> / Laporan PM</div>
    </div>

    {{-- FILTER FORM --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                {{-- Filter Tanggal --}}
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                </div>
                
                {{-- Filter Teknisi --}}
                <div class="col-md-3">
                    <label for="technician_id" class="form-label">Filter Teknisi</label>
                    <select class="form-select form-control" id="technician_id" name="technician_id">
                        <option value="">-- Semua Teknisi --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Equipment --}}
                <div class="col-md-3">
                    <label for="equipment_id" class="form-label">Filter Equipment</label>
                    <select class="form-select form-control" id="equipment_id" name="equipment_id">
                        <option value="">-- Semua Equipment --</option>
                        @foreach($equipmentList as $eq)
                            <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-12 mt-3">
                    <button type="button" id="filterButton" class="btn btn-success me-2">
                        <i class="ri-filter-3-line"></i> Terapkan Filter
                    </button>
                    <button type="button" id="resetButton" class="btn btn-secondary">
                        <i class="ri-refresh-line"></i> Reset Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DATATABLES REPORT --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">Hasil Laporan Pelaksanaan PM</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="pmReportTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Form</th>
                            <th>Equipment</th>
                            <th>Teknisi</th>
                            <th>Tanggal PM</th>
                            <th>Waktu</th>
                            <th>Durasi</th>
                            <th>Hasil Akhir</th>
                            <th>Catatan Singkat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables akan mengisi ini --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables
        var table = $('#pmReportTable').DataTable({
            processing: true,
            serverSide: true,
            // PASTIKAN Anda telah mendefinisikan rute ini di web.php
            ajax: {
                url: "{{ route('pm.get_report_data') }}",
                data: function(d) {
                    // Mengirim nilai filter ke Controller
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.technician_id = $('#technician_id').val();
                    d.equipment_id = $('#equipment_id').val();
                }
            },
            lengthMenu: [10, 25, 50, 100, -1],
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
                "<'row mb-3'<'col-sm-12'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-success',
                    title: 'Daftar Ticketing'
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: 'Daftar Ticketing'
                }
            ],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'equipment_name', name: 'equipment.name' },
                { data: 'technician_name', name: 'technician.name' },
                { data: 'pm_date', name: 'pm_date' },
                { data: 'time_range', name: 'time_range', orderable: false, searchable: false },
                { data: 'duration', name: 'duration', orderable: false, searchable: false },
                { data: 'overall_result', name: 'overall_result' },
                { data: 'notes_summary', name: 'notes', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });

        // Event listener untuk tombol filter
        $('#filterButton').click(function() {
            table.draw(); // Muat ulang DataTables dengan data filter baru
        });

        // Event listener untuk tombol reset
        $('#resetButton').click(function() {
            $('#filterForm')[0].reset(); // Reset nilai form
            table.draw(); // Muat ulang DataTables tanpa filter
        });
    });
</script>
@endsection
